@can('impersonate-user')
    <a class="btn btn-sm small btn btn-info" href="{{ route('impersonate', ['id' => $user->id, 'guardName' => 'follower']) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Impersonate') }}">
        <i class="ti ti-new-section text-white"></i>
    </a>
@endcan
{{-- @if ($user->email_verified_at)
    <a class="btn btn-sm small btn btn-danger" href="{{ route('user.email.verified', $user->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Email Unverified') }}">
        <i class="ti ti-mail text-white"></i>
    </a>
@else
    <a class="btn btn-sm small btn btn-success " href="{{ route('user.email.verified', $user->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Email Verified') }}">
        <i class="ti ti-mail text-white"></i>
    </a>
@endif
@if ($user->phone_verified_at)
    <a class="btn btn-sm small btn btn-danger" href="{{ route('user.phone.verified', $user->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Phone Unverified') }}">
        <i class="ti ti-message-circle text-white"></i>
    </a>
@else
    <a class="btn btn-sm small btn btn-success " href="{{ route('user.phone.verified', $user->id) }}"
        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Phone Verified') }}">
        <i class="ti ti-message-circle text-white"></i>
    </a>
@endif --}}
@can('edit-user')
    <a class="btn btn-sm small btn btn-warning " href="{{ route('follower.edit', $user->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
@can('delete-user')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-inline',
        'route' => ['follower.destroy', $user->id],
        'id' => 'delete-form-' . $user->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
