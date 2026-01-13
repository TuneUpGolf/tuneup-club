@extends('layouts.main')
@section('title', __('Add Video'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Video') }}</li>
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

        #fileInfo1,
        #fileInfo2 {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
        }

        .upload-btn {
            display: none;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add detail for your instructor to view') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => [
                                'purchase.video.add',
                                ['purchase_id' => $purchase->id ?? null, 'redirect' => true, 'checkout' => request()->checkout],
                            ],
                            'method' => 'POST',
                            'data-validate',
                            'files' => 'true',
                            'enctype' => 'multipart/form-data',
                            'id' => 'videoForm',
                        ]) !!}

                        <!-- Video 1 (Required) -->
                        <div class="form-group">
                            {{ Form::label('video', __('Submit Video or Image'), ['class' => 'form-label']) }} *
                            {{ Form::file('video', [
                                'class' => 'form-control',
                                'id' => 'fileInput1',
                                'accept' => 'image/*,video/*',
                                'required' => 'required',
                            ]) }}
                            <div id="fileInfo1"></div>

                            <button type="button" id="uploadBtn1" class="btn btn-primary btn-sm upload-btn">
                                {{ __('Click Here - Upload Video/Image First') }}
                            </button>

                            <div class="progress" id="progressContainer1">
                                <div class="progress-bar" id="progressBar1" role="progressbar" style="width: 0%;">
                                    0%
                                </div>
                            </div>

                            <div class="upload-status" id="uploadStatus1"></div>
                            <input type="hidden" name="video_path" id="videoPath1">
                            <input type="hidden" name="video_type" id="videoType1">
                        </div>

                        <!-- Video 2 (Optional) -->
                        <div class="form-group">
                            {{ Form::label('video_2', __('Submit Video or Image (Optional)'), ['class' => 'form-label']) }}
                            {{ Form::file('video_2', [
                                'class' => 'form-control',
                                'id' => 'fileInput2',
                                'accept' => 'image/*,video/*',
                            ]) }}
                            <div id="fileInfo2"></div>

                            <button type="button" id="uploadBtn2" class="btn btn-primary btn-sm upload-btn">
                                {{ __('Click Here - Upload Video/Image First') }}
                            </button>

                            <div class="progress" id="progressContainer2">
                                <div class="progress-bar" id="progressBar2" role="progressbar" style="width: 0%;">
                                    0%
                                </div>
                            </div>

                            <div class="upload-status" id="uploadStatus2"></div>
                            <input type="hidden" name="video_2_path" id="videoPath2">
                            <input type="hidden" name="video_2_type" id="videoType2">
                        </div>

                        <div class="form-group">
                            {{ Form::label('Provide a note to your instructor', __('Provide a note to your instructor'), ['class' => 'form-label']) }}
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
                                {{ Form::button(__('Submit'), [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary',
                                    'id' => 'submitBtn',
                                ]) }}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration for both file inputs
            const fileConfigs = {
                1: {
                    fileInput: document.getElementById('fileInput1'),
                    uploadBtn: document.getElementById('uploadBtn1'),
                    progressBar: document.getElementById('progressBar1'),
                    progressContainer: document.getElementById('progressContainer1'),
                    uploadStatus: document.getElementById('uploadStatus1'),
                    videoPath: document.getElementById('videoPath1'),
                    videoType: document.getElementById('videoType2'),
                    fileInfo: document.getElementById('fileInfo1'),
                    isRequired: true
                },
                2: {
                    fileInput: document.getElementById('fileInput2'),
                    uploadBtn: document.getElementById('uploadBtn2'),
                    progressBar: document.getElementById('progressBar2'),
                    progressContainer: document.getElementById('progressContainer2'),
                    uploadStatus: document.getElementById('uploadStatus2'),
                    videoPath: document.getElementById('videoPath2'),
                    videoType: document.getElementById('videoType2'),
                    fileInfo: document.getElementById('fileInfo2'),
                    isRequired: false
                }
            };

            const submitBtn = document.getElementById('submitBtn');
            let uploadInProgress = false;

            // Initialize both file inputs
            Object.keys(fileConfigs).forEach(key => {
                const config = fileConfigs[key];

                config.fileInput.addEventListener('change', function(e) {
                    handleFileSelect(e, config);
                });

                config.uploadBtn.addEventListener('click', function() {
                    handleUpload(config);
                });
            });

            // Form submission validation
            document.getElementById('videoForm').addEventListener('submit', function(e) {
                const requiredConfig = fileConfigs[1];
                const file = requiredConfig.fileInput.files[0];

                // Check if required file is selected and not uploaded yet
                if (file && !requiredConfig.videoPath.value) {
                    e.preventDefault();
                    showStatus(requiredConfig, 'Please upload the video first before submitting the form.',
                        'error');
                }

                // Check if any upload is in progress
                if (uploadInProgress) {
                    e.preventDefault();
                    showStatus(fileConfigs[1], 'Please wait for uploads to complete before submitting.',
                        'error');
                }
            });

            function handleFileSelect(e, config) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    config.fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;

                    // Show upload button for large files or all files
                    config.uploadBtn.style.display = 'block';
                    config.videoPath.value = ''; // Reset previous upload

                    // Disable submit if required file is not uploaded
                    if (config.isRequired) {
                        submitBtn.disabled = true;
                    }
                } else {
                    config.uploadBtn.style.display = 'none';
                    config.fileInfo.innerHTML = '';
                }
            }

            function handleUpload(config) {
                const file = config.fileInput.files[0];
                if (!file) return;

                uploadVideoChunks(file, config);
            }

            function uploadVideoChunks(file, config) {
                const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB chunks
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                let currentChunk = 0;

                // Generate unique upload ID
                const uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                config.uploadBtn.disabled = true;
                config.uploadBtn.textContent = 'Uploading...';
                config.progressContainer.style.display = 'block';
                uploadInProgress = true;
                submitBtn.disabled = true;

                showStatus(config, 'Starting upload...', 'info');

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
                    formData.append('folderName', 'online_video_submissions');
                    formData.append('_token', '{{ csrf_token() }}');

                    // Update progress
                    const progress = ((currentChunk + 1) / totalChunks) * 100;
                    config.progressBar.style.width = progress + '%';
                    config.progressBar.textContent = Math.round(progress) + '%';

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
                                    finalizeUpload(data.fileUrl, data.fileType, config);
                                }
                            } else {
                                throw new Error(data.message || 'Upload failed');
                            }
                        })
                        .catch(error => {
                            console.error('Upload failed:', error);
                            showStatus(config, 'Upload failed: ' + error.message, 'error');
                            config.uploadBtn.disabled = false;
                            config.uploadBtn.textContent = 'Click Here - Upload Video/Image First';
                            uploadInProgress = false;
                            updateSubmitButton();
                        });
                }

                uploadChunk();
            }

            function finalizeUpload(filePath, fileType, config) {
                config.videoPath.value = filePath;
                config.videoType.value = fileType;

                showStatus(config, 'File uploaded successfully!', 'success');
                config.uploadBtn.style.display = 'none';
                config.uploadBtn.disabled = false;
                uploadInProgress = false;

                updateSubmitButton();
            }

            function updateSubmitButton() {
                // Enable submit button only if required file is uploaded and no uploads in progress
                const requiredConfig = fileConfigs[1];
                const requiredUploaded = requiredConfig.videoPath.value !== '';
                const noUploadsInProgress = !uploadInProgress;

                submitBtn.disabled = !(requiredUploaded && noUploadsInProgress);
            }

            function showStatus(config, message, type) {
                config.uploadStatus.textContent = message;
                config.uploadStatus.className = 'upload-status';

                if (type === 'success') {
                    config.uploadStatus.classList.add('upload-success');
                } else if (type === 'error') {
                    config.uploadStatus.classList.add('upload-error');
                } else {
                    config.uploadStatus.style.backgroundColor = '#d1ecf1';
                    config.uploadStatus.style.color = '#0c5460';
                }

                config.uploadStatus.style.display = 'block';
            }
        });
    </script>
@endpush
