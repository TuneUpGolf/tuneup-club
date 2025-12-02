@extends('layouts.main')
@section('title', __('Create Album Category'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Album Category') }}</li>
@endsection
@section('content')
  <style>
        .card-body {
            min-height: unset !important;
        }

        @media (max-width: 768px) {
            .card-body {
                max-height: unset !important;
            }
        }
   
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
                        <h5>{{ __('Create Album Category') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'album.category.store',
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
                            'data-validate',
                        ]) !!}
                        <div class="row">
                            <div class="col-xl-6">
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
                                        'required' => 'required',
                                    ]) !!}
                                    <div id="fileInfo"></div>

                                    <!-- Upload Button -->
                                    <button type="button" id="uploadBtn" class="btn btn-primary btn-sm">
                                        {{ __('Upload Video/Image First') }}
                                    </button>

                                    <!-- Progress Bar -->
                                    <div class="progress" id="progressContainer">
                                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;">
                                            0%
                                        </div>
                                    </div>

                                    <!-- Upload Status -->
                                    <div class="upload-status" id="uploadStatus"></div>

                                    <!-- Hidden field for chunk path -->
                                    <input type="hidden" name="chunk_path" id="chunkPath">
                                </div>

                            </div>

                            <div class="col-xl-6">
                                @if (Auth::user()->type != 'Student')
                                    <div class="form-group flex flex-col">
                                        {{ Form::label('Paid', __('Paid *'), ['class' => 'form-label']) }}
                                        {!! Form::checkbox('paid', null, true, [
                                            'class' => 'form-check form-control',
                                            'data-onstyle' => 'primary',
                                            'data-toggle' => 'switchbutton',
                                        ]) !!}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                        {{ Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Enter Price'), 'step' => '0.01']) }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group">
                                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }} *
                                    {!! Form::textarea('description', null, [
                                        'class' => 'form-control col-md-12',
                                        'placeholder' => __('Enter description'),
                                        'required' => 'required',
                                    ]) !!}
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="filePath" id="filePath">
                    <input type="hidden" name="fileType" id="fileType">
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('blogs.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary',  'id' => 'submitBtn']) }}
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
            const saveBtn = document.getElementById('submitBtn');

            let uploadId = null;

            // Show upload button when file is selected
            fileInput.addEventListener('change', function(e) {
                saveBtn.disabled = false;

                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;

                    // Show upload button for large files
                    // if (file.size > 10 * 1024 * 1024) { // 10MB+
                    uploadBtn.style.display = 'block';
                    chunkPathInput.value = ''; // Reset previous upload
                    saveBtn.disabled = true;

                    // } else {
                    //     uploadBtn.style.display = 'none';
                    //     chunkPathInput.value = 'direct_upload'; // Small files upload directly
                    // }
                }
            });

            // Handle chunk upload
            uploadBtn.addEventListener('click', function() {
                const file = fileInput.files[0];
                if (!file) return;

                uploadVideoChunks(file);
            });

            // Form submission validation
            document.getElementById('albumForm').addEventListener('submit', function(e) {
                const file = fileInput.files[0];

                if (file && file.size > 10 * 1024 * 1024 && !chunkPathInput.value) {
                    e.preventDefault();
                    showStatus('Please upload the video first before submitting the form.', 'error');
                }
            });

            function uploadVideoChunks(file) {
                const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB chunks
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                let currentChunk = 0;

                // Generate unique upload ID
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
                    formData.append('folderName', 'album_category');
                    formData.append('_token', '{{ csrf_token() }}');

                    // Update progress
                    const progress = ((currentChunk + 1) / totalChunks) * 100;
                    progressBar.style.width = progress + '%';
                    progressBar.textContent = Math.round(progress) + '%';

                    fetch('{{ route('album.upload.chunk') }}', {
                            method: 'POST',
                            body: formData,
                            credentials: 'include'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                currentChunk++;

                                if (currentChunk < totalChunks) {
                                    uploadChunk();
                                } else {
                                    // All chunks uploaded
                                    // finalizeUpload(file.name);
                                    console.log(data);
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
                            uploadBtn.textContent = 'Upload Video/Image First';
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
                //             console.log(response);
                //             // Store both chunk path AND original filename
                //             chunkPathInput.value = data.chunkPath + '|' + fileName;
                //             showStatus('Video uploaded successfully! You can now submit the form.', 'success');
                //             uploadBtn.style.display = 'none';
                //             saveBtn.disabled = false;

                //         } else {
                //             throw new Error(data.message || 'Finalization failed');
                //             saveBtn.disabled = false;

                //         }
                //     })
                //     .catch(error => {
                //         showStatus('Finalization failed: ' + error.message, 'error');
                //         uploadBtn.disabled = false;
                //         uploadBtn.textContent = 'Upload Video First';
                //         saveBtn.disabled = false;

                //     });
            }

            // function finalizeUpload(fileName) {
            //     fetch('{{ route('album.upload.finalize') }}', {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //             },
            //             body: JSON.stringify({
            //                 fileName: fileName,
            //                 uploadId: uploadId
            //             })
            //         })
            //         .then(response => response.json())
            //         .then(data => {
            //             if (data.success) {
            //                 console.log(response);
            //                 // Store both chunk path AND original filename
            //                 chunkPathInput.value = data.chunkPath + '|' + fileName;
            //                 showStatus('Video uploaded successfully! You can now submit the form.', 'success');
            //                 uploadBtn.style.display = 'none';
            //                 saveBtn.disabled = false;

            //             } else {
            //                 throw new Error(data.message || 'Finalization failed');
            //                 saveBtn.disabled = false;

            //             }
            //         })
            //         .catch(error => {
            //             showStatus('Finalization failed: ' + error.message, 'error');
            //             uploadBtn.disabled = false;
            //             uploadBtn.textContent = 'Upload Video First';
            //             saveBtn.disabled = false;

            //         });
            // }

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
