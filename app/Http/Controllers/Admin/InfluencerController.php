<?php
namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendSMS;
use App\DataTables\Admin\InfluencerDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnnotationVideoApiResource;
use App\Http\Resources\InfluencerAPIResource;
use App\Imports\InfluencersImport;
use App\Mail\Admin\WelcomeMail;
use App\Models\AnnotationVideos;
use App\Models\Follow;
use App\Models\Follower;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Purchase;
use App\Models\ReportUser;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Services\ChatService;
use App\Traits\ConvertVideos;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Stancl\Tenancy\Database\Models\Domain;

class InfluencerController extends Controller
{
    use ConvertVideos;

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $path            = storage_path() . "/json/country.json";
        $this->countries = json_decode(file_get_contents($path), true);
        $this->chatService = $chatService;
    }

    public function index(InfluencerDataTable $dataTable)
    {
        if (Auth::user()->can('manage-influencers')) {
            return $dataTable->render('admin.influencers.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function create()
    {

        if (Auth::user()->can('create-influencers')) {
            return view('admin.influencers.create');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }
    public function import()
    {

        if (Auth::user()->can('create-influencers')) {
            return view('admin.influencers.import');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function influencerProfile()
    {
        $influencers = User::where('type', Role::ROLE_INFLUENCER)->get();
        return view('admin.influencers.profiles', compact('influencers'));
    }

    public function viewProfile(Request $request)
    {
        $influencer   = User::where('type', Role::ROLE_INFLUENCER)->where('id', request()->query('influencer_id'))->first();
        $posts        = Post::where('influencer_id', $influencer->id);
        $totalLessons = Lesson::where('created_by', request()->query('influencer_id'))->count();
        $totalPosts   = $posts->count();
        $posts        = $posts->orderBy('created_at', 'desc')->paginate(6);
        $section      = $request->section;
        $follow       = Follow::where('influencer_id', $influencer->id);
        $followers    = $follow->count();
        $isFollowing  = $follow->where('follower_id', Auth::user()->id)
            ->where('active_status', 1)
            ->exists();
        $subscribers       = Follow::where('influencer_id', $influencer->id)->where('active_status', 1)->where('isPaid', true)->count();
        $plans             = Plan::where('influencer_id', $influencer->id)->get();
        $isInfluencer      = Auth::user()->type === Role::ROLE_INFLUENCER;
        $feedEnabledPlanId = Plan::where('influencer_id', $influencer->id)
            ->where('is_feed_enabled', true)->pluck('id')->toArray();

        $isSubscribed = in_array(Auth::user()->plan_id, $feedEnabledPlanId);
        return view('admin.influencers.profile', compact('influencer', 'totalPosts', 'totalLessons', 'followers', 'subscribers', 'section', 'posts', 'follow', 'plans', 'isInfluencer', 'isSubscribed', 'isFollowing'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-influencers')) {
            if (Auth::user()->type == 'Admin') {
                request()->validate([
                    'name'         => 'required|max:50',
                    'email'        => 'required|email|unique:users,email,',
                    'password'     => 'same:confirm-password',
                    'country_code' => 'required',
                    'dial_code'    => 'required',
                    'phone'        => 'required',
                ]);
                $userData                      = $request->all();
                $userData['uuid']              = Str::uuid();
                $userData['unhashedPass']      = $userData['password'];
                $userData['password']          = Hash::make($userData['password']);
                $userData['type']              = 'Influencer';
                $userData['created_by']        = Auth::user()->id;
                $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['country_code']      = $request->country_code;
                $userData['dial_code']         = $request->dial_code;
                $userData['phone']             = str_replace(' ', '', $request->phone);
                $user                          = User::create($userData);
                $user->assignRole('Influencer');
                if ($request->hasFile('file')) {
                    $user['logo'] = $request->file('file')->store('dp');
                }
                $user->update();
                $chatUserDetails = $this->chatService->getUserProfile($request->email);
                if (! empty($chatUserDetails['data'])) {
                    $existingTenantId = $this->chatService->fetchExistingTenantIds($chatUserDetails['data']);
                    $this->chatService->updateUser($chatUserDetails['data']['_id'], 'tenant_id', $existingTenantId);
                    $user->update([
                        'chat_user_id' => $chatUserDetails['data']['_id'],
                    ]);
                } else {
                    $this->chatService->createUser($user);
                }
                SendEmail::dispatch($userData['email'], new WelcomeMail($userData));
                $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                    'name' => $userData['name'],
                    'link' => route('login'),
                ]);
                $userPhone = Str::of($userData['dial_code'])->append($userData['phone'])->value();
                $userPhone = str_replace(['(', ')'], '', $userPhone);
                SendSMS::dispatch("+" . $userPhone, $message);
            }
            return redirect()->route('influencer.index')->with('success', __('Influencer created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-user')) {
            $user = User::find($id);
            if (Auth::user()->type == 'Admin') {
                $roles   = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles   = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            }
            $countries = $this->countries;
            return view('admin.influencers.edit', compact('user', 'roles', 'domains', 'countries'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function reportInfluencer()
    {
        try {
            request()->validate([
                'influencer_id' => 'required',
                'commnet'       => 'max:255',
            ]);

            $influencer = User::findOrFail(request()->get('influencer_id'));

            if (! ! $influencer) {

                $reportUser                = new ReportUser();
                $reportUser->influencer_id = $influencer->id;

                if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                    $reportUser->follower_id = Auth::user()->id;
                } else {
                    throw new Exception('UnAuthorized', 401);
                }

                if (isset(request()->comment)) {
                    $reportUser->comment = request()->comment;
                }

                $reportUser->save();

                return response('Influencer Successfully Reported', 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function addReview()
    {
        try {
            request()->validate([
                'influencer_id' => 'required',
                'review'        => 'max:255',
                'rating'        => 'required|gte:1|lte:5',
            ]);

            $influencer = User::findOrFail(request()->get('influencer_id'));

            if (! ! $influencer && Auth::user()->type === Role::ROLE_FOLLOWER) {

                $review = Review::firstOrCreate(['follower_id' => Auth::user()->id, 'influencer_id' => $influencer->id]);

                if (isset(request()->review)) {
                    $review->review = request()->review;
                }

                $review->rating = request()->rating;

                $review->save();

                $influencer->avg_rate = DB::table('reviews')
                    ->where('influencer_id', request()->get('influencer_id'))
                    ->groupBy('influencer_id')
                    ->avg('rating');

                $influencer->save();

                return response(['message' => 'Success', 'review' => $review], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getReviews(Request $request)
    {
        try {
            $request->validate([
                'influencer_id' => 'required',
            ]);
            $influencer = User::findOrFail(request()->get('influencer_id'));

            if (! ! $influencer) {
                $reviews = Review::where('influencer_id', $request->get('influencer_id'))->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->paginate(request()->get('perPage'));
                return response()->json([
                    'reviews' => $reviews,
                ]);
            } else {
                throw new Exception('influencer not found', 404);
            }

        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function updateInfluencerBio()
    {
        request()->validate([
            'influencer_id' => 'required',
            'bio'           => 'required|max:250',
        ]);
        try {
            if (Auth::user()->type = Role::ROLE_INFLUENCER) {
                $influencer = User::where('type', Role::ROLE_INFLUENCER)->find(request()->influencer_id);
                if ($influencer->active_status == true) {
                    $influencer['bio'] = request()?->bio;
                    $influencer->update();
                    return response(new InfluencerAPIResource($influencer));
                } else {
                    return response()->json(['error' => 'Influencer is currently disabled, please contact administror', 419]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function annotate(Request $request)
    {
        try {
            $request->validate([
                'video' => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            ]);
            if (Auth::user()->type == Role::ROLE_INFLUENCER) {
                $annotationVideo = AnnotationVideos::create(
                    [
                        'uuid'          => Str::uuid(),
                        'influencer_id' => Auth::user()->id,
                        'video_url'     => $request->hasFile('video') ? $request->file('video')->store('AnnotationVideos') : '/error',
                    ]
                );
                if (Str::endsWith($annotationVideo->video_url, '.mov')) {
                    $path                       = $this->convertSingleVideo($annotationVideo->video_url);
                    $annotationVideo->video_url = $path;
                    $annotationVideo->save();
                }
            } else {
                throw new Exception('UnAuthorized', 401);
            }

            return response()->json(new AnnotationVideoApiResource($annotationVideo));
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function setProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'dp' => 'required',
            ]);
            if ($request->hasFile('dp') && Auth::user()->type === Role::ROLE_INFLUENCER) {
                $influencer         = User::find(Auth::user()?->id);
                $influencer['logo'] = $request->file('dp')->store('dp/');
                $influencer->update();
                return response()->json([
                    'influencer' => $influencer,
                    'message'    => 'Profile Picture has been successfully updated',
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
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
                'country'      => 'required',
            ]);
            $input              = $request->all();
            $user               = User::find($id);
            $user->country_code = $request->country_code;
            $user->dial_code    = $request->dial_code;
            $user->phone        = str_replace(' ', '', $request->phone);
            $currentdate        = Carbon::now();
            $newEndingDate      = date("Y-m-d", strtotime(date("Y-m-d", strtotime($user->created_at)) . " + 1 year"));
            if ($currentdate <= $newEndingDate) {
            }
            $user->update($input);
            if (! empty($request->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            return redirect()->route('influencer.index')->with('success', __('User updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-user')) {
            $user = User::find($id);
            $user->purchase()->delete();
            $user->lessons()->delete();
            $user->post()->delete();
            $user->delete();

            return redirect()->route('influencer.index')->with('success', __('User deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function userEmailVerified($id)
    {
        $user = User::find($id);
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
        $user = User::find($id);
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

    public function getStats(Request $request)
    {
        // Total Followers, Total Lessons Pending, Total Lessons Completed, Lesson Revenue.
        $request->validate([
            'influencer_id' => 'required',
        ]);
        $influencer = User::where('type', Role::ROLE_INFLUENCER)->where('id', $request->influencer_id)->first();
        if ($influencer) {
            try {
                return response()->json([
                    'followers'          => Follower::where('active_status', 1)->where('isGuest', false)->count(),
                    'lessons_pending'    => Purchase::where('influencer_id', $request->influencer_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_ONLINE);
                    })->where('isFeedbackComplete', false)->count(),
                    'lessons_completed'  => Purchase::where('influencer_id', $request->influencer_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_ONLINE);
                    })->where('isFeedbackComplete', true)->count(),
                    'lessons_revenue'    => Purchase::where('influencer_id', $request->influencer_id)->where('status', Purchase::STATUS_COMPLETE)->sum('total_amount'),
                    'inPerson_completed' => Purchase::where('influencer_id', $request->influencer_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_INPERSON);
                    })->count(),
                    'inPerson_pending'   => Purchase::where('influencer_id', $request->influencer_id)->where('status', Purchase::STATUS_INCOMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_INPERSON);
                    })->count(),
                ]);
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
        }
        return response()->json('Influencer not found', 419);
    }

    public function userStatus(Request $request, $id)
    {
        $user  = User::find($id);
        $input = ($request->value == "true") ? 1 : 0;
        if ($user) {
            $user->active_status = $input;
            $user->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('User status changed successfully.'),
        ]);
    }
    public function getAllUsers()
    {
        try {
            if (Auth::user()->active_status == true) {
                $influencers = User::where('type', Role::ROLE_INFLUENCER)->where('active_status', 1)->orderBy(request()->get('sortKey', 'created_at'), request()->get('sortOrder', 'desc'));
                return InfluencerAPIResource::collection($influencers->paginate(request()->get('perPage')));
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function deleteAPI($id)
    {
        try {

            if (Auth::user()->id == $id && Auth::user()->type === Role::ROLE_INFLUENCER) {
                $user = User::find($id);
                if ($user->id != 1) {
                    $user->purchase()->delete();
                    $user->post()->delete();
                    $user->lessons()->delete();
                    $user->delete();
                }
                return response()->json(['message' => 'Influencer successfully deleted '], 200);
            } else {
                response()->json(['message' => 'unsuccessful'], 419);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function importfun(Request $request)
    {
        if (Auth::user()->can('create-influencers')) {
            if (Auth::user()->type == 'Admin') {
                Excel::import(new InfluencersImport, $request->file('file'));

                $imported = Excel::toArray(new InfluencersImport(), $request->file('file'));
                foreach ($imported[0] as $import) {
                    SendEmail::dispatch($import['email'], new WelcomeMail($import));
                }

                return redirect()->route('influencer.index')->with('success', __('Influencers imported successfully.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
