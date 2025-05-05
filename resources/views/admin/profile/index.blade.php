@extends('layouts.main')
@section('title', __('Profile'))
@section('breadcrumb')
<li class="breadcrumb-item">
    {!! Html::link(route('home'), __('Dashboard'), ['']) !!}
</li>
<li class="breadcrumb-item">{{ __('Profile') }}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-xl-3">
        <div class="card sticky-top">
            <div class="list-group list-group-flush" id="useradd-sidenav">
                <a href="#useradd-1" class="border-0 list-group-item list-group-item-action active">{{ __('Profile') }}
                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                </a>
                <a href="#useradd-2" class="border-0 list-group-item list-group-item-action">{{ __('Basic Info') }}
                    <div
                        class="float-end"><i class="ti ti-chevron-right"></i></div>
                </a>
                @if(Auth::user()->type == 'Influencer')
                    <a href="#useradd-7" class="border-0 list-group-item list-group-item-action">{{ __('Social Media Links') }}
                        <div
                            class="float-end"><i class="ti ti-chevron-right"></i></div>
                    </a>
                    <a href="#useradd-4" class="border-0 list-group-item list-group-item-action">{{ __('Banner Image') }}
                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                    </a>
                @endif
                <a href="#useradd-3" class="border-0 list-group-item list-group-item-action">{{ __('Update Login') }}
                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                </a>
                @if (Utility::getsettings('2fa'))
                    <a href="#useradd-4" class="border-0 list-group-item list-group-item-action">{{ __('2FA') }}
                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                    </a>
                    @endif
                    @if (Auth::user()->type == 'Influencer')
                    <a href="#useradd-6"
                        class="border-0 list-group-item list-group-item-action">{{ __('Stripe Connect') }}
                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                    </a>
                @endif
                <a href="#useradd-5" class="border-0 list-group-item list-group-item-action">{{ __('Delete Account') }}
                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xl-9">
        <div id="useradd-1" class="text-white card bg-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar me-3">
                        <img src="{{ $user->avatar ?? $user->dp }}" class="img-user wid-80 rounded-circle">
                    </div>
                    <div class="d-block d-sm-flex align-items-center justify-content-between w-100">
                        <div class="mb-3 mb-sm-0">
                            <h4 class="mb-1 text-white">{{ $user->name }}</h4>
                            <p class="mb-0 text-sm">{{ $user->email }}</p>
                            <p class="mb-0 text-sm">{{ $user->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="useradd-2" class="card">
            <div class="card-header">
                <h5>{{ __('Basic info') }}</h5>
            </div>
            {{ Form::open(['route' => 'profile.update.basicinfo', 'method' => 'Post', 'class' => 'form-horizontal', 'data-validate']) }}
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('name', $user->name, [
                            'class' => 'form-control',
                            'placeholder' => __('Name'),
                            'required',
                            ]) !!}
                            @if ($errors->has('name'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                            {!! Form::hidden('country_code', $user->country_code, []) !!}
                            {!! Form::hidden('dial_code', $user->dial_code, []) !!}
                            <input id="phone" name="phone" class="form-control"
                                value="+{{ $user->dial_code . '0' . $user->phone }}" type="tel"
                                placeholder="{{ __('Enter phone') }}" required>
                        </div>
                    </div>
                    @if (Auth::user()->type == 'Influencer')
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                            <select class="form-control form-control-inline-block" data-trigger name="country"
                                id="country">
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach ($countries as $val)
                                <option value="{{ $val['name'] }}"
                                    {{ $user->country == $val['name'] ? 'selected' : '' }}>
                                    {{ $val['name'] }}
                                </option>
                                @endforeach
                            </select>
                            @if ($errors->has('country'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('country') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if (Auth::user()->type == 'Influencer')
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                            {!! Form::text('address', $user->address, [
                            'class' => 'form-control',
                            'id' => 'address',
                            'placeholder' => __('Address'),
                            'required',
                            ]) !!}
                            @if ($errors->has('address'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('address') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('bio', __('Bio'), ['class' => 'form-label']) }}
                            {!! Form::textarea('bio', $user->bio, [
                            'class' => 'form-control',
                            'id' => 'bio',
                            'placeholder' => __('bio'),
                            'required',
                            ]) !!}
                            @if ($errors->has('bio'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('bio') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {{ Form::label('role', __('Role'), ['class' => 'form-label']) }}
                            {!! Form::text('role', $role ? $role->name : 'Role Not Set', [
                            'class' => 'form-control',
                            'readonly',
                            ]) !!}
                        </div>
                        @if (Auth::user()->type == 'Influencer')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{ Form::label('sub_price', __('Subscription Price ($)'), ['class' => 'form-label']) }}
                                    {!! Form::number('sub_price', $user->sub_price, [
                                    'class' => 'form-control',
                                    'id' => 'sub_price',
                                    'placeholder' => __('Subscription Price'),
                                    'required',
                                    ]) !!}
                                    @if ($errors->has('sub_price'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('sub_price') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if (Auth::user()->type == 'Influencer')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{ Form::label('stripe_account_id', __('Stripe Account Id'), ['class' => 'form-label']) }}
                                    {!! Form::text('stripe_account_id', $user->stripe_account_id, [
                                    'class' => 'form-control',
                                    'id' => 'stripe_account_id',
                                    'placeholder' => __('Stripe Account Id'),
                                    'disabled',
                                    ]) !!}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            {{ Form::label('push_token', __('Push Token'), ['class' => 'form-label']) }}
                            {!! Form::text('push_token', $user?->pushToken?->token ? $user->pushToken->token : 'Push Token Not Set', [
                            'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="mx-auto mb-0 avatar_crop btn btn-primary btn-lg d-block col-sm-12"
                                for="avatarCrop">
                                {{ __('Update Avatar') }}
                                {{ Form::file('file', ['id' => 'avatarCrop', 'class' => 'd-none', 'accept' => 'image/jpeg, image/png']) }}
                            </label>
                        </div>
                        <div id="avatar-updater" class="col-xs-12 d-none">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="image-preview"></div>
                                </div>
                                <div class="col-md-12">
                                    {!! Form::text('avatar-url', route('update.avatar'), [
                                    'class' => 'd-none',
                                    ]) !!}
                                    {{ Form::button(__('Rotate Image'), ['id' => 'rotate-image', 'class' => 'btn btn-gradient-info col-sm-12 mb-1']) }}
                                    {{ Form::button(__('Crop Image'), ['id' => 'crop_image', 'class' => 'btn btn-gradient-primary col-sm-12']) }}
                                    {{ Form::button(__('Cancel'), ['id' => 'avatar-cancel-btn', 'class' => 'btn btn-gradient-secondary col-sm-12 mt-1']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        @if(Auth::user()->type == 'Influencer')
            <div id="useradd-7" class="card">
                <div class="card-header">
                    <h5>{{ __('Social Media Links') }}</h5>
                </div>
                {{ Form::open(['route' => 'update.socialmedia', 'method' => 'Post', 'class' => 'form-horizontal', 'data-validate']) }}
                <div class="card-body">
                    <div class="row form-group">
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('name', __('Linkedin'), ['class' => 'form-label']) }}
                                {!! Form::text('linkedin', $user->social_url_ln, [
                                'class' => 'form-control',
                                'placeholder' => __('Linkedin'),
                                ]) !!}
                                @if ($errors->has('linkedin'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('linkedin') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('name', __('Facebook'), ['class' => 'form-label']) }}
                                {!! Form::text('facebook', $user->social_url_fb, [
                                'class' => 'form-control',
                                'placeholder' => __('facebook'),
                                ]) !!}
                                @if ($errors->has('Facebook'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('facebook') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('name', __('Instagram'), ['class' => 'form-label']) }}
                                {!! Form::text('instagram', $user->social_url_ig, [
                                'class' => 'form-control',
                                'placeholder' => __('Instagram'),
                                ]) !!}
                                @if ($errors->has('instagram'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('instagram') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('name', __('Twitter'), ['class' => 'form-label']) }}
                                {!! Form::text('twitter', $user->social_url_x, [
                                'class' => 'form-control',
                                'placeholder' => __('Twitter'),
                                ]) !!}
                                @if ($errors->has('twitter'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('twitter') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('name', __('YouTube'), ['class' => 'form-label']) }}
                                {!! Form::text('youtube', $user->social_url_yt, [
                                'class' => 'form-control',
                                'placeholder' => __('YouTube'),
                                ]) !!}
                                @if ($errors->has('youtube'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('youtube') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-end">
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <div id="useradd-4" class="card">
                <div class="card-header">
                    <h5>{{ __('Banner') }}</h5>
                    <small class="text-muted">{{ __('Upload banner image') }}</small>
                </div>
    
                {{ Form::open(['route' => 'update.banner.image', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal', 'data-validate']) }}
                <div class="card-body">
                    <div class="row form-group">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('Banner Image', __('Banner Image'), ['class' => 'col-form-label ms-4']) }}
                                <div class="pt-0 card-body">
                                    <div class="setting_card">
                                        <div class="logo-content">
                                            <a href="{{ !empty($user->banner_image) ? $user->banner_image : '#' }}" target="_blank">
    
                                                <img id="banner-preview"
                                                    src="{{ $user->banner_image }}"
                                                    alt="Banner Image"
                                                    style="max-height: 150px; width: auto; border-radius: 8px;">
                                            </a>
                                        </div>
                                        <div class="mt-4 choose-files">
                                            <label for="banner_image">
                                                <div class="bg-primary logo input-img-div">
                                                    <i class="px-1 ti ti-upload"></i>
                                                    {{ __('Choose file here') }}
                                                    <input type="file"
                                                        class="form-control file image-input"
                                                        accept="image/png, image/gif, image/jpeg, image/jpg"
                                                        id="banner_image"
                                                        name="banner_image"
                                                        onchange="document.getElementById('banner-preview').src = window.URL.createObjectURL(this.files[0])"
                                                        data-filename="banner_image">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="card-footer">
                    <div class="text-end">
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        @endif

        <div id="useradd-3" class="card">
            <div class="card-header">
                <h5>{{ __('Login Details') }}</h5>
                <small class="text-muted">{{ __('Login Informations') }}</small>
            </div>
            {{ Form::open(['route' => 'update.login.details', 'method' => 'Post', 'class' => 'form-horizontal', 'data-validate']) }}
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                            {!! Form::password('password', [
                            'class' => 'form-control',
                            'autocomplete' => 'off',
                            'placeholder' => __('Leave blank if you donot want to change'),
                            ]) !!}
                            @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('password_confirmation', __('Confirm password'), ['class' => 'form-label']) }}
                            {!! Form::password('password_confirmation', [
                            'class' => 'form-control',
                            'id' => 'password_confirmation',
                            'autocomplete' => 'off',
                            'placeholder' => __('Leave blank if you donot want to change'),
                            ]) !!}
                            @if ($errors->has('password_confirmation'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        @if (Utility::getsettings('2fa'))
        <div id="useradd-4" class="card">
            <div class="card-header">
                <h5>{{ __('Two-factor authentication') }}</h5>
            </div>
            <!--Google Two Factor Authentication card-->
            @if (empty(auth()->user()->loginSecurity))
            <!--=============Generate QRCode for Google 2FA Authentication=============-->
            <div class="card-body">
                <div class="p-0 row">
                    <div class="col-md-12">
                        <p>{{ __('To activate Two factor Authentication Generate QRCode') }} </p>
                    </div>
                    <div class="col-md-12">
                        {{ Form::open(['route' => ['generate2faSecret'], 'method' => 'Post']) }}
                        {{ Form::button(__('Activate 2fa'), ['type' => 'submit', 'class' => 'btn btn-primary  col-md-2 float-end']) }}
                        {!! Form::close() !!}
                    </div>
                    <div class="mt-3 col-md-12 collapse" id="collapseExample">
                        <hr>
                        <h3>
                            {{ __('Two Factor Authentication(2FA) Setup Instruction') }}
                        </h3>
                        <hr>
                        <div class="mt-4">
                            <h4>{{ __('Below is a step by step instruction on setting up Two Factor Authentication') }}
                            </h4>
                            <p><label>{{ __('Step 1') }}:</label>
                                {{ __('download') }}
                                <strong>{{ __('Google Authenticator App') }}</strong>
                                {{ __('Application for Andriod or iOS') }}
                            </p>
                            <p class="text-center">
                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en"
                                    target="_blank"
                                    class="btn btn-success">{{ __('Download for Andriod') }}<i
                                        class="ml-2 ti ti-brand-android fa-2x"></i></a>
                                <a href="https://apps.apple.com/us/app/google-authenticator/id388497605"
                                    target="_blank"
                                    class="ml-2 btn btn-dark">{{ __('Download for iPhones') }}<i
                                        class="ml-2 ti ti-brand-apple fa-2x"></i></a>
                            </p>
                            <p><label>{{ __('Step 2') }}:</label>
                                {{ __('Click on Generate Secret Key on the platform to generate a QRCode') }}
                            </p>
                            <p><label>{{ __('Step 3') }}:</label>
                                {{ __('Open the') }}
                                <strong>{{ __('Google Authenticator App') }}</strong>
                                {{ __('and clcik on') }}
                                <strong>{{ __('Begin') }}</strong>
                                {{ __('on the mobile app') }}
                            </p>
                            <p><label>{{ __('Step 4') }}:</label>
                                {{ __('After which click on') }}
                                <strong>{{ __('Scan a QRcode') }}</strong>
                            </p>
                            <p><label>{{ __('Step 5') }}:</label>
                                {{ __('Then scan the barcode on the platform') }}
                            </p>
                            <p><label>{{ __('Step 6') }}:</label>
                                {{ __('Enter the verification code generated on the platform and Enable 2FA') }}
                            </p>
                            <hr>
                            <p><label>{{ __('Note') }}:</label>
                                {{ __('To disable 2FA enter code from the Google Authenticator App and account password to disable 2FA') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!--=============Generate QRCode for Google 2FA Authentication=============-->
            @elseif(!auth()->user()->loginSecurity->google2fa_enable)
            <!--=============Enable Google 2FA Authentication=============-->
            {{ Form::open(['route' => ['enable2fa'], 'method' => 'Post', 'class' => 'form-horizontal', 'data-validate']) }}
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-md-12">
                        <p><strong>{{ __('Scan the QRcode with') }}
                                <dfn>{{ __('Google Authenticator App') }}</dfn>
                                {{ __('Enter the generated code below') }}</strong>
                        </p>
                    </div>
                    <div class="col-md-12">
                        @if (!extension_loaded('imagick'))
                        {!! $google2fa_url !!}
                        @else
                        <img src="{{ $google2fa_url }}" />
                        @endif
                    </div>
                    <div class="col-md-12">
                        <p>{{ __('To enable 2-Factor Authentication verify QRCode') }} </p>
                    </div>
                    <div class="col-sm-12">
                        {{ Form::label('secret', __('Verification code'), ['class' => 'form-label']) }}
                        {!! Form::password('secret', [
                        'class' => 'form-control',
                        'id' => 'code',
                        'placeholder' => __('Enter the verification code generated on the platform and Enable 2FA'),
                        'required',
                        ]) !!}
                        @if ($errors->has('verify-code'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('verify-code') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    {{ Form::button(__('Enable 2FA'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                </div>
            </div>
            {!! Form::close() !!}
            <!--=============Enable Google 2FA Authentication=============-->
            @elseif(auth()->user()->loginSecurity->google2fa_enable)
            <!--=============Disable Google 2FA Authentication=============-->
            {{ Form::open(['route' => ['disable2fa'], 'method' => 'Post', 'class' => 'form-horizontal']) }}
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-md-12">
                        @if (!extension_loaded('imagick'))
                        {!! $google2fa_url !!}
                        @else
                        <img src="{{ $google2fa_url }}" />
                        @endif
                    </div>
                    <div class="col-md-12">
                        <p>{{ __('To disable 2-Factor Authentication verify QRCode') }} </p>
                    </div>
                    <div class="col-sm-12">
                        {{ Form::label('current-password', __('current password'), ['class' => 'form-label']) }}
                        {!! Form::password('current-password', [
                        'class' => "form-control $errors->has('password') ? ' is-invalid' : '' ",
                        'id' => 'password',
                        'required',
                        'placeholder' => __('Current Password'),
                        ]) !!}
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $error('password') }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    {{ Form::button(__('Disable 2FA'), ['type' => 'submit', 'class' => 'btn btn-danger']) }}
                </div>
            </div>
            {!! Form::close() !!}
            <!--=============Disable Google 2FA Authentication=============-->
            @endif
        </div>
        @endif
        @if (Auth::user()->type == 'Influencer')
        <div id="useradd-6" class="card">
            <div class="card-header">
                <h5>{{ __('Stripe Connect') }}</h5>
                <small class="text-muted">{{ __('Connect your Stripe account for payments') }}</small>
            </div>
            {{ Form::open(['route' => 'profile.verify.stripe', 'method' => 'Post', 'class' => 'form-horizontal', 'data-validate']) }}
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <div class="form-group">
                            {{ Form::label('stripe_account_id', __('Stripe Account ID'), ['class' => 'form-label']) }}
                            {!! Form::text('stripe_account_id', $user->stripe_account_id ?? '', [
                            'class' => 'form-control',
                            'id' => 'stripe_account_id',
                            'placeholder' => __('Enter your Stripe Account ID'),
                            ]) !!}

                            @if ($errors->has('stripe_account_id'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('stripe_account_id') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mt-4">
                            @if (isset($user->is_stripe_connected) && $user->is_stripe_connected)
                            <span class="badge bg-success p-2">{{ __('Verified') }}</span>
                            @else
                            <span class="badge bg-warning p-2">{{ __('Not Verified') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    {{ Form::button(__('Verify & Save'), [
                                'type' => 'submit',
                                'class' => 'btn btn-primary',
                            ]) }}
                </div>
            </div>
            {{ Form::close() }}
        </div>
        @endif
        <div id="useradd-5" class="card">
            <div class="card-header">
                <h5>{{ __('Delete Account') }}</h5>
                <small
                    class="text-muted">{{ __('Once you delete your account, there is no going back.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                please
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                be certain.') }}</small>
            </div>
            <div class="card-body">
                <div class="mt-3 row justify-content-between float-end">
                    <div class="col-sm-auto text-sm-end d-flex">
                        @if ($user->active_status == 1)
                        {!! Html::link('profile-status', __('Deactivate'), ['class' => 'btn btn-secondary d-flex me-3 float-end']) !!}
                        @endif
                        {!! Form::open([
                        'method' => 'DELETE',
                        'route' => 'profile.delete',
                        'id' => 'delete-form-' . $user->id,
                        ]) !!}
                        <a class="btn btn-danger show_confirm d-flex" data-toggle="tooltip"
                            href="#!">{{ __('Delete Account') }}<i
                                class="ti ti-chevron-right ms-1 ms-sm-2"></i></a>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('vendor/croppie/css/croppie.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
<script src="{{ asset('vendor/croppie/js/croppie.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
<script>
    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#useradd-sidenav',
        offset: 200
    })
</script>
<script>
    $(document).ready(function() {
        window.setTimeout(function() {}, 9e3), $image_crop = $(".image-preview").croppie({
            enableExif: !0,
            enforceBoundary: !1,
            enableOrientation: !0,
            viewport: {
                width: 200,
                height: 200,
                type: "square"
            },
            boundary: {
                width: 300,
                height: 300
            }
        }), $(" #avatarCrop").change(function() {
            $("#avatar-holder").addClass("d-none"), $("#avatar-updater").removeClass("d-none");
            var e = new FileReader;
            e.onload = function(e) {
                $image_crop.croppie("bind", {
                    url: e.target.result
                })
            }, e.readAsDataURL(this.files[0])
        }), $("#toggleClose").click(function() {
            $("#toggleClose").css("display", "none"), $(".app-logo").css("display", "none"), $(
                ".toggleopen").css("display", "block")
        }), $(".toggleopen").click(function() {
            $(".toggleopen").css("display", "none"), $(".app-logo").css("display", "block"), $(
                "#toggleClose").css("display", "block")
        }), $("#rotate-image").click(function(e) {
            $image_crop.croppie("rotate", 90)
        }), $("#crop_image").click(function() {
            $image_crop.croppie("result", {
                type: "canvas",
                size: "viewport"
            }).then(function(e) {
                var a = $("input[name=avatar-url]").val(),
                    t = $('meta[name="csrf-token"]').attr("content"),
                    o = $("#crop_image");
                o.html("Saving Avatar..."), o.attr("disabled", "disabled"), $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    }
                }), $.ajax({
                    url: a,
                    type: "POST",
                    data: {
                        avatar: e,
                        _token: t
                    },
                    dataType: "json",
                    success: function(e) {},
                    complete: function(e) {
                        new swal({
                            text: e.responseText,
                            icon: "success"
                        }).then(() => {
                            location.reload()
                        })
                    }
                })
            })
        }), $("#avatar-cancel-btn").click(function() {
            $("#avatar-holder").removeClass("d-none"), $("#avatar-updater").addClass("d-none")
        });
        $("#backup-file-btn").click(function() {
            swal({
                text: "Application file backup is disabled by Administrator",
                icon: 'error',
            });
        });
        $("#backup-database-btn").click(function() {
            swal({
                text: "Database backup is disabled by Administrator",
                icon: 'error',
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        var genericExamples = document.querySelectorAll('[data-trigger]');
        for (i = 0; i < genericExamples.length; ++i) {
            var element = genericExamples[i];
            new Choices(element, {
                placeholderValue: 'This is a placeholder set in the config',
                searchPlaceholderValue: 'This is a search placeholder',
            });
        }
    });
    $("#phone").intlTelInput({
        geoIpLookup: function(callback) {
            $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "";
                callback('{{ $user->country_code }}');
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