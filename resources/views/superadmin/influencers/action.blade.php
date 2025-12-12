<div class="action-btn-fix-wraper">

    @can('edit-user')
        <a class="btn btn-sm small btn btn-warning action-btn-fix"
            href="{{ route('superadmin.influencer.edit', ['tenant_id' => $user->tenant_id, 'influencer_id' => $user->id]) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
        @php
            $instructor_subscription = tenancy()->central(function () use ($user) {
                return \App\Models\InfluencerSubscription::where('influencer_id', $user->id)
                    ->where('tenant_id', $user->tenant_id)
                    ->where('status', 'active')
                    ->exists();
            });
        @endphp

        @if ($instructor_subscription)
            <a class="btn btn-sm small btn btn-danger action-btn-fix"
                href="{{ route('superadmin.influencer.deactive_sub', ['tenant_id' => $user->tenant_id, 'influencer_id' => $user->id]) }}"
                data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-original-title="{{ __('Deactive Subscription') }}">
                {{-- <i class="ti ti-edit text-white"></i> --}}
                Deactive Subscription
            </a>
        @endif

    @endcan
</div>
