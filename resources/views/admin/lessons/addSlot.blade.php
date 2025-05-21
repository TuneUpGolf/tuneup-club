@extends('layouts.main')
@section('title', __('Add Slot to ' . $lesson->lesson_name))
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
                        <div class="card-header flex items-center justify-between">
                            <h5>{{ __('Create Slot') }}</h5>
                            @if (isset($lesson->lesson_duration))
                                <p>{{ __('Lesson Duration : ' . $lesson->lesson_duration . 'hour(s)') }}
                                </p>
                            @else
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                    <a class="text-white" href="{{ route('lesson.edit', $lesson->id) }}">
                                        {{ __('Set Lesson Duration') }}
                                    </a>
                                </button>
                            @endif

                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['slot.add', ['redirect' => 1, 'lesson_id' => $lesson->id]],
                                'method' => 'Post',
                                'data-validate',
                                'files' => 'true',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group ">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::input('date', 'start_date', null, ['id' => 'start_date', 'class' => 'form-control']) }}
                            </div>
                            <div class="form-group ">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::input('date', 'end_date', null, ['id' => 'end_date', 'class' => 'form-control']) }}
                            </div>
                            <div class="form-group ">
                                {{ Form::label('start_time', __('Start Time'), ['class' => 'form-label']) }}
                                {{ Form::input('time', 'start_time', null, ['id' => 'start_time', 'class' => 'form-control']) }}
                            </div>
                            <div class="form-group ">
                                {{ Form::label('end_time', __('End Time'), ['class' => 'form-label']) }}
                                {{ Form::input('time', 'end_time', null, ['id' => 'end_time', 'class' => 'form-control']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                                {!! Form::text('location', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Location'),
                                ]) !!}
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
    </script>
@endpush
