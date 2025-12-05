@extends('layouts.main')
@section('title', __('Edit Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Lesson') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-8 col-md-8 col-xxl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Lesson') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($user, [
                            'route' => ['lesson.update', $user->id],
                            'method' => 'PUT',
                            'data-validate',
                            'enctype' => 'multipart/form-data',
                        ]) !!}

                        @if ($user->is_package_lesson)
                            <div class="form-group">
                                <div class="form-check">
                                    {!! Form::checkbox('is_package_lesson', 1, true, [
                                        'class' => 'form-check-input',
                                        'id' => 'is_package_lesson',
                                        'disabled' => 'disabled',
                                    ]) !!}
                                    {{ Form::label('is_package_lesson', __('Package Lesson (Cannot be changed)'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                        </div>

                        <div class="form-group">
                            {{ Form::label('price', __('Price ($)'), ['class' => 'form-label']) }}
                            {!! Form::number('lesson_price', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('Enter Price'),
                            ]) !!}
                        </div>

                        @if ($user->type !== 'inPerson')
                            {{-- <div class="form-group">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_quantity', null, ['class' => 'form-control', 'placeholder' => __('Enter Quantity')]) !!}
                            </div> --}}

                            <div class="form-group">
                                {{ Form::label('response_time', __('Response Time'), ['class' => 'form-label']) }}
                                {!! Form::number('required_time', null, ['class' => 'form-control', 'placeholder' => __('Enter Required Time')]) !!}
                            </div>
                        @endif

                        @if ($user->type === 'inPerson')
                            <div class="form-group">
                                {{ Form::label('lesson_duration', __('Duration (hours)'), ['class' => 'form-label']) }}
                                {!! Form::select(
                                    'lesson_duration',
                                    [
                                        '0.5' => '30 Minutes',
                                        '0.75' => '45 Minutes',
                                        '1' => '1 Hour',
                                        '1.5' => '1.5 Hours',
                                        '2' => '2 Hours',
                                        '2.5' => '2.5 Hours',
                                        '3' => '3 Hours',
                                    ],
                                    null,
                                    ['class' => 'form-control', 'data-trigger', 'required', 'placeholder' => __('Duration')],
                                ) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('max_followers', __('Group Size'), ['class' => 'form-label']) }}
                                {!! Form::number('max_followers', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter group size'),
                                    'min' => 1,
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash', 'both' => 'Both'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'placeholder' => __('Payment Method'),
                                ]) !!}
                            </div>
                        @endif

                           <div class="form-group">
                            {{ Form::label('logo', __('Logo'), ['class' => 'form-label']) }}
                            {!! Form::file('logo', ['class' => 'form-control']) !!}

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

                        <div class="form-group">
                            {{ Form::label('description', __('Long Description'), ['class' => 'form-label']) }}
                            {!! Form::textarea('lesson_description', null, [
                                'class' => 'form-control',
                                'required',
                                'id' => 'lesson_description',
                                'placeholder' => __('Enter Long Description'),
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
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        const MAX_SHORT = 300;

        CKEDITOR.replace('lesson_description', {
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form',
            removeButtons: 'Link,Unlink'
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

        function getPlainText(editor) {
            return editor.getData().replace(/<[^>]*>/g, '').trim();
        }

        function updateShortCount(evt) {
            const editor = evt.editor;
            const text = getPlainText(editor);
            const length = text.length;

            document.getElementById("short-desc-count").textContent = length;

            if (length >= MAX_SHORT) {
                editor.container.addClass('is-invalid');
                document.getElementById("short-desc-warning").style.display = 'block';
            } else {
                editor.container.removeClass('is-invalid');
                document.getElementById("short-desc-warning").style.display = 'none';
            }
        }

        function updateLongCount(evt) {
            const editor = evt.editor;
            const text = getPlainText(editor);
            document.getElementById("long-desc-count").textContent = text.length;
        }

        CKEDITOR.instances.short_description.on('key', function(evt) {
            const text = getPlainText(evt.editor);
            if (text.length >= MAX_SHORT && evt.data.keyCode != 8 && evt.data.keyCode != 46) {
                evt.cancel(); // stop typing when limit reached
            }
        });

        CKEDITOR.instances.short_description.on('change', updateShortCount);
        CKEDITOR.instances.lesson_description.on('change', updateLongCount);

        document.querySelector("form").addEventListener("submit", function(e) {
            const shortText = getPlainText(CKEDITOR.instances.short_description);
            if (shortText.length > MAX_SHORT) {
                e.preventDefault();

            }
        });
    </script>
@endpush
