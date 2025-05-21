<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta name="title"
        content="{{ !empty(Utility::getsettings('meta_title'))
            ? Utility::getsettings('meta_title')
            : 'Full Multi Tenancy Laravel Admin Saas' }}">
    <meta name="keywords"
        content="{{ !empty(Utility::getsettings('meta_keywords'))
            ? Utility::getsettings('meta_keywords')
            : 'Full Multi Tenancy Laravel Admin Saas,Multi Domains,Multi Databases' }}">
    <meta name="description"
        content="{{ !empty(Utility::getsettings('meta_description'))
            ? Utility::getsettings('meta_description')
            : 'Discover the efficiency of Full Multi Tenancy, a user-friendly web application by Quebix Apps.' }}">

    <title>@yield('title') | {{ Utility::getsettings('app_name') }}</title>
    <link rel="icon"
        href="{{ Utility::getsettings('favicon_logo') ? Utility::getpath('logo/app-favicon-logo.png') : asset('assets/images/app-favicon-logo.png') }}"
        type="image/png">

    @if (Utility::getsettings('seo_setting') == 'on')
        {!! app('seotools')->generate() !!}
    @endif

    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/landingpage-2.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/landingpage2-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    @stack('css')
</head>

<body class="light">
    @if (request()->is('login') || request()->is('register'))
        @yield('content')
    @else
        <div class="auth-wrapper auth-v3">
            <header class="site-header header-style-one">
                <div class="main-navigationbar">
                    <div class="container">
                        <div class="navigation-row d-flex align-items-center ">
                            <nav class="menu-items-col d-flex align-items-center justify-content-between ">
                                <div class="logo-col">
                                    <h1>
                                        <a href="javascript:void(0);" tabindex="0">
                                            <img
                                                src="{{ Utility::getsettings('app_dark_logo')
                                                    ? Utility::getpath('logo/app-dark-logo.png')
                                                    : asset('assets/images/app-dark-logo.png') }}">
                                        </a>
                                    </h1>
                                </div>
                                <div class="menu-item-right-col d-flex align-items-center justify-content-between">
                                    <div class="menu-left-col">
                                        <ul class="main-nav d-flex align-items-center">
                                            <li class="menus-lnk">
                                                <a href="{{ route('landingpage') }}"
                                                    tabindex="0">{{ __('Home') }}</a>
                                            </li>
                                            @php
                                                $header_main_menus = App\Models\HeaderSetting::get();
                                            @endphp
                                            @if (!empty($header_main_menus))
                                                @foreach ($header_main_menus as $header_main_menu)
                                                    <li class="menu-has-items">
                                                        @php
                                                            $page = App\Models\PageSetting::find(
                                                                $header_main_menu->page_id,
                                                            );
                                                        @endphp
                                                        <a @if ($page->type == 'link') ?  href="{{ $page->page_url }}"  @else  href="{{ route('description.page', $header_main_menu->slug) }}" @endif
                                                            tabindex="0">
                                                            {{ $page->title }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                            @if (Utility::getsettings('landing_page_status') == '1')
                                                @if (Utility::getsettings('testimonial_setting_enable') == 'on')
                                                    <li>
                                                        <a href="{{ url('/') }}/#testimonials"
                                                            tabindex="0">{{ __('Testimonials') }}</a>
                                                    </li>
                                                @endif
                                                @if (tenant('id') == null)
                                                    @if (Utility::getsettings('plan_setting_enable') == 'on')
                                                        <li>
                                                            <a href="{{ url('/') }}/#plans"
                                                                tabindex="0">{{ __('Pricing') }}</a>
                                                        </li>
                                                    @endif
                                                @else
                                                    @if (Utility::getsettings('blog_setting_enable') == 'on')
                                                        <li class="mobile-item has-children">
                                                            <a href="{{ url('/') }}/#blogs" tabindex="0">
                                                                {{ __('Blogs') }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="menu-right-col">
                                        <ul class="d-flex align-items-center">
                                            <li class="switch-toggle" onclick="myFunction()">
                                                <a class="switch-sun d-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="26"
                                                        height="26" viewBox="0 0 26 26" fill="none">
                                                        <path
                                                            d="M13 18C15.7614 18 18 15.7614 18 13C18 10.2386 15.7614 8 13 8C10.2386 8 8 10.2386 8 13C8 15.7614 10.2386 18 13 18Z"
                                                            stroke="black" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M13 3V1" stroke="black" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M13 25V23" stroke="black" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M3 13H1" stroke="black" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M25 13H23" stroke="black" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M5.92977 20.07L4.50977 21.49" stroke="black"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M21.4903 4.51001L20.0703 5.93001" stroke="black"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M20.0703 20.07L21.4903 21.49" stroke="black"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M4.50977 4.51001L5.92977 5.93001" stroke="black"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <a class="switch-moon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="512"
                                                        viewBox="0 0 512 512" width="512">
                                                        <title />
                                                        <path
                                                            d="M152.62,126.77c0-33,4.85-66.35,17.23-94.77C87.54,67.83,32,151.89,32,247.38,32,375.85,136.15,480,264.62,480c95.49,0,179.55-55.54,215.38-137.85-28.42,12.38-61.8,17.23-94.77,17.23C256.76,359.38,152.62,255.24,152.62,126.77Z" />
                                                    </svg>
                                                </a>
                                            </li>
                                            @yield('auth-topbar')
                                            <li class="mobile-menu">
                                                <button class="mobile-menu-button" id="menu">
                                                    <div class="one"></div>
                                                    <div class="two"></div>
                                                    <div class="three"></div>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- Mobile menu start here -->
                <div class="container">
                    <div class="mobile-menu-wrapper">
                        <div class="mobile-menu-bar">
                            <ul>
                                <li>
                                    <a href="{{ route('landingpage') }}" tabindex="0">{{ __('Home') }}</a>
                                </li>
                                @php
                                    $header_main_menus = App\Models\HeaderSetting::get();
                                @endphp
                                @if (!empty($header_main_menus))
                                    @foreach ($header_main_menus as $header_main_menu)
                                        <li class="mobile-item has-children">
                                            @php
                                                $page = App\Models\PageSetting::find($header_main_menu->page_id);
                                            @endphp
                                            <a tabindex="0"
                                                @if ($page->type == 'link') ?  href="{{ $page->page_url }}"  @else  href="{{ route('description.page', $header_main_menu->slug) }}" @endif
                                                class="nav-label d-flex align-items-center">
                                                {{ $page->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                @if (Utility::getsettings('feature_setting_enable') == 'on')
                                    <li>
                                        <a href="{{ url('/') }}/#features"
                                            tabindex="0">{{ __('Features') }}</a>
                                    </li>
                                @endif
                                @if (Utility::getsettings('menu_setting_section1_enable') == 'on' ||
                                        Utility::getsettings('menu_setting_section2_enable') == 'on' ||
                                        Utility::getsettings('menu_setting_section3_enable') == 'on')
                                    <li class="mobile-item has-children">
                                        <a tabindex="0" href="javascript:;"
                                            class="nav-label d-flex align-items-center">
                                            {{ __('Menu Section') }}
                                            <svg class="menu-open-arrow" xmlns="http://www.w3.org/2000/svg"
                                                width="7" height="5" viewBox="0 0 7 5" fill="none">
                                                <path
                                                    d="M6.76521 0H0.234792C0.0389193 0 -0.0704512 0.217432 0.0508507 0.365871L3.31606 4.34654C3.40952 4.46049 3.58948 4.46049 3.68394 4.34654L6.94915 0.365871C7.07045 0.217432 6.96108 0 6.76521 0Z"
                                                    fill="#1F1F1F" />
                                            </svg>
                                            <svg class="close-menu-icon" xmlns="http://www.w3.org/2000/svg"
                                                height="48" viewBox="0 0 48 48" width="48">
                                                <path
                                                    d="M38 12.83l-2.83-2.83-11.17 11.17-11.17-11.17-2.83 2.83 11.17 11.17-11.17 11.17 2.83 2.83 11.17-11.17 11.17 11.17 2.83-2.83-11.17-11.17z" />
                                                <path d="M0 0h48v48h-48z" fill="none" />
                                            </svg>
                                        </a>
                                        <div class="menu-dropdown mobile-menu-inner nav-list menu-gap">
                                            <ul>
                                                @if (Utility::getsettings('menu_setting_section1_enable') == 'on')
                                                    <li><a href="{{ url('/') }}/#menu_section_1"
                                                            tabindex="0">{{ __('Section 1') }}</a></li>
                                                @endif
                                                @if (Utility::getsettings('menu_setting_section2_enable') == 'on')
                                                    <li><a href="{{ url('/') }}/#menu_section_2"
                                                            tabindex="0">{{ __('Section 2') }}</a></li>
                                                @endif
                                                @if (Utility::getsettings('menu_setting_section3_enable') == 'on')
                                                    <li><a href="{{ url('/') }}/#menu_section_3"
                                                            tabindex="0">{{ __('Section 3') }}</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                                @if (Utility::getsettings('business_growth_setting_enable') == 'on')
                                    <li>
                                        <a href="{{ url('/') }}/#business_growth"
                                            tabindex="0">{{ __('Business Growth') }}</a>
                                    </li>
                                @endif
                                @if (Utility::getsettings('testimonial_setting_enable') == 'on')
                                    <li>
                                        <a href="{{ url('/') }}/#testimonials"
                                            tabindex="0">{{ __('Testimonials') }}</a>
                                    </li>
                                @endif
                                @if (Utility::getsettings('faq_setting_enable') == 'on')
                                    <li>
                                        <a href="{{ url('/') }}/#faqs" tabindex="0">{{ __('FAQs') }}</a>
                                    </li>
                                @endif
                                @if (tenant('id') == null)
                                    @if (Utility::getsettings('plan_setting_enable') == 'on')
                                        <li>
                                            <a href="{{ url('/') }}/#plans"
                                                tabindex="0">{{ __('Pricing') }}</a>
                                        </li>
                                    @endif
                                @else
                                    @if (Utility::getsettings('blog_setting_enable') == 'on')
                                        <li class="mobile-item has-children">
                                            <a href="{{ url('/') }}/#blogs" tabindex="0">
                                                {{ __('Blogs') }}
                                            </a>
                                        </li>
                                    @endif
                                @endif
                                @if (Utility::getsettings('start_view_setting_enable') == 'on')
                                    <li>
                                        <a href="{{ url('/') }}/#start_view"
                                            tabindex="0">{{ __('Start View') }}</a>
                                    </li>
                                @endif
                                @yield('auth-topbar')
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Mobile menu end here -->
            </header>

            <div class="login-page-wrapper">
                <div class="login-container">
                    <div class="login-row d-flex">
                        <div class="login-col-6">
                            @yield('content')
                        </div>
                        <div class="login-media-col">
                            <div class="login-media-inner">
                                @if (tenant('id') == null)
                                    <img
                                        src="{{ Utility::getsettings('login_image')
                                            ? Storage::url(Utility::getsettings('login_image'))
                                            : asset('vendor/landing-page2/image/img-auth-3.svg') }}">
                                @else
                                    <img
                                        src="{{ Utility::getsettings('login_image')
                                            ? Storage::url(tenant('id') . '/' . Utility::getsettings('login_image'))
                                            : asset('vendor/landing-page2/image/img-auth-3.svg') }}">
                                @endif
                                <h3>
                                    {{ Utility::getsettings('login_name') ? Utility::getsettings('login_name') : __('“Attention is the new currency”') }}
                                </h3>
                                <p>
                                    {{ Utility::getsettings('login_detail')
                                        ? Utility::getsettings('login_detail')
                                        : __('The more effortless the writing looks, the more effort the writer actually put into the process.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="site-footer">
            <div class="container">
                <div class="footer-row">
                    <div class="footer-col footer-link">
                        <div class="footer-widget">
                            <div class="footer-logo">
                                <a href="{{ route('home') }}" tabindex="0">
                                    <img src="{{ Storage::url(Utility::getsettings('app_logo')) ? Utility::getpath('logo/app-logo.png') : asset('assets/images/app-logo.png') }}"
                                        alt="footer-logo" class="footer-light-logo">
                                    <img src="{{ Utility::getsettings('app_dark_logo') ? Utility::getpath('logo/app-dark-logo.png') : asset('assets/images/app-dark-logo.png') }}"
                                        alt="footer-logo" class="footer-dark-logo">
                                </a>
                            </div>
                            <p>{{ Utility::getsettings('footer_description')
                                ? Utility::getsettings('footer_description')
                                : 'A feature is a unique quality or characteristic that something has. Real-life examples: Elaborately colored tail feathers are peacocks most well-known feature.' }}
                            </p>
                        </div>
                    </div>
                    @php
                        $footerMainMenus = App\Models\FooterSetting::where('parent_id', 0)->get();
                    @endphp
                    @if (!empty($footerMainMenus))
                        @foreach ($footerMainMenus as $footerMainMenu)
                            <div class="footer-col">
                                <div class="footer-widget">
                                    <h3>{{ $footerMainMenu->menu }}</h3>
                                    @php
                                        $sub_menus = App\Models\FooterSetting::where(
                                            'parent_id',
                                            $footerMainMenu->id,
                                        )->get();
                                    @endphp
                                    <ul>
                                        @foreach ($sub_menus as $sub_menu)
                                            @php
                                                $page = App\Models\PageSetting::find($sub_menu->page_id);
                                            @endphp
                                            <li>
                                                <a @if ($page->type == 'link') ?  href="{{ $page->page_url }}"  @else  href="{{ route('description.page', $sub_menu->slug) }}" @endif
                                                    tabindex="0">{{ $page->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <p> {{ __('© 2023 Full Multi Tenancy Laravel Admin Saas') }} </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    @endif
</body>

<script src="{{ asset('vendor/landing-page2/js/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/landing-page2/js/slick.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('vendor/notifier/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bouncer.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/form-validation.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('vendor/landing-page2/js/custom.js') }}"></script>
<script>
    function myFunction() {
        const element = document.body;
        element.classList.toggle("dark-mode");
        const isDarkMode = element.classList.contains("dark-mode");
        const expirationDate = new Date();
        expirationDate.setDate(expirationDate.getDate() + 30);
        document.cookie = `mode=${isDarkMode ? "dark" : "light"}; expires=${expirationDate.toUTCString()}; path=/`;
        if (isDarkMode) {
            $('.switch-toggle').find('.switch-moon').addClass('d-none');
            $('.switch-toggle').find('.switch-sun').removeClass('d-none');
        } else {
            $('.switch-toggle').find('.switch-sun').addClass('d-none');
            $('.switch-toggle').find('.switch-moon').removeClass('d-none');
        }
    }
    window.addEventListener("DOMContentLoaded", () => {
        const modeCookie = document.cookie.split(";").find(cookie => cookie.includes("mode="));
        if (modeCookie) {
            const mode = modeCookie.split("=")[1];
            if (mode === "dark") {
                $('.switch-toggle').find('.switch-moon').addClass('d-none');
                $('.switch-toggle').find('.switch-sun').removeClass('d-none');
                document.body.classList.add("dark-mode");
            } else {
                $('.switch-toggle').find('.switch-sun').addClass('d-none');
                $('.switch-toggle').find('.switch-moon').removeClass('d-none');
            }
        }
    });
</script>

@include('layouts.includes.alerts')
@stack('javascript')

</body>

@if (Utility::getsettings('cookie_setting_enable') == 'on')
    @include('layouts.cookie_consent')
@endif

</html>
