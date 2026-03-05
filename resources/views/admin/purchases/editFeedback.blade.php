@extends('layouts.main')
@section('title', __('Edit Feedback'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Feedback') }}</li>
@endsection
@section('content')

    <style>
        .add-video-btn,
        .remove-video-btn,
        .remove-new-video-btn {
            height: 100%;
        }

        @media (min-width: 768px) {

            .remove-video-btn,
            .remove-new-video-btn {
                height: 30%;
            }
        }

        @media (max-width: 768px) {
            .card-body {
                max-height: unset;
            }
        }

        .existing-video {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin-bottom: 10px;
        }

        .progress {
            height: 20px;
            margin-bottom: 10px;
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
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
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

        .file-info {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
        }

        .upload-btn {
            display: none;
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .video-row {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .video-row:last-child {
            border-bottom: none;
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-10 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Feedback') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'purchase.feedback.update',
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
                            'class' => 'form-horizontal',
                            'data-validate',
                            'id' => 'feedbackForm',
                        ]) !!}
                        <div class="row">
                            <input type="hidden" name="purchase_video_id" value="{{ $purchaseVideo->id }}" />
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}" />
                            <input type="hidden" name="redirect" value="1" />

                            <div id="video-container">
                                @php
                                    $existingFeedback = json_decode($purchaseVideo->feedback, true) ?? [];
                                    $existingVideos = [];
                                    if ($feedbackContent && !empty($feedbackContent->url)) {
                                        if (is_string($feedbackContent->url)) {
                                            $existingVideos = json_decode($feedbackContent->url, true) ?? [];
                                        } else {
                                            $existingVideos = $feedbackContent->url;
                                        }
                                    }
                                @endphp

                                {{-- EXISTING ROWS --}}
                                @foreach ($existingFeedback as $index => $feedback)
                                    <div class="row video-row mb-3 existing-row"
                                        data-row-index="existing-{{ $index }}">
                                        <div class="col-12 col-md-10">
                                            <div class="existing-video">
                                                <small class="text-muted">Existing Video {{ $index + 1 }}</small>
                                                @if (isset($existingVideos[$index]['url']))
                                                    <div class="mb-2">
                                                        <a href="{{ $existingVideos[$index]['url'] }}" target="_blank"
                                                            class="btn btn-sm btn-info">
                                                            View Current Video
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="hidden" name="existing_index[]" value="{{ $index }}">
                                            <input type="hidden" name="existing_video_url[{{ $index }}]"
                                                value="{{ $existingVideos[$index]['url'] ?? '' }}">

                                            <label class="form-label">Replace Video (Optional)</label>
                                            <input type="file" name="existing_fdbk_video[{{ $index }}]"
                                                class="form-control video-input" data-row="existing-{{ $index }}"
                                                accept="image/*,video/*">
                                            <div class="file-info" id="fileInfo-existing-{{ $index }}"></div>

                                            <button type="button" class="btn btn-primary btn-sm upload-btn"
                                                id="uploadBtn-existing-{{ $index }}"
                                                data-row="existing-{{ $index }}">
                                                {{ __('Upload Replacement Video') }}
                                            </button>

                                            <div class="progress" id="progressContainer-existing-{{ $index }}">
                                                <div class="progress-bar" id="progressBar-existing-{{ $index }}"
                                                    role="progressbar" style="width: 0%;">
                                                    0%
                                                </div>
                                            </div>

                                            <div class="upload-status" id="uploadStatus-existing-{{ $index }}">
                                            </div>
                                            <input type="hidden" name="existing_fdbk_video_path[{{ $index }}]"
                                                id="videoPath-existing-{{ $index }}">
                                            <input type="hidden" name="existing_fdbk_video_type[{{ $index }}]"
                                                id="videoType-existing-{{ $index }}">

                                            <label class="form-label mt-2">Feedback</label>
                                            <textarea name="existing_row_feedback[{{ $index }}]" class="form-control" required placeholder="Enter Feedback">{{ $feedback }}</textarea>
                                        </div>

                                        <div
                                            class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                            <button type="button" class="btn btn-danger remove-video-btn"
                                                data-index="{{ $index }}">–</button>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- NEW ROWS CONTAINER --}}
                                <div id="new-rows-container">
                                    <!-- New rows will be added here -->
                                </div>

                                {{-- ADD NEW ROW BUTTON --}}
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-success add-video-btn">+ Add Feedback</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Update'), [
                                'type' => 'submit',
                                'class' => 'btn btn-primary',
                                'id' => 'submitBtn',
                            ]) }}
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
    let newRowCount = 0;
    let uploadInProgress = false;
    let uploadTrackers = {}; // Track upload status for each row

    function refreshButtons() {
        let existingRows = document.querySelectorAll('#video-container .existing-row');
        let newRows = document.querySelectorAll('#video-container .new-row');
        
        // Show remove button on all rows except if only one exists
        existingRows.forEach((row, index) => {
            let removeBtn = row.querySelector('.remove-video-btn');
            if (existingRows.length > 1) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    function createNewRow() {
        let container = document.getElementById('new-rows-container');
        let newRowIndex = `new-${newRowCount++}`;

        let newRow = document.createElement('div');
        newRow.className = 'row video-row mb-3 new-row';
        newRow.setAttribute('data-row-index', newRowIndex);

        newRow.innerHTML = `
            <div class="col-12 col-md-10">
                <label class="form-label">New Video</label>
                <input type="file" name="new_fdbk_video[]" class="form-control video-input" data-row="${newRowIndex}" accept="image/*,video/*" required>
                <div class="file-info" id="fileInfo-${newRowIndex}"></div>
                
                <button type="button" class="btn btn-primary btn-sm upload-btn" id="uploadBtn-${newRowIndex}" data-row="${newRowIndex}">
                    {{ __('Upload Video First') }}
                </button>

                <div class="progress" id="progressContainer-${newRowIndex}">
                    <div class="progress-bar" id="progressBar-${newRowIndex}" role="progressbar" style="width: 0%;">
                        0%
                    </div>
                </div>

                <div class="upload-status" id="uploadStatus-${newRowIndex}"></div>
                <input type="hidden" name="new_fdbk_video_path[]" id="videoPath-${newRowIndex}">
                <input type="hidden" name="new_fdbk_video_type[]" id="videoType-${newRowIndex}">

                <label class="form-label mt-2">New Feedback</label>
                <textarea name="new_row_feedback[]" class="form-control" required placeholder="Enter Feedback"></textarea>
            </div>
            <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                <button type="button" class="btn btn-danger remove-new-video-btn">–</button>
            </div>
        `;

        container.appendChild(newRow);
        initializeRowEvents(newRowIndex);
        
        // Initialize tracker for new row
        uploadTrackers[newRowIndex] = {
            hasFile: false,
            isUploaded: false,
            isUploading: false
        };
    }

    function initializeRowEvents(rowIndex) {
        const fileInput = document.querySelector(`.video-input[data-row="${rowIndex}"]`);
        const uploadBtn = document.getElementById(`uploadBtn-${rowIndex}`);
        const progressBar = document.getElementById(`progressBar-${rowIndex}`);
        const progressContainer = document.getElementById(`progressContainer-${rowIndex}`);
        const uploadStatus = document.getElementById(`uploadStatus-${rowIndex}`);
        const videoPath = document.getElementById(`videoPath-${rowIndex}`);
        const videoType = document.getElementById(`videoType-${rowIndex}`);
        const fileInfo = document.getElementById(`fileInfo-${rowIndex}`);

        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;
                uploadBtn.style.display = 'block';
                videoPath.value = '';
                
                // Update tracker
                uploadTrackers[rowIndex] = {
                    hasFile: true,
                    isUploaded: false,
                    isUploading: false
                };
                
                updateSubmitButton();
            } else {
                uploadBtn.style.display = 'none';
                fileInfo.innerHTML = '';
                
                // Update tracker - no file selected
                uploadTrackers[rowIndex] = {
                    hasFile: false,
                    isUploaded: false,
                    isUploading: false
                };
                
                updateSubmitButton();
            }
        });

        uploadBtn.addEventListener('click', function() {
            const file = fileInput.files[0];
            if (!file) return;
            uploadVideoChunks(file, rowIndex);
        });
    }

    function uploadVideoChunks(file, rowIndex) {
        const CHUNK_SIZE = 5 * 1024 * 1024;
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        let currentChunk = 0;

        const uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const uploadBtn = document.getElementById(`uploadBtn-${rowIndex}`);
        const progressBar = document.getElementById(`progressBar-${rowIndex}`);
        const progressContainer = document.getElementById(`progressContainer-${rowIndex}`);
        const uploadStatus = document.getElementById(`uploadStatus-${rowIndex}`);
        const videoPath = document.getElementById(`videoPath-${rowIndex}`);
        const videoType = document.getElementById(`videoType-${rowIndex}`);

        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';
        progressContainer.style.display = 'block';
        
        // Update tracker
        uploadTrackers[rowIndex].isUploading = true;
        uploadInProgress = true;
        
        updateSubmitButton();

        showStatus(rowIndex, 'Starting upload...', 'info');

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
            formData.append('folderName', 'feedback_videos');
            formData.append('_token', '{{ csrf_token() }}');

            const progress = ((currentChunk + 1) / totalChunks) * 100;
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';

            fetch('{{ route("album.upload.chunk") }}', {
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
                        finalizeUpload(data.fileUrl, data.fileType, rowIndex);
                    }
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                console.error('Upload failed:', error);
                showStatus(rowIndex, 'Upload failed: ' + error.message, 'error');
                uploadBtn.disabled = false;
                uploadBtn.textContent = rowIndex.startsWith('existing-') ? 'Upload Replacement Video' : 'Upload Video First';
                
                // Update tracker
                uploadTrackers[rowIndex].isUploading = false;
                uploadInProgress = Object.values(uploadTrackers).some(tracker => tracker.isUploading);
                
                updateSubmitButton();
            });
        }

        uploadChunk();
    }

    function finalizeUpload(filePath, fileType, rowIndex) {
        const videoPath = document.getElementById(`videoPath-${rowIndex}`);
        const videoType = document.getElementById(`videoType-${rowIndex}`);
        const uploadBtn = document.getElementById(`uploadBtn-${rowIndex}`);

        videoPath.value = filePath;
        videoType.value = fileType;

        showStatus(rowIndex, 'Video uploaded successfully!', 'success');
        uploadBtn.style.display = 'none';
        uploadBtn.disabled = false;
        
        // Update tracker
        uploadTrackers[rowIndex].isUploaded = true;
        uploadTrackers[rowIndex].isUploading = false;
        uploadInProgress = Object.values(uploadTrackers).some(tracker => tracker.isUploading);
        
        updateSubmitButton();
    }

    function showStatus(rowIndex, message, type) {
        const uploadStatus = document.getElementById(`uploadStatus-${rowIndex}`);
        if (!uploadStatus) return;
        
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

    function updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        
        // Check if any uploads are in progress
        const anyUploadInProgress = Object.values(uploadTrackers).some(tracker => tracker.isUploading);
        
        // Check if any rows have files selected but not uploaded
        const hasUnuploadedFiles = Object.values(uploadTrackers).some(tracker => 
            tracker.hasFile && !tracker.isUploaded && !tracker.isUploading
        );
        
        // Disable submit button if:
        // 1. Any upload is in progress, OR
        // 2. Any file is selected but not uploaded
        submitBtn.disabled = anyUploadInProgress || hasUnuploadedFiles;
        
        // Update button text to show reason for disable
        if (submitBtn.disabled) {
            if (anyUploadInProgress) {
                submitBtn.title = 'Please wait for uploads to complete';
            } else if (hasUnuploadedFiles) {
                submitBtn.title = 'Please upload all selected videos first';
            }
        } else {
            submitBtn.title = '';
        }
    }

    function initializeExistingRows() {
        // Initialize events for existing rows
        @foreach($existingFeedback as $index => $feedback)
            initializeRowEvents('existing-{{ $index }}');
            
            // Initialize tracker for existing rows (no file selected initially)
            uploadTrackers['existing-{{ $index }}'] = {
                hasFile: false,
                isUploaded: false,
                isUploading: false
            };
        @endforeach
    }

    document.addEventListener('DOMContentLoaded', function() {
        refreshButtons();
        initializeExistingRows();
        
        // Form submission validation
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            // Check if any uploads are in progress
            const anyUploadInProgress = Object.values(uploadTrackers).some(tracker => tracker.isUploading);
            
            // Check if any rows have files selected but not uploaded
            const hasUnuploadedFiles = Object.values(uploadTrackers).some(tracker => 
                tracker.hasFile && !tracker.isUploaded && !tracker.isUploading
            );

            if (anyUploadInProgress) {
                e.preventDefault();
                // Show error on first available row
                const firstRow = document.querySelector('.video-row');
                if (firstRow) {
                    const rowIndex = firstRow.getAttribute('data-row-index');
                    showStatus(rowIndex, 'Please wait for uploads to complete before submitting.', 'error');
                }
            }

            if (hasUnuploadedFiles) {
                e.preventDefault();
                // Show error on first unuploaded row
                for (const [rowIndex, tracker] of Object.entries(uploadTrackers)) {
                    if (tracker.hasFile && !tracker.isUploaded && !tracker.isUploading) {
                        showStatus(rowIndex, 'Please upload the video first before submitting.', 'error');
                        break;
                    }
                }
            }
        });
    });

    document.addEventListener('click', function(e) {
        // ADD NEW ROW
        if (e.target.classList.contains('add-video-btn')) {
            createNewRow();
        }

        // REMOVE EXISTING ROW
        if (e.target.classList.contains('remove-video-btn')) {
            let index = e.target.getAttribute('data-index');
            let rowIndex = `existing-${index}`;
            
            // Remove from upload trackers
            delete uploadTrackers[rowIndex];
            
            let deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'deleted_indexes[]';
            deleteInput.value = index;
            document.getElementById('video-container').appendChild(deleteInput);
            
            e.target.closest('.video-row').remove();
            refreshButtons();
            updateSubmitButton();
        }

        // REMOVE NEW ROW
        if (e.target.classList.contains('remove-new-video-btn')) {
            const row = e.target.closest('.video-row');
            const rowIndex = row.getAttribute('data-row-index');
            
            // Remove from upload trackers
            delete uploadTrackers[rowIndex];
            
            row.remove();
            updateSubmitButton();
        }
    });
</script>
@endpush

