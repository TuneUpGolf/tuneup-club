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

                                {{-- FIRST VIDEO ROW (Required) --}}
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

                            {{-- <div class="form-group">
                                {{ Form::label('feedback', __('Feedback'), ['class' => 'form-label']) }}
                                *
                                {!! Form::textarea('feedback', $purchaseVideo->feedback ?? '', [
                                    'class' => 'form-control ',
                                    'placeholder' => __('Enter Feedback'),
                                    'required' => 'required',
                                ]) !!}
                            </div> --}}
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
@endpush
