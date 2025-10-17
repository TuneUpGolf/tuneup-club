<div class="action-btn-fix-wraper">

    @can('edit-user')
        <a class="btn btn-sm small btn btn-warning action-btn-fix"
            href="{{ route('superadmin.influencer.edit', ['tenant_id' => $user->tenant_id, 'influencer_id' => $user->id]) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
    @endcan
</div>
