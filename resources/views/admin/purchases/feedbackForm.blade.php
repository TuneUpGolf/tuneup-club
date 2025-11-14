@extends('layouts.main')
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
                            'class' => 'form-horizontal',
                            'data-validate',
                        ]) !!}
                        <div class="row">
                            <div class="container" id="video-container">
                                <div class="row video-row mb-2">
                                    <div class="col-10">
                                        <label for="fdbk_video_0" class="form-label">Feedback Video</label>
                                        <input type="file" name="fdbk_video[]" id="fdbk_video_0" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-2 d-flex align-items-center">
                                        <button type="button" class="btn btn-success add-video-btn">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="feedback" class="form-label">Feedback</label>
                                <textarea name="feedback" id="feedback" class="form-control" placeholder="Enter Feedback" required>{{ $purchaseVideo->feedback ?? '' }}</textarea>
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
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        let videoIndex = 1; // to give unique IDs

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('add-video-btn')) {
                var container = document.getElementById('video-container');

                // Create new row
                var newRow = document.createElement('div');
                newRow.classList.add('row', 'video-row', 'mb-2');

                // Video input col
                var colInput = document.createElement('div');
                colInput.classList.add('col-10');

                var label = document.createElement('label');
                label.classList.add('form-label');
                label.setAttribute('for', 'fdbk_video_' + videoIndex);
                label.innerText = 'Feedback Video';

                var input = document.createElement('input');
                input.type = 'file';
                input.name = 'fdbk_video[]';
                input.id = 'fdbk_video_' + videoIndex;
                input.classList.add('form-control');
                // new inputs are NOT required

                colInput.appendChild(label);
                colInput.appendChild(input);

                // Button col
                var colButton = document.createElement('div');
                colButton.classList.add('col-2', 'd-flex', 'align-items-center');
                var button = document.createElement('button');
                button.type = 'button';
                button.classList.add('btn', 'btn-success', 'add-video-btn');
                button.innerText = '+';
                colButton.appendChild(button);

                // Append cols to row
                newRow.appendChild(colInput);
                newRow.appendChild(colButton);

                // Append row to container
                container.appendChild(newRow);

                videoIndex++;
            }
        });
    </script>

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
@endpush
