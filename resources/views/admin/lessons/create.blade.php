@extends('layouts.main')
@section('title', __('Create Lesson'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Lesson') }}</li>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @if (tenant('id') == null)
                    <div class="alert alert-warning">
                        {{ __('Your database user must have permission to CREATE DATABASE, because we need to create database when new tenant create.') }}
                    </div>
                @endif

                <div class="m-auto col-lg-8 col-md-8 col-xxl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Create Lesson') }}</h5>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['lesson.store', ['type' => 'online']],
                                'method' => 'POST',
                                'data-validate',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_price', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Price'),
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_quantity', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Quantity'),
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('response_time', __('Response Time'), ['class' => 'form-label']) }}
                                {!! Form::number('required_time', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Required Time'),
                                ]) !!}
                            </div>

                            {{-- ✅ Short Description --}}
                            <div class="form-group">
                                {{ Form::label('short_description', __('Short Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('short_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Short Description'),
                                    'id' => 'short_description',
                                ]) !!}
                                <small class="text-muted">
                                    Characters: <span id="short-desc-count">0</span> / 300
                                </small>
                                <div id="short-desc-warning" class="text-danger" style="display: none;">
                                    {{ __('Maximum 300 characters allowed.') }}
                                </div>
                            </div>

                            {{-- ✅ Long Description --}}
                            <div class="form-group">
                                {{ Form::label('description', __('Long Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('lesson_description', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter Long Description'),
                                    'id' => 'lesson_description',
                                ]) !!}
                                <small class="text-muted">
                                    Characters: <span id="long-desc-count">0</span>
                                </small>
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
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush

@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>

    <script>
        const MAX_SHORT = 300;

        CKEDITOR.replace('lesson_description', {
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });

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
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
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
            CKEDITOR.instances.lesson_description.on('change', updateLongCount);

            // ✅ Form validation
            form.addEventListener("submit", function(e) {
                const shortText = getPlainText(CKEDITOR.instances.short_description);
                if (shortText.length > MAX_SHORT) {
                    e.preventDefault();

                }
            });
        });
    </script>
@endpush
