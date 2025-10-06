@extends('layouts.main')
@section('title', __('Upload'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('help-section.index') }}">{{ __('Help') }}</a></li>
    <li class="breadcrumb-item">{{ __('Upload') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            @if (tenant('id') == null)
                @if ($databasePermission == null)
                    <div class="alert alert-warning">
                        {{ __('Please on your database permission to create auto generate DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('On database permission') }}</a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        {{ __('Please off your database permission to create your own DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('Off database permission') }}</a>
                    </div>
                @endif
            @endif
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Upload Videos, Files or Images for help') }}</h5>
                    </div>
                    {!! Form::open([
                        'route' => 'help-section.store',
                        'method' => 'Post',
                        'data-validate',
                        'enctype' => 'multipart/form-data',
                        'id' => 'uploadForm',
                    ]) !!}
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                {{ Form::label('roles', __('Role'), ['class' => 'form-label']) }}
                                <select name="role" id="role" class="form-select" required>
                                    @foreach ($roles as $key => $role)
                                        <option value="{{ $key }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    {{ __('Role is required') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="file_input" class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title"
                                    placeholder="Enter Title" required>
                            </div>

                            <div class="col-md-6">
                                {{ Form::label('roles', __('Type'), ['class' => 'form-label']) }}
                                <select name="type" id="type" class="form-select" required>
                                    <option value="video">Video</option>
                                    <option value="image">Image</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ __('Role is required') }}
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <input type="hidden" id="uploadFileName" name="uploadFileName" class="form-control">
                                <label for="file_input" class="form-label">Upload</label>
                                <input type="file" class="form-control" name="file" id="file_input">
                                <div id="progress" style="margin-top: 20px;">
                                    <progress id="progressBar" value="0" max="100"
                                        style="width: 100%;"></progress>
                                    <span id="progressText">0%</span>
                                </div>
                                <div id="status"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-end">
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'submitButton']) }}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </section>
    </div>
@endsection
@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    <script>
        if (typeof Resumable === 'undefined') {
            document.getElementById('status').innerText = 'Error: Resumable.js failed to load.';
            console.error('Resumable.js not found. Check CDN or use local file.');
        } else {
            const resumable = new Resumable({
                target: '{{ route('upload-chunk') }}',
                chunkSize: 5 * 1024 * 1024,
                simultaneousUploads: 3,
                testChunks: true,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                query: {
                    fileName: () => Date.now() + '_' + (resumable.files[0]?.fileName || 'file')
                }
            });

            resumable.assignBrowse(document.getElementById('file_input'));

            resumable.on('fileAdded', function(file) {
                document.getElementById('status').innerText = 'Starting upload...';
                document.getElementById('submitButton').disabled = true;
                resumable.upload();
            });

            resumable.on('fileProgress', function(file) {
                const progress = Math.floor(file.progress() * 100);
                document.getElementById('progressBar').value = progress;
                document.getElementById('progressText').innerText = `${progress}%`;
            });

            resumable.on('fileSuccess', function(file, message) {
                document.getElementById('status').innerText = 'All chunks uploaded! Finalizing...';
                finalizeUpload(file.uniqueIdentifier, file.fileName);
            });

            resumable.on('fileError', function(file, message) {
                document.getElementById('status').innerText = `Error: ${message}`;
                document.getElementById('status').classList.add('error');
            });

            async function finalizeUpload(fileId, fileName) {
                try {
                    const response = await fetch('{{ route('finalize-upload') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            fileId,
                            fileName
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        document.getElementById('status').innerText = 'File uploaded successfully!';
                        const filePathInput = document.getElementById('file_path');
                        document.getElementById('uploadFileName').value = 'storage/' + result.filePath;
                        console.log("result.filePath =>", result.filePath);
                        if (filePathInput) {
                            filePathInput.value = result.filePath;
                        } else {
                            console.error('file_path input not found in the DOM');
                        }
                        document.getElementById('submitButton').disabled = false;
                    } else {
                        document.getElementById('status').innerText = `Error: ${result.message}`;
                        document.getElementById('status').classList.add('error');
                    }
                } catch (error) {
                    document.getElementById('status').innerText = 'Error finalizing upload.';
                    document.getElementById('status').classList.add('error');
                    console.error(error);
                }
            }
        }
    </script>
@endpush
