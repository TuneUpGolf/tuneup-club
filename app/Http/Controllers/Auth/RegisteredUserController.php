<?php
namespace App\Http\Controllers\Auth;

use App\Actions\SendEmail;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Mail\Admin\WelcomeMailStudent;
use App\Models\Role;
use App\Models\Follower;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    use RegistersUsers;
    protected $redirectTo = RouteServiceProvider::HOME;

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
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => 'required'
        ]);
        $user = Follower::create([
            'name' => $request->name,
            'email' => $request->email,
            'uuid' => Str::uuid(),
            'password' => Hash::make($request->password),
            'tenant_id' => tenant('id'),
            'type' => $request->type,
            'created_by' => 'signup',
            'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
            'country_code' => $request->country_code,
            'dial_code' => $request->dial_code,
            'phone' => str_replace(' ', '', $request->phone),
            'phone_verified_at' => Carbon::now(),
            'lang' => 'en',
            'active_status' => 1
        ]);
        $user->assignRole($request->type);
        SendEmail::dispatch($user->email, new WelcomeMailStudent($user, ''));

        // else {
        //     $user = User::create([
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'uuid' => Str::uuid(),
        //         'password' => Hash::make($request->password),
        //         'tenant_id' => tenant('id'),
        //         'type' => Role::ROLE_INFLUENCER,
        //         'created_by' => 'signup',
        //         'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
        //         'country_code' => $request->country_code,
        //         'dial_code' => $request->dial_code,
        //         'phone' => str_replace(' ', '', $request->phone),
        //         'phone_verified_at' => Carbon::now(),
        //         'lang' => 'en',
        //         'active_status' => 0
        //     ]);
        //     $user->assignRole(Role::ROLE_INFLUENCER);
        //     return redirect(RouteServiceProvider::LOGIN)->with('success', 'Signup successful, please contact admin to activate your account.');
        // }
        return redirect(RouteServiceProvider::LOGIN)->with('success', 'Signup successful, please login with your credentials');
    }
}
