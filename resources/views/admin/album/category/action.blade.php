<div class="action-btn-fix-wraper d-flex align-items-center justify-content-center gap-2">
   @can('edit-blog')
    {{-- <a href="{{ route('album.category.change-order', $post->id) }}" 
        class="btn btn-sm btn-warning action-btn-fix"
        data-bs-toggle="tooltip" 
        data-bs-placement="bottom" 
        data-bs-original-title="{{ __('Change Order') }}">
        <i class="ti ti-arrows-sort text-white"></i>
    </a> --}}
@endcan
    @can('edit-blog')
        <a href="{{ route('album.category.edit', $post->id) }}" class="btn btn-sm btn-warning action-btn-fix"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
    @endcan

    @can('delete-blog')
        {!! Form::open([
            'method' => 'DELETE',
            'class' => 'd-flex',
            'route' => ['album.category.destroy', $post->id],
            'id' => 'delete-form-' . $post->id,
        ]) !!}
        <a href="javascript:void(0);" class="btn btn-sm btn-danger show_confirm action-btn-fix"
            id="delete-form-{{ $post->id }}" data-bs-toggle="tooltip" data-bs-placement="bottom"
            data-bs-original-title="{{ __('Delete') }}">
            <i class="ti ti-trash text-white"></i>
        </a>
        {!! Form::close() !!}
    @endcan

    {{-- @can('create-blog')
        <a href="{{ route('album.category.create-album', $post->id) }}" class="btn btn-sm btn-warning action-btn-fix"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Plus') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endcan --}}
</div>
