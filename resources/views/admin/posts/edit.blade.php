@extends('layouts.main')
@section('title', __('Edit Post'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Post') }}</li>
@endsection
@section('content')

    <style>
        .progress {
            height: 20px;
            margin-bottom: 20px;
            overflow: hidden;
            background-color: #f5f5f5;
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
            display: none;
        }

        .progress-bar {
            float: left;
            width: 0%;
            height: 100%;
            font-size: 12px;
            line-height: 20px;
            color: #fff;
            text-align: center;
            background-color: #007bff;
            transition: width .6s ease;
        }

        .upload-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }

        .upload-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .upload-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        #fileInfo {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
        }

        #uploadBtn {
            display: none;
            margin-top: 10px;
            margin-bottom: 10px;

        }
    </style>

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
                                    {{ Form::label('file', __('Photo / Video'), ['class' => 'form-label']) }} *
                                    {!! Form::file('file', [
                                        'class' => 'form-control',
                                        'id' => 'fileInput',
                                        'accept' => 'image/*,video/*',
                                    ]) !!}
                                    <div id="fileInfo"></div>

                                    <!-- Upload Button -->
                                    <button type="button" id="uploadBtn" class="btn btn-primary btn-sm">
                                        {{ __('Click Here - Upload Video/Image First') }}
                                    </button>

                                    <!-- Progress Bar -->
                                    <div class="progress" id="progressContainer">
                                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;">0%
                                        </div>
                                    </div>

                                    <!-- Upload Status -->
                                    <div class="upload-status" id="uploadStatus"></div>

                                    <!-- Hidden field for chunk path -->
                                    <input type="hidden" name="chunk_path" id="chunkPath" value="{{ $posts->file }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            {{-- <div class="form-group">
                                {{ Form::label('slug', __('Slug'), ['class' => 'form-label']) }} *
                                {!! Form::text('slug', $posts->slug ?? null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter slug'),
                                    'required' => 'required',
                                ]) !!}
                            </div> --}}
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
                <input type="hidden" name="filePath" id="filePath" value="{{ $posts->file }}">
                <input type="hidden" name="fileType" id="fileType" value="{{ $posts->file_type }}">
                <div class="card-footer">
                    <div class="text-end">
                        <a href="{{ route('blogs.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'submitBtn']) }}
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const uploadBtn = document.getElementById('uploadBtn');
            const progressBar = document.getElementById('progressBar');
            const progressContainer = document.getElementById('progressContainer');
            const uploadStatus = document.getElementById('uploadStatus');
            const chunkPathInput = document.getElementById('chunkPath');
            const submitBtn = document.getElementById('submitBtn');
            const fileInfo = document.getElementById('fileInfo');

            let uploadId = null;

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;

                    // if (file.size > 10 * 1024 * 1024) {
                    uploadBtn.style.display = 'block';
                    chunkPathInput.value = '';
                    submitBtn.disabled = true;
                    // } else {
                    //      uploadBtn.style.display = 'none';
                    //     chunkPathInput.value = 'direct_upload';
                    // }
                }
            });

            uploadBtn.addEventListener('click', function() {
                const file = fileInput.files[0];
                if (!file) return;
                uploadVideoChunks(file);
            });

            document.getElementById('postForm').addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (file && file.size > 10 * 1024 * 1024 && !chunkPathInput.value) {
                    e.preventDefault();
                    showStatus('Please upload the video first before submitting the form.', 'error');
                }
            });

            function uploadVideoChunks(file) {
                const CHUNK_SIZE = 5 * 1024 * 1024;
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                let currentChunk = 0;
                uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Uploading...';
                progressContainer.style.display = 'block';
                showStatus('Starting upload...', 'info');

                function uploadChunk() {
                    const start = currentChunk * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('chunkIndex', currentChunk);
                    formData.append('totalChunks', totalChunks);
                    formData.append('originalName', file.name);
                    formData.append('uploadId', uploadId);
                    formData.append('folderName', 'posts');

                    formData.append('_token', '{{ csrf_token() }}');

                    const progress = ((currentChunk + 1) / totalChunks) * 100;
                    progressBar.style.width = progress + '%';
                    progressBar.textContent = Math.round(progress) + '%';

                    fetch('{{ route('album.upload.chunk') }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                currentChunk++;
                                if (currentChunk < totalChunks) {
                                    uploadChunk();
                                } else {
                                    finalizeUpload(data.fileUrl, data.fileType);
                                }
                            } else {
                                throw new Error(data.message || 'Upload failed');
                            }
                        })
                        .catch(error => {
                            console.error('Upload failed:', error);
                            showStatus('Upload failed: ' + error.message, 'error');
                            uploadBtn.disabled = false;
                            uploadBtn.textContent = 'Click Here - Upload Video/Image First';
                        });
                }

                uploadChunk();
            }

            function finalizeUpload(filePath, fileType) {
                document.getElementById('filePath').value = filePath;
                document.getElementById('fileType').value = fileType;
                chunkPathInput.value = filePath;

                showStatus('Video uploaded successfully! You can now submit the form.', 'success');
                uploadBtn.style.display = 'none';
                saveBtn.disabled = false;
                // fetch('{{ route('album.upload.finalize') }}', {
                //         method: 'POST',
                //         headers: {
                //             'Content-Type': 'application/json',
                //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                //         },
                //         body: JSON.stringify({
                //             fileName: fileName,
                //             uploadId: uploadId
                //         })
                //     })
                //     .then(response => response.json())
                //     .then(data => {
                //         if (data.success) {
                //             chunkPathInput.value = data.chunkPath + '|' + fileName;
                //             showStatus('Video uploaded successfully! You can now submit the form.', 'success');
                //             uploadBtn.style.display = 'none';
                //             submitBtn.disabled = false;
                //         } else {
                //             throw new Error(data.message || 'Finalization failed');
                //         }
                //     })
                //     .catch(error => {
                //         showStatus('Finalization failed: ' + error.message, 'error');
                //         uploadBtn.disabled = false;
                //         uploadBtn.textContent = 'Upload Video First';
                //         submitBtn.disabled = false;
                //     });
            }

            function showStatus(message, type) {
                uploadStatus.textContent = message;
                uploadStatus.className = 'upload-status';

                if (type === 'success') {
                    uploadStatus.classList.add('upload-success');
                } else if (type === 'error') {
                    uploadStatus.classList.add('upload-error');
                } else {
                    uploadStatus.style.backgroundColor = '#d1ecf1';
                    uploadStatus.style.color = '#0c5460';
                }

                uploadStatus.style.display = 'block';
            }
        });
    </script>
@endpush
