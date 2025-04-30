@php
    $languages = \App\Facades\UtilityFacades::languages();
    config([
        'captcha.sitekey' => Utility::getsettings('recaptcha_key'),
        'captcha.secret' => Utility::getsettings('recaptcha_secret'),
    ]);
    $user = Auth::user();
@endphp
@extends('layouts.app')
@section('title', __('Sign Up'))
@section('content')
    <section>
        <div class="login-container-new">
            <div class="login-design w-100 h-100">
                <div class="login-design-header">
                    <svg width="103" height="73" viewBox="0 0 103 73" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.1597 58.3703H0V54.9302H14.0487V58.3703H8.88998V71.8862H5.1597V58.3703Z" fill="white" />
                        <path
                            d="M16.6416 64.6197V54.9312H20.3719V64.5237C20.3719 67.2845 21.7528 68.7139 24.0294 68.7139C26.3059 68.7139 27.6879 67.333 27.6879 64.645V54.9322H31.4171V64.4994C31.4171 69.6349 28.535 72.1531 23.9818 72.1531C19.4287 72.1531 16.6426 69.6096 16.6426 64.6208L16.6416 64.6197Z"
                            fill="white" />
                        <path
                            d="M35.2217 54.9312H38.6618L46.6066 65.3709V54.9312H50.2894V71.8862H47.1161L38.9055 61.1078V71.8862H35.2227V54.9312H35.2217Z"
                            fill="white" />
                        <path
                            d="M54.3594 54.9312H67.1495V58.25H58.0664V61.6891H66.0597V65.0079H58.0664V68.5684H67.2708V71.8872H54.3604V54.9322L54.3594 54.9312Z"
                            fill="white" />
                        <path
                            d="M70.3242 64.6197V54.9312H74.0545V64.5237C74.0545 67.2845 75.4354 68.7139 77.712 68.7139C79.9886 68.7139 81.3705 67.333 81.3705 64.645V54.9322H85.0997V64.4994C85.0997 69.6349 82.2176 72.1531 77.6645 72.1531C73.1113 72.1531 70.3252 69.6096 70.3252 64.6208L70.3242 64.6197Z"
                            fill="white" />
                        <path
                            d="M88.9053 54.9312H95.8331C99.8787 54.9312 102.324 57.329 102.324 60.7934V60.842C102.324 64.7663 99.2732 66.8003 95.4691 66.8003H92.6355V71.8872H88.9053V54.9322V54.9312ZM95.5915 63.4815C97.4556 63.4815 98.5464 62.3674 98.5464 60.9137V60.8652C98.5464 59.1942 97.3838 58.2975 95.5187 58.2975H92.6355V63.4815H95.5915Z"
                            fill="white" />
                        <path d="M29.3809 0.0742188V12.6631H35.6131L48.2021 0.0742188H29.3809Z" fill="white" />
                        <path d="M54.0361 0.0742188L66.6261 12.6631H72.9412V0.0742188H54.0361Z" fill="white" />
                        <path
                            d="M59.9489 14.7061V32.3425H42.2579V30.3004H42.27V14.7061H29.3809V32.024C29.3809 39.2419 34.9692 45.0931 41.8626 45.0931H60.4594C67.3528 45.0931 72.9411 39.2419 72.9411 32.024V14.7061H59.9489Z"
                            fill="white" />
                        <path
                            d="M38.5352 12.6629H44.3125H44.3206V30.2993H57.9063V14.706V12.6629H57.9174H63.7029L51.119 0.0800781L38.5352 12.6629Z"
                            fill="white" />
                    </svg>
                    <h2 class="text-white font-semibold">Welcome to Tune Up</h2>
                </div>
                <div class="img-container-login">
                    <img src="{{ asset('assets/images/iPhone.png') }}" alt="Iphone" class="login-img">
                    <div class="svg-4">
                        <svg width="363" height="404" viewBox="0 0 363 404" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="21.6416" cy="365.14" r="38.6396" fill="#FED530" />
                            <path d="M133.281 215.069C133.281 158.736 178.948 113.069 235.281 113.069" stroke="#5EAFEB"
                                stroke-width="80" />
                            <path d="M25.2813 206.069C25.2813 97.821 113.033 10.0688 221.281 10.0688" stroke="#5EAFEB"
                                stroke-width="20" />
                            <path
                                d="M60.2812 192.069C60.2812 274.912 127.439 342.069 210.281 342.069C293.124 342.069 360.281 274.912 360.281 192.069C360.281 109.226 293.124 42.0689 210.281 42.0689"
                                stroke="#5EAFEB" stroke-width="4" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="login-desing-footer">
                    <h2>Master Your Swing, One Lesson at a Time</h2>
                    <p>Discover the perfect golf instructor tailored to your needs on our dedicated platform.
                        With an array
                        of experts at your fingertips, elevate your golf game seamlessly. Start today and embrace the
                        journey towards your best swing.</p>
                </div>
                <div class="svg-1">
                    <svg width="99" height="100" viewBox="0 0 99 100" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <circle cx="49.4692" cy="50.3687" r="49.4409" fill="#5EAFEB" />
                    </svg>
                </div>
                <div class="svg-2">
                    <svg width="72" height="51" viewBox="0 0 72 51" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M36.7914 -0.303051L54.7371 -18.2488L68.907 -4.07891L50.9613 13.8668L49.9006 14.9275L50.9613 15.9882L68.907 33.9339L54.7371 48.1038L36.7914 30.158L35.7307 29.0974L34.6701 30.158L16.7243 48.1038L2.55443 33.9339L20.5002 15.9882L21.5608 14.9275L20.5002 13.8668L2.55443 -4.07891L16.7243 -18.2488L34.6701 -0.303051L35.7307 0.75761L36.7914 -0.303051Z"
                            stroke="#FED530" stroke-width="3" />
                    </svg>
                </div>
                <div class="svg-3">
                    <svg width="239" height="86" viewBox="0 0 239 86" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <circle cx="119.376" cy="119.878" r="103.838" stroke="url(#paint0_linear_172_1393)"
                            stroke-opacity="0.9" stroke-width="31" />
                        <defs>
                            <linearGradient id="paint0_linear_172_1393" x1="224.242" y1="85.2182" x2="0.0380844"
                                y2="92.1642" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#5EAFEB" />
                                <stop offset="1" stop-color="#5EAFEB" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
            <div class="sign-in-container">
                <h2 c>Sign Up</h2>
                <div class="form-container-signin">
                    {{ Form::open(['route' => ['register'], 'method' => 'POST', 'data-validate', 'class' => 'needs-validation']) }}
                    @csrf
                    <div class="form-group">
                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                        {!! Form::text('name', old('name'), [
                            'class' => 'form-control-new',
                            'placeholder' => __('Enter name'),
                            'required',
                            'id' => 'name',
                            'autocomplete' => 'name',
                            'autofocus',
                        ]) !!}
                    </div>
                    <div class="form-group">
                        {{ Form::label('email', __('Email'), ['class' => 'form-label mb-2']) }}
                        {!! Form::email('email', old('email'), [
                            'class' => 'form-control-new',
                            'id' => 'email',
                            'placeholder' => __('Enter email address'),
                            'onfocus',
                            'required',
                        ]) !!}
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">{{ __('Password') }}</label>

                        <input id="password" type="password" class="form-control-new"
                            placeholder="{{ __('Enter password') }}" name="password" tabindex="2" required
                            autocomplete="current-password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('Password Confirmation') }}</label>
                        <input id="password-confirm" type="password" class="form-control-new" name="password_confirmation"
                            required autocomplete="new-password" placeholder="{{ __('Enter password confirmation') }}">
                    </div>
                    <div class="form-group">
                        {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                        <input id="phone" name="phone" type="tel" class="form-control"
                            placeholder="{{ __('Enter phone') }}" required>
                        {!! Form::hidden('country_code', null, []) !!}
                        {!! Form::hidden('dial_code', null, []) !!}
                    </div>

                    <div class="form-check check-box">
                        <label class="switch">
                            <input type="checkbox" name="terms" id="flexCheckChecked">
                            <span class="slider round"></span>
                        </label>
                        <label for="flexCheckChecked" class="form-label lbl-check-box">{{ __('I accept the ') }}
                            <a href="{{ route('terms.and.conditions') }}">
                                {{ __('Terms & conditions') }}
                            </a>
                        </label>
                    </div>


                    {{ Form::button(__('Sign Up'), ['type' => 'submit', 'class' => 'lesson-btn']) }}

                    {{ Form::close() }}
                    <div class='signup-text'> Already Registered?
                        <a href="{{ route('login') }}">SignIn</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('javascript')
    {{-- <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script>
        $("#phone").intlTelInput({
            geoIpLookup: function(callback) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            initialCountry: "auto",
            separateDialCode: true,
        });
        $('#phone').on('countrychange', function(e) {
            $(this).val('');
            var selectedCountry = $(this).intlTelInput('getSelectedCountryData');
            var dialCode = selectedCountry.dialCode;
            var maskNumber = intlTelInputUtils.getExampleNumber(selectedCountry.iso2, 0, 0);
            maskNumber = intlTelInputUtils.formatNumber(maskNumber, selectedCountry.iso2, 2);
            maskNumber = maskNumber.replace('+' + dialCode + ' ', '');
            mask = maskNumber.replace(/[0-9+]/ig, '0');
            $('input[name="country_code"]').val(selectedCountry.iso2);
            $('input[name="dial_code"]').val(dialCode);
            $('#phone').mask(mask, {
                placeholder: maskNumber
            });
        });
    </script> --}}
@endpush
