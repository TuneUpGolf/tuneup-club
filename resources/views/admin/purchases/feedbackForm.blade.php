@extends('layouts.main')
@section('title', __(auth()->user()->type == 'Influencer' ? 'Provide Feedback' : 'Create Purchase'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Feedback') }}</li>
@endsection
@section('content')

<style>
    /* Your existing styles plus these additions */
    .existing-items-container {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
    }
    
    .existing-item {
        padding: 8px;
        margin-bottom: 5px;
        border: 1px solid #eee;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .existing-item:hover {
        background: #f0f0f0;
    }
    
    .existing-item.selected {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .existing-item .item-type {
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 3px;
        margin-left: 5px;
    }
    
    .item-type.post {
        background: #28a745;
        color: white;
    }
    
    .item-type.album {
        background: #ffc107;
        color: #212529;
    }
    
    .no-content-checkbox {
        margin-top: 10px;
    }
    
    .video-upload-section, .existing-selection-section {
        transition: all 0.3s;
    }
    
    .hidden-section {
        display: none;
    }
</style>

<div class="main-content">
    <section class="section">
        <div class="col-sm-12 col-md-8 m-auto">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Add Feedback') }}</h5>
                </div>
                <div class="card-body">
                    {!! Form::open([
                        'route' => ['purchase.feedback.add', $purchase->id],
                        'method' => 'Post',
                        'class' => 'form-horizontal',
                        'data-validate',
                        'id' => 'feedbackForm'
                    ]) !!}
                    <div class="row">
                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}" />
                        <input type="hidden" name="redirect" value="1" />

                        <div id="video-container">

                            {{-- FIRST VIDEO ROW (Required) --}}
                            <div class="row video-row mb-3" data-row-index="0">
                                <div class="col-12 col-md-10">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Feedback Video</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input no-content-checkbox" id="noContent-0" data-row="0">
                                            <label class="form-check-label" for="noContent-0">No Video Content (Skip)</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Upload Section -->
                                    <div class="video-upload-section" id="upload-section-0">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary toggle-existing-btn" data-row="0">
                                                Or Select from Tips/Drills
                                            </button>
                                        </div>
                                        
                                        <input type="file" name="fdbk_video[]" class="form-control video-input" data-row="0" accept="video/*">
                                        <div class="file-info" id="fileInfo-0"></div>
                                        
                                        <button type="button" class="btn btn-primary btn-sm upload-btn" id="uploadBtn-0" data-row="0">
                                            {{ __('Upload Video') }}
                                        </button>

                                        <div class="progress" id="progressContainer-0">
                                            <div class="progress-bar" id="progressBar-0" role="progressbar" style="width: 0%;">
                                                0%
                                            </div>
                                        </div>

                                        <div class="upload-status" id="uploadStatus-0"></div>
                                    </div>
                                    
                                    <!-- Existing Items Section (Initially Hidden) -->
                                    <div class="existing-selection-section hidden-section" id="existing-section-0">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary back-to-upload-btn" data-row="0">
                                                Back to Upload
                                            </button>
                                        </div>
                                        
                                        <label class="form-label">Select from Tips/Drills</label>
                                        <div class="existing-items-container" id="existing-items-0">
                                            @foreach($items as $item)
                                                <div class="existing-item" 
                                                     data-id="{{ $item->id }}" 
                                                     data-type="{{ $item->file_type }}"
                                                     data-url="{{ $item->file }}"
                                                     data-title="{{ $item->title ?? $item->name ?? 'Untitled' }}">
                                                    <strong>{{ $item->title ?? $item->name ?? 'Untitled' }}</strong>
                                                    <span class="item-type {{ $item instanceof \App\Models\Post ? 'post' : 'album' }}">
                                                        {{ $item instanceof \App\Models\Post ? 'Post' : 'Album' }}
                                                    </span>
                                                    <div><small>{!! Str::limit($item->description ?? '', 50) !!}</small></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Hidden fields to store the actual values -->
                                    <input type="hidden" name="fdbk_video_path[]" id="videoPath-0" value="no-content">
                                    <input type="hidden" name="fdbk_video_type[]" id="videoType-0" value="none">

                                    <label class="form-label mt-2">Feedback (Required)</label>
                                    <textarea name="row_feedback[]" class="form-control" required placeholder="Enter Feedback"></textarea>
                                </div>

                                <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                    <button type="button" class="btn btn-success add-video-btn">+</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-end">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::button(__('Save'), [
                            'type' => 'submit', 
                            'class' => 'btn btn-primary',
                            'id' => 'submitBtn'
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
    let uploadInProgress = false;
    let rowCounter = 1;

    function refreshButtons() {
        let rows = document.querySelectorAll('#video-container .video-row');
        rows.forEach((row, index) => {
            let addBtn = row.querySelector('.add-video-btn');
            let removeBtn = row.querySelector('.remove-video-btn');

            if (index === 0) {
                addBtn.style.display = rows.length === 1 ? 'inline-block' : 'none';
                if (removeBtn) removeBtn.remove();
            } else {
                addBtn.style.display = (index === rows.length - 1) ? 'inline-block' : 'none';
                if (!removeBtn) {
                    let btnCol = row.querySelector('.col-md-2');
                    let removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.classList.add('btn', 'btn-danger', 'remove-video-btn');
                    removeButton.innerText = '–';
                    btnCol.appendChild(removeButton);
                }
            }
        });
    }

    function updateRowState(rowIndex) {
        const noContentCheckbox = document.getElementById(`noContent-${rowIndex}`);
        const uploadSection = document.getElementById(`upload-section-${rowIndex}`);
        const existingSection = document.getElementById(`existing-section-${rowIndex}`);
        const videoPath = document.getElementById(`videoPath-${rowIndex}`);
        const videoType = document.getElementById(`videoType-${rowIndex}`);
        const fileInput = document.querySelector(`.video-input[data-row="${rowIndex}"]`);
        
        if (noContentCheckbox.checked) {
            // No content mode
            uploadSection.classList.add('hidden-section');
            existingSection.classList.add('hidden-section');
            videoPath.value = 'no-content';
            videoType.value = 'none';
            
            // Clear file input if any
            if (fileInput) fileInput.value = '';
        } else {
            // Content mode - show upload section by default
            uploadSection.classList.remove('hidden-section');
            existingSection.classList.add('hidden-section');
            
            // Don't change videoPath value here - keep whatever was set
        }
        
        updateSubmitButton();
    }

    function selectExistingItem(rowIndex, itemUrl, itemType) {
        const videoPath = document.getElementById(`videoPath-${rowIndex}`);
        const videoType = document.getElementById(`videoType-${rowIndex}`);
        const noContentCheckbox = document.getElementById(`noContent-${rowIndex}`);
        
        // Uncheck no-content if it was checked
        if (noContentCheckbox) {
            noContentCheckbox.checked = false;
        }
        
        // Set the values
        videoPath.value = itemUrl;
        videoType.value = itemType;
        
        // Hide upload section, show existing section (but we're already there)
        // Just make sure the correct sections are visible
        document.getElementById(`upload-section-${rowIndex}`).classList.add('hidden-section');
        document.getElementById(`existing-section-${rowIndex}`).classList.remove('hidden-section');
        
        updateSubmitButton();
    }

    function createNewRow() {
        let container = document.getElementById('video-container');
        let newRowIndex = rowCounter++;

        let newRow = document.createElement('div');
        newRow.className = 'row video-row mb-3';
        newRow.setAttribute('data-row-index', newRowIndex);

        newRow.innerHTML = `
            <div class="col-12 col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Additional Video</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input no-content-checkbox" id="noContent-${newRowIndex}" data-row="${newRowIndex}">
                        <label class="form-check-label" for="noContent-${newRowIndex}">No Video Content (Skip)</label>
                    </div>
                </div>
                
                <!-- Upload Section -->
                <div class="video-upload-section" id="upload-section-${newRowIndex}">
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary toggle-existing-btn" data-row="${newRowIndex}">
                            Or Select from Tips/Drills
                        </button>
                    </div>
                    
                    <input type="file" name="fdbk_video[]" class="form-control video-input" data-row="${newRowIndex}" accept="video/*">
                    <div class="file-info" id="fileInfo-${newRowIndex}"></div>
                    
                    <button type="button" class="btn btn-primary btn-sm upload-btn" id="uploadBtn-${newRowIndex}" data-row="${newRowIndex}">
                        {{ __('Upload Video') }}
                    </button>

                    <div class="progress" id="progressContainer-${newRowIndex}">
                        <div class="progress-bar" id="progressBar-${newRowIndex}" role="progressbar" style="width: 0%;">
                            0%
                        </div>
                    </div>

                    <div class="upload-status" id="uploadStatus-${newRowIndex}"></div>
                </div>
                
                <!-- Existing Items Section (Initially Hidden) -->
                <div class="existing-selection-section hidden-section" id="existing-section-${newRowIndex}">
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary back-to-upload-btn" data-row="${newRowIndex}">
                            Back to Upload
                        </button>
                    </div>
                    
                    <label class="form-label">Select from Tips/Drills</label>
                    <div class="existing-items-container" id="existing-items-${newRowIndex}">
                        @foreach($items as $item)
                            <div class="existing-item" 
                                 data-id="{{ $item->id }}" 
                                 data-type="{{ $item instanceof \App\Models\Post ? 'post' : 'album' }}"
                                 data-url="{{ $item->file }}"
                                 data-title="{{ $item->title ?? $item->name ?? 'Untitled' }}">
                                <strong>{{ $item->title ?? $item->name ?? 'Untitled' }}</strong>
                                <span class="item-type {{ $item instanceof \App\Models\Post ? 'post' : 'album' }}">
                                    {{ $item instanceof \App\Models\Post ? 'Post' : 'Album' }}
                                </span>
                                <div><small>{{ Str::limit($item->description ?? '', 50) }}</small></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden fields to store the actual values -->
                <input type="hidden" name="fdbk_video_path[]" id="videoPath-${newRowIndex}" value="no-content">
                <input type="hidden" name="fdbk_video_type[]" id="videoType-${newRowIndex}" value="none">

                <label class="form-label mt-2">Additional Feedback</label>
                <textarea name="row_feedback[]" class="form-control" placeholder="Enter Feedback"></textarea>
            </div>

            <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                <button type="button" class="btn btn-success add-video-btn">+</button>
            </div>
        `;

        container.appendChild(newRow);
        initializeRowEvents(newRowIndex);
        refreshButtons();
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
        const noContentCheckbox = document.getElementById(`noContent-${rowIndex}`);
        const toggleExistingBtn = document.querySelector(`.toggle-existing-btn[data-row="${rowIndex}"]`);
        const backToUploadBtn = document.querySelector(`.back-to-upload-btn[data-row="${rowIndex}"]`);

        // No content checkbox
        if (noContentCheckbox) {
            noContentCheckbox.addEventListener('change', function() {
                updateRowState(rowIndex);
            });
        }

        // Toggle to existing items section
        if (toggleExistingBtn) {
            toggleExistingBtn.addEventListener('click', function() {
                document.getElementById(`upload-section-${rowIndex}`).classList.add('hidden-section');
                document.getElementById(`existing-section-${rowIndex}`).classList.remove('hidden-section');
                
                // Uncheck no-content if it was checked
                if (noContentCheckbox && noContentCheckbox.checked) {
                    noContentCheckbox.checked = false;
                    updateRowState(rowIndex);
                }
            });
        }

        // Back to upload section
        if (backToUploadBtn) {
            backToUploadBtn.addEventListener('click', function() {
                document.getElementById(`existing-section-${rowIndex}`).classList.add('hidden-section');
                document.getElementById(`upload-section-${rowIndex}`).classList.remove('hidden-section');
            });
        }

        // Existing items click
        const existingItems = document.querySelectorAll(`#existing-items-${rowIndex} .existing-item`);
        existingItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove selected class from all items in this row
                existingItems.forEach(i => i.classList.remove('selected'));
                
                // Add selected class to clicked item
                this.classList.add('selected');
                
                // Set the values directly
                selectExistingItem(rowIndex, this.dataset.url, this.dataset.type);
            });
        });

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;
                    uploadBtn.style.display = 'block';
                    
                    // Uncheck no-content if it was checked
                    if (noContentCheckbox && noContentCheckbox.checked) {
                        noContentCheckbox.checked = false;
                    }
                    
                    // Make sure we're in upload section
                    document.getElementById(`existing-section-${rowIndex}`).classList.add('hidden-section');
                    document.getElementById(`upload-section-${rowIndex}`).classList.remove('hidden-section');
                    
                    // Don't set video path yet - wait for upload
                } else {
                    uploadBtn.style.display = 'none';
                    fileInfo.innerHTML = '';
                }
                updateSubmitButton();
            });
        }

        if (uploadBtn) {
            uploadBtn.addEventListener('click', function() {
                const file = fileInput.files[0];
                if (!file) return;
                uploadVideoChunks(file, rowIndex);
            });
        }
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
                uploadBtn.textContent = 'Upload Video';
                uploadInProgress = false;
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
        uploadInProgress = false;
        
        updateSubmitButton();
    }

    function showStatus(rowIndex, message, type) {
        const uploadStatus = document.getElementById(`uploadStatus-${rowIndex}`);
        if (uploadStatus) {
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
    }

    function updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const rows = document.querySelectorAll('#video-container .video-row');
        let allValid = true;
        let anyUploadInProgress = uploadInProgress;
        
        // Check first row (required) - must have either uploaded video, selected item, or no-content checked
        const firstRowNoContent = document.getElementById('noContent-0')?.checked || false;
        const firstRowVideoPath = document.getElementById('videoPath-0')?.value || 'no-content';
        
        if (!firstRowNoContent && firstRowVideoPath === 'no-content') {
            allValid = false;
        }
        
        submitBtn.disabled = !(allValid && !anyUploadInProgress);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize first row
        initializeRowEvents(0);
        
        // Form submission validation
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const firstRowNoContent = document.getElementById('noContent-0')?.checked || false;
            const firstRowVideoPath = document.getElementById('videoPath-0')?.value || 'no-content';
            
            if (!firstRowNoContent && firstRowVideoPath === 'no-content') {
                e.preventDefault();
                showStatus(0, 'Please either upload a video, select from tips/drills, or check "No Video Content" for the first feedback.', 'error');
            }

            if (uploadInProgress) {
                e.preventDefault();
                showStatus(0, 'Please wait for uploads to complete before submitting.', 'error');
            }
        });
    });

    document.addEventListener('click', function(e) {
        // ADD NEW ROW
        if (e.target.classList.contains('add-video-btn')) {
            createNewRow();
        }

        // REMOVE ROW
        if (e.target.classList.contains('remove-video-btn')) {
            const row = e.target.closest('.video-row');
            row.remove();
            refreshButtons();
            updateSubmitButton();
        }
    });

    refreshButtons();
</script>
@endpush