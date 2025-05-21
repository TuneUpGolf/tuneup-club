@php
    $languages = \App\Facades\UtilityFacades::languages();
@endphp
@extends('layouts.main-landing')
@section('title', __('FAQs'))
@section('auth-topbar')
    <li class="language-btn">
        <select class="nice-select"
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"
            id="language">
            @foreach ($languages as $language)
                <option class="" @if ($lang == $language) selected focus @endif
                    value="{{ route('change.lang', [$language]) }}">
                    {{ Str::upper($language) }}
                </option>
            @endforeach
        </select>
    </li>
@endsection
@section('content')
    <main class="blog-wrapper">
        <section class="blog-page-banner"
            style="background-image: url({{ tenant('id') == null
                ? (Utility::getsettings('background_image')
                    ? Storage::url(Utility::getsettings('background_image'))
                    : asset('vendor/landing-page2/image/blog-banner-image.png'))
                : (Utility::getsettings('background_image')
                    ? Storage::url(tenant('id') . '/' . Utility::getsettings('background_image'))
                    : asset('vendor/landing-page2/image/blog-banner-image.png')) }});"
            width="100% " height="100%">
            <div class="container">
                <div class="common-banner-content">
                    <div class="section-title">
                        <h2>{{ __('Faqs') }}</h2>
                    </div>
                    <ul class="back-cat-btn d-flex align-items-center justify-content-center">
                        <li><a href="{{ route('landingpage') }}">{{ __('Home') }}</a>
                            <span>/</span>
                        </li>
                        <li><a href="javascript:void(0);">{{ __('Faqs') }}</a></li>
                    </ul>
                </div>
            </div>
        </section>
        <section class="home-faqs-sec pt pb ">
            <div class="container">
                <div class="faqs-right-div">
                    @foreach ($faqs as $faq)
                        <div class="set has-children">
                            <a href="javascript:void(0)" class="nav-label">
                                <span>{{ $faq->quetion }}</span>
                            </a>
                            <div class="nav-list">
                                <p>{!! $faq->answer !!}</p>
                            </div>
                        </div>
                    @endforeach
                    <img src="{{ asset('assets/images/landingpage-2-image/test-bg.png') }}" alt="bacground-image"
                        class="faqs-bg">
                </div>
            </div>
        </section>
    </main>
@endsection
