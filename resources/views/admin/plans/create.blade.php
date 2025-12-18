@extends('layouts.main')
@section('title', __('Create MyPlan'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('plans.myplan') }}">{{ __('MyPlans') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create MyPlan') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-lg-6 col-md-8 col-xxl-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Create MyPlan') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'plans.store',
                            'method' => 'Post',
                            'data-validate',
                        ]) !!}
                        <div class="form-group">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('name', old('name'), ['placeholder' => __('Enter name'), 'class' => 'form-control', 'required']) !!}
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('price', __('Monthly Price'), ['class' => 'form-label']) }}
                                    {!! Form::text('price', old('price'), [
                                        'placeholder' => __('Enter price'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                    <small class="text-muted">{{ __('Billed monthly') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('price_quarter', __('Quarterly Price'), ['class' => 'form-label']) }}
                                    {!! Form::text('price_quarter', old('price_quarter'), [
                                        'placeholder' => __('Enter price'),
                                        'class' => 'form-control',
                                    ]) !!}
                                    <small class="text-muted">{{ __('Billed every 3 months') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('price_year', __('Yearly Price'), ['class' => 'form-label']) }}
                                    {!! Form::text('price_year', old('price_year'), [
                                        'placeholder' => __('Enter price'),
                                        'class' => 'form-control',
                                    ]) !!}
                                    <small class="text-muted">{{ __('Billed annually') }}</small>
                                </div>
                            </div>
                        </div>

                        @if (Auth::user()->type != 'Super Admin')
                            <div class="form-group">
                                {{ Form::label('max_users', __('Maximum users'), ['class' => 'form-label']) }}
                                {!! Form::number('max_users', old('max_users'), [
                                    'placeholder' => __('Enter maximum users'),
                                    'class' => 'form-control',
                                    'required',
                                ]) !!}
                            </div>
                        @endif

                        <div class="form-group mt-3">
                            {{ Form::label('lesson_limit', __('Lesson Limit'), ['class' => 'form-label d-block']) }}

                            @php
                                // Generate lesson limits: 1 to 10, plus "Unlimited"
                                $lessonLimits = collect(range(1, 10))
                                    ->mapWithKeys(fn($num) => [$num => "{$num} lessons/month"])
                                    ->toArray();

                                // Add "Unlimited" option (-1)
                                $lessonLimits[-1] = 'Unlimited lessons/month';

                                // Prepend placeholder at the top (key 0)
                                $lessonLimits = [0 => 'Select lesson limit'] + $lessonLimits;
                            @endphp

                            {!! Form::select('lesson_limit', $lessonLimits, old('lesson_limit'), [
                                'class' => 'form-select',
                                'id' => 'lesson_limit',
                            ]) !!}

                        </div>

                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::textarea('description', old('description'), [
                                'placeholder' => __('Enter description'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>

                        @if (Auth::user()->type == 'Influencer')
                            <div class="form-group flex flex-row gap-4">
                                <div class="flex flex-col">
                                    {{ Form::label('Chat', __('Chat *'), ['class' => 'form-label']) }}
                                    {!! Form::checkbox('chat', 1, old('chat'), [
                                        'class' => 'form-check form-control',
                                        'data-onstyle' => 'primary',
                                        'data-toggle' => 'switchbutton',
                                    ]) !!}
                                </div>
                                <div class="flex flex-col">
                                    {{ Form::label('Feed', __('Feed *'), ['class' => 'form-label']) }}
                                    {!! Form::checkbox('feed', 1, old('feed'), [
                                        'class' => 'form-check form-control',
                                        'data-onstyle' => 'primary',
                                        'data-toggle' => 'switchbutton',
                                    ]) !!}
                                </div>
                            </div>
                        @endif

                        <div class="card-footer">
                            <div class="float-end">
                                <a href="{{ route('plans.myplan') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
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

        CKEDITOR.replace('description', {
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });
    </script>
@endpush