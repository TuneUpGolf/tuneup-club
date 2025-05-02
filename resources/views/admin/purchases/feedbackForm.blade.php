@extends('layouts.main')
@section('title', __('Create Purchase'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Feedback') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add Feedback') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => ['purchase.feedback.add', ['purchase_video_id' => $purchaseVideo->id, 'redirect' => '1']],
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
                            'class' => 'form-horizontal',
                            'data-validate',
                        ]) !!}
                        <div class="row">
                            <div class="form-group">
                                {{ Form::label('fdbk_video', _('Feedback Video'), ['class' => 'form-label']) }}
                                {{ Form::file('fdbk_video[]', ['class' => 'form-control', 'required', 'multiple']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('feedback', __('Feedback'), ['class' => 'form-label']) }}
                                *
                                {!! Form::textarea('feedback', $purchaseVideo->feedback ?? '', [
                                    'class' => 'form-control ',
                                    'placeholder' => __('Enter Feedback'),
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('purchase.feedback.index', ['purchase_id' => $purchaseVideo->purchase_id]) }}"
                                class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
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
        CKEDITOR.replace('short_description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });
        CKEDITOR.replace('description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
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
