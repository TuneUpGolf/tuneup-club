@extends('layouts.main')
@section('title', __('Edit Album Category'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Album Category') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Album Category') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($posts, [
                            'route' => ['album.update', $posts->id],
                            'method' => 'PATCH',
                            'class' => 'form-horizontal',
                            'data-validate',
                            'enctype' => 'multipart/form-data',
                        ]) !!}

                        <div class="row">
                            {{-- Row 1 --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('title', __('Title'), ['class' => 'form-label']) }} *
                                    {!! Form::text('title', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter title'),
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('album_category_id', __('Album Category'), ['class' => 'form-label']) }}
                                    *
                                    {!! Form::select('album_category_id', $album_categories->pluck('title', 'id'), null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Select Album Category'),
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('file', __('Photo/Video'), ['class' => 'form-label']) }} *
                                    {!! Form::file('file', ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }} *
                                    {!! Form::textarea('description', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter description'),
                                        'rows' => 3,
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="text-end">
                            <a href="{{ route('album.manage') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
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
    <script type="text/javascript">
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
