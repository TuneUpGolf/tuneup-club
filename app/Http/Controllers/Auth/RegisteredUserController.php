<?php

namespace App\Http\Controllers\Auth;

use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessSignupEmails;
use App\Models\Follow;
use App\Models\Follower;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\ChatService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    use RegistersUsers;
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function create()
    {
        $lang = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $roles = Role::whereNotIn('name', ['Super Admin', 'Admin'])->pluck('name', 'name')->all();
        return view('auth.register', compact('roles', 'lang'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255|unique:followers',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        DB::beginTransaction();
        try {
            $user = Follower::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'uuid'              => Str::uuid(),
                'password'          => Hash::make($request->password),
                'tenant_id'         => tenant('id'),
                'type'              => Role::ROLE_FOLLOWER,
                'created_by'        => 'signup',
                'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
                'country_code'      => $request->country_code,
                'dial_code'         => $request->dial_code,
                'phone'             => str_replace(' ', '', $request->phone),
                'phone_verified_at' => Carbon::now(),
                'lang'              => 'en',
                'active_status'     => 1,
            ]);

            $user->assignRole(Role::ROLE_FOLLOWER);
            $chatUserDetails = $this->chatService->getUserProfile($request->email);
            if ($chatUserDetails['code'] == 200) {
                $this->chatService->updateUser($chatUserDetails['data']['_id'], 'tenant_id', tenant('id'), $request->eamil);
                $user->update([
                    'chat_user_id' => $chatUserDetails['data']['_id'],
                ]);
            } elseif ($chatUserDetails['code'] == 204) {
                $created = $this->chatService->createUser($user);
                if (! $created) {
                    throw new \Exception('Failed to chat user.');
                }
            } else {
                throw new \Exception('Failed to chat user.');
            }

            $influencer = User::where('type', Role::ROLE_INFLUENCER)->orderBy('id', 'desc')->first();
            if ($influencerId = $influencer->id ?? false) {
                Follow::updateOrCreate(
                    ['follower_id' => $user->id, 'influencer_id' => $influencerId],
                    ['active_status' => true, 'isPaid' => false]
                );
            }

            ProcessSignupEmails::dispatchSync($user, tenant('id'));
            DB::commit();
            return redirect(RouteServiceProvider::LOGIN)->with('success', 'Signup successful, please login with your credentials');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Signup failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'Signup failed. Please try again later.'])->withInput();
        }
    }
}
