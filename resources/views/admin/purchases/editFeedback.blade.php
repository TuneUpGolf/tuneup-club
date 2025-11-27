@extends('layouts.main')
@section('title', __('Edit Feedback'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Feedback') }}</li>
@endsection
@section('content')

<style>
    .add-video-btn, .remove-video-btn, .remove-new-video-btn {
        height: 100%;
    }

    @media (min-width: 768px) {
        .remove-video-btn, .remove-new-video-btn {
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
                    ]) !!}
                    <div class="row">
                        <input type="hidden" name="purchase_video_id" value="{{ $purchaseVideo->id }}" />
                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}" />
                        <input type="hidden" name="redirect" value="1" />

                        <div id="video-container">
                            @php
                                $existingFeedback = json_decode($purchaseVideo->feedback, true) ?? [];
                                $existingVideos = [];
                                if($feedbackContent && !empty($feedbackContent->url)) {
                                    if(is_string($feedbackContent->url)) {
                                        $existingVideos = json_decode($feedbackContent->url, true) ?? [];
                                    } else {
                                        $existingVideos = $feedbackContent->url;
                                    }
                                }
                            @endphp

                            {{-- EXISTING ROWS --}}
                            @foreach($existingFeedback as $index => $feedback)
                            <div class="row video-row mb-3 existing-row">
                                <div class="col-12 col-md-10">
                                    <div class="existing-video">
                                        <small class="text-muted">Existing Video {{ $index + 1 }}</small>
                                        @if(isset($existingVideos[$index]['url']))
                                            <div class="mb-2">
                                                <a href="{{ $existingVideos[$index]['url'] }}" target="_blank" class="btn btn-sm btn-info">
                                                    View Current Video
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <input type="hidden" name="existing_index[]" value="{{ $index }}">
                                    <input type="hidden" name="existing_video_url[{{ $index }}]" value="{{ $existingVideos[$index]['url'] ?? '' }}">
                                    
                                    <label class="form-label">Replace Video (Optional)</label>
                                    <input type="file" name="existing_fdbk_video[{{ $index }}]" class="form-control">
                                    
                                    <label class="form-label mt-2">Feedback</label>
                                    <textarea name="existing_row_feedback[{{ $index }}]" class="form-control" required placeholder="Enter Feedback">{{ $feedback }}</textarea>
                                </div>

                                <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                                    <button type="button" class="btn btn-danger remove-video-btn" data-index="{{ $index }}">–</button>
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
                        {{ Form::button(__('Update'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
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

    document.addEventListener('DOMContentLoaded', function() {
        refreshButtons();
    });

    document.addEventListener('click', function(e) {
        // ADD NEW ROW
        if (e.target.classList.contains('add-video-btn')) {
            let container = document.getElementById('new-rows-container');
            let newRow = document.createElement('div');
            newRow.className = 'row video-row mb-3 new-row';
            newRow.innerHTML = `
                <div class="col-12 col-md-10">
                    <label class="form-label">New Video</label>
                    <input type="file" name="new_fdbk_video[]" class="form-control" required>
                    
                    <label class="form-label mt-2">New Feedback</label>
                    <textarea name="new_row_feedback[]" class="form-control" required placeholder="Enter Feedback"></textarea>
                </div>
                <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end mt-2 mt-md-4 gap-2">
                    <button type="button" class="btn btn-danger remove-new-video-btn">–</button>
                </div>
            `;
            container.appendChild(newRow);
            newRowCount++;
        }

        // REMOVE EXISTING ROW
        if (e.target.classList.contains('remove-video-btn')) {
            let index = e.target.getAttribute('data-index');
            let deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'deleted_indexes[]';
            deleteInput.value = index;
            document.getElementById('video-container').appendChild(deleteInput);
            
            e.target.closest('.video-row').remove();
            refreshButtons();
        }

        // REMOVE NEW ROW
        if (e.target.classList.contains('remove-new-video-btn')) {
            e.target.closest('.video-row').remove();
        }
    });
</script>
@endpush