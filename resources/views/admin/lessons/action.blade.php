@can('manage-lessons')
    @if ($lesson->type === 'inPerson')
        <a class="'btn btn-sm small btn btn-info ' " href="{{ route('slot.view', ['lesson_id' => $lesson->id]) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Manage Slots') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    @endif
@endcan
@can('edit-lessons')
    <a class="btn btn-sm small btn btn-warning " href="{{ route('lesson.edit', $lesson->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
@can('delete-lessons')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-inline',
        'route' => ['lesson.destroy', $lesson->id],
        'id' => 'delete-form-' . $lesson->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
