@if ($purchase->status !== 'complete' && Auth::user()->type == 'Follower')
    @can('create-purchases')
        {!! Form::open([
            'method' => 'POST',
            'class' => 'd-inline',
            'route' => ['purchase-confirm-redirect', ['purchase_id' => $purchase->id]],
            'id' => 'confirm-form-' . $purchase->id,
        ]) !!}
        {{ Form::button(__('Confirm'), ['type' => 'submit', 'class' => 'btn btn-sm small btn btn-info ']) }}
        <i class="ti ti-eye text-white"></i>
        </a>
        {!! Form::close() !!}
    @endcan
@endif
@if (
    $purchase->status == 'complete' &&
        $purchase->lesson->lesson_quantity !== $purchase->lessons_used &&
        Auth::user()->type == 'Follower' &&
        $purchase->lesson->type === 'online')
    @can('manage-purchases')
        <a class="btn btn-sm small btn btn-warning "
            href="{{ route('purchase.video.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('View Lesson') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endcan
@endif

@if ($purchase->status == 'complete' && Auth::user()->type == 'Influencer' && $purchase->lesson->type === 'online')
    @can('manage-purchases')
        <a class="btn btn-sm small btn btn-warning "
            href="{{ route('purchase.feedback.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Click to Begin Analyzing') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endcan
@endif
@can('delete-purchases')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-inline',
        'route' => ['purchase.destroy', $purchase->id],
        'id' => 'delete-form-' . $purchase->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
