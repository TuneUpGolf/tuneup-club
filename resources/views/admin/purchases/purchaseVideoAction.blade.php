<a class="btn btn-sm small btn btn-danger "
    href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . asset('/storage' . '/' . tenant('id') . '/' . $purchaseVideo->video_url) }}"
    data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Analyze') }}">
    <i class="ti ti-refresh text-white"></i>
</a>
@if (!$purchaseVideo->feedback)
    <a class="btn btn-sm small btn btn-warning "
        href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideo->video_url]) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Add Feedback') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
@endif
@if (!!$purchaseVideo->feedback)
    <a class="btn btn-sm small btn btn-warning "
        href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideo->video_url]) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit Feedback') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
@endif
