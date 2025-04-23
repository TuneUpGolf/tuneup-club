<?php

namespace App\Http\Middleware;

use App\Facades\UtilityFacades;
use Closure;
use Illuminate\Support\Facades\Storage;

class Setting
{
    public function handle($request, Closure $next)
    {
        if (tenant('domains') == null) {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            }
        }

        if (tenant('domains') == null) {
            config([
                'chatify.routes.middleware' => env('CHATIFY_ROUTES_MIDDLEWARE', ['web', 'auth', 'Setting'])
            ]);
        } else {
            config([
                'chatify.routes.middleware' => env('CHATIFY_ROUTES_MIDDLEWARE', ['web', 'auth', 'Setting', Middleware\InitializeTenancyByDomain::class])
            ]);
        }

        config([
            'app.name' => UtilityFacades::getsettings('app_name'),

            'filesystems.default' => (UtilityFacades::getsettings('storage_type') != '') ? UtilityFacades::getsettings('storage_type') : 'local',
            'filesystems.disks.s3.key' => UtilityFacades::getsettings('s3_key'),
            'filesystems.disks.s3.secret' => UtilityFacades::getsettings('s3_secret'),
            'filesystems.disks.s3.region' => UtilityFacades::getsettings('s3_region'),
            'filesystems.disks.s3.bucket' => UtilityFacades::getsettings('s3_bucket'),


            'filesystems.disks.wasabi.key' => UtilityFacades::getsettings('wasabi_key'),
            'filesystems.disks.wasabi.secret' => UtilityFacades::getsettings('wasabi_secret'),
            'filesystems.disks.wasabi.region' => UtilityFacades::getsettings('wasabi_region'),
            'filesystems.disks.wasabi.bucket' => UtilityFacades::getsettings('wasabi_bucket'),
            'filesystems.disks.wasabi.endpoint' => UtilityFacades::getsettings('wasabi_root'),

            'mail.default' => "smtp",
            'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'mail.mailers.smtp.port' =>  env('MAIL_PORT', 587),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail.mailers.smtp.username' =>  env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' =>  env('MAIL_PASSWORD'),
            'mail.from.address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mail.from.name' => env('MAIL_FROM_NAME', 'Example'),

            'chatify.pusher.key' => UtilityFacades::getsettings('pusher_key'),
            'chatify.pusher.secret' => UtilityFacades::getsettings('pusher_secret'),
            'chatify.pusher.app_id' => UtilityFacades::getsettings('pusher_id'),
            'chatify.pusher.options.cluster' => UtilityFacades::getsettings('pusher_cluster'),

            'captcha.sitekey' => UtilityFacades::getsettings('recaptcha_key'),
            'captcha.secret' => UtilityFacades::getsettings('recaptcha_secret'),

            'services.twilio.sid' => env('TWILIO_SID'),
            'services.twilio.token' => env('TWILIO_AUTH_TOKEN'),
            'services.twilio.phone' => env('TWILIO_PHONE_NUMBER'),

            'paypal.mode' => UtilityFacades::getsettings('paypal_mode'),
            'paypal.sandbox.client_id' => UtilityFacades::getsettings('paypal_client_id'),
            'paypal.sandbox.client_secret' => UtilityFacades::getsettings('paypal_client_secret'),
            'paypal.sandbox.app_id' => 'sb-435ylq19779747@business.example.com',
            'paypal.live.app_id' => 'sb-435ylq19779747@business.example.com',

            'paytabs.profile_id' => UtilityFacades::getsettings('paytab_profile_id'),
            'paytabs.server_key' => UtilityFacades::getsettings('paytab_server_key'),
            'paytabs.region' => UtilityFacades::getsettings('paytab_region'),
            'paytabs.currency' => 'INR',

            'services.paytm.env' => UtilityFacades::getsettings('paytm_environment'),
            'services.paytm.merchant_id' => UtilityFacades::getsettings('paytm_merchant_id'),
            'services.paytm.merchant_key' => UtilityFacades::getsettings('paytm_merchant_key'),

            'services.google.client_id' => UtilityFacades::getsettings('google_client_id', ''),
            'services.google.client_secret' => UtilityFacades::getsettings('google_client_secret', ''),
            'services.google.redirect' => UtilityFacades::getsettings('google_redirect', ''),

            'services.facebook.client_id' => UtilityFacades::getsettings('FACEBOOK_CLIENT_ID', ''),
            'services.facebook.client_secret' => UtilityFacades::getsettings('FACEBOOK_CLIENT_SECRET', ''),
            'services.facebook.redirect' => UtilityFacades::getsettings('FACEBOOK_REDIRECT', ''),

            'services.github.client_id' => UtilityFacades::getsettings('GITHUB_CLIENT_ID', ''),
            'services.github.client_secret' => UtilityFacades::getsettings('GITHUB_CLIENT_SECRET', ''),
            'services.github.redirect' => UtilityFacades::getsettings('GITHUB_REDIRECT', ''),

            'services.linkedin.client_id' => UtilityFacades::getsettings('LINKEDIN_CLIENT_ID', ''),
            'services.linkedin.client_secret' => UtilityFacades::getsettings('LINKEDIN_CLIENT_SECRET', ''),
            'services.linkedin.redirect' => UtilityFacades::getsettings('LINKEDIN_REDIRECT', ''),

            'google-calendar.default_auth_profile' => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => Storage::path(UtilityFacades::getsettings('google_calender_json_file')),
            'google-calendar.auth_profiles.oauth.credentials_json' => Storage::path(UtilityFacades::getsettings('google_calender_json_file')),
            'google-calendar.auth_profiles.oauth.token_json' => Storage::path(UtilityFacades::getsettings('google_calender_json_file')),
            'google-calendar.calendar_id' => UtilityFacades::getsettings('google_calender_id'),
            'google-calendar.user_to_impersonate' => '',
        ]);

        return $next($request);
    }
}
