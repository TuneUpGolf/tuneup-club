@extends('layouts.main')
@section('title', __('Edit Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Lesson') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-8 col-md-8 col-xxl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Lesson') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($user, [
                            'route' => ['lesson.update', $user->id],
                            'method' => 'Put',
                            'data-validate',
                        ]) !!}

                        <!-- Package Lesson Checkbox (Disabled if it's a package lesson) -->
                        @if ($user->is_package_lesson)
                            <div class="form-group">
                                <div class="form-check">
                                    {!! Form::checkbox('is_package_lesson', 1, true, [
                                        'class' => 'form-check-input',
                                        'id' => 'is_package_lesson',
                                        'disabled' => 'disabled',
                                    ]) !!}
                                    {{ Form::label('is_package_lesson', __('Package Lesson (Cannot be changed)'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group ">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                        </div>
                        <div class="form-group">
                            {{ Form::label('price', __('Price ($)'), ['class' => 'form-label']) }}
                            {!! Form::number('lesson_price', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('Enter Price'),
                            ]) !!}
                        </div>

                        @if ($user->type !== 'inPerson')
                            <div class="form-group">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_quantity', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter Quantity'),
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('response_time', __('Response Time'), ['class' => 'form-label']) }}
                                {!! Form::number('required_time', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter Required Time'),
                                ]) !!}
                            </div>
                        @endif

                        @if ($user->type === 'inPerson')
                            <div class="form-group">
                                {{ Form::label('lesson_duration', __('Duration (hours)'), ['class' => 'form-label']) }}
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
                            <div class="form-group">
                                {{ Form::label('max_followers', __('Group Size'), ['class' => 'form-label']) }}
                                {!! Form::number('max_followers', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter group size'),
                                    'min' => 1,
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash', 'both' => 'Both'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'placeholder' => __('Payment Method'),
                                ]) !!}
                            </div>
                        @endif

                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::textarea('lesson_description', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('Enter Description'),
                            ]) !!}
                        </div>

                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('lesson.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
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
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
    CKEDITOR.replace('lesson_description', {
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });
    </script>
@endpush
