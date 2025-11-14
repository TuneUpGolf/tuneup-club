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
                        ]) !!}

                        <div id="video-container">

                            {{-- FIRST VIDEO ROW (Required) --}}
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
@endpush
