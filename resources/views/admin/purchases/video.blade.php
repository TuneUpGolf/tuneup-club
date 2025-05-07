@extends('layouts.main')
@section('title', __('Add Video'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Video') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add detail for your influencer to view') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => [
                                'purchase.video.add',
                                ['purchase_id' => $purchase->id, 'redirect' => true, 'checkout' => request()->checkout],
                            ],
                            'method' => 'POST',
                            'data-validate',
                            'files' => 'true',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="form-group">
                            {{ Form::label('video', _('Purchase Video'), ['class' => 'form-label']) }}
                            {{ Form::file('video', ['class' => 'form-control', 'required']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('video_2', _('Purchase Video 2 (Optional)'), ['class' => 'form-label']) }}
                            {{ Form::file('video_2', ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('Note', _('Note'), ['class' => 'form-label']) }}
                            {!! Form::textarea('note', null, [
                                'class' => 'form-control',
                                'placeholder' => __('Enter Note'),
                                'rows' => 4,
                                'style' => 'resize: vertical; overflow-y: auto;',
                            ]) !!}
                        </div>
                        <div class="card-footer">
                            <div class="float-end">
                                <a href="{{ route('purchase.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {{ Form::button(__('Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
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
