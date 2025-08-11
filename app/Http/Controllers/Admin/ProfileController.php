<?php

namespace App\Http\Controllers\Admin;

use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowerAPIResource;
use App\Http\Resources\InfluencerAPIResource;
use App\Models\Follower;
use App\Models\PushToken;
use App\Models\Role;
use App\Models\User;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    protected $country;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
        $path            = storage_path() . "/json/country.json";
        $this->countries = json_decode(file_get_contents($path), true);
    }

    public function index()
    {
        $role      = Auth::user()->roles->first();
        $tenantId  = tenant('id');
        $countries = $this->countries;
        return view('admin.profile.index', [
            'user'      => Auth::user(),
            'role'      => $role,
            'tenant_id' => $tenantId,
            'countries' => $countries,
        ]);
    }

    private function activeTwoFactor()
    {
        $user         = Auth::user();
        $google2faUrl = "";
        $secretKey    = "";

        if ($user->loginSecurity()->exists()) {
            $google2fa    = (new \PragmaRX\Google2FAQRCode\Google2FA());
            $google2faUrl = $google2fa->getQRCodeInline(
                @UtilityFacades::getsettings('app_name'),
                $user->name,
                $user->loginSecurity->google2fa_secret
            );
            $secretKey = $user->loginSecurity->google2fa_secret;
        }
        $user      = auth()->user();
        $role      = $user->roles->first();
        $tenantId  = tenant('id');
        $countries = $this->countries;
        $data      = [
            'user'          => $user,
            'secret'        => $secretKey,
            'google2fa_url' => $google2faUrl,
            'tenant_id'     => $tenantId,
            'countries'     => $countries,
        ];
        return view('admin.profile.index', [
            'user'          => $user,
            'role'          => $role,
            'secret'        => $secretKey,
            'google2fa_url' => $google2faUrl,
            'tenant_id'     => $tenantId,
            'countries'     => $countries,
        ]);
    }

    public function verify()
    {
        return redirect(URL()->previous());
    }

    public function BasicInfoUpdate(Request $request)
    {
        $userDetail = Auth::user();
        if ($userDetail->type !== Role::ROLE_FOLLOWER) {
            $user = User::find(Auth::id());
        } else {
            $user = Follower::find(Auth::id());
        }

        request()->validate([
            'name'         => "required|max:50|regex:/^[A-Za-z0-9_.,()' ]+$/|max:255",
            'address'      => "max:191|regex:/^[A-Za-z0-9_.,()' ]+$/",
            'phone'        => 'required',
            'country_code' => 'required',
            'dial_code'    => 'required',
            'bio'          => 'required',
            'golf_course'  => 'max:100',
            'token'        => 'max:100',

        ]);

        $user->name = $request?->name;

        $user->country_code = $request?->country_code;
        $user->dial_code    = $request?->dial_code;
        $user->phone        = str_replace(' ', '', $request->phone);
        $user->bio          = $request?->bio;

        if ($user->type === Role::ROLE_INFLUENCER) {
            $user->address = $request?->address;
            $user->country     = $request?->country;
            $user->sub_price   = $request?->sub_price;
            $user->golf_course = $request?->golf_course;
        }

        if (Auth::user()->type == Role::ROLE_FOLLOWER) {
            PushToken::updateOrCreate([
                'follower_id' => Auth::user()->id,
            ], ['token' => $request->get('push_token')]);
        }

        if (Auth::user()->type == Role::ROLE_INFLUENCER) {
            PushToken::updateOrCreate([
                'influencer_id' => Auth::user()->id,
            ], ['token' => $request->get('push_token')]);
        }
        if ($request->filled('avatar')) {
            $image     = $request->avatar;
            $image     = str_replace('data:image/png;base64,', '', $image);
            $image     = str_replace('data:image/jpeg;base64,', '', $image);
            $image     = str_replace(' ', '+', $image);
            $imageName = time() . '.png';
            $basePath  = tenant('domains')[0]->domain . '/uploads/avatar/' . strtolower($user->type) . '/';
            $filePath  = $basePath . Auth::user()->id . '/' . $imageName;

            Storage::disk('spaces')->put($filePath, base64_decode($image), 'public');
            $imagePath = Storage::disk('spaces')->url($filePath);

            if ($user->type !== Role::ROLE_FOLLOWER) {
                $user->avatar = $imagePath;
                $user->logo   = $imagePath;
            } else {
                $user->dp = $imagePath;
            }
        }
        $user->save();
        return redirect()->back()->with('success', __('Account details updated successfully.'));
    }

    public function SocialMediaUpdate(Request $request)
    {
        $userDetail = Auth::user();
        if ($userDetail->type === Role::ROLE_INFLUENCER) {
            $user = User::find(Auth::id());
        } else {
            $user = Follower::find(Auth::id());
        }

        if ($user->type === Role::ROLE_INFLUENCER) {
            $socialFields = [
                'linkedin'  => 'social_url_ln',
                'facebook'  => 'social_url_fb',
                'instagram' => 'social_url_ig',
                'twitter'   => 'social_url_x',
                'youtube'   => 'social_url_yt',
            ];

            foreach ($socialFields as $requestKey => $attribute) {
                if ($request->filled($requestKey)) {
                    $user->$attribute = $request->$requestKey;
                }
            }
        }
        $user->save();
        return redirect()->back()->with('success', __('Social Media details updated successfully.'));
    }

    public function BannerDetails(Request $request)
    {
        $request->validate([
            'banner_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userDetail    = Auth::user();
        $user          = User::findOrFail($userDetail['id']);
        $currentDomain = tenant('domains');
        $currentDomain = $currentDomain[0]->domain;

        if ($request->hasFile('banner_image')) {
            $file     = $request->file('banner_image');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $filePath = $currentDomain . '/' . "uploads/banner/" . Auth::user()->id . '/' . $fileName;
            Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
            $relativePath = Storage::disk('spaces')->url($filePath);
            // Save relative path in DB
            $user->update([
                'banner_image' => $relativePath,
            ]);

            return redirect()->back()->with('success', __('Banner image updated successfully.'));
        }

        return redirect()->back()->with('error', __('No image file selected.'));
    }

    public function LoginDetails(Request $request)
    {
        $userDetail = Auth::user();
        if (Auth::user()->type == Role::ROLE_FOLLOWER) {
            $user = Follower::findOrFail($userDetail['id']);
        }
        if (Auth::user()->type !== Role::ROLE_FOLLOWER) {
            $user = User::findOrFail($userDetail['id']);
        }
        request()->validate([
            'email'    => 'email|unique:users,email,' . $userDetail['id'],
            'avatar'   => 'image|mimes:jpeg,png,jpg,svg|max:3072',
            'password' => 'same:password_confirmation',
        ]);
        if ($request->hasFile('avatar')) {
            $filenameWithExt = $request->file('avatar')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('avatar')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir             = storage_path('avatar/');
            $imagePath       = $dir . $userDetail['avatar'];
            if (File::exists($imagePath)) {
                //File::delete($imagePath);
            }
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $path = $request->file('avatar')->storeAs('avatar/', $fileNameToStore);
        }
        if (! empty($request->avatar)) {
            $user['avatar'] = 'avatar/' . $fileNameToStore;
        }
        if (! empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        if (\Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($request, $userDetail) {
                $users = User::where('tenant_id', $userDetail->tenant_id)->first();
                if (! empty($request->password)) {
                    $users->password = bcrypt($request->password);
                }
                $users->save();
            });
        }
        return redirect()->back()->with('success', __('Successfully updated.'));
    }

    public function changePasswordAPI(Request $request)
    {
        try {
            $userDetail = Auth::user();

            request()->validate([
                'password' => 'same:password_confirmation',
            ]);

            if (Auth::user()->type == Role::ROLE_FOLLOWER) {
                $user = Follower::findOrFail($userDetail['id']);
            }

            if (Auth::user()->type !== Role::ROLE_FOLLOWER) {
                $user = User::findOrFail($userDetail['id']);
            }

            if (! empty($request->password)) {
                $user->password = bcrypt($request->password);
            }

            $user->save();
            return response()->json(['message' => 'Password Successfully Changed']);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function updateAvatar(Request $request)
    {
        $currentDomain = tenant('domains');
        $currentDomain = $currentDomain[0]->domain;
        $logo          = false;

        if (auth()->user()->type !== Role::ROLE_FOLLOWER) {
            $user     = User::find(auth()->id());
            $basePath = $currentDomain . '/' . "uploads/avatar/influencer/";
            $column   = 'avatar';
            $logo     = true;
        } else {
            $user     = Follower::find(auth()->id());
            $basePath = $currentDomain . '/' . "uploads/avatar/follower/";
            $column   = 'dp';
        }

        request()->validate([
            'avatar' => 'required',
        ]);
        // dd($request);
        $image     = $request->avatar;
        $image     = str_replace('data:image/png;base64,', '', $image);
        $image     = str_replace(' ', '+', $image);
        $imageName = time() . '.' . 'png';
        $filePath  = $basePath . Auth::user()->id . '/' . $imageName;
        Storage::disk('spaces')->put($filePath, base64_decode($image), 'public');
        $imagePath = Storage::disk('spaces')->url($filePath);

        $user->$column = $imagePath;
        if ($logo) {
            $user->logo = $imagePath;
        }
        if ($user->save()) {
            return __("Avatar updated successfully.");
        }
        return __("Avatar updated failed.");
    }

    public function profileStatus()
    {
        $user = tenancy()->central(function ($tenant) {
            $centralUser                = User::find($tenant->id);
            $centralUser->active_status = 0;
            $centralUser->save();
        });
        $user                = User::find(Auth::user()->id);
        $user->active_status = 0;
        $user->save();
        auth()->logout();
        return redirect()->route('home');
    }

    public function updateProfileAPI(Request $request)
    {
        try {
            $request->validate([
                'name'              => 'max:50',
                'country'           => 'max:56',
                'country_code'      => 'min:2|max:3',
                'dial_code'         => 'min:3|max:3',
                'phone'             => 'min:10|max:10',
                'dp'                => 'image|mimes:jpeg,png,jpg,svg',
                'address'           => 'max:255',
                'bio'               => 'max:255',
                'sub_price'         => 'numeric|between:0,999.99',
                'golf_course'       => 'max:255',
                'experience'        => 'max:3',
                'social_url_ig'     => 'max:255',
                'social_url_fb'     => 'max:255',
                'social_url_x'      => 'max:255',
                'stripe_account_id' => 'max:255',
            ]);

            if (Auth::user()->type === Role::ROLE_INFLUENCER && Auth::user()->active_status == true) {
                $user = User::find(Auth::user()->id);
                if ($request->hasFile('dp')) {
                    $user['logo'] = $request->file('dp')->store('dp');
                }
                $user->update($request->all());
                $user->save();
                return response(new InfluencerAPIResource($user), 200);
            } else if (Auth::user()->type === Role::ROLE_FOLLOWER && Auth::user()->active_status == true) {
                $user = Follower::find(Auth::user()->id);
                if ($request->hasFile('dp')) {
                    $user['dp'] = $request->file('dp')->store('dp');
                }
                $user->update($request->only(['name', 'bio', 'dial_code', 'phone', 'country', 'country_code', 'social_url_ig', 'social_url_fb', 'social_url_x']));
                $user->save();
                return response(new FollowerAPIResource($user), 200);
            }
            return response()->json(['error' => 'Follower is currently disabled, please contact admin.', 419]);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    /**
     * Verify user's Stripe Connect account
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyStripe(Request $request)
    {
        $user = Auth::user();

        request()->validate([
            'stripe_account_id' => 'required|string|max:255',
        ]);

        $stripeAccountId = $request->stripe_account_id;

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
            $account      = $stripeClient->accounts->retrieve($stripeAccountId);

            if ($account && $account->id) {
                $isVerified = false;

                if (isset($account->charges_enabled) && $account->charges_enabled) {
                    $isVerified = true;
                }

                if (isset($account->payouts_enabled) && $account->payouts_enabled) {
                    $isVerified = true;
                }

                // Save the account ID and verification status
                $user->stripe_account_id   = $stripeAccountId;
                $user->is_stripe_connected = $isVerified;
                $user->save();

                if ($isVerified) {
                    return redirect()->back()->with('success', __('Stripe account verified and saved successfully.'));
                } else {
                    return redirect()->back()->with('warning', __('Stripe account found but not fully verified. Please complete the verification process on Stripe.'));
                }
            } else {
                return redirect()->back()->with('errors', __('Invalid Stripe account ID.'));
            }
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return redirect()->back()->with('errors', __('Stripe API authentication failed. Please check your API credentials.'));
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return redirect()->back()->with('errors', __('Invalid Stripe account ID or account not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', __('Failed to verify Stripe account: ') . $e->getMessage());
        }
    }

    //API Start

    public function setPushToken(Request $request)
    {
        try {
            $request->validate([
                'push_token' => 'required',
            ]);

            if (Auth::user()->type == Role::ROLE_FOLLOWER) {
                $pushToken = PushToken::updateOrCreate([
                    'follower_id' => Auth::user()->id,
                ], ['token' => $request->get('push_token')]);
                return response()->json(['message' => 'Success', 'push_token' => $pushToken], 200);
            }

            if (Auth::user()->type == Role::ROLE_INFLUENCER) {
                $pushToken = PushToken::updateOrCreate([
                    'influencer_id' => Auth::user()->id,
                ], ['token' => $request->get('push_token')]);
                return response()->json(['message' => 'Success', 'push_token' => $pushToken], 200);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function destroy()
    {
        if (Auth::user()->can('delete-user')) {
            $user = User::find(auth()->id());
            tenancy()->central(function ($tenant) {
                $centralUser                = User::find($tenant->id);
                $centralUser->active_status = 0;
                $centralUser->save();
            });
            if ($user->type == 'Admin') {
                $subUsers = User::where('type', '!=', 'Admin')->get();
            } else {
                $subUsers = User::where('created_by', $user->id)->get();
            }
            foreach ($subUsers as $subUser) {
                if ($subUser) {
                    $subUser->active_status = 0;
                    $subUser->save();
                }
            }
            $user->delete();
            auth()->logout();
            return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
