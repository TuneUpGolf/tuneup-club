<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\FooterSetting;
use App\Models\HeaderSetting;
use App\Models\PageSetting;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function landingPageSetting()
    {
        return view('admin.landing-page.app-setting');
    }

    public function appSettingStore(Request $request)
    {
        request()->validate([
            'apps_name'         => 'required|max:50',
            'apps_bold_name'    => 'required|max:50',
            'app_detail'        => 'required',
            'apps_image'        => 'image|mimes:png,jpg,jpeg',
        ]);
        if ($request->apps_multiple_image != '') {
            $data = [];
            if ($request->hasFile('apps_multiple_image')) {
                $images     = $request->file('apps_multiple_image');
                foreach ($images as $image) {
                    $imageName  = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('landing-page/app/', $imageName);
                    $data[]     = ['apps_multiple_image' => 'landing-page/app/' . $imageName];
                }
            }
            $data = json_encode($data);
            Setting::updateOrCreate(
                ['key'      => 'apps_multiple_image_setting'],
                ['value'    => $data]
            );
        }
        $data = [
            'apps_setting_enable'   => $request->apps_setting_enable == 'on' ? 'on' : 'off',
            'apps_name'             => $request->apps_name,
            'apps_bold_name'        => $request->apps_bold_name,
            'app_detail'            => $request->app_detail,
        ];
        if ($request->apps_image) {
            Storage::delete(UtilityFacades::getsettings('apps_image'));
            $imageName          = 'app.' . $request->apps_image->extension();
            $request->apps_image->storeAs('landing-page/app/', $imageName);
            $data['apps_image'] = 'landing-page/app/' . $imageName;
        }
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('App setting updated successfully.'));
    }

    public function menuSetting()
    {
        return view('admin.landing-page.menu-setting');
    }

    public function menuSettingSection1Store(Request $request)
    {
        request()->validate([
            'menu_name_section1'        => 'required|max:50',
            'menu_bold_name_section1'   => 'required|max:50',
            'menu_detail_section1'      => 'required',
            'menu_image_section1'       => 'image|mimes:png,jpg,jpeg',
        ]);
        $data = [
            'menu_setting_section1_enable'  => $request->menu_setting_section1_enable == 'on' ? 'on' : 'off',
            'menu_name_section1'            => $request->menu_name_section1,
            'menu_bold_name_section1'       => $request->menu_bold_name_section1,
            'menu_detail_section1'          => $request->menu_detail_section1,
        ];
        if ($request->menu_image_section1) {
            Storage::delete(UtilityFacades::getsettings('menu_image_section1'));
            $image_name                     = 'menusection1.' . $request->menu_image_section1->extension();
            $request->menu_image_section1->storeAs('landing-page/menu/', $image_name);
            $data['menu_image_section1']    = 'landing-page/menu/' . $image_name;
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Menu setting updated successfully.'));
    }

    public function menuSettingSection2Store(Request $request)
    {
        request()->validate([
            'menu_name_section2'        => 'required|max:50',
            'menu_bold_name_section2'   => 'required|max:50',
            'menu_detail_section2'      => 'required',
            'menu_image_section2'       => 'image|mimes:png,jpg,jpeg',
        ]);
        $data = [
            'menu_setting_section2_enable'  => $request->menu_setting_section2_enable == 'on' ? 'on' : 'off',
            'menu_name_section2'            => $request->menu_name_section2,
            'menu_bold_name_section2'       => $request->menu_bold_name_section2,
            'menu_detail_section2'          => $request->menu_detail_section2,
        ];
        if ($request->menu_image_section2) {
            Storage::delete(UtilityFacades::getsettings('menu_image_section2'));
            $imageName                      = 'menusection12.' . $request->menu_image_section2->extension();
            $request->menu_image_section2->storeAs('landing-page/menu/', $imageName);
            $data['menu_image_section2']    = 'landing-page/menu/' . $imageName;
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Menu setting updated successfully.'));
    }

    public function menuSettingSection3Store(Request $request)
    {
        request()->validate([
            'menu_name_section3'        => 'required|max:50',
            'menu_bold_name_section3'   => 'required|max:50',
            'menu_detail_section3'      => 'required',
            'menu_image_section3'       => 'image|mimes:png,jpg,jpeg',
        ]);
        $data = [
            'menu_setting_section3_enable'  => $request->menu_setting_section3_enable == 'on' ? 'on' : 'off',
            'menu_name_section3'            => $request->menu_name_section3,
            'menu_bold_name_section3'       => $request->menu_bold_name_section3,
            'menu_detail_section3'          => $request->menu_detail_section3,
        ];
        if ($request->menu_image_section3) {
            Storage::delete(UtilityFacades::getsettings('menu_image_section3'));
            $imageName                      = 'menusection13.' . $request->menu_image_section3->extension();
            $request->menu_image_section3->storeAs('landing-page/menu/', $imageName);
            $data['menu_image_section3']    = 'landing-page/menu/' . $imageName;
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Menu setting updated successfully.'));
    }

    public function faqSetting()
    {
        return view('admin.landing-page.faq-setting');
    }

    public function faqSettingStore(Request $request)
    {
        request()->validate([
            'faq_name'  => 'required|max:50',
        ]);
        $data = [
            'faq_setting_enable'    => $request->faq_setting_enable == 'on' ? 'on' : 'off',
            'faq_name'              => $request->faq_name,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Faq setting updated successfully.'));
    }

    public function featureSetting(Request $request)
    {
        $featureSettings = json_decode(UtilityFacades::getsettings('feature_setting'), true) ?? [];
        return view('admin.landing-page.feature.index', compact('featureSettings'));
    }

    public function featureSettingStore(Request $request)
    {
        request()->validate([
            'feature_name'      => 'required|max:50',
            'feature_bold_name' => 'required|max:50',
            'feature_detail'    => 'required',
        ]);
        $data = [
            'feature_setting_enable'    => $request->feature_setting_enable == 'on' ? 'on' : 'off',
            'feature_name'              => $request->feature_name,
            'feature_bold_name'         => $request->feature_bold_name,
            'feature_detail'            => $request->feature_detail,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Feature setting updated successfully.'));
    }

    public function featureCreate()
    {
        return view('admin.landing-page.feature.create');
    }

    public function featureStore(Request $request)
    {
        request()->validate([
            'feature_name'      => 'required|max:50',
            'feature_bold_name' => 'required|max:50',
            'feature_detail'    => 'required',
            'image'             => 'image|mimes:svg',
        ]);
        $data       = json_decode(UtilityFacades::getsettings('feature_setting'), true) ?? [];
        if ($request->feature_image) {
            $feature_image          = time() . "-feature_image." . $request->feature_image->extension();
            $request->feature_image->storeAs('landing-page/feature', $feature_image);
            $datas['feature_image'] = 'landing-page/feature/' . $feature_image;
        }
        $datas['feature_name']      = $request->feature_name;
        $datas['feature_bold_name'] = $request->feature_bold_name;
        $datas['feature_detail']    = $request->feature_detail;
        $data[]                     = $datas;
        $data                       = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'feature_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Feature setting created successfully.']);
    }

    public function featureEdit($key)
    {
        $features   = json_decode(UtilityFacades::getsettings('feature_setting'), true) ?? [];
        $feature    = $features[$key];
        return view('admin.landing-page.feature.edit', compact('feature', 'key'));
    }

    public function featureUpdate(Request $request, $key)
    {
        request()->validate([
            'feature_name'      => 'required|max:50',
            'feature_bold_name' => 'required|max:50',
            'feature_detail'    => 'required',
            'image'             => 'image|mimes:svg',
        ]);
        $data       = json_decode(UtilityFacades::getsettings('feature_setting'), true) ?? [];
        if ($request->feature_image) {
            $feature_image          = time() . "-feature_image." . $request->feature_image->extension();
            $request->feature_image->storeAs('landing-page/feature', $feature_image);
            $datas['feature_image'] = 'landing-page/feature/' . $feature_image;
        }
        $data[$key]['feature_name']         = $request->feature_name;
        $data[$key]['feature_bold_name']    = $request->feature_bold_name;
        $data[$key]['feature_detail']       = $request->feature_detail;
        $data                               = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'feature_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Feature setting updated successfully.']);
    }

    public function featureDelete($key)
    {
        $featureData  = json_decode(UtilityFacades::getsettings('feature_setting'), true) ?? [];
        unset($featureData[$key]);
        Setting::updateOrCreate(
            ['key'      => 'feature_setting'],
            ['value'    => $featureData]
        );
        return redirect()->back()->with(['success' => 'Feature setting deleted successfully']);
    }

    public function startViewSetting()
    {
        return view('admin.landing-page.start-view-setting');
    }

    public function startViewSettingStore(Request $request)
    {
        request()->validate([
            'start_view_name'       => 'required|max:50',
            'start_view_detail'     => 'required',
            'start_view_link_name'  => 'required|max:50',
            'start_view_link'       => 'required',
            'start_view_image'      => 'image|mimes:png,jpg,jpeg',
        ]);
        $data = [
            'start_view_setting_enable' => $request->start_view_setting_enable == 'on' ? 'on' : 'off',
            'start_view_name'           => $request->start_view_name,
            'start_view_detail'         => $request->start_view_detail,
            'start_view_link_name'      => $request->start_view_link_name,
            'start_view_link'           => $request->start_view_link,
        ];
        if ($request->start_view_image) {
            Storage::delete(UtilityFacades::getsettings('start_view_image'));
            $image_name                 = 'startview.' . $request->start_view_image->extension();
            $request->start_view_image->storeAs('landing-page', $image_name);
            $data['start_view_image']   = 'landing-page/' . $image_name;
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Start view setting updated successfully.'));
    }

    public function businessGrowthSetting(Request $request)
    {
        $businessGrowthSettings       = json_decode(UtilityFacades::getsettings('business_growth_setting'), true) ?? [];
        $businessGrowthViewSettings   = json_decode(UtilityFacades::getsettings('business_growth_view_setting'), true) ?? [];
        return view('admin.landing-page.business-growth.index', compact('businessGrowthSettings', 'businessGrowthViewSettings'));
    }

    public function businessGrowthSettingStore(Request $request)
    {
        request()->validate([
            'business_growth_name'          => 'required|max:50',
            'business_growth_bold_name'     => 'required|max:50',
            'business_growth_detail'        => 'required',
            'business_growth_video'         => 'mimes:mp4,avi,wmv,mov,webm',
            'business_growth_front_image'   => 'image|mimes:png,jpg,jpeg',
        ]);
        $data = [
            'business_growth_setting_enable'    => $request->business_growth_setting_enable == 'on' ? 'on' : 'off',
            'business_growth_name'              => $request->business_growth_name,
            'business_growth_bold_name'         => $request->business_growth_bold_name,
            'business_growth_detail'            => $request->business_growth_detail,
        ];
        if ($request->business_growth_front_image) {
            Storage::delete(UtilityFacades::getsettings('business_growth_front_image'));
            $imageName      = '10.' . $request->business_growth_front_image->extension();
            $request->business_growth_front_image->storeAs('landing-page/businessgrowth/', $imageName);
            $data['business_growth_front_image'] = 'landing-page/businessgrowth/' . $imageName;
        }
        if ($request->business_growth_video) {
            Storage::delete(UtilityFacades::getsettings('business_growth_video'));
            $fileName   = 'video.' . $request->business_growth_video->extension();
            $request->business_growth_video->storeAs('landing-page/businessgrowth/', $fileName);
            $data['business_growth_video'] = $request->business_growth_video->storeAs('landing-page/businessgrowth/', $fileName);
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Business growth updated successfully.'));
    }

    public function businessGrowthCreate()
    {
        return view('admin.landing-page.business-growth.create');
    }

    public function businessGrowthStore(Request $request)
    {
        request()->validate([
            'business_growth_title'     => 'required|max:50',
        ]);
        $data                           = json_decode(UtilityFacades::getsettings('business_growth_setting'), true) ?? [];
        $datas['business_growth_title'] = $request->business_growth_title;
        $data[]                         = $datas;
        $data                           = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'business_growth_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Business growth setting created successfully.']);
    }

    public function businessGrowthEdit($key)
    {
        $businessGrowthSettings = json_decode(UtilityFacades::getsettings('business_growth_setting'), true) ?? [];
        $businessGrowthSetting    = $businessGrowthSettings[$key];
        return view('admin.landing-page.business-growth.edit', compact('businessGrowthSetting', 'key'));
    }

    public function businessGrowthUpdate(Request $request, $key)
    {
        request()->validate([
            'business_growth_title'     => 'required|max:50',
        ]);
        $data                                   = json_decode(UtilityFacades::getsettings('business_growth_setting'), true) ?? [];
        $data[$key]['business_growth_title']    = $request->business_growth_title;
        $data                                   = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'business_growth_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Business growth setting updated successfully.']);
    }

    public function businessGrowthDelete($key)
    {
        $bisinessGrowth   = json_decode(UtilityFacades::getsettings('business_growth_setting'), true) ?? [];
        unset($bisinessGrowth[$key]);
        Setting::updateOrCreate(
            ['key'      =>  'business_growth_setting'],
            ['value'    => $bisinessGrowth]
        );
        return redirect()->back()->with(['success' => 'Business growth setting deleted successfully']);
    }

    public function businessGrowthViewCreate()
    {
        return view('admin.landing-page.business-growth.business-growth-view-create');
    }

    public function businessGrowthViewStore(Request $request)
    {
        request()->validate([
            'business_growth_view_name'     => 'required|max:50',
            'business_growth_view_amount'   => 'required',
        ]);
        $data = json_decode(UtilityFacades::getsettings('business_growth_view_setting'), true) ?? [];
        $datas['business_growth_view_name']     = $request->business_growth_view_name;
        $datas['business_growth_view_amount']   = $request->business_growth_view_amount;
        $data[]                                 = $datas;
        $data                                   = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'business_growth_view_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Business growth view setting created successfully.']);
    }

    public function businessGrowthViewEdit($key)
    {
        $businessGrowthViewSettings = json_decode(UtilityFacades::getsettings('business_growth_view_setting'), true) ?? [];
        $businessGrowthViewSetting  = $businessGrowthViewSettings[$key];
        return view('admin.landing-page.business-growth.business-growth-view-edit', compact('businessGrowthViewSetting', 'key'));
    }

    public function businessGrowthViewUpdate(Request $request, $key)
    {
        request()->validate([
            'business_growth_view_name'     => 'required|max:50',
            'business_growth_view_amount'   => 'required',
        ]);
        $data                                       = json_decode(UtilityFacades::getsettings('business_growth_view_setting'), true) ?? [];
        $data[$key]['business_growth_view_name']    = $request->business_growth_view_name;
        $data[$key]['business_growth_view_amount']  = $request->business_growth_view_amount;
        $data                                       = json_encode($data);
        Setting::updateOrCreate(
            ['key'      => 'business_growth_view_setting'],
            ['value'    => $data]
        );
        return redirect()->back()->with(['success' => 'Business growth view setting updated successfully.']);
    }

    public function businessGrowthViewDelete($key)
    {
        $businessGrowthViewSetting  = json_decode(UtilityFacades::getsettings('business_growth_view_setting'), true) ?? [];
        unset($businessGrowthViewSetting[$key]);
        Setting::updateOrCreate(
            ['key'      =>  'business_growth_view_setting'],
            ['value'    => $businessGrowthViewSetting]
        );
        return redirect()->back()->with(['success' => 'Business growth view setting deleted successfully']);
    }

    public function contactusSetting()
    {
        return view('admin.landing-page.contactus-setting');
    }

    public function contactusSettingStore(Request $request)
    {
        request()->validate([
            'contactus_name'        => 'required|max:50',
            'contactus_bold_name'   => 'required|max:50',
            'contactus_detail'      => 'required',
            'contactus_email'       => 'required|email',
            'contactus_latitude'    => 'required',
            'contactus_longitude'   => 'required',
        ]);
        $data = [
            'contactus_setting_enable'  => $request->contactus_setting_enable == 'on' ? 'on' : 'off',
            'contactus_name'            => $request->contactus_name,
            'contactus_bold_name'       => $request->contactus_bold_name,
            'contactus_detail'          => $request->contactus_detail,
            'contactus_email'           => $request->contactus_email,
            'contactus_latitude'        => $request->contactus_latitude,
            'contactus_longitude'       => $request->contactus_longitude,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Contactus setting updated successfully.'));
    }

    public function blogSetting()
    {
        return view('admin.landing-page.blog-setting');
    }

    public function blogSettingStore(Request $request)
    {
        request()->validate([
            'blog_name'     => 'required|max:50',
            'blog_detail'   => 'required',
        ]);
        $data = [
            'blog_setting_enable'   => $request->blog_setting_enable == 'on' ? 'on' : 'off',
            'blog_name'             => $request->blog_name,
            'blog_detail'           => $request->blog_detail,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Blog setting updated successfully.'));
    }

    public function loginSetting()
    {
        return view('admin.landing-page.login-setting');
    }

    public function loginSettingStore(Request $request)
    {
        request()->validate([
            'login_image'   => 'image|mimes:svg',
            'login_name'    => 'required|max:50',
            'login_detail'  => 'required',
        ]);
        $data = [
            'login_setting_enable'  => $request->login_setting_enable == 'on' ? 'on' : 'off',
            'login_name'            => $request->login_name,
            'login_detail'          => $request->login_detail,
        ];
        if ($request->login_image) {
            Storage::delete(UtilityFacades::getsettings('login_image'));
            $imageName      = 'menusection1.' . $request->login_image->extension();
            $request->login_image->storeAs('landing-page/menu/', $imageName);
            $data['login_image'] = 'landing-page/menu/' . $imageName;
        }
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Login setting updated successfully.'));
    }

    public function recaptchaSetting()
    {
        return view('superadmin.landing-page.recaptcha-setting');
    }

    public function recaptchaSettingStore(Request $request)
    {
        if ($request->contact_us_recaptcha_status == '1' || $request->login_recaptcha_status == '1') {
            request()->validate([
                'recaptcha_key'     => 'required',
                'recaptcha_secret'  => 'required',
            ]);
        }
        $data = [
            'contact_us_recaptcha_status'   => ($request->contact_us_recaptcha_status == 'on') ? '1' : '0',
            'login_recaptcha_status'        => ($request->login_recaptcha_status == 'on') ? '1' : '0',
            'recaptcha_key'                 => $request->recaptcha_key,
            'recaptcha_secret'              => $request->recaptcha_secret,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Recaptcha setting updated successfully.'));
    }

    public function footerSetting(Request $request)
    {
        $footerMainMenus  = FooterSetting::where('parent_id', 0)->get();
        $footerSubMenus   = FooterSetting::where('parent_id', '!=', 0)->get();
        return view('admin.landing-page.footer.index', compact('footerMainMenus', 'footerSubMenus'));
    }

    public function footerSettingStore(Request $request)
    {
        request()->validate([
            'footer_description'    => 'required',
        ]);
        $data = [
            'footer_setting_enable' => $request->footer_setting_enable == 'on' ? 'on' : 'off',
            'footer_description'    => $request->footer_description,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Footer setting updated successfully.'));
    }

    public function footerMainMenuCreate()
    {
        return view('admin.landing-page.footer.create');
    }

    public function footerMainMenuStore(Request $request)
    {
        request()->validate([
            'menu'      => 'required',
        ]);
        $footerMainMenu               = new FooterSetting();
        $footerMainMenu->menu         = $request->menu;
        $footerMainMenu->parent_id    = 0;
        $footerMainMenu->save();
        return redirect()->back()->with('success', 'Footer main menu created successfully');
    }

    public function footerMainMenuEdit($id)
    {
        $footerMainMenuEdit  = FooterSetting::where('id', $id)->first();
        return view('admin.landing-page.footer.edit', compact('footerMainMenuEdit'));
    }

    public function footerMainMenuUpdate(Request $request, $id)
    {
        request()->validate([
            'menu'      => 'required',
        ]);
        $footerMainMenu               = FooterSetting::where('id', $id)->first();
        $footerMainMenu->menu         = $request->menu;
        $footerMainMenu->parent_id    = 0;
        $footerMainMenu->save();
        return redirect()->back()->with('success', 'Footer main menu updated successfully');
    }

    public function footerMainMenuDelete($id)
    {
        $footerMainMenu = FooterSetting::where('id', $id)->first();
        $footerMainMenu->delete();
        return redirect()->back()->with('success', 'Footer menu deleted Successfully');
    }

    public function footerSubMenuCreate()
    {
        $pages      = PageSetting::pluck('title', 'id');
        $footers    = FooterSetting::where('parent_id', 0)->pluck('menu', 'id');
        return view('admin.landing-page.footer.create-sub-menu', compact('pages', 'footers'));
    }

    public function footerSubMenuStore(Request $request)
    {
        request()->validate([
            'page_id'       => 'required',
        ]);
        $pages                     = PageSetting::where('id', $request->page_id)->first();
        $footerSubMenu             = new FooterSetting();
        $footerSubMenu->menu       = $pages->title;
        $footerSubMenu->page_id    = $request->page_id;
        $footerSubMenu->parent_id  = $request->parent_id;
        $footerSubMenu->save();
        return redirect()->route('landing.footer.index')->with('success', 'Footer sub menu created successfully');
    }

    public function footerSubMenuEdit($id)
    {
        $footerPage     = FooterSetting::find($id);
        $pages          = PageSetting::pluck('title', 'id');
        $footer         = FooterSetting::where('parent_id', 0)->pluck('menu', 'id');
        $footerMenu     = FooterSetting::where('id', $footerPage->parent_id)->pluck('menu', 'id');
        return view('admin.landing-page.footer.edit-sub-menu', compact('footerPage', 'pages', 'footer', 'footerMenu'));
    }

    public function footerSubMenuUpdate(Request $request, $id)
    {
        request()->validate([
            'page_id'   => 'required',
        ]);
        $pages                      = PageSetting::where('id', $request->page_id)->first();
        $footerSubMenu              = FooterSetting::where('id', $id)->first();
        $footerSubMenu->menu        = $pages->title;
        $footerSubMenu->page_id     = $request->page_id;
        $footerSubMenu->parent_id   = $request->parent_id;
        $footerSubMenu->save();
        return redirect()->route('landing.footer.index')->with('success', 'Footer sub menu updated successfully');
    }

    public function footerSubMenuDelete($id)
    {
        $footerSubMenu  = FooterSetting::where('id', $id)->first();
        $footerSubMenu->delete();
        return redirect()->back()->with('success', 'Footer sub menu updated successfully');
    }

    public function pageDescription($slug)
    {
        $lang               = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $pageFooter         = FooterSetting::where('slug', $slug)->first();
        $footerMainMenus    = FooterSetting::where('parent_id', 0)->get();
        return view('admin.landing-page.footer.page-description', compact('pageFooter', 'footerMainMenus', 'lang', 'slug'));
    }

    public function headerSetting(Request $request)
    {
        $headerSettings     = HeaderSetting::all();
        return view('admin.landing-page.header.index', compact('headerSettings'));
    }

    public function headerSubMenuCreate()
    {
        $pages      = PageSetting::pluck('title', 'id');
        $headers    = HeaderSetting::pluck('menu', 'id');
        return view('admin.landing-page.header.create-sub-menu', compact('headers', 'pages'));
    }

    public function headerSubMenuStore(Request $request)
    {
        request()->validate([
            'page_id'   => 'required',
        ]);
        $pages                     = PageSetting::where('id', $request->page_id)->first();
        $headerSubMenu             = new HeaderSetting();
        $headerSubMenu->menu       = $pages->title;
        $headerSubMenu->page_id    = $request->page_id;
        $headerSubMenu->save();
        return redirect()->route('landing.header.index')->with('success', 'Header sub menu created successfully.');
    }

    public function headerSubMenuEdit($id)
    {
        $headerPage = HeaderSetting::find($id);
        $pages      = PageSetting::pluck('title', 'id');
        return view('admin.landing-page.header.edit-sub-menu', compact('pages', 'headerPage'));
    }

    public function headerSubMenuUpdate(Request $request, $id)
    {
        request()->validate([
            'page_id' => 'required',
        ]);
        $pages                  = PageSetting::where('id', $request->page_id)->first();
        $headerSubMenu          = HeaderSetting::where('id', $id)->first();
        $headerSubMenu->menu    = $pages->title;
        $headerSubMenu->page_id = $request->page_id;
        $headerSubMenu->save();
        return redirect()->route('landing.header.index')->with('success', 'Header sub menu updated successfully.');
    }

    public function headerSubMenuDelete($id)
    {
        $headerSubMenu = HeaderSetting::where('id', $id)->first();
        $headerSubMenu->delete();
        return redirect()->back()->with('success', 'Header Sub Menu deleted Successfully.');
    }

    public function pageBackground()
    {
        return view('superadmin.landing-page.background-image');
    }

    public function pageBackgroundStore(Request $request)
    {
        request()->validate([
            'background_image'  => 'image|mimes:png,jpg,jpeg',
        ]);
        if ($request->background_image) {
            Storage::delete(UtilityFacades::getsettings('background_image'));
            $image_name                 = 'background.' . $request->background_image->extension();
            $request->background_image->storeAs('landing-page/', $image_name);
            $data['background_image']   = 'landing-page/' . $image_name;
        }
        if (isset($data)) {
            foreach ($data as $key => $value) {
                UtilityFacades::storesettings([
                    'key'   => $key,
                    'value' => $value
                ]);
            }
        } else {
            return redirect()->back()->with('failed', __('Please select background image.'));
        }
        return redirect()->back()->with('success', __('Background setting updated successfully.'));
    }

    public function testimonialSetting()
    {
        return view('admin.landing-page.testimonial-setting');
    }

    public function testimonialSettingStore(Request $request)
    {
        request()->validate([
            'testimonial_name'      => 'required|max:50',
            'testimonial_bold_name' => 'required|max:50',
            'testimonial_detail'    => 'required',
        ]);
        $data = [
            'testimonial_setting_enable'    => $request->testimonial_setting_enable == 'on' ? 'on' : 'off',
            'testimonial_name'              => $request->testimonial_name,
            'testimonial_bold_name'         => $request->testimonial_bold_name,
            'testimonial_detail'            => $request->testimonial_detail,
        ];
        foreach ($data as $key => $value) {
             UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return redirect()->back()->with('success', __('Testimonial setting updated successfully.'));
    }
}
