@extends('layouts.main')
@section('title', __('Edit Plan'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">{{ __('Plans') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Plan') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-lg-6 col-md-8 col-xxl-6 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Plan') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($plan, [
                            'route' => ['plans.update', $plan->id],
                            'method' => 'put',
                            'data-validate',
                        ]) !!}
                        <div class="form-group">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('name', null, ['placeholder' => __('Enter name'), 'class' => 'form-control', 'required']) !!}
                        </div>
                        <div class="form-group">
                            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                            {!! Form::text('price', null, ['placeholder' => __('Enter price'), 'class' => 'form-control', 'required']) !!}
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                                    {!! Form::number('duration', null, [
                                        'placeholder' => __('Enter duration'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                                    {!! Form::select('durationtype', ['Month' => 'Month', 'Year' => 'Year'], $plan->durationtype, [
                                        'class' => 'form-control',
                                        'data-trigger',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('max_users', __('Maximum Users'), ['class' => 'form-label']) }}
                                    {!! Form::number('max_users', $plan->max_users, [
                                        'placeholder' => __('Enter maximum users'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('max_roles', __('Maximum Roles'), ['class' => 'form-label']) }}
                                    {!! Form::number('max_roles', $plan->max_roles, [
                                        'placeholder' => __('Enter maximum roles'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('max_documents', __('Maximum Documents'), ['class' => 'form-label']) }}
                                    {!! Form::number('max_documents', $plan->max_documents, [
                                        'placeholder' => __('Enter maximum documents'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('max_blogs', __('Maximum Blogs'), ['class' => 'form-label']) }}
                                    {!! Form::number('max_blogs', $plan->max_blogs, [
                                        'placeholder' => __('Enter maximum blogs'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-8">
                                    {{ Form::label('discount_setting', __('Discount Setting'), ['class' => 'form-label']) }}
                                </div>
                                <div class="col-md-4 form-check form-switch">
                                    <input class="form-check-input float-end" id="discount_setting" name="discount_setting"
                                        {{ $plan->discount_setting == 'on' ? 'checked' : '' }} type="checkbox">
                                    <span class="custom-switch-indicator"></span>
                                </div>
                            </div>
                            <div class="form-group main_discount {{ $plan->discount_setting == 'off' ? 'd-none' : '' }}">
                                {{ Form::label('discount', __('Discount'), ['class' => 'form-label']) }}
                                {{ Form::number('discount', $plan->discount, ['class' => 'form-control', 'placeholder' => __('Enter discount'), 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::text('description', null, ['placeholder' => __('Enter description'), 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-end">
                            <a href="{{ route('plans.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </section>
    </div>
@endsection
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
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
        $(document).on('click', 'input[name="discount_setting"]', function() {
            if ($(this).is(':checked')) {
                $('.main_discount').removeClass('d-none');
            } else {
                $('.main_discount').addClass('d-none');
            }
        });
    </script>
@endpush
