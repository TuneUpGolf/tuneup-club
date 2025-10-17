@extends('layouts.main')

@section('title', __('Subscription Inactive'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Subscription Inactive') }}</li>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="col-sm-12 col-md-8 m-auto">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white text-center py-3 rounded-top">
                    <h4 class="mb-0 text-white"><i class="ti ti-alert-circle"></i> {{ __('Your Subscription is Inactive') }}</h4>
                </div>
                <div class="card-body text-center py-5">
              

                    @if($subscription)
                        <h5 class="mb-3 text-dark fw-semibold">{{ $subscription->name }}</h5>
                        <p class="text-muted mb-4">
                            {{ __('Your plan') }} <strong>{{ $subscription->name }}</strong> 
                            {{ __('is currently inactive or expired.') }}
                        </p>
                    @else
                        <p class="text-muted mb-4">
                            {{ __('You don’t have an active subscription plan assigned yet.') }}
                        </p>
                    @endif

                    <a href="{{ route('subscription.inactive.purchase') }}" class="btn btn-primary px-4">
                        <i class="ti ti-shopping-cart"></i> {{ __('Purchase / Renew Plan') }}
                    </a>

                    <div class="mt-4">
                        <small class="text-muted">
                            {{ __('If you think this is a mistake, please contact admin.') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

