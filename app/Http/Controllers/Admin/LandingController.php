<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Posts;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Mail\Admin\ConatctMail;
use App\Http\Controllers\Controller;
use App\Models\NotificationsSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use Spatie\MailTemplates\Models\MailTemplate;
use App\Notifications\Admin\ConatctNotification;

class LandingController extends Controller
{
    public function landingPage()
    {
        $centralDomain = config('tenancy.central_domains')[0];
        $currentDomain = tenant('domains');
        if (!empty($currentDomain)) {
            $currentDomain = $currentDomain->pluck('domain')->toArray()[0];
        }
        if ($currentDomain == null) {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            }
            $lang   = UtilityFacades::getActiveLanguage();
            \App::setLocale($lang);
            $plans  = Plan::where('active_status', 1)->get();
            return view('welcome', compact('plans', 'lang'));
        } else {
            $lang                           = UtilityFacades::getActiveLanguage();
            \App::setLocale($lang);
            $influencerDetails = User::where('type', Role::ROLE_INFLUENCER)
                ->with(['lessons', 'post', 'post.likePost'])
                ->first();

            $plans = null;
            if (!empty($influencerDetails)) {
                $plans = Plan::where('influencer_id', $influencerDetails->id)
                    ->where('active_status', 1)->orderBy('column_order', 'asc')->get();
            }
            $admin = User::where('type', Role::ROLE_ADMIN)
                ->first();
            if (UtilityFacades::getsettings('landing_page_status') == '1') {

            
                $feedEnabledPlanId = Plan::where('influencer_id', $influencerDetails->id)
                    ->where('is_feed_enabled', true)->pluck('id')->toArray();
                $isSubscribed = in_array(Auth::user()?->plan_id, $feedEnabledPlanId);



                return view('welcome', compact(
                    'lang',
                    'influencerDetails',
                    'plans',
                    'admin',
                    'feedEnabledPlanId'
                ));
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function getCategoryPost(Request $request)
    {
        $post       = Posts::where('category_id', $request->category)->get();
        return response()->json($post, 200);
    }

    public function postDetails($slug, Request $request)
    {
        $post           = Posts::where('slug', $slug)->first();
        $randomPosts    = Posts::where('slug', '!=', $slug)->limit(3)->get();
        return view('admin.posts.details', compact('post', 'randomPosts'));
    }

    public function contactUs()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        return view('contactus', compact('lang'));
    }

    public function termsAndConditions()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        return view('terms-and-conditions', compact('lang'));
    }

    public function faqs()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $faqs       = Faq::orderBy('order')->get();
        return view('faq', compact('lang', 'faqs'));
    }

    public function contactMail(Request $request)
    {
        if (UtilityFacades::getsettings('contact_us_recaptcha_status') == '1') {
            request()->validate([
                'g-recaptcha-response' => 'required',
            ]);
        }
        $user   = User::where('tenant_id', tenant('id'))->first();
        $notify = NotificationsSetting::where('title', 'New Enquiry Details')->first();
        if (UtilityFacades::getsettings('email_setting_enable') == 'on') {
            if (isset($notify)) {
                if ($notify->notify = '1') {
                    $user->notify(new ConatctNotification($request));
                }
            }
        }
        if (UtilityFacades::getsettings('email_setting_enable') == 'on'  && UtilityFacades::getsettings('contact_email') != '') {
            if (isset($notify)) {
                if ($notify->email_notification == '1') {
                    if (UtilityFacades::getsettings('email_setting_enable') == 'on' && UtilityFacades::getsettings('contact_email') != '') {
                        if (MailTemplate::where('mailable', ConatctMail::class)->first()) {
                            try {
                                Mail::to(UtilityFacades::getsettings('contact_email'))->send(new ConatctMail($request->all()));
                            } catch (\Exception $e) {
                                return redirect()->back()->with('errors', $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
        return redirect()->back()->with('success', __('Enquiry details send successfully'));
    }

    public function changeLang($lang = '')
    {
        if ($lang == '') {
            $lang   = UtilityFacades::getActiveLanguage();
        }
        Cookie::queue('lang', $lang, 120);
        return redirect()->back()->with('success', __('Language successfully changed.'));
    }
}
