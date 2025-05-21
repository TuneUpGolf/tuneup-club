<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Facades\UtilityFacades;
use App\Mail\Admin\TestMail;
use App\Models\ChangeDomainRequest;
use App\Models\NotificationsSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\MailTemplates\Models\MailTemplate;
use function PHPUnit\Framework\fileExists;

class SettingsController extends Controller
{
    public function index()
    {
        $user   = Auth::user()->tenant_id;
        $order  = tenancy()->central(function ($tenant) use ($user) {
            $changeDomainRequest = ChangeDomainRequest::where('tenant_id', $user)->latest()->first();
            return $changeDomainRequest;
        });
        $notificationsSettings = NotificationsSetting::all();
        return view('admin.settings.index', compact('order', 'notificationsSettings'));
    }

    public function appNameUpdate(Request $request)
    {
        request()->validate([
            'app_logo'          => 'image|max:2048|mimes:png',
            'app_dark_logo'     => 'image|max:2048|mimes:png',
            'favicon_logo'      => 'image|max:2048|mimes:png',
            'app_name'          => 'required|max:50'
        ]);
        $data = [
            'app_name' => $request->app_name,
        ];
        if ($request->app_logo) {
            Storage::delete(UtilityFacades::getsettings('app_logo'));
            $appLogoName        = 'app-logo.' . $request->app_logo->extension();
            $request->app_logo->storeAs('logo', $appLogoName);
            $data['app_logo']   = 'logo/' . $appLogoName;
        }
        if ($request->app_dark_logo) {
            Storage::delete(UtilityFacades::getsettings('app_dark_logo'));
            $appDarkLogoName        = 'app-dark-logo.' . $request->app_dark_logo->extension();
            $request->app_dark_logo->storeAs('logo', $appDarkLogoName);
            $data['app_dark_logo']  = 'logo/' . $appDarkLogoName;
        }
        if ($request->favicon_logo) {
            Storage::delete(UtilityFacades::getsettings('favicon_logo'));
            $faviconLogoName        = 'app-favicon-logo.' . $request->favicon_logo->extension();
            $request->favicon_logo->storeAs('logo', $faviconLogoName);
            $data['favicon_logo']   = 'logo/' . $faviconLogoName;
        }
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('App Setting updated successfully.'));
    }

    public function authSettingsUpdate(Request $request)
    {
        $user = \Auth::user();
        if ($request->database_permission == 'on') {
            try {
                DB::statement('create database test_db');
            } catch (\Exception $e) {
                return redirect()->back()->with('errors', __('Please give permission to create database to user.'));
            }
            DB::statement('drop database test_db');
        }

        $data = [
            '2fa'                   => ($request->two_factor_auth == 'on') ? '1' : '0',
            'rtl'                   => ($request->rtl_setting == 'on') ? '1' : '0',
            'register_setting'      => ($request->register_setting == 'on') ? '1' : '0',
            'date_format'           => $request->date_format,
            'time_format'           => $request->time_format,
            'gtag'                  => $request->gtag,
            'default_language'      => $request->default_language,
            'dark_mode'             => ($request->dark_mode == 'on') ? 'on' : 'off',
            'transparent_layout'    => ($request->transparent_layout == 'on') ? '1' : '0',
            'color'                 => ($request->color) ? $request->color : UtilityFacades::getsettings('color'),
            'database_permission'   => ($request->database_permission == 'on') ? '1' : '0',
            'email_verification'    => ($request->email_verification == 'on') ? '1' : '0',
            'sms_verification'      => ($request->sms_verification == 'on') ? 1 : 0,
            'roles'                 => $request->roles,
            'landing_page_status'   => ($request->landing_page_status == 'on') ? '1' : '0',
            'enable_email_notification' => ($request->enable_email_notifiaction == 'on') ? 'on' : 'off',
            'enable_sms_notification' => ($request->enable_email_notifiaction == 'on') ? 'on' : 'off',

        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        $user->dark_layout          = ($request->dark_mode && $request->dark_mode == 'on') ? 1 : 0;
        $user->rtl_layout           = ($request->rtl_setting && $request->rtl_setting == 'on') ? 1 : 0;
        $user->transprent_layout    = ($request->transparent_layout && $request->transparent_layout == 'on') ? 1 : 0;
        $user->theme_color          = ($request->color) ? $request->color : UtilityFacades::getsettings('color');
        $user->save();
        return redirect()->back()->with('success', __('General settings updated successfully.'));
    }

    public function changeDomainRequest(Request $request)
    {
        $order = tenancy()->central(function ($tenant) use ($request) {
            request()->validate([
                'domain_name' => 'required|max:50',
            ]);
            $data['name']               = Auth::user()->name;
            $data['email']              = Auth::user()->email;
            $data['domain_name']        = $request->domain_name;
            $data['actual_domain_name'] = $request->domain_name;
            $data['tenant_id']          = Auth::user()->tenant_id;
            $data['status']             = 0;
            $datas  = ChangeDomainRequest::create($data);
        });
        return redirect()->back()->with('success', __('Change domain request send successfully.'));
    }

    public function s3SettingUpdate(Request $request)
    {
        if ($request->storage_type == 's3') {
            request()->validate([
                's3_key'    => 'required',
                's3_secret' => 'required',
                's3_region' => 'required',
                's3_bucket' => 'required',
            ]);
            $data = [
                's3_key'        => $request->s3_key,
                's3_secret'     => $request->s3_secret,
                's3_region'     => $request->s3_region,
                's3_bucket'     => $request->s3_bucket,
                'storage_type'  => $request->storage_type,
            ];
            foreach ($data as $key => $value) {
                UtilityFacades::storesettings([
                    'key'   => $key,
                    'value' => $value
                ]);
            }
        } else if ($request->storage_type == 'wasabi') {
            request()->validate([
                'wasabi_key'    => 'required',
                'wasabi_secret' => 'required',
                'wasabi_region' => 'required',
                'wasabi_bucket' => 'required',
                'wasabi_url'    => 'required',
                'wasabi_root'   => 'required',
            ]);
            $data = [
                'wasabi_key'    => $request->wasabi_key,
                'wasabi_secret' => $request->wasabi_secret,
                'wasabi_region' => $request->wasabi_region,
                'wasabi_bucket' => $request->wasabi_bucket,
                'wasabi_url'    => $request->wasabi_url,
                'wasabi_root'   => $request->wasabi_root,
                'storage_type'  => $request->storage_type,
            ];
            foreach ($data as $key => $value) {
                UtilityFacades::storesettings([
                    'key'   => $key,
                    'value' => $value
                ]);
            }
        } else {
            UtilityFacades::storesettings([
                'key'   => 'storage_type',
                'value' => $request->storage_type
            ]);
        }
        return redirect()->back()->with('success', __('Storage setting updated successfully.'));
    }

    public function emailSettingUpdate(Request $request)
    {
        request()->validate([
            'mail_mailer'       => 'required',
            'mail_host'         => 'required',
            'mail_port'         => 'required',
            'mail_username'     => 'required|email',
            'mail_password'     => 'required',
            'mail_encryption'   => 'required',
            'mail_from_address' => 'required',
            'mail_from_name'    => 'required',
        ]);
        $data = [
            'email_setting_enable'  => $request->email_setting_enable == 'on' ? 'on' : 'off',
            'mail_mailer'           => $request->mail_mailer,
            'mail_host'             => $request->mail_host,
            'mail_port'             => $request->mail_port,
            'mail_username'         => $request->mail_username,
            'mail_password'         => $request->mail_password,
            'mail_encryption'       => $request->mail_encryption,
            'mail_from_address'     => $request->mail_from_address,
            'mail_from_name'        => $request->mail_from_name,
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Email Setting updated successfully.'));
    }

    public function pusherSettingUpdate(Request $request)
    {
        request()->validate([
            'pusher_id'         => 'required|regex:/^[0-9]+$/',
            'pusher_key'        => 'required|regex:/^[A-Za-z0-9_.,()]+$/',
            'pusher_secret'     => 'required|regex:/^[A-Za-z0-9_.,()]+$/',
            'pusher_cluster'    => 'required|regex:/^[A-Za-z0-9_.,()]+$/',
        ]);
        $data = [
            'pusher_id'         => $request->pusher_id,
            'pusher_key'        => $request->pusher_key,
            'pusher_secret'     => $request->pusher_secret,
            'pusher_cluster'    => $request->pusher_cluster,
            'pusher_status'     => ($request->pusher_status == 'on') ? 1 : 0,
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Pusher API Keys updated successfully.'));
    }

    public function cookieSettingUpdate(Request $request)
    {
        request()->validate([
            'cookie_title'                  => 'required|max:50',
            'strictly_cookie_title'         => 'required|max:50',
            'cookie_description'            => 'required',
            'strictly_cookie_description'   => 'required',
            'contact_us_description'        => 'required',
            'contact_us_url'                => 'required',
        ]);
        $data = [
            'cookie_setting_enable'         => $request->cookie_setting_enable == 'on' ? 'on' : 'off',
            'cookie_logging'                => $request->cookie_logging == 'on' ? 'on' : 'off',
            'necessary_cookies'             => $request->necessary_cookies == 'on' ? 'on' : 'off',
            'cookie_title'                  => $request->cookie_title,
            'strictly_cookie_title'         => $request->strictly_cookie_title,
            'cookie_description'            => $request->cookie_description,
            'strictly_cookie_description'   => $request->strictly_cookie_description,
            'contact_us_description'        => $request->contact_us_description,
            'contact_us_url'                => $request->contact_us_url,
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Cookie updated successfully'));
    }

    public function CookieConsent(Request $request)
    {
        if (UtilityFacades::getsettings('cookie_setting_enable') == "on" &&  UtilityFacades::getsettings('cookie_logging') == "on") {
            try {
                $whichBrowser       = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                // Generate new CSV line
                $browserName        = $whichBrowser->browser->name ?? null;
                $osName             = $whichBrowser->os->name ?? null;
                $browserLanguage    = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                $deviceType         = UtilityFacades::GetDeviceType($_SERVER['HTTP_USER_AGENT']);
                $ip                 = $_SERVER['REMOTE_ADDR'];
                $query              = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));
                if ($query['status'] == 'success') {
                    $date       = (new \DateTime())->format('Y-m-d');
                    $time       = (new \DateTime())->format('H:i:s') . ' UTC';
                    $newLine    = implode(',', [$ip, $date, $time, implode('-', $request['cookie']), $deviceType, $browserLanguage, $browserName, $osName, isset($query) ? $query['country'] : '', isset($query) ? $query['region'] : '', isset($query) ? $query['regionName'] : '', isset($query) ? $query['city'] : '', isset($query) ? $query['zip'] : '', isset($query) ? $query['lat'] : '', isset($query) ? $query['lon'] : '']);
                    if (!fileExists(Storage::url('cookie-csv/cookie_data.csv'))) {
                        $firstLine = 'IP,Date,Time,Accepted-cookies,Device type,Browser anguage,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                        file_put_contents(base_path() . Storage::url('cookie-csv/cookie_data.csv'), $firstLine . PHP_EOL, FILE_APPEND | LOCK_EX);
                    }
                    file_put_contents(base_path() . Storage::url('cookie-csv/cookie_data.csv'), $newLine . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            } catch (\Throwable $th) {
                return response()->json('errors');
            }
            return response()->json('success');
        }
        return response()->json('errors');
    }

    public function SeoSetting(Request $request)
    {
        request()->validate([
            'meta_title'        => 'required',
            'meta_keywords'     => 'required',
            'meta_description'  => 'required',
            'meta_image_logo'   => 'image|max:2048|mimes:jpeg,jpg,png,gif',
        ]);
        $data = [
            'meta_title'        => $request->meta_title,
            'meta_keywords'     => $request->meta_keywords,
            'meta_description'  => $request->meta_description,
        ];
        if ($request->meta_image_logo) {
            Storage::delete(UtilityFacades::getsettings('meta_image_logo'));
            $meta_image_logo            = 'meta-image-logo.' . $request->meta_image_logo->extension();
            $request->meta_image_logo->storeAs('seo', $meta_image_logo);
            $data['meta_image_logo']    = 'seo/' . $meta_image_logo;
        }
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('SEO Setting updated successfully'));
    }

    public function GoogleCalenderUpdate(Request $request)
    {
        request()->validate([
            'google_calender_id'    => 'required',
        ]);
        $data = [
            'google_setting_enable' => $request->google_setting_enable == 'on' ? 'on' : 'off',
            'google_calender_id'    => $request->google_calender_id,
        ];
        if ($request->hasFile('google_calender_json_file')) {
            Storage::delete(UtilityFacades::getsettings('google_calender_json_file'));
            $google_calender_json_file          =  md5(time()) . "." . $request->google_calender_json_file->extension();
            $request->file('google_calender_json_file')->storeAs('google_calender_json_file', $google_calender_json_file);
            $data['google_calender_json_file']  = 'google_calender_json_file/' . $google_calender_json_file;
        }
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success',  __('Google calender API key updated successfully.'));
    }

    public function smsSettingUpdate(Request $request)
    {
        if ($request->smssetting == 'twilio') {
            request()->validate([
                'twilio_sid'        => 'required',
                'twilio_auth_token' => 'required',
                'twilio_verify_sid' => 'required',
                'twilio_number'     => 'required',
            ]);
        } else if ($request->smssetting == 'nexmo') {
            request()->validate([
                'nexmo_key'     => 'required',
                'nexmo_secret'  => 'required',
                'nexmo_url'     => 'required',
            ]);
        } else if ($request->smssetting == 'fast2sms') {
            request()->validate([
                'fast2sms_api_key' => 'required',
            ]);
        }
        $data = [
            'sms_setting_enable'    => $request->sms_setting_enable == 'on' ? 'on' : 'off',
            'smssetting'            => ($request->smssetting),
            'nexmo_key'             => $request->nexmo_key,
            'nexmo_secret'          => $request->nexmo_secret,
            'nexmo_url'             => $request->nexmo_url,
            'twilio_sid'            => $request->twilio_sid,
            'twilio_auth_token'     => $request->twilio_auth_token,
            'twilio_verify_sid'     => $request->twilio_verify_sid,
            'twilio_number'         => $request->twilio_number,
            'fast2sms_api_key'      => $request->fast2sms_api_key,
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success',  __('Sms setting updated successfully.'));
    }

    public function socialSettingUpdate(Request $request)
    {
        if ($request->socialsetting) {
            if (in_array('google', $request->get('socialsetting'))) {
                request()->validate([
                    'google_client_id'      => 'required',
                    'google_client_secret'  => 'required',
                    'google_redirect'       => 'required',
                ]);
                $data = [
                    'google_client_id'      => $request->google_client_id,
                    'google_client_secret'  => $request->google_client_secret,
                    'google_redirect'       => $request->google_redirect,
                    'googlesetting'         => (!empty($request->googlesetting)) ? 'on' : 'off',
                ];
            }
            if (in_array('facebook', $request->get('socialsetting'))) {
                request()->validate([
                    'facebook_client_id'        => 'required',
                    'facebook_client_secret'    => 'required',
                    'facebook_redirect'         => 'required',
                ]);
                $data = [
                    'facebook_client_id'        => $request->facebook_client_id,
                    'facebook_client_secret'    => $request->facebook_client_secret,
                    'facebook_redirect'         => $request->facebook_redirect,
                    'facebooksetting'           => (!empty($request->facebooksetting)) ? 'on' : 'off',
                ];
            }
            if (in_array('github', $request->get('socialsetting'))) {
                request()->validate([
                    'github_client_id'      => 'required',
                    'github_client_secret'  => 'required',
                    'github_redirect'       => 'required',
                ]);
                $data = [
                    'github_client_id'      => $request->github_client_id,
                    'github_client_secret'  => $request->github_client_secret,
                    'github_redirect'       => $request->github_redirect,
                    'githubsetting'         => (!empty($request->githubsetting)) ? 'on' : 'off',
                ];
            }
            if (in_array('linkedin', $request->get('socialsetting'))) {
                request()->validate([
                    'linkedin_client_id'        => 'required',
                    'linkedin_client_secret'    => 'required',
                    'linkedin_redirect'         => 'required',
                ]);
                $data = [
                    'linkedin_client_id'        => $request->linkedin_client_id,
                    'linkedin_client_secret'    => $request->linkedin_client_secret,
                    'linkedin_redirect'         => $request->linkedin_redirect,
                    'linkedinsetting'           => (!empty($request->linkedinsetting)) ? 'on' : 'off',
                ];
            }
            $data = [
                'google_client_id'          => $request->google_client_id,
                'google_client_secret'      => $request->google_client_secret,
                'google_redirect'           => $request->google_redirect,
                'googlesetting'             => (in_array('google', $request->get('socialsetting'))) ? 'on' : 'off',

                'facebook_client_id'        => $request->facebook_client_id,
                'facebook_client_secret'    => $request->facebook_client_secret,
                'facebook_redirect'         => $request->facebook_redirect,
                'facebooksetting'           => (in_array('facebook', $request->get('socialsetting'))) ? 'on' : 'off',

                'github_client_id'          => $request->github_client_id,
                'github_client_secret'      => $request->github_client_secret,
                'github_redirect'           => $request->github_redirect,
                'githubsetting'             => (in_array('github', $request->get('socialsetting'))) ? 'on' : 'off',

                'linkedin_client_id'        => $request->linkedin_client_id,
                'linkedin_client_secret'    => $request->linkedin_client_secret,
                'linkedin_redirect'         => $request->linkedin_redirect,
                'linkedinsetting'           => (in_array('linkedin', $request->get('socialsetting'))) ? 'on' : 'off',
            ];
        } else {
            $data = [
                'googlesetting'     => 'off',
                'facebooksetting'   => 'off',
                'githubsetting'     => 'off',
                'linkedinsetting'   => 'off',
            ];
        }
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Social setting updated successfully.'));
    }

    public function paymentSettingUpdate(Request $request)
    {
        $this->validate($request, [
            'paymentsetting'    => 'required|min:1'
        ]);
        if (in_array('stripe', $request->get('paymentsetting'))) {
            request()->validate([
                'stripe_key'            => 'required',
                'stripe_secret'         => 'required',
                'stripe_description'    => 'required',
            ]);
            $data = [
                'stripe_key'            => $request->stripe_key,
                'stripe_secret'         => $request->stripe_secret,
                'stripe_description'    => $request->stripe_description,
                'stripesetting'         => (in_array('stripe', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('razorpay', $request->paymentsetting)) {
            request()->validate([
                'razorpay_key'          => 'required',
                'razorpay_secret'       => 'required',
                'razorpay_description'  => 'required',
            ]);
            $data = [
                'razorpay_key'          => $request->razorpay_key,
                'razorpay_secret'       =>  $request->razorpay_secret,
                'razorpay_description'  => $request->razorpay_description,
                'razorpaysetting'       => (in_array('razorpay', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('paypal', $request->paymentsetting)) {
            if ($request->paypal_mode == 'sandbox') {
                request()->validate([
                    'client_id'     => 'required',
                    'client_secret' => 'required',
                ]);
                $data = [
                    'paypal_sandbox_client_id'      => $request->client_id,
                    'paypal_sandbox_client_secret'  => $request->client_secret,
                    'paypalsetting'                 => (in_array('paypal', $request->get('paymentsetting'))) ? 'on' : 'off',
                ];
            } else {
                request()->validate([
                    'client_id'     => 'required',
                    'client_secret' => 'required',
                ]);
                $data = [
                    'paypal_live_client_id'     => $request->client_id,
                    'paypal_live_client_secret' => $request->client_secret,
                    'paypalsetting'             => (in_array('paypal', $request->get('paymentsetting'))) ? 'on' : 'off',
                ];
            }
        }
        if (in_array('flutterwave', $request->get('paymentsetting'))) {
            request()->validate([
                'flutterwave_key'           => 'required',
                'flutterwave_secret'        => 'required',
                'flutterwave_description'   => 'required',
            ]);
            $data = [
                'flutterwave_key'           => $request->flutterwave_key,
                'flutterwave_secret'        => $request->flutterwave_secret,
                'flutterwave_description'   => $request->flutterwave_description,
                'flutterwavesetting'        => (in_array('flutterwave', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('paystack', $request->get('paymentsetting'))) {
            request()->validate([
                'public_key'            => 'required',
                'secret_key'            => 'required',
                'paystack_description'  => 'required',
            ]);
            $data = [
                'paystack_public_key'   => $request->public_key,
                'paystack_secret_key'   => $request->secret_key,
                'paystack_description'  => $request->paystack_description,
                'paystacksetting'       => (in_array('paystack', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('paytm', $request->get('paymentsetting'))) {
            request()->validate([
                'merchant_id'       => 'required',
                'merchant_key'      => 'required',
                'paytm_environment' => 'required',
            ]);
            $data = [
                'PAYTM_MERCHANT_ID'     => $request->merchant_id,
                'PAYTM_MERCHANT_KEY'    => $request->merchant_key,
                'PAYTM_ENVIRONMENT'     =>  $request->paytm_environment,
                'paytmsetting'          => (in_array('merchant', $request->get('paymentsetting'))) ? 'on' : 'off',
                'PAYTM_DESCRIPTION'     => $request->merchant_description,
            ];
        }
        if (in_array('coingate', $request->get('paymentsetting'))) {
            request()->validate([
                'coingate_mode'         => 'required',
                'coingate_auth_token'   => 'required',
                'coingate_description'  => 'required',
            ]);
            $data = [
                'coingate_environment'  => $request->coingate_mode,
                'coingate_auth_token'   => $request->coingate_auth_token,
                'coingate_description'  => $request->coingate_description,
                'coingatesetting'       => (in_array('coingate', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('mercado', $request->get('paymentsetting'))) {
            request()->validate([
                'mercado_mode'          => 'required',
                'mercado_access_token'  => 'required',
                'mercado_description'   => 'required',
            ]);
            $data = [
                'mercado_mode'          => $request->mercado_mode,
                'mercado_access_token'  => $request->mercado_access_token,
                'mercado_description'   => $request->mercado_description,
                'mercadosetting'        => (in_array('mercado', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('payfast', $request->get('paymentsetting'))) {
            request()->validate([
                'payfast_merchant_id'   => 'required',
                'payfast_merchant_key'  => 'required',
                'payfast_description'   => 'required',
            ]);
            $data = [
                'payfast_merchant_id'   => $request->payfast_merchant_id,
                'payfast_merchant_key'  => $request->payfast_merchant_key,
                'payfast_description'   => $request->payfast_description,
                'payfastsetting'        => (in_array('payfast', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('toyyibpay', $request->get('paymentsetting'))) {
            request()->validate([
                'toyyibpay_secret_key'      => 'required',
                'toyyibpay_category_code'   => 'required',
                'toyyibpay_description'     => 'required',
            ]);
            $data = [
                'toyyibpay_secret_key'      => $request->toyyibpay_secret_key,
                'toyyibpay_category_code'   => $request->toyyibpay_category_code,
                'toyyibpay_description'     => $request->toyyibpay_description,
                'toyyibpaysetting'          => (in_array('toyyibpay', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('iyzipay', $request->get('paymentsetting'))) {
            request()->validate([
                'iyzipay_mode'          => 'required',
                'iyzipay_key'           => 'required',
                'iyzipay_secret'        => 'required',
                'iyzipay_description'   => 'required',
            ]);
            $data = [
                'iyzipay_key'           => $request->iyzipay_key,
                'iyzipay_secret'        => $request->iyzipay_secret,
                'iyzipay_mode'          => $request->iyzipay_mode,
                'iyzipay_description'   => $request->iyzipay_description,
                'iyzipaysetting'        => (in_array('iyzipay', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('sspay', $request->paymentsetting)) {
            request()->validate([
                'sspay_category_code'   => 'required',
                'sspay_secret_key'      => 'required',
                'sspay_description'     => 'required',
            ]);
            $data = [
                'sspay_category_code'   => $request->sspay_category_code,
                'sspay_secret_key'      => $request->sspay_secret_key,
                'sspay_description'     => $request->sspay_description,
                'sspaysetting'          => (in_array('sspay', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('cashfree', $request->paymentsetting)) {
            request()->validate([
                'cashfree_mode'         => 'required',
                'cashfree_app_id'       => 'required',
                'cashfree_secret_key'   => 'required',
                'cashfree_description'  => 'required',
            ]);
            $data = [
                'cashfree_mode'         => $request->cashfree_mode,
                'cashfree_app_id'       => $request->cashfree_app_id,
                'cashfree_secret_key'   => $request->cashfree_secret_key,
                'cashfree_description'  => $request->cashfree_description,
                'cashfreesetting'       => (in_array('cashfree', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('aamarpay', $request->paymentsetting)) {
            request()->validate([
                'aamarpay_store_id'         => 'required',
                'aamarpay_signature_key'    => 'required',
                'aamarpay_description'      => 'required',
            ]);
            $data = [
                'aamarpay_store_id'         => $request->aamarpay_store_id,
                'aamarpay_signature_key'    => $request->aamarpay_signature_key,
                'aamarpay_description'      => $request->aamarpay_description,
                'aamarpaysetting'           => (in_array('aamarpay', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('payumoney', $request->get('paymentsetting'))) {
            request()->validate([
                'payumoney_mode'            => 'required',
                'payumoney_merchant_key'    => 'required',
                'payumoney_salt_key'        => 'required',
                'payumoney_description'     => 'required',
            ]);
            $data = [
                'payumoney_mode'            => $request->payumoney_mode,
                'payumoney_merchant_key'    => $request->payumoney_merchant_key,
                'payumoney_salt_key'        => $request->payumoney_salt_key,
                'payumoney_description'     => $request->payumoney_description,
                'payumoneysetting'          => (in_array('payumoney', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('paytab', $request->get('paymentsetting'))) {
            request()->validate([
                'paytab_profile_id'     => 'required',
                'paytab_server_key'     => 'required',
                'paytab_region'         => 'required',
                'paytab_description'    => 'required',
            ]);
            $data = [
                'paytab_profile_id'     => $request->paytab_profile_id,
                'paytab_server_key'     => $request->paytab_server_key,
                'paytab_region'         => $request->paytab_region,
                'paytab_description'    => $request->paytab_description,
                'paytabsetting'         => (in_array('paytab', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('benefit', $request->get('paymentsetting'))) {
            request()->validate([
                'benefit_key'           => 'required',
                'benefit_secret_key'    => 'required',
                'benefit_description'   => 'required',
            ]);
            $data = [
                'benefit_key'           => $request->benefit_key,
                'benefit_secret_key'    => $request->benefit_secret_key,
                'benefit_description'   => $request->benefit_description,
                'benefitsetting'        => (in_array('benefit', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('mollie', $request->get('paymentsetting'))) {
            request()->validate([
                'mollie_api_key'        => 'required',
                'mollie_profile_id'     => 'required',
                'mollie_partner_id'     => 'required',
                'mollie_description'    => 'required',
            ]);
            $data = [
                'mollie_api_key'        => $request->mollie_api_key,
                'mollie_profile_id'     => $request->mollie_profile_id,
                'mollie_partner_id'     => $request->mollie_partner_id,
                'mollie_description'    => $request->mollie_description,
                'molliesetting'         => (in_array('mollie', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('skrill', $request->get('paymentsetting'))) {
            request()->validate([
                'skrill_email'      => 'required',
            ]);
            $data = [
                'skrill_email'          => $request->skrill_email,
                'skrill_description'    => $request->skrill_description,
                'skrillsetting'         => (in_array('skrill', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('easebuzz', $request->get('paymentsetting'))) {
            request()->validate([
                'easebuzz_environment'  => 'required',
                'easebuzz_merchant_key' => 'required',
                'easebuzz_salt'         => 'required',
                'easebuzz_description'  => 'required',
            ]);
            $data = [
                'easebuzz_environment'  => $request->easebuzz_environment,
                'easebuzz_merchant_key' => $request->easebuzz_merchant_key,
                'easebuzz_salt'         => $request->easebuzz_salt,
                'easebuzz_description'  => $request->easebuzz_description,
                'easebuzzsetting' => (in_array('easebuzz', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        if (in_array('offline', $request->paymentsetting)) {
            request()->validate([
                'payment_details'   => 'required',
            ]);
            $data = [
                'payment_details'   =>  $request->payment_details,
                'offlinesetting'    => (in_array('offline', $request->get('paymentsetting'))) ? 'on' : 'off',
            ];
        }
        $data = [
            'currency'                      => $request->currency,
            'currency_symbol'               => $request->currency_symbol,

            'stripe_key'                    => $request->stripe_key,
            'stripe_secret'                 => $request->stripe_secret,
            'stripe_description'            => $request->stripe_description,
            'stripesetting'                 => (in_array('stripe', $request->get('paymentsetting'))) ? 'on' : 'off',

            'razorpay_key'                  => $request->razorpay_key,
            'razorpay_secret'               => $request->razorpay_secret,
            'razorpay_description'          => $request->razorpay_description,
            'razorpaysetting'               => (in_array('razorpay', $request->get('paymentsetting'))) ? 'on' : 'off',

            'paypal_mode'                   => $request->paypal_mode,
            'paypal_sandbox_client_id'      => $request->client_id,
            'paypal_sandbox_client_secret'  => $request->client_secret,
            'paypal_description'            => $request->paypal_description,
            'paypalsetting'                 => (in_array('paypal', $request->get('paymentsetting'))) ? 'on' : 'off',

            'flutterwave_key'               => $request->flutterwave_key,
            'flutterwave_secret'            => $request->flutterwave_secret,
            'flutterwave_description'       => $request->flutterwave_description,
            'flutterwavesetting'            => (in_array('flutterwave', $request->get('paymentsetting'))) ? 'on' : 'off',

            'paystack_public_key'           => $request->public_key,
            'paystack_secret_key'           => $request->secret_key,
            'paystack_description'          => $request->paystack_description,
            'paystack_currency'             => $request->paystack_currency,
            'paystacksetting'               => (in_array('paystack', $request->get('paymentsetting'))) ? 'on' : 'off',

            'paytm_merchant_id'             => $request->merchant_id,
            'paytm_merchant_key'            => $request->merchant_key,
            'paytm_environment'             =>  $request->paytm_environment,
            'paytm_merchant_website'        => 'local',
            'paytm_channel'                 => 'WEB',
            'paytm_description'             => $request->paytm_description,
            'paytmsetting'                  => (in_array('paytm', $request->get('paymentsetting'))) ? 'on' : 'off',

            'coingate_environment'          => $request->coingate_mode,
            'coingate_auth_token'           => $request->coingate_auth_token,
            'coingate_description'          => $request->coingate_description,
            'coingatesetting'               => (in_array('coingate', $request->get('paymentsetting'))) ? 'on' : 'off',

            'mercado_mode'                  => $request->mercado_mode,
            'mercado_access_token'          => $request->mercado_access_token,
            'mercado_description'           => $request->mercado_description,
            'mercadosetting'                => (in_array('mercado', $request->get('paymentsetting'))) ? 'on' : 'off',

            'payfast_mode'                  => $request->payfast_mode,
            'payfast_signature'             => $request->payfast_signature,
            'payfast_merchant_id'           => $request->payfast_merchant_id,
            'payfast_merchant_key'          => $request->payfast_merchant_key,
            'payfast_description'           => $request->payfast_description,
            'payfastsetting'                => (in_array('payfast', $request->get('paymentsetting'))) ? 'on' : 'off',

            'toyyibpay_secret_key'          => $request->toyyibpay_secret_key,
            'toyyibpay_category_code'       => $request->toyyibpay_category_code,
            'toyyibpay_description'         => $request->toyyibpay_description,
            'toyyibpaysetting'              => (in_array('toyyibpay', $request->get('paymentsetting'))) ? 'on' : 'off',

            'iyzipay_key'                   => $request->iyzipay_key,
            'iyzipay_secret'                => $request->iyzipay_secret,
            'iyzipay_mode'                  => $request->iyzipay_mode,
            'iyzipay_description'           => $request->iyzipay_description,
            'iyzipaysetting'                => (in_array('iyzipay', $request->get('paymentsetting'))) ? 'on' : 'off',

            'sspay_category_code'           => $request->sspay_category_code,
            'sspay_secret_key'              => $request->sspay_secret_key,
            'sspay_description'             => $request->sspay_description,
            'sspaysetting'                  => (in_array('sspay', $request->get('paymentsetting'))) ? 'on' : 'off',

            'cashfree_mode'                 => $request->cashfree_mode,
            'cashfree_app_id'               => $request->cashfree_app_id,
            'cashfree_secret_key'           => $request->cashfree_secret_key,
            'cashfree_description'          => $request->cashfree_description,
            'cashfreesetting'               => (in_array('cashfree', $request->get('paymentsetting'))) ? 'on' : 'off',

            'aamarpay_store_id'             => $request->aamarpay_store_id,
            'aamarpay_signature_key'        => $request->aamarpay_signature_key,
            'aamarpay_description'          => $request->aamarpay_description,
            'aamarpaysetting'               => (in_array('aamarpay', $request->get('paymentsetting'))) ? 'on' : 'off',

            'payumoney_mode'                => $request->payumoney_mode,
            'payumoney_merchant_key'        => $request->payumoney_merchant_key,
            'payumoney_salt_key'            => $request->payumoney_salt_key,
            'payumoney_description'         => $request->payumoney_description,
            'payumoneysetting'              => (in_array('payumoney', $request->get('paymentsetting'))) ? 'on' : 'off',

            'paytab_profile_id'             => $request->paytab_profile_id,
            'paytab_server_key'             => $request->paytab_server_key,
            'paytab_region'                 => $request->paytab_region,
            'paytab_description'            => $request->paytab_description,
            'paytabsetting'                 => (in_array('paytab', $request->get('paymentsetting'))) ? 'on' : 'off',

            'benefit_key'                   => $request->benefit_key,
            'benefit_secret_key'            => $request->benefit_secret_key,
            'benefit_description'           => $request->benefit_description,
            'benefitsetting'                => (in_array('benefit', $request->get('paymentsetting'))) ? 'on' : 'off',

            'mollie_api_key'                => $request->mollie_api_key,
            'mollie_profile_id'             => $request->mollie_profile_id,
            'mollie_partner_id'             => $request->mollie_partner_id,
            'mollie_description'            => $request->mollie_description,
            'molliesetting'                 => (in_array('mollie', $request->get('paymentsetting'))) ? 'on' : 'off',

            'skrill_email'                  => $request->skrill_email,
            'skrill_description'            => $request->skrill_description,
            'skrillsetting'                 => (in_array('skrill', $request->get('paymentsetting'))) ? 'on' : 'off',

            'easebuzz_environment'          => $request->easebuzz_environment,
            'easebuzz_merchant_key'         => $request->easebuzz_merchant_key,
            'easebuzz_salt'                 => $request->easebuzz_salt,
            'easebuzz_description'          => $request->easebuzz_description,
            'easebuzzsetting'               => (in_array('easebuzz', $request->get('paymentsetting'))) ? 'on' : 'off',

            'payment_details'               =>  $request->payment_details,
            'offlinesetting'                => (in_array('offline', $request->get('paymentsetting'))) ? 'on' : 'off',
        ];
        if (Auth::user()->type == 'Admin') {
            foreach ($data as $key => $value) {
                UtilityFacades::storesettings([
                    'key'   => $key,
                    'value' => $value
                ]);
            }
        } else {
            foreach ($data as $key => $value) {
                UtilityFacades::setEnvironmentValue([$key => $value]);
            }
        }
        return redirect()->back()->with('success', __('Payment setting updated successfully.'));
    }

    public function testMail()
    {
        return view('admin.settings.test-mail');
    }

    public function testSendMail(Request $request)
    {
        $validator      = \Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            $messages   = $validator->getMessageBag();
            return redirect()->back()->with('errors', $messages->first());
        }
        $notify     = NotificationsSetting::where('title', 'Testing Purpose')->first();
        if (UtilityFacades::getsettings('email_setting_enable') == 'on') {
            if (isset($notify)) {
                if ($notify->email_notification == '1') {
                    if (MailTemplate::where('mailable', TestMail::class)->first()) {
                        try {
                            Mail::to($request->email)->send(new TestMail());
                        } catch (\Exception $e) {
                            return redirect()->back()->with('errors', $e->getMessage());
                        }
                    }
                }
            }
        }
        return redirect()->back()->with('success', __('Email send successfully.'));
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName         = $request->file('upload')->getClientOriginalName();
            $fileName           = pathinfo($originName, PATHINFO_FILENAME);
            $extension          = $request->file('upload')->getClientOriginalExtension();
            $fileName           = $fileName . '_' . time() . '.' . $extension;
            $request->file('upload')->move(public_path('images'), $fileName);
            $CKEditorFuncNum    = $request->input('CKEditorFuncNum');
            $url                = asset('public/images/' . $fileName);
            $msg                = 'Image uploaded successfully';
            $response           = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }
}
