<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta name="keywords"
        content="{{ !empty(Utility::getsettings('meta_keywords'))
            ? Utility::getsettings('meta_keywords')
            : 'Full Multi Tenancy Laravel Admin Saas,Multi Domains,Multi Databases' }}">
    <meta name="description"
        content="{{ !empty(Utility::getsettings('meta_description'))
            ? Utility::getsettings('meta_description')
            : 'Discover the efficiency of Full Multi Tenancy, a user-friendly web application by Quebix Apps.' }}">

    <title>TuneUp</title>
    <link rel="icon"
        href="{{ Utility::getsettings('favicon_logo') ? Utility::getpath('logo/app-favicon-logo.png') : asset('assets/images/app-favicon-logo.png') }}"
        type="image/png">

    @if (Utility::getsettings('seo_setting') == 'on')
        {!! app('seotools')->generate() !!}
    @endif

    {{-- <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/landingpage-2.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/globle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/media.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/landingpage2-responsive.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('vendor/landing-page2/css/custom.css') }}">
    {{-- Front Payment Coupon Checkbox --}}
     <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
        integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
        <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    #hero-top {
        font-size: 22px;
        padding: 30px 0px 20px 90px;
        font-weight: 700;
    }

    #hero-heading {
        width: 665px;
        padding: 0px 0px 20px 90px;
        font-size: 54px;
        font-weight: 700;
        line-height: 70px;
    }

    #hero-para {
        padding: 0px 0px 20px 90px;
        width: 470px;
        font-weight: 500;
        font-size: 18px;
    }

    #hero-btn {
        height: 45px;
        width: 200px;
        margin: 10px 0px 100px 100px;
        color: #3d42df;
        font-size: 18px;
        font-weight: 700;
    }

    #last-section {
        background-color: #0033a1;
    }

    .your {
        color: #0071ce;
    }

    .iphone-15 {
        margin-bottom: 100px !important;
    }

    .features-empower {
        font-size: 18px !important;
        max-width: 500px;
        line-height: 32px;
    }

    .features {
        max-width: 650px;
        font-size: 21px;
        line-height: 37px;
    }

    .golf-with {
        font-size: 18px;
    }

    .tool {
        max-width: 500px;
    }

    .lesson {
        max-width: 629px;
    }

    .last-but {
        background-color: #576974;
    }

    /* Add media queries for smaller screens */
    @media (max-width: 767px) {

        #hero-top,
        #hero-heading,
        #hero-para {
            width: 300px;
            padding-left: 15px;
            /* padding-right: 15px; */
            margin: 0px;
        }

        #hero-para {
            font-size: 17.5px;
            margin-top: 0px;
        }

        #hero-top {
            font-size: 18px;
        }

        #hero-heading {
            font-size: 33px;
            line-height: 40px;
        }

        #hero-btn {
            width: 150px;
            margin: 10px 0 30px 30px;
        }

        .management {
            flex-direction: column;
        }

        .iphone,
        .iphone-15 {
            max-height: 350px;
        }

        .lesson {
            max-width: 100%;
            margin-top: 20px;
        }

        .access-wrapper {
            height: 450px;
            padding: 10px;
            align-items: baseline;
            padding-top: 40px;
            margin-top: 20px;
        }
    }
</style>
</head>


@stack('css')

<body class="theme-2">


    @yield('content')


    <script src="{{ asset('vendor/landing-page2/js/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/landing-page2/js/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bouncer.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/form-validation.js') }}"></script>
    <script src="{{ asset('vendor/notifier/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('vendor/landing-page2/js/custom.js') }}"></script>
    {{-- tostr notification close --}}
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>

    @include('layouts.includes.alerts')
    @stack('javascript')
</body>
@if (Utility::getsettings('cookie_setting_enable') == 'on')
    @include('layouts.cookie_consent')
@endif

</html>