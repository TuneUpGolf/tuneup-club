<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Facades\UtilityFacades;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\ConatctMail;
use App\Models\Posts;
use Spatie\MailTemplates\Models\MailTemplate;
use App\Models\Faq;
use App\Models\FooterSetting;
use App\Models\NotificationsSetting;
use App\Models\Testimonial;
use App\Notifications\Admin\ConatctNotification;
use Illuminate\Support\Facades\Cookie;

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
            $appsMultipleImageSettings      = json_decode(UtilityFacades::getsettings('apps_multiple_image_setting'));
            $features                       = json_decode(UtilityFacades::getsettings('feature_setting'));
            $menuSettings                   = json_decode(UtilityFacades::getsettings('menu_setting'));
            $businessGrowthsSettings        = json_decode(UtilityFacades::getsettings('business_growth_setting'));
            $businessGrowthsViewSettings    = json_decode(UtilityFacades::getsettings('business_growth_view_setting'));
            $testimonials                   = Testimonial::where('status', 1)->get();
            $faqs                           = Faq::latest()->take(4)->get();
            $footerMainMenus                = FooterSetting::where('parent_id', 0)->get();
            $blogs = Posts::all();
            if (UtilityFacades::getsettings('landing_page_status') == '1') {
                return view('welcome', compact(
                    'blogs',
                    'features',
                    'faqs',
                    'testimonials',
                    'menuSettings',
                    'businessGrowthsSettings',
                    'businessGrowthsViewSettings',
                    'appsMultipleImageSettings',
                    'footerMainMenus',
                    'lang'
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
        return redirect()->back()->with('success',__('Language successfully changed.'));
    }
}
