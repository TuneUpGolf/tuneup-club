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
            <div class="row">
            @foreach ($plans as $plan)
            @if ($plan->active_status == 1)
            <div class="col-xl-3 col-md-6 py-4">
               <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"  data-wow-delay="0.2s">
                  <div class="rounded-lg shadow popular-wrap h-100">
                     <div class="px-3 pt-4 ">
                        <p class="text-2xl font-bold mb-1">{{ $plan->name }}</p>
                        <div class="flex gap-2 items-center mt-2 ">
                           <p class=" text-6xl font-bold">{{ $currency_symbol . ' ' . $plan->price }} /</p>
                           <p class="text-2xl text-gray-600">{{ $plan->duration . ' ' . $plan->durationtype }}</p>
                        </div>
                     </div>
                     <div class="border-t border-gray-300"></div>
                     <div class="px-3 py-4">
                        @if ($plan->id != 1)
                        @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date))
                        <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                           class="lesson-btn text-center font-bold text-lg mt-auto"
                           data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                         {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                        @else
                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                           class="lesson-btn text-center font-bold text-lg mt-auto">{{ __('Buy Plan') }}
                        </a>
                        @endif
                        @endif
                        <p class="font-semibold text-xl mb-2 mt-2">Features</p>
                        <p class="text-gray-600">
                           {!! $plan->description !!}
                        </p>
                        
                        {{-- <ul class="mt-2 pl-0">
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
                        </ul> --}}
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
