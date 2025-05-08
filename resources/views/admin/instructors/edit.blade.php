@extends('layouts.main')
@section('title', __('Edit Instructor'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('instructor.index') }}">{{ __('Instructors') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Instructors') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Instructor') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($user, [
                            'route' => ['instructor.update', $user->id],
                            'method' => 'Put',
                            'data-validate',
                        ]) !!}
                        <div class="form-group ">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('name', null, ['class' => 'form-control', ' required', 'placeholder' => __('Enter name')]) !!}
                        </div>
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
                        <div class="form-group">
                            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                            {!! Form::hidden('country_code', $user->country_code, []) !!}
                            {!! Form::hidden('dial_code', $user->dial_code, []) !!}
                            <input id="phone" name="phone" class="form-control"
                                value="+{{ $user->dial_code . '0' . $user->phone }}" type="tel"
                                placeholder="{{ __('Enter phone') }}" required>
                        </div>
                        <div class="form-group">
                            {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                            <select class="form-control form-control-inline-block" data-trigger name="country"
                                id="country">
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach ($countries as $val)
                                    <option value="{{ $val['name'] }}"
                                        {{ $user->country == $val['name'] ? 'selected' : '' }}>
                                        {{ $val['name'] }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('country'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('country') }}</strong>
                                </span>
                            @endif
                        </div>
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

                        {{-- @if (tenant('id') != null && $user->type != 'Admin')
                            <div class="form-group">
                                {{ Form::label('roles', __('Role'), ['class' => 'form-label']) }}
                                {!! Form::select('roles', $roles, $user->type, [
                                    'class' => 'form-control',
                                    'required',
                                    'data-trigger',
                                    'id' => 'roles',
                                ]) !!}
                            </div>
                        @endif --}}
                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('instructor.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
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
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script>
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
        $(document).on('change', '#role', function() {
            var roles = $(this).val();
            if (roles == 'Super Admin') {
                $('#domain').hide();
                $('#domain').val('');

            } else {
                $('#domain').show();
            }
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
    </script>
@endpush
