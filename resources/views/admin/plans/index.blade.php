@php
    use Carbon\Carbon;
    if (Auth::user()->type == 'Admin') {
        $currency_symbol = tenancy()->central(function ($tenant) {
            return Utility::getsettings('currency_symbol');
        });
    } else {
        $currency_symbol = Utility::getsettings('currency_symbol');
    }
    if (Auth::user()->type != 'Admin') {
        $currency = Utility::getsettings('currency');
    } else {
        $currency = tenancy()->central(function ($tenant) {
            return Utility::getsettings('currency');
        });
    }
@endphp
@extends('layouts.main')
@if (Auth::user()->type == 'Super Admin')
    @section('title', __('Plans List'))
@else
    @section('title', __('Pricing'))
@endif
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Plans') }}</li>
@endsection
@section('content')
    <section id="price" class="price-section">
        <div class="container">
            <div class="row ">
                @foreach ($plans as $plan)
                    @if ($plan->active_status == 1)
                        <div class="col-xl-3 col-md-6">
                            <div class="card price-card price-1 wow animate__fadeInUp ani-fade" data-wow-delay="0.2s">
                                <div class="card-body">
                                    <span class="price-badge bg-primary">{{ $plan->name }}</span>
                                    <span class="mb-4 f-w-600 p-price"> {{ $currency_symbol . '' . $plan->price }}<small
                                            class="text-sm">/{{ $plan->duration . ' ' . $plan->durationtype }}</small></span>
                                    <p class="mb-0">
                                        {{ $plan->description }}
                                    </p>
                                    <ul class="mt-4">
                                        <li class="list-unstyled d-flex">
                                            <span class="theme-avtar">
                                                <i class="text-primary ti ti-circle-plus"></i></span>
                                            {{ $plan->max_users . ' ' . __('Users') }}
                                        </li>
                                        <li class="list-unstyled d-flex">
                                            <span class="theme-avtar">
                                                <i class="text-primary ti ti-circle-plus"></i></span>
                                            {{ $plan->duration . ' ' . $plan->durationtype . ' ' . __('Duration') }}
                                        </li>
                                        @if (Auth::user()->type == 'Admin')
                                            <li class="list-unstyled d-flex">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>
                                                {{ $plan->max_roles . ' ' . __('Roles') }}
                                            </li>
                                            <li class="list-unstyled d-flex">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>
                                                {{ $plan->max_documents . ' ' . __('Documents') }}
                                            </li>
                                            <li class="list-unstyled d-flex">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>
                                                {{ $plan->max_blogs . ' ' . __('Blogs') }}
                                            </li>
                                        @endif
                                    </ul>
                                    <div class="text-center">
                                        @if ($plan->id != 1)
                                            @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date))
                                                <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                                    class="btn btn-primary"
                                                    data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                    {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                            @else
                                                <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                    class="btn btn-primary">{{ __('Buy Plan') }}
                                                    <i class="ti ti-chevron-right ms-2"></i></a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
