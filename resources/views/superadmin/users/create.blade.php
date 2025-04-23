@extends('layouts.main')
@section('title', __('Create Admin'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Golf Courses') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Golf Course') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            @if (tenant('id') == null)
                @if ($databasePermission == null)
                    <div class="alert alert-warning">
                        {{ __('Please on your database permission to create auto generate DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('On database permission') }}</a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        {{ __('Please off your database permission to create your own DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('Off database permission') }}</a>
                    </div>
                @endif
            @endif
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Create Golf Course') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'users.store',
                            'method' => 'Post',
                            'data-validate',
                            'files' => 'true',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                    {!! Form::text('name', null, ['class' => 'form-control', ' required', 'placeholder' => __('Enter name')]) !!}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                    {!! Form::text('email', null, [
                                        'class' => 'form-control',
                                        ' required',
                                        'placeholder' => __('Enter email address'),
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                                    <input id="phone" name="phone" type="tel" class="form-control"
                                        placeholder="{{ __('Enter phone') }}" required>
                                    {!! Form::hidden('country_code', null, []) !!}
                                    {!! Form::hidden('dial_code', null, []) !!}
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group">
                                    {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                                    {!! Form::password('password', ['class' => 'form-control', ' required', 'placeholder' => __('Enter password')]) !!}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('confirm-password', __('Confirm Password'), ['class' => 'form-label']) }}
                                    {{ Form::password('confirm-password', ['class' => 'form-control', ' required', 'placeholder' => __('Enter confirm password')]) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('logo', _('Logo'), ['class' => 'form-label']) }}
                                    {{ Form::file('logo', ['class' => 'form-control', 'required', 'accept' => 'image/jpeg, image/png']) }}
                                </div>
                            </div>
                            <div class="alert alert-info" style="display:none;">
                                <ul>
                                    <li>{{ __('If you want to run your website in localhost then it is necessary to be a vhost,
                                                                        because of
                                                                        tenancy-based software it is necessary to create a vhost.') }}
                                    </li>
                                    <li class="text-danger">
                                        {{ __('If you give incorrect website host,then 404 error will be
                                                                            shown throughout the
                                                                            whole
                                                                            website') }}
                                    </li>
                                    <li> {{ __('if your website URL is') }} <span
                                            class="text-danger">{{ __('https://example.com/') }}</span>
                                        {{ __(',then host will be') }}
                                        <span class="text-danger">{{ __('example.com') }}</span>
                                    </li>
                                    <li> {{ __('if your website URL is') }} <span
                                            class="text-danger">{{ __('https://subdomain.example.com/') }}</span>
                                        {{ __(',then host
                                                                                will
                                                                                be') }}
                                        <span class="text-danger">{{ __('subdomain.example.com') }}</span>
                                    </li>
                                </ul>
                            </div>
                            <h5 class="mt-5">{{ __('Create Domain and Database') }}</h5>
                            <hr>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        {{ Form::label('domains', __('Domain Configration'), ['class' => 'form-label']) }}
                                        @if (Utility::getsettings('domain_config') == 'on')
                                            <div class="input-group">
                                                {!! Form::text('domains', null, [
                                                    'class' => 'form-control',
                                                    'id' => 'domains',
                                                    'required',
                                                    'placeholder' => __('Enter domain name'),
                                                ]) !!}
                                                <span
                                                    class="input-group-text">{{ '.' . parse_url(env('APP_URL'), PHP_URL_HOST) }}</span>
                                            </div>
                                        @else
                                            {!! Form::text('domains', null, [
                                                'class' => 'form-control',
                                                'required',
                                                'id' => 'domains',
                                                'placeholder' => __('Enter domain name'),
                                            ]) !!}
                                        @endif
                                        <div class="error-message" id="bouncer-error_domains" style="display:none;"></div>
                                        <span>
                                            {{ __('Note: how to add-on domain in your hosting panel.') }}
                                            <a href="https://demo.quebixtechnology.com/document/full-tenancy/"
                                                class="m-2" target="_blank">
                                                {{ __('Document') }}
                                            </a>
                                        </span>
                                    </div>
                                    @if ($databasePermission == null)
                                        <div class="form-group">
                                            {{ Form::label('db_name', __('Database Name'), ['class' => 'form-label']) }}
                                            {!! Form::text('db_name', null, [
                                                'class' => 'form-control',
                                                ' required',
                                                'placeholder' => __('Enter database name'),
                                            ]) !!}
                                        </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        {{ Form::label('db_username', __('Database User'), ['class' => 'form-label']) }}
                                        {!! Form::text('db_username', null, [
                                            'class' => 'form-control',
                                            ' required',
                                            'placeholder' => __('Enter database username'),
                                        ]) !!}
                                    </div>
                                    <div class="form-group ">
                                        {{ Form::label('db_password', __('Database Password'), ['class' => 'form-label']) }}
                                        <div class="input-group-prepend">
                                        </div>
                                        {!! Form::password('db_password', [
                                            'class' => 'form-control',
                                            ' required',
                                            'placeholder' => __('Enter database password'),
                                        ]) !!}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="float-end">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
        </section>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
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
    </script>
@endpush
