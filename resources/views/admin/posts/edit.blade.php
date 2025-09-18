@extends('layouts.main')
@section('title', __('Edit Post'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Post') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Post') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($posts, [
                            'route' => ['blogs.update', $posts->id],
                            'method' => 'Patch',
                            'class' => 'form-horizontal',
                            'data-validate',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{ Form::label('title', __('Title'), ['class' => 'form-label']) }} *
                                    {!! Form::text('title', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter title'),
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('file', __('Photo/Video'), ['class' => 'form-label']) }} *
                                    {!! Form::file('file', ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{ Form::label('slug', __('Slug'), ['class' => 'form-label']) }} *
                                    {!! Form::text('slug', $posts->slug ?? null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter slug'),
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                                <div class="row form-inline">
                                    <div class="form-group col-md-6">
                                        {{ Form::label('Paid', __('Paid'), ['class' => 'form-label']) }} *
                                        {!! Form::checkbox('paid', 1, $posts->paid, [
                                            'class' => 'form-check',
                                            'data-onstyle' => 'primary',
                                            'data-toggle' => 'switchbutton',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                        {{ Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Enter Price'), 'step' => '0.01']) }}
                                    </div>
                                </div>

                            </div>
                            <div class="form-group mb-3">
                                {{ Form::label('short_description', __('Short Description'), ['class' => 'form-label']) }}
                                *
                                {!! Form::textarea('short_description', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter short description'),
                                    'required',
                                    'rows' => 3,
                                ]) !!}
                                <small class="text-muted">
                                    Characters: <span id="short-desc-count">0</span> / 300
                                </small>
                                <div id="short-desc-warning" class="text-danger" style="display: none;">
                                    {{ __('Maximum 300 characters allowed.') }}
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }} *
                                {!! Form::textarea('description', null, [
                                    'class' => 'form-control ',
                                    'placeholder' => __('Enter description'),
                                    'required' => 'required',
                                ]) !!}
                                <small class="text-muted">
                                    Characters: <span id="long-desc-count">0</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-end">
                            <a href="{{ route('blogs.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
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
        const MAX_SHORT = 300;
        CKEDITOR.replace('short_description', {
            toolbar: [{
                    name: 'basicstyles',
                    items: ['Bold', 'Italic']
                },
                {
                    name: 'paragraph',
                    items: ['BulletedList']
                }
            ],
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });
        CKEDITOR.replace('description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form',
            removeButtons: 'Link,Unlink'
        });
        document.addEventListener("DOMContentLoaded", function() {
            const shortCount = document.getElementById("short-desc-count");
            const longCount = document.getElementById("long-desc-count");
            const shortWarning = document.getElementById("short-desc-warning");
            const form = document.querySelector("form");

            function getPlainText(editor) {
                return editor.getData().replace(/<[^>]*>/g, '').trim();
            }

            function updateShortCount(evt) {
                const editor = evt.editor;
                const text = getPlainText(editor);
                const length = text.length;

                shortCount.textContent = length;

                if (length >= MAX_SHORT) {
                    editor.container.addClass('is-invalid');
                    shortWarning.style.display = 'block';
                } else {
                    editor.container.removeClass('is-invalid');
                    shortWarning.style.display = 'none';
                }
            }

            function updateLongCount(evt) {
                const editor = evt.editor;
                const text = getPlainText(editor);
                longCount.textContent = text.length;
            }

            // ✅ Live word count + prevent typing after limit
            CKEDITOR.instances.short_description.on('key', function(evt) {
                const text = getPlainText(evt.editor);
                if (text.length >= MAX_SHORT && evt.data.keyCode != 8 && evt.data.keyCode != 46) {
                    // allow backspace(8) and delete(46)
                    evt.cancel(); // stop the keystroke

                }
            });

            // ✅ Update counts on change
            CKEDITOR.instances.short_description.on('change', updateShortCount);
            CKEDITOR.instances.description.on('change', updateLongCount);

            // ✅ Form validation
            form.addEventListener("submit", function(e) {
                const shortText = getPlainText(CKEDITOR.instances.short_description);
                if (shortText.length > MAX_SHORT) {
                    e.preventDefault();

                }
            });
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
