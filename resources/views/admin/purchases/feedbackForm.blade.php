{{-- @extends('layouts.main')
@section('title', __('Provide Feedback'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Feedback') }}</li>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add Feedback') }}</h5>
                    </div>

                    <div class="card-body">
                        {!! Form::open([
                            'route' => ['purchase.feedback.add', ['purchase_video_id' => $purchaseVideo->id, 'redirect' => '1']],
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
                        ]) !!}

                        <div id="video-container">

                            <div class="row video-row mb-3">
                                <div class="col-12 col-md-10">
                                    <label class="form-label">Feedback Video (Required)</label>
                                    <input type="file" name="fdbk_video[]" class="form-control" required>
                                </div>

                                <div
                                    class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                    <button type="button" class="btn btn-success add-video-btn">+</button>
                                </div>
                            </div>

                        </div>


                        <div class="form-group mt-3">
                            <label class="form-label">Feedback</label>
                            <textarea name="feedback" class="form-control" required>{{ $purchaseVideo->feedback ?? '' }}</textarea>
                        </div>

                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        {!! Form::close() !!}
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('javascript')
<script>
    function refreshButtons() {
        let rows = document.querySelectorAll('#video-container .video-row');

        rows.forEach((row, index) => {
            let addBtn = row.querySelector('.add-video-btn');
            let removeBtn = row.querySelector('.remove-video-btn');

            if (index === 0) {
                // first row: only + button
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

    document.addEventListener('click', function(e) {

        // ADD NEW ROW
        if (e.target.classList.contains('add-video-btn')) {

            let container = document.getElementById('video-container');

            let newRow = document.createElement('div');
            newRow.className = 'row video-row mb-3';

            newRow.innerHTML = `
                <div class="col-12 col-md-10">
                    <label class="form-label">Additional Video</label>
                    <input type="file" name="fdbk_video[]" class="form-control">
                </div>

                <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                    <button type="button" class="btn btn-success add-video-btn">+</button>
                </div>
            `;

            container.appendChild(newRow);
            refreshButtons();
        }

        // REMOVE ROW
        if (e.target.classList.contains('remove-video-btn')) {
            e.target.closest('.video-row').remove();
            refreshButtons();
        }
    });

    refreshButtons();
</script>
@endpush --}}

{{-- 
@extends('layouts.main')
@section('title', __(auth()->user()->type == 'Instructor' ? 'Provide Feedback' : 'Create Purchase'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Feedback') }}</li>
@endsection
@section('content')

<style>
    .add-video-btn, .remove-video-btn {
        height: 100%;
    }

    /* From 700px width and up */
    @media (min-width: 768px) {
        .add-video-btn,
        .remove-video-btn {
            height: 30%;
        }
    }

    @media (max-width: 768px) {
    .card-body {
        max-height: unset;
    }
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
                            'route' => 'purchase.feedback.add',
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
                            'class' => 'form-horizontal',
                            'data-validate',
                        ]) !!}
                        <div class="row">
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}" />
                            <input type="hidden" name="redirect" value="1" />

                            <div id="video-container">

                       
                                <div class="row video-row mb-3">
                                    <div class="col-12 col-md-10">
                                        <label class="form-label">Feedback Video (Required)</label>
                                        <input type="file" name="fdbk_video[]" class="form-control" required>

                                        <label class="form-label mt-2">Feedback (Required)</label>
                                        <textarea name="row_feedback[]" class="form-control" required placeholder="Enter Feedback"></textarea>
                                    </div>

                                    <div
                                        class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                        <button type="button" class="btn btn-success add-video-btn" >+</button>
                                    </div>
                                </div>

                            </div>

                
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
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
    <script>
        function refreshButtons() {
            let rows = document.querySelectorAll('#video-container .video-row');

            rows.forEach((row, index) => {
                let addBtn = row.querySelector('.add-video-btn');
                let removeBtn = row.querySelector('.remove-video-btn');

                if (index === 0) {
                    // first row: only + button
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

        document.addEventListener('click', function(e) {

            // ADD NEW ROW
            if (e.target.classList.contains('add-video-btn')) {

                let container = document.getElementById('video-container');

                let newRow = document.createElement('div');
                newRow.className = 'row video-row mb-3';

                newRow.innerHTML = `
                    <div class="col-12 col-md-10">
                        <label class="form-label">Additional Video</label>
                        <input type="file" name="fdbk_video[]" class="form-control">

                        <label class="form-label mt-2">Additional Feedback</label>
                        <textarea name="row_feedback[]" class="form-control" placeholder="Enter Feedback"></textarea>
                    </div>

                    <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                        <button type="button" class="btn btn-success add-video-btn">+</button>
                    </div>
                    `;

                container.appendChild(newRow);
                refreshButtons();
            }

            // REMOVE ROW
            if (e.target.classList.contains('remove-video-btn')) {
                e.target.closest('.video-row').remove();
                refreshButtons();
            }
        });

        refreshButtons();
    </script>
@endpush --}}


@extends('layouts.main')
@section('title', __(auth()->user()->type == 'Instructor' ? 'Provide Feedback' : 'Create Purchase'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add Feedback') }}</li>
@endsection
@section('content')

<style>
    .add-video-btn, .remove-video-btn {
        height: 100%;
    }

    /* From 700px width and up */
    @media (min-width: 768px) {
        .add-video-btn,
        .remove-video-btn {
            height: 30%;
        }
    }

    @media (max-width: 768px) {
        .card-body {
            max-height: unset;
        }
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
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add Feedback') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open([
                            'route' => 'purchase.feedback.add',
                            'method' => 'Post',
                            'enctype' => 'multipart/form-data',
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
                                        <label class="form-label">Feedback Video (Required)</label>
                                        <input type="file" name="fdbk_video[]" class="form-control video-input" data-row="0" required>
                                        <div class="file-info" id="fileInfo-0"></div>
                                        
                                        <button type="button" class="btn btn-primary btn-sm upload-btn" id="uploadBtn-0" data-row="0">
                                            {{ __('Upload Video First') }}
                                        </button>

                                        <div class="progress" id="progressContainer-0">
                                            <div class="progress-bar" id="progressBar-0" role="progressbar" style="width: 0%;">
                                                0%
                                            </div>
                                        </div>

                                        <div class="upload-status" id="uploadStatus-0"></div>
                                        <input type="hidden" name="fdbk_video_path[]" id="videoPath-0">
                                        <input type="hidden" name="fdbk_video_type[]" id="videoType-0">

                                        <label class="form-label mt-2">Feedback (Required)</label>
                                        <textarea name="row_feedback[]" class="form-control" required placeholder="Enter Feedback"></textarea>
                                    </div>

                                    <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                        <button type="button" class="btn btn-success add-video-btn" >+</button>
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
        let rowCounter = 1; // Start from 1 since we already have row 0

        function refreshButtons() {
            let rows = document.querySelectorAll('#video-container .video-row');

            rows.forEach((row, index) => {
                let addBtn = row.querySelector('.add-video-btn');
                let removeBtn = row.querySelector('.remove-video-btn');

                if (index === 0) {
                    // first row: only + button
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

        function createNewRow() {
            let container = document.getElementById('video-container');
            let newRowIndex = rowCounter++;

            let newRow = document.createElement('div');
            newRow.className = 'row video-row mb-3';
            newRow.setAttribute('data-row-index', newRowIndex);

            newRow.innerHTML = `
                <div class="col-12 col-md-10">
                    <label class="form-label">Additional Video</label>
                    <input type="file" name="fdbk_video[]" class="form-control video-input" data-row="${newRowIndex}">
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
                    <input type="hidden" name="fdbk_video_path[]" id="videoPath-${newRowIndex}">
                    <input type="hidden" name="fdbk_video_type[]" id="videoType-${newRowIndex}">

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

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    fileInfo.innerHTML = `Selected: ${file.name} (${fileSize} MB)`;
                    uploadBtn.style.display = 'block';
                    videoPath.value = '';
                    updateSubmitButton();
                } else {
                    uploadBtn.style.display = 'none';
                    fileInfo.innerHTML = '';
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
                    uploadBtn.textContent = 'Upload Video First';
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
            const requiredRowUploaded = document.getElementById('videoPath-0').value !== '';
            const noUploadsInProgress = !uploadInProgress;
            
            submitBtn.disabled = !(requiredRowUploaded && noUploadsInProgress);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize first row
            initializeRowEvents(0);
            
            // Form submission validation
            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
                const requiredUploaded = document.getElementById('videoPath-0').value !== '';
                
                if (!requiredUploaded) {
                    e.preventDefault();
                    showStatus(0, 'Please upload the required video first before submitting.', 'error');
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
                const rowIndex = row.getAttribute('data-row-index');
                
                // If removing a row that has upload in progress, we should handle it
                // For now, just remove the row
                row.remove();
                refreshButtons();
                updateSubmitButton();
            }
        });

        refreshButtons();
    </script>
@endpush