@extends('layouts.main')
@section('title', __('Edit Document'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Document') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Edit Document') }}</h5>
                </div>
                <div class="card-body">
                    {!! Form::model($document, [
                        'route' => ['document.update', $document->id],
                        'method' => 'Put',
                        'enctype' => 'multipart/form-data',
                        'data-validate',
                    ]) !!}
                    <div class="form-group">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                        {!! Form::text('title', $document->title, [
                            'class' => 'form-control',
                            ' required',
                            'placeholder' => __('Enter title'),
                        ]) !!}
                    </div>
                    <div class="form-group">
                        {{ Form::label('slug', __('Slug'), ['class' => 'form-label']) }}
                        {!! Form::text('slug', $document->slug, [
                            'placeholder' => __('Enter slug'),
                            'class' => 'form-control',
                            'required',
                        ]) !!}
                    </div>
                    <div class="form-group">
                        @if ($document->logo)
                            <div class="text-center">
                                {!! Form::image(
                                    Storage::exists($document->logo)
                                        ? asset('storage/' . tenant('id') . '/' . $document->logo)
                                        : Storage::url('seeder-image/78x78.png'),
                                    null,
                                    [
                                        'class' => 'img img-responsive justify-content-center text-center document-img',
                                        'id' => 'app-dark-logo',
                                    ],
                                ) !!}
                            </div>
                        @endif
                        {{ Form::label('document_logo', __('Select Logo'), ['class' => 'form-label']) }}
                        {!! Form::file('document_logo', ['class' => 'form-control']) !!}
                        <small>
                            {{ __('Note: Select an image of 250X 60 size.') }}
                        </small>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-8">
                                {{ Form::label('change_log_status', __('Change Log'), ['class' => 'form-label']) }}
                            </div>
                            <div class="col-md-4 form-check form-switch">
                                <input class="form-check-input float-end" id="change_log_status" name="change_log_status"
                                    {{ $document->change_log_status == 'on' ? 'checked' : '' }} type="checkbox">
                                <span class="custom-switch-indicator"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group changeLog {{ $document->change_log_status == 'off' ? 'd-none' : '' }} ">
                        <div class="repeater2">
                            <div data-repeater-list="change_log_json">
                                <div data-repeater-item>
                                    <hr />
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('version', __('Change Log Version'), ['class' => 'form-label']) }}
                                                {!! Form::text('version', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter change log version'),
                                                    'id' => 'version',
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('date', __('Change Log Date'), ['class' => 'form-label']) }}
                                                {!! Form::text('date', null, [
                                                    'class' => 'form-control datePicker',
                                                    'placeholder' => __('Enter change log date'),
                                                    'id' => 'date',
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="inner-repeater">
                                                    <div data-repeater-list="inner-list">
                                                        <hr />
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        {{ Form::label('badge', __('Change Log Badge'), ['class' => 'form-label']) }}
                                                                        {!! Form::text('badge', null, [
                                                                            'class' => 'form-control',
                                                                            'placeholder' => __('Enter change log badge'),
                                                                            'id' => 'badge',
                                                                        ]) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        {{ Form::label('color', __('Change Log Color'), ['class' => 'form-label']) }}
                                                                        {!! Form::select('color', $colors, null, [
                                                                            'class' => 'form-control',
                                                                            'data-trigger',
                                                                            'id' => 'color',
                                                                        ]) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-11">
                                                                    <div class="form-group">
                                                                        {{ Form::label('text', __('Change Log Text'), ['class' => 'form-label']) }}
                                                                        {!! Form::text('text', null, [
                                                                            'class' => 'form-control',
                                                                            'placeholder' => __('Enter change log text'),
                                                                            'id' => 'text',
                                                                        ]) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="mt-27 col-lg-1 text-end">
                                                                    <input data-repeater-delete class="btn btn-danger mt-4"
                                                                        type="button" value="Delete" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input data-repeater-create class="btn btn-primary" type="button"
                                                        value="Add" />
                                                    <input data-repeater-delete class="btn btn-danger" type="button"
                                                        value="Delete" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <input data-repeater-create class="btn btn-primary" type="button" value="Add" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="select-themes" class="form-label">{{ __('Select Themes') }}</label>
                    </div>
                    <div class="uploaded-pics gy-3 row">
                        <input id="themefile1" name="theme" type="hidden" value="{{ $document->theme }}">
                        <div
                            class="col-xxl-3 col-lg-4 col-md-6 col-sm-5 theme-view-card {{ $document->theme == 'theme1' ? 'selected-theme' : '' }}">
                            <div class="theme-view-inner">
                                <div class="theme-view-img">
                                    <img data-id="theme1" src="{{ Storage::url('document/stisla.png') }}">
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-xxl-3 col-lg-4 col-md-6 col-sm-5 theme-view-card {{ $document->theme == 'theme2' ? 'selected-theme' : '' }}">
                            <div class="theme-view-inner">
                                <div class="theme-view-img">
                                    <img data-id="theme2" src="{{ Storage::url('document/editor.png') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-end">
                        <a href="{{ route('document.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
@endpush
@push('javascript')
    <script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/repeater/reapeater.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.theme-view-img', function() {
                var theme = $(this).find('img').attr('data-id');
                $('input[name="theme"]').val(theme);
                $('.theme-view-card').removeClass('selected-theme');
                $(this).parents('.theme-view-card').addClass('selected-theme');
            });

            $(document).on('click', 'input[name="change_log_status"]', function() {
                if ($(this).is(':checked')) {
                    $('.changeLog').removeClass('d-none');
                } else {
                    $('.changeLog').addClass('d-none');
                }
            });

            var $repeater2 = $('.repeater2').repeater({
                initEmpty: false,
                repeaters: [{
                    selector: '.inner-repeater',
                    show: function() {
                        $(this).slideDown();
                        var selectName = $(this).find('select').attr('name');
                        var escapedSelectName = selectName.replace(/[-\/\\^$*+?.()|[\]{}]/g,
                            '\\$&');
                        var select = $(this).find('select[name="' + escapedSelectName + '"]');
                        var multipleCancelButton = new Choices(select[0], {
                            removeItemButton: true,
                        });
                    },
                    hide: function(deleteElement) {
                        $(this).slideUp(deleteElement);
                    },
                    ready: function(setIndexes) {
                        var genericExamples = document.querySelectorAll('[data-trigger]');
                        for (i = 0; i < genericExamples.length; ++i) {
                            var element = genericExamples[i];
                            new Choices(element, {
                                placeholderValue: 'This is a placeholder set in the config',
                                searchPlaceholderValue: 'This is a search placeholder',
                            });
                        }
                    }
                }],
                defaultValues: {},
                show: function() {
                    $(this).slideDown();
                    var data = $(this).find('input,textarea,select').toArray();
                    data.forEach(function(val) {
                        $(val).parents('.form-group').find('label').attr('for', $(val).attr(
                            'name'));
                        $(val).attr('id', $(val).attr('name'));
                    });
                    var selectName = $(this).find('select').attr('name');
                    var escapedSelectName = selectName.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    var select = $(this).find('select[name="' + escapedSelectName + '"]');
                    var multipleCancelButton = new Choices(select[0], {
                        removeItemButton: true,
                    });
                    var datePicker = document.querySelectorAll('.datePicker');
                    for (i = 0; i < datePicker.length; ++i) {
                        var element = datePicker[i];
                        const d_week = new Datepicker(element, {
                            buttonClass: 'btn',
                        });
                    }
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                    }
                },
                ready: function(setIndexes) {
                    var genericExamples = document.querySelectorAll('[data-trigger]');
                    for (i = 0; i < genericExamples.length; ++i) {
                        var element = genericExamples[i];
                        new Choices(element, {
                            placeholderValue: 'This is a placeholder set in the config',
                            searchPlaceholderValue: 'This is a search placeholder',
                        });
                    }
                },
            });
            @if ($document->change_log_json)
                var Json = JSON.parse({!! json_encode($document->change_log_json) !!});
                $repeater2.setList(Json);
            @endif
        });
    </script>
@endpush
