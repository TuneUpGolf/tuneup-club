<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendSMS;
use App\DataTables\Admin\FollowerDataTable;
use App\DataTables\Admin\FollowerPurchaseDataTable;
use App\Facades\Utility;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowerAPIResource;
use App\Imports\FollowersImport;
use App\Mail\Admin\WelcomeMail;
use App\Mail\Admin\WelcomeMailFollower;
use App\Models\Follower;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Services\ChatService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Stancl\Tenancy\Database\Models\Domain;

class FollowerController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(FollowerDataTable $dataTable)
    {
        if (Auth::user()->can('manage-followers')) {
            return $dataTable->render('admin.followers.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-followers')) {
            return view('admin.followers.create');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function show($id, Utility $utility)
    {
        $follower     = Follower::with('plan')->findOrFail($id);
        $dataTable    = new FollowerPurchaseDataTable($id); // Pass follower ID to the datatable
        $token        = $this->chatService->getChatToken($follower->chat_user_id);
        $isSubscribed = $this->isSubscribed($follower);
        $currencySymbol = $utility->getsettings('currency');

        return $dataTable->render('admin.followers.show', compact('follower', 'dataTable', 'token', 'isSubscribed', 'currencySymbol'));
    }
    public function import()
    {

        if (Auth::user()->can('create-followers')) {
            return view('admin.followers.import');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-followers')) {
            request()->validate([
                'name'         => 'required|max:50',
                'email'        => 'required|email|unique:followers,email|unique:users,email',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
            ]);
            $randomPassword                = Str::random(10);
            $userData                      = $request->all();
            $userData['uuid']              = Str::uuid();
            $userData['password']          = Hash::make($randomPassword);
            $userData['type']              = 'Follower';
            $userData['created_by']        = Auth::user()->id;
            $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['country_code']      = $request->country_code;
            $userData['dial_code']         = $request->dial_code;
            $userData['phone']             = str_replace(' ', '', $request->phone);
            $user                          = Follower::create($userData);
            $user->assignRole(Role::ROLE_FOLLOWER);
            if ($request->hasFile('dp')) {
                $user['dp'] = $request->file('dp')->store('dp');
            }
            $user->update();
            SendEmail::dispatch($userData['email'], new WelcomeMailFollower($user, $randomPassword));
            $message = __('Welcome, :name, you have successfully signed up!, Please login with password :password at :link', [
                'name'     => $userData['name'],
                'password' => $randomPassword,
                'link'     => route('login'),
            ]);
            // $userPhone = Str::of($userData['dial_code'])->append($userData['phone'])->value();
            // $userPhone = str_replace(array('(', ')'), '', $userPhone);
            // SendSMS::dispatch("+" . $userPhone, $message);

            return redirect()->route('follower.index')->with('success', __('Follower created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-user')) {
            $user = Follower::find($id);
            if (Auth::user()->type == 'Admin') {
                $roles   = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles   = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            }
            return view('admin.followers.edit', compact('user', 'roles', 'domains'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-user')) {
            request()->validate([
                'name'         => 'required|max:50',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
                'password'     => 'same:password_confirmation',

            ]);
            $input              = $request->all();
            $user               = Follower::find($id);
            $user->country_code = $request->country_code;
            $user->dial_code    = $request->dial_code;
            $user->phone        = str_replace(' ', '', $request->phone);
            $user->update($input);
            if (! empty($request->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            return redirect()->route('follower.index')->with('success', __('User updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-user')) {
            $user = Follower::find($id);
            $user->purchasePost()->delete();
            $user->purchase()->delete();
            $user->follows()->delete();
            $user->post()->delete();
            $user->delete();

            return redirect()->route('follower.index')->with('success', __('User deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function deleteAPI($id)
    {
        try {
            if (Auth::user()->id == $id && Auth::user()->type === Role::ROLE_FOLLOWER) {
                $user = Follower::find($id);
                $user->purchasePost()->delete();
                $user->purchase()->delete();
                $user->follows()->delete();
                $user->post()->delete();
                $user->delete();
                return response()->json(['message' => 'Follower successfully deleted '], 200);
            } else {
                response()->json(['message' => 'unsuccessful'], 419);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function signup(Request $request)
    {

        try {

            request()->validate([
                'name'         => 'required|max:50',
                'email'        => 'required|email|unique:followers,email|unique:users,email',
                'password'     => 'same:confirm-password',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
                'bio'          => 'nullable|max:250',
            ]);

            $userData                      = $request->all();
            $userData['password']          = Hash::make($userData['password']);
            $userData['type']              = Role::ROLE_FOLLOWER;
            $userData['created_by']        = "signup";
            $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['country_code']      = $request?->country_code;
            $userData['dial_code']         = $request?->dial_code;
            $userData['bio']               = $request?->bio;
            $userData['phone']             = str_replace(' ', '', $request->phone);
            $user                          = Follower::create($userData);
            $user->assignRole(Role::ROLE_FOLLOWER);

            if ($request->hasFile('profile_picture')) {
                $user['dp'] = $request->file('profile_picture')->store('dp');
            }

            $user->save();
            $newUserData = ['name' => $request->name, 'unhashedPass' => $request->password];
            //$newUserData
            // $followerPhone = Str::of($userData['country_code'])->append($userData['dial_code'])->append($userData['phone'])->value();

            SendEmail::dispatch($request->email, new WelcomeMail($newUserData));

            $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                'name' => $userData['name'],
                'link' => route('login'),
            ]);
            // SendSMS::dispatch($followerPhone, $message);
            return response(["user" => $user], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateFollowerBio()
    {
        request()->validate([
            'bio' => 'required|max:250',
        ]);
        try {
            if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                $follower = Follower::find(Auth::user()->id);
                if ($follower->active_status == true) {
                    $follower['bio'] = request()?->bio;
                    $follower->update();
                    return response(new FollowerAPIResource($follower));
                } else {
                    return response()->json(['error' => 'Follower is currently disabled, please contact admin.', 419]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function userEmailVerified($id)
    {
        $user = Follower::find($id);
        if ($user->email_verified_at) {
            $user->email_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User email unverified successfully.'));
        } else {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User email verified successfully.'));
        }
    }

    public function userPhoneVerified($id)
    {
        $user = Follower::find($id);
        if ($user->phone_verified_at) {
            $user->phone_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User phone unverified successfully.'));
        } else {
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User phone verified successfully.'));
        }
    }

    public function userStatus(Request $request, $id)
    {
        $user  = Follower::find($id);
        $input = ($request->value == "true") ? 1 : 0;
        if ($user) {
            $user->active_status = $input;
            $user->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('Follower status changed successfully.'),
        ]);
    }
    public function userChatStatus(Request $request, $id)
    {
        $user  = Follower::find($id);
        $input = ($request->value === "true") ? 1 : 0;
        if ($user) {
            $user->chat_status = $input;
            $user->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('Follower chat status changed successfully.'),
        ]);
    }
    public function getAllUsers()
    {
        try {
            if (Auth::user()->active_status == true) {
                return FollowerAPIResource::collection(Follower::where('active_status', true)->orderBy(request()->get('sortKey', 'created_at'), request()->get('sortOrder', 'desc'))->paginate(request()->get('perPage')));
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }
    public function importfun(Request $request)
    {
        if (Auth::user()->can('create-influencers')) {
            if (Auth::user()->type == 'Admin') {
                Excel::import(new FollowersImport(), $request->file('file'));

                $imported = Excel::toArray(new FollowersImport(), $request->file('file'));
                foreach ($imported[0] as $import) {
                    SendEmail::dispatch($import['email'], new WelcomeMail($import));
                    $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                        'name' => $import['name'],
                        'link' => route('login'),
                    ]);
                    $followerPhone = Str::of($import['country_code'])->append($import['dial_code'])->append($import['phone'])->value();
                    SendSMS::dispatch($followerPhone, $message);
                }

                return redirect()->route('follower.index')->with('success', __('Followers imported successfully.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function updateProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'dp' => 'required',
            ]);
            if ($request->hasFile('dp') && Auth::user()->type === Role::ROLE_INFLUENCER) {
                $follower       = Follower::find(Auth::user()?->id);
                $follower['dp'] = $request->file('dp')->store('dp');
                $follower->update();
                return response()->json([
                    'follower' => $follower,
                    'message'  => 'Profile Picture has been successfully updated',
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function followerChat(Utility $utility)
    {
        $user = Auth::user();
        if (!$utility->chatEnabled($user)) {
            return redirect()->route('home')->with('error', 'Chat feature not available!');
        }

        $influencer   = User::where('tenant_id', tenant('id'))->where('id', $user->follows->first()->influencer_id)->first();
        $token        = $this->chatService->getChatToken(Auth::user()->chat_user_id);
        return view('admin.followers.chat', compact('influencer', 'token'));
    }

    public function isSubscribed($user)
    {
        $influencer = User::where('tenant_id', tenant('id'))->where('id', $user->follows?->first()?->influencer_id)->first();
        if ($influencer) {
            $chatEnabledPlanId = Plan::where('influencer_id', $influencer->id)
                ->where('is_chat_enabled', true)->pluck('id')->toArray();
            return in_array($user->plan_id, $chatEnabledPlanId);
        }
        return false;
    }

    public function followerPurchasesData(\App\DataTables\Admin\FollowerPurchasesDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function purchases(\App\DataTables\Admin\FollowerPurchasesDataTable $dataTable)
    {
        return $dataTable->render('admin.followers.purchases');
    }
}
