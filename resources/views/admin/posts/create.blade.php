@extends('layouts.main')
@section('title', __('Create Post'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Posts') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Create Post') }}</h5>
                    </div>

                    {!! Form::open([
                        'route' => 'blogs.store',
                        'method' => 'POST',
                        'enctype' => 'multipart/form-data',
                        'data-validate',
                    ]) !!}

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    {{ Form::label('title', __('Title'), ['class' => 'form-label']) }} *
                                    {!! Form::text('title', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter title'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group mb-3">
                                    {{ Form::label('photo', __('Photo'), ['class' => 'form-label']) }} *
                                    {!! Form::file('photo', [
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
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

                            <div class="form-group mb-3">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }} *
                                {!! Form::textarea('description', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter description'),
                                    'rows' => 5,
                                ]) !!}
                                <small class="text-muted">
                                    Characters: <span id="long-desc-count">0</span>
                                </small>
                            </div>
                            <!-- Right Column -->
                            <div class="col-xl-6">
                                @if (Auth::user()->type != 'Follower')
                                    <div class="form-group mb-3">
                                        {{ Form::label('paid', __('Paid *'), ['class' => 'form-label d-block']) }}
                                        <div class="form-check form-switch">
                                            {!! Form::checkbox('paid', 1, false, [
                                                'class' => 'form-check-input',
                                                'id' => 'paidSwitch',
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                        {!! Form::number('price', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('Enter price'),
                                            'step' => '0.01',
                                        ]) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </section>
    </div>
@endsection
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
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
            filebrowserUploadMethod: 'form',

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
            for (var i = 0; i < genericExamples.length; ++i) {
                var element = genericExamples[i];
                new Choices(element, {
                    placeholderValue: element.getAttribute('placeholder') || 'Select an option',
                    searchPlaceholderValue: 'Search...',
                    removeItemButton: element.multiple ? true : false,
                    shouldSort: false,
                });
            }
        });
    </script>
@endpush
