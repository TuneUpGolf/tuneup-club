@extends('layouts.main')
@section('title', __('Create In-Person Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create In-Person Lesson') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @if (tenant('id') == null)
                    <div class="alert alert-warning">
                        {{ __('Your database user must have permission to CREATE DATABASE, because we need to create database when new tenant create.') }}
                    </div>
                @endif
                <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Create In-Person Lesson') }}</h5>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['lesson.store', ['type' => 'inPerson']],
                                'method' => 'Post',
                                'data-validate',
                                'files' => 'true',
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            <!-- Package Lesson Checkbox -->
                            <div class="form-group">
                                <div class="form-check">
                                    {!! Form::checkbox('is_package_lesson', 1, false, [
                                        'class' => 'form-check-input',
                                        'id' => 'is_package_lesson',
                                    ]) !!}
                                    {{ Form::label('is_package_lesson', __('Package Lesson'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Description'),
                                ]) !!}
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_price', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Price'),
                                    'min' => 1,
                                ]) !!}
                                <small id="package_note" class="text-muted d-none">
                                    {{ __('Since this is a package lesson, the price should account for all booked slots.') }}
                                </small>
                            </div>

                            <!-- Lesson Duration -->
                            <div class="form-group">
                                {{ Form::label('lesson_duration', __('Duration'), ['class' => 'form-label']) }}
                                {!! Form::select(
                                    'lesson_duration',
                                    [
                                        '0.5' => '30 Minutes',
                                        '0.75' => '45 Minutes',
                                        '1' => '1 Hour',
                                        '1.5' => '1.5 Hours',
                                        '2' => '2 Hours',
                                        '2.5' => '2.5 Hours',
                                        '3' => '3 Hours',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'data-trigger',
                                        'required',
                                        'placeholder' => __('Duration'),
                                    ],
                                ) !!}
                            </div>

                            <!-- Group Size -->
                            <div class="form-group">
                                {{ Form::label('max_followers', __('Group Size'), ['class' => 'form-label']) }}
                                {!! Form::number('max_followers', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter group size'),
                                    'min' => 1,
                                ]) !!}
                            </div>

                            <!-- Payment Method -->
                            <div class="form-group">
                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash', 'both' => 'Both'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'required',
                                    'id' => 'payment_method',
                                ]) !!}
                                <!-- Hidden input to store payment method when disabled -->
                                {!! Form::hidden('payment_method', 'online', ['id' => 'hidden_payment_method']) !!}
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="float-end">
                                <a href="{{ route('follower.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                            </div>
                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush

@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let packageLessonCheckbox = document.getElementById('is_package_lesson');
            let paymentMethodSelect = document.getElementById('payment_method');
            let hiddenPaymentMethod = document.getElementById('hidden_payment_method');
            let packageNote = document.getElementById('package_note');

            function togglePackageLessonSettings() {
                if (packageLessonCheckbox.checked) {
                    paymentMethodSelect.value = 'online';
                    paymentMethodSelect.setAttribute('disabled', 'disabled');
                    hiddenPaymentMethod.value = 'online'; // Ensure it's sent in form data
                    packageNote.classList.remove('d-none'); // Show the price note
                } else {
                    paymentMethodSelect.removeAttribute('disabled');
                    packageNote.classList.add('d-none'); // Hide the price note
                }
            }

            packageLessonCheckbox.addEventListener('change', togglePackageLessonSettings);
            togglePackageLessonSettings(); // Run on page load
        });
    </script>
@endpush
