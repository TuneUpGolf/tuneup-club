@if ($document->document_menu && $document->status == 1)
    <a class="btn btn-success btn-sm copy_menu" onclick="copyToClipboard('#copy-menu-{{ $document->id }}')"
        href="javascript:void(0)" id="copy-menu-{{ $document->id }}" data-bs-toggle="tooltip" data-bs-placement="bottom"
        data-bs-original-title="{{ __('Copy Document URL') }}"
        data-url="{{ route('document.public', $document->document_menu->slug) }}"><i class="ti ti-copy"></i></a>

    <a href="{{ route('document.public', $document->document_menu->slug) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('View Document') }}" target="_blank"
        class="btn btn-info mr-1 btn-sm" data-toggle="tooltip"><i class="ti ti-eye"></i></a>
@endif
@if ($document->status != 1)
    <a class="btn btn-sm small btn btn-success" href="{{ route('document.status', $document->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Active') }}">
        <i class="ti ti-checks text-white"></i>
    </a>
@else
    <a class="btn btn-sm small btn btn-danger" href="{{ route('document.status', $document->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Deactive') }}">
        <i class="ti ti-ban text-white"></i>
    </a>
@endif
<a class="btn btn-sm small btn btn-info edit_menu cust_btn" data-bs-toggle="tooltip" data-bs-placement="bottom"
    data-bs-original-title="{{ __('Design document') }}" href="{{ route('document.design', $document->id) }}"
    id="edit-menu">
    <i class="ti ti-brush"></i>
</a>
@can('edit-document')
    <a class="btn btn-sm small btn btn-primary" href="{{ route('document.edit', $document->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
@can('delete-document')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-inline',
        'route' => ['document.destroy', $document->id],
        'id' => 'delete-form-' . $document->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
