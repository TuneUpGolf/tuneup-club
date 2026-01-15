@extends('layouts.main')
@section('title', __('Create Post'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Post') }}</li>
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

        .switch-button-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .switch-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0;
        }

        .price-container {
            transition: all 0.3s ease;
        }

        .hidden {
            display: none !important;
            opacity: 0;
            height: 0;
            overflow: hidden;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .card-header {
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }

        .text-end {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .required::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="col-lg-10 col-xl-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Create Post') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'blogs.store',
                            'method' => 'POST',
                            'class' => 'form-horizontal',
                            'data-validate',
                            'enctype' => 'multipart/form-data',
                            'id' => 'postForm',
                        ]) !!}

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="title" class="form-label required">{{ __('Title') }}</label>
                                    {!! Form::text('title', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter title'),
                                        'required' => 'required',
                                        'id' => 'title',
                                    ]) !!}
                                </div>

                                <div class="form-group">
                                    <label for="category_id" class="form-label">{{ __('Category') }}</label>
                                    <select name="category_id" id="category_id" class="form-select">
                                        <option value="">Select Category (Optional)</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">If no category is selected, price settings will be
                                        available</small>
                                </div>

                                <!-- Price and Switch Container - Only show for non-Student users -->
                                @if (Auth::user()->type != 'Student')
                                    <div id="priceSwitchContainer" class="price-container">
                                        <div class="switch-button-container">
                                            <div>
                                                <label class="switch-label">{{ __('Paid Content') }}</label>
                                                <div class="form-check form-switch">
                                                    {!! Form::checkbox('paid', 1, true, [
                                                        'class' => 'form-check-input',
                                                        'id' => 'paidSwitch',
                                                        'role' => 'switch',
                                                        'style' => 'width: 3em; height: 1.5em;',
                                                    ]) !!}
                                                    <label class="form-check-label" for="paidSwitch"></label>
                                                </div>
                                            </div>

                                            <div id="priceFieldContainer">
                                                <div class="form-group" style="margin-bottom: 0;">
                                                    <label for="price" class="form-label">{{ __('Price') }}</label>
                                                    {!! Form::number('price', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => __('0.00'),
                                                        'step' => '0.01',
                                                        'min' => '0',
                                                        'id' => 'price',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="description" class="form-label required">{{ __('Description') }}</label>
                                    {!! Form::textarea('description', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter description'),
                                        'required' => 'required',
                                        'rows' => '8',
                                        'id' => 'description',
                                    ]) !!}
                                </div>
                                  <div class="form-group">
                                    <label for="short_description"
                                        class="form-label required">{{ __('Short Description') }}</label>
                                    {!! Form::textarea('short_description', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter short_description'),
                                        'required' => 'required',
                                        'rows' => '8',
                                        'id' => 'short_description',
                                    ]) !!}
                                </div>
                            </div>

                          
                        </div>

                        <!-- File Upload Section - Full Width -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="file" class="form-label required">{{ __('Photo / Video') }}</label>
                                    <div class="input-group">
                                        {!! Form::file('file', [
                                            'class' => 'form-control',
                                            'id' => 'fileInput',
                                            'accept' => 'image/*,video/*',
                                            'required' => 'required',
                                        ]) !!}
                                        <button type="button" id="uploadBtn" class="btn btn-primary">
                                            {{ __('Upload') }}
                                        </button>
                                    </div>
                                    <div id="fileInfo" class="mt-2"></div>

                                    <!-- Progress Bar -->
                                    <div class="progress mt-2" id="progressContainer">
                                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;">0%
                                        </div>
                                    </div>

                                    <!-- Upload Status -->
                                    <div class="upload-status mt-2" id="uploadStatus"></div>

                                    <!-- Hidden fields -->
                                    <input type="hidden" name="chunk_path" id="chunkPath">
                                    <input type="hidden" name="filePath" value="test" id="filePath">
                                    <input type="hidden" name="fileType" value="image" id="fileType">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-0">
                            <div class="text-end pt-3">
                                <a href="{{ route('blogs.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {!! Form::button(__('Save Changes'), [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary',
                                    'id' => 'submitBtn',
                                    'disabled' => true,
                                ]) !!}
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
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script type="text/javascript">
        // Initialize CKEditor
        CKEDITOR.replace('description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form',
            height: 300
        });

        CKEDITOR.replace('short_description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form',
            height: 300
        });

        // Initialize Choices.js
        document.addEventListener('DOMContentLoaded', function() {
            var categorySelect = document.getElementById('category_id');
            if (categorySelect) {
                new Choices(categorySelect, {
                    removeItemButton: true,
                    searchEnabled: true,
                    placeholder: true,
                    searchPlaceholderValue: 'Search categories...'
                });
            }

            // Toggle price field based on paid switch
            const paidSwitch = document.getElementById('paidSwitch');
            const priceFieldContainer = document.getElementById('priceFieldContainer');

            if (paidSwitch) {
                paidSwitch.addEventListener('change', function() {
                    if (this.checked) {
                        priceFieldContainer.style.display = 'block';
                        document.getElementById('price').focus();
                    } else {
                        priceFieldContainer.style.display = 'none';
                        document.getElementById('price').value = '';
                    }
                });

                // Initialize based on initial state
                if (paidSwitch.checked) {
                    priceFieldContainer.style.display = 'block';
                } else {
                    priceFieldContainer.style.display = 'none';
                }
            }

            // Toggle price/switch container based on category selection
            const categorySelectElement = document.getElementById('category_id');
            const priceSwitchContainer = document.getElementById('priceSwitchContainer');

            if (categorySelectElement && priceSwitchContainer) {
                categorySelectElement.addEventListener('change', function() {
                    if (this.value) {
                        // Category selected - hide price/switch container
                        priceSwitchContainer.classList.add('hidden');
                    } else {
                        // No category selected - show price/switch container
                        priceSwitchContainer.classList.remove('hidden');
                    }
                });

                // Initialize on page load
                if (categorySelectElement.value) {
                    priceSwitchContainer.classList.add('hidden');
                }
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
                    const fileType = file.type.split('/')[0];
                    fileInfo.innerHTML = `
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                Selected: <strong>${file.name}</strong><br>
                                Size: ${fileSize} MB | Type: ${fileType.charAt(0).toUpperCase() + fileType.slice(1)}
                            </div>
                        </div>
                    `;

                    // Show upload button for all files (not just large ones)
                    uploadBtn.style.display = 'block';
                    chunkPathInput.value = '';
                    submitBtn.disabled = true;
                }
            });

            uploadBtn.addEventListener('click', function() {
                const file = fileInput.files[0];
                if (!file) {
                    showStatus('Please select a file first.', 'error');
                    return;
                }
                uploadVideoChunks(file);
            });

            document.getElementById('postForm').addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (file && !chunkPathInput.value) {
                    e.preventDefault();
                    showStatus('Please upload the file first before submitting the form.', 'error');
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
                            uploadBtn.textContent = 'Upload';
                        });
                }

                uploadChunk();
            }

            function finalizeUpload(filePath, fileType) {
                document.getElementById('filePath').value = filePath;
                document.getElementById('fileType').value = fileType;
                chunkPathInput.value = filePath;

                showStatus('File uploaded successfully! You can now submit the form.', 'success');
                uploadBtn.style.display = 'none';
                submitBtn.disabled = false;
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
                    uploadStatus.classList.add('upload-info');
                }

                uploadStatus.style.display = 'block';
            }
        });
    </script>
@endpush
