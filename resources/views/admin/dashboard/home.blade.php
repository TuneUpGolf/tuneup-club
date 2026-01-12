@php
    use Carbon\Carbon;
    $users = \Auth::user();
    $currantLang = $users->currentLanguage();
    $primary_color = \App\Facades\UtilityFacades::getsettings('color');
    if (isset($primary_color)) {
        $color = $primary_color;
    } else {
        $color = 'theme-1';
    }
    if ($color == 'theme-1') {
        $chatcolor = '#0CAF60';
    } elseif ($color == 'theme-2') {
        $chatcolor = '#584ED2';
    } elseif ($color == 'theme-3') {
        $chatcolor = '#6FD943';
    } elseif ($color == 'theme-4') {
        $chatcolor = '#145388';
    } elseif ($color == 'theme-5') {
        $chatcolor = '#B9406B';
    } elseif ($color == 'theme-6') {
        $chatcolor = '#008ECC';
    } elseif ($color == 'theme-7') {
        $chatcolor = '#922C88';
    } elseif ($color == 'theme-8') {
        $chatcolor = '#C0A145';
    } elseif ($color == 'theme-9') {
        $chatcolor = '#48494B';
    } elseif ($color == 'theme-10') {
        $chatcolor = '#0C7785';
    }

    $influncerSubscriptions = DB::table('influencer_subscription')->where('status', 'active')->latest()->first();
    if ($influncerSubscriptions) {
        $subscriptions = true;
    } else {
        $subscriptions = false;
    }
    $influencer_plans = DB::table('influencer_plan')->where('influencer_id', $users->id)->first();

@endphp
@extends('layouts.main')
@section('title', __('Dashboard'))
@section('content')
    <style>
        .top-boxes {
            min-height: 120px;
        }

        @media (min-width: 786px) {
            .top-boxes {
                min-height: unset;
            }
        }

        /* Filter container */
        .dataTables_filter {
            display: flex !important;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: nowrap !important;
            width: 100%;
        }

        /* Remove label width issue */
        .dataTables_filter label {
            display: contents !important;
        }

        /* Equal width for mobile */
        .dt-search,
        .dt-select {
            height: 38px;
            width: 50% !important;
            flex: 0 0 50% !important;
            min-width: 0 !important;
        }

        /* Desktop */
        @media (min-width: 769px) {

            .dt-search,
            .dt-select {
                width: 220px !important;
                flex: unset !important;
            }
        }
    </style>
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">
                @can('manage-lessons')
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12 pb-3">
                        <div class="relative flex flex-col bg-white rounded-lg h-100 top-boxes">
                            <div class="p-2 p-sm-3">
                                <div class="flex items-center gap-3 w-100">

                                    <!-- Icon -->
                                    <div class="bg-card1 p-2 rounded">
                                        <svg width="28" height="28" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                            <path d="M32,2C15.431,2,2,15.432,2,32c0,16.568,13.432,30,30,30
                                            c16.568,0,30-13.432,30-30C62,15.432,48.568,2,32,2z
                                            M25.025,50L11,35.6l7.029-7.164l6.977,7.184l21-21.619
                                            L53,21.199L25.025,50z" fill="#4AD991" />
                                        </svg>
                                    </div>

                                    <!-- Title -->
                                    <span class="font-roboto font-semibold text-nowrap">
                                        {{ __('Completed Submissions') }}
                                    </span>

                                    <!-- Count -->
                                    <p class="mb-0 font-sans bg-card-text text-2xl ms-auto">
                                        {{ $purchaseComplete }}
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-3 col-md-6 col-sm-6 col-12 pb-3">
                        <div class="relative flex flex-col bg-white rounded-lg h-100 top-boxes">
                            <div class="p-2 p-sm-3">
                                <div class="flex items-center gap-3 w-100">

                                    <!-- Icon -->
                                    <div class="bg-card4 p-2 rounded">
                                        <svg width="28" height="28" viewBox="0 0 128 128"
                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <!-- SVG content same -->
                                            <path d="M23.36 116.32v-7.42c7.4-1.9 67.86 0 81.28 0v7.42
                                    c0 4.24-18.2 7.68-40.64 7.68s-40.64-3.44-40.64-7.68z" fill="#8b5738" />
                                            <!-- baqi svg paths unchanged -->
                                        </svg>
                                    </div>

                                    <!-- Title -->
                                    <span class="font-roboto font-semibold text-nowrap">
                                        {{ __('Pending Submissions') }}
                                    </span>

                                    <!-- Count -->
                                    <p class="mb-0 font-sans bg-card4-text text-2xl ms-auto">
                                        {{ $purchaseInprogress }}
                                    @endcan
                                    @can('manage-followers')
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-12 pb-3">
                                        <div class="relative flex flex-col bg-white rounded-lg h-100">
                                            <div class="p-2 p-sm-3">
                                                <div class="flex flex-row flex-wrap items-center gap-3">
                                                    <div class="bg-card2 p-2 rounded">
                                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <rect width="24" height="24" fill="none" />
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M5 9.5C5 7.01472 7.01472 5 9.5 5C11.9853 5 14 7.01472 14 9.5C14 11.9853 11.9853 14 9.5 14C7.01472 14 5 11.9853 5 9.5Z"
                                                                fill="#2DBCFF" />
                                                            <path
                                                                d="M14.3675 12.0632C14.322 12.1494 14.3413 12.2569 14.4196 12.3149C15.0012 12.7454 15.7209 13 16.5 13C18.433 13 20 11.433 20 9.5C20 7.567 18.433 6 16.5 6C15.7209 6 15.0012 6.2546 14.4196 6.68513C14.3413 6.74313 14.322 6.85058 14.3675 6.93679C14.7714 7.70219 15 8.5744 15 9.5C15 10.4256 14.7714 11.2978 14.3675 12.0632Z"
                                                                fill="#2DBCFF" />
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M4.64115 15.6993C5.87351 15.1644 7.49045 15 9.49995 15C11.5112 15 13.1293 15.1647 14.3621 15.7008C15.705 16.2847 16.5212 17.2793 16.949 18.6836C17.1495 19.3418 16.6551 20 15.9738 20H3.02801C2.34589 20 1.85045 19.3408 2.05157 18.6814C2.47994 17.2769 3.29738 16.2826 4.64115 15.6993Z"
                                                                fill="#2DBCFF" />
                                                            <path
                                                                d="M14.8185 14.0364C14.4045 14.0621 14.3802 14.6183 14.7606 14.7837V14.7837C15.803 15.237 16.5879 15.9043 17.1508 16.756C17.6127 17.4549 18.33 18 19.1677 18H20.9483C21.6555 18 22.1715 17.2973 21.9227 16.6108C21.9084 16.5713 21.8935 16.5321 21.8781 16.4932C21.5357 15.6286 20.9488 14.9921 20.0798 14.5864C19.2639 14.2055 18.2425 14.0483 17.0392 14.0008L17.0194 14H16.9997C16.2909 14 15.5506 13.9909 14.8185 14.0364Z"
                                                                fill="#2DBCFF" />
                                                        </svg>
                                                    </div>
                                                    <div class="order-3 order-sm-2">
                                                        <span class="font-roboto font-semibold">{{ __('Total Users') }}</span>
                                                    </div>
                                                    <p class="order-2 order-sm-3 mb-0 font-sans bg-card2-text text-2xl ml-auto">
                                                        {{ $followers }} </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @if (Auth::user()->type == 'Admin' || Auth::user()->type == 'Influencer')
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-12 pb-3">
                                        <div class="relative flex flex-col bg-white rounded-lg h-100">
                                            <div class="p-2 p-sm-3">
                                                <div class="flex flex-row flex-wrap items-center gap-3">
                                                    <div class="bg-card3 p-2 rounded">

                                                        <svg width="28" height="28" viewBox="0 0 128 128"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                                            role="img" class="iconify iconify--noto"
                                                            preserveAspectRatio="xMidYMid meet">
                                                            <g fill="none">
                                                                <path
                                                                    d="M93.46 39.45c6.71-1.49 15.45-8.15 16.78-11.43c.78-1.92-3.11-4.92-4.15-6.13c-2.38-2.76-1.42-4.12-.5-7.41c1.05-3.74-1.44-7.87-4.97-9.49s-7.75-1.11-11.3.47c-3.55 1.58-6.58 4.12-9.55 6.62c-2.17-1.37-5.63-7.42-11.23-3.49c-3.87 2.71-4.22 8.61-3.72 13.32c1.17 10.87 3.85 16.51 8.9 18.03c6.38 1.92 13.44.91 19.74-.49z"
                                                                    fill="#FFCA28">
                                                                </path>
                                                                <path
                                                                    d="M104.36 8.18c-.85 14.65-15.14 24.37-21.92 28.65l4.4 3.78s2.79.06 6.61-1.16c6.55-2.08 16.12-7.96 16.78-11.43c.97-5.05-4.21-3.95-5.38-7.94c-.61-2.11 2.97-6.1-.49-11.9zm-24.58 3.91s-2.55-2.61-4.44-3.8c-.94 1.77-1.61 3.69-1.94 5.67c-.59 3.48 0 8.42 1.39 12.1c.22.57 1.04.48 1.13-.12c1.2-7.91 3.86-13.85 3.86-13.85z"
                                                                    fill="#E2A610">
                                                                </path>
                                                                <path
                                                                    d="M61.96 38.16S30.77 41.53 16.7 68.61c-14.07 27.08-2.11 43.5 10.55 49.48c12.66 5.98 44.56 8.09 65.31 3.17s25.94-15.12 24.97-24.97c-1.41-14.38-14.77-23.22-14.77-23.22s.53-17.76-13.25-29.29c-12.23-10.24-27.55-5.62-27.55-5.62z"
                                                                    fill="#FFCA28">
                                                                </path>
                                                                <path
                                                                    d="M74.76 83.73c-6.69-8.44-14.59-9.57-17.12-12.6c-1.38-1.65-2.19-3.32-1.88-5.39c.33-2.2 2.88-3.72 4.86-4.09c2.31-.44 7.82-.21 12.45 4.2c1.1 1.04.7 2.66.67 4.11c-.08 3.11 4.37 6.13 7.97 3.53c3.61-2.61.84-8.42-1.49-11.24c-1.76-2.13-8.14-6.82-16.07-7.56c-2.23-.21-11.2-1.54-16.38 8.31c-1.49 2.83-2.04 9.67 5.76 15.45c1.63 1.21 10.09 5.51 12.44 8.3c4.07 4.83 1.28 9.08-1.9 9.64c-8.67 1.52-13.58-3.17-14.49-5.74c-.65-1.83.03-3.81-.81-5.53c-.86-1.77-2.62-2.47-4.48-1.88c-6.1 1.94-4.16 8.61-1.46 12.28c2.89 3.93 6.44 6.3 10.43 7.6c14.89 4.85 22.05-2.81 23.3-8.42c.92-4.11.82-7.67-1.8-10.97z"
                                                                    fill="#6B4B46">
                                                                </path>
                                                                <path d="M71.16 48.99c-12.67 27.06-14.85 61.23-14.85 61.23"
                                                                    stroke="#6B4B46" stroke-width="5"
                                                                    stroke-miterlimit="10">
                                                                </path>
                                                                <path
                                                                    d="M81.67 31.96c8.44 2.75 10.31 10.38 9.7 12.46c-.73 2.44-10.08-7.06-23.98-6.49c-4.86.2-3.45-2.78-1.2-4.5c2.97-2.27 7.96-3.91 15.48-1.47z"
                                                                    fill="#6D4C41">
                                                                </path>
                                                                <path
                                                                    d="M81.67 31.96c8.44 2.75 10.31 10.38 9.7 12.46c-.73 2.44-10.08-7.06-23.98-6.49c-4.86.2-3.45-2.78-1.2-4.5c2.97-2.27 7.96-3.91 15.48-1.47z"
                                                                    fill="#6B4B46">
                                                                </path>
                                                                <path
                                                                    d="M96.49 58.86c1.06-.73 4.62.53 5.62 7.5c.49 3.41.64 6.71.64 6.71s-4.2-3.77-5.59-6.42c-1.75-3.35-2.43-6.59-.67-7.79z"
                                                                    fill="#E2A610">
                                                                </path>
                                                            </g>
                                                        </svg>
                                                    </div>
                                                    <div class="order-3 order-sm-2">
                                                        <span
                                                            class="font-roboto font-semibold">{{ __('Total Earnings') }}</span>
                                                    </div>
                                                    <p
                                                        class="order-2 order-sm-3 mb-0 font-sans bg-card3-text text-2xl ml-auto">
                                                        {{ Utility::amount_format($earning) }} </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (Auth::user()->type == 'Influencer' && !$users->is_stripe_connected)
                                    <div class="col-lg-4">
                                        <div class="card bg-primary">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-sm">
                                                        <h2 class="text-white ">{{ 'Connect Stripe' }}</h2>
                                                        <p class="text-white">
                                                            {{ __('To receive payments for your lessons and subscriptions, please connect your Stripe account.') }}
                                                            {{ __('Ensure that payouts are enabled in your Stripe settings to start receiving funds.') }}
                                                        </p>
                                                        <div class="quick-add-btn">
                                                            {!! Form::open([
                                                                'method' => 'POST',
                                                                'class' => 'd-inline',
                                                                'route' => ['stripe.create', ['influencer_id' => $users->id]],
                                                                'id' => 'stripe-create',
                                                            ]) !!}
                                                            {{ Form::button(__('Connect Stripe'), ['type' => 'submit', 'class' => 'btn-q-add  dash-btn btn btn-default btn-light']) }}
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if (Auth::user()->type == 'Influencer' && $users->stripe_account_id && $subscriptions == false && $influencer_plans)
                                    <div class="col-lg-4">
                                        <div class="card bg-primary">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-sm">
                                                        <h2 class="text-white ">{{ 'Subscribe to a Plan' }}</h2>
                                                        <p class="text-white">
                                                            {{ __('To unlock premium features and maximize your influence, please subscribe to one of our plans.') }}
                                                            {{ __('Choose a plan that suits your needs and start enjoying the benefits today!') }}
                                                        </p>
                                                        <div class="quick-add-btn">
                                                            {!! Form::open([
                                                                'method' => 'POST',
                                                                'class' => 'd-inline',
                                                                'route' => ['subscribe.service.plan', ['id' => $users->id]],
                                                                'id' => 'subscription-create',
                                                            ]) !!}
                                                            {{ Form::button(__('Subscribe'), ['type' => 'submit', 'class' => 'btn-q-add dash-btn btn btn-default btn-light']) }}
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if (Auth::user()->type == 'Influencer')
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    {{ $dataTable->table(['width' => '100%']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (Auth::user()->type == 'Admin')
                                <div class="card dash-supports mt-2">
                                    <div class="card-header">
                                        <h5>{{ __('Influencer Statistics') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Influencer Name') }}</th>
                                                        <th>{{ __('Earnings') }}</th>
                                                        <th>{{ __('Completed In-Person Lessons') }}</th>
                                                        <th>{{ __('Completed Online Lessons') }}</th>
                                                        <th>{{ __('Pending In-Person Lessons') }}</th>
                                                        <th>{{ __('Pending Online Lessons') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($influencerStats as $influencer)
                                                        <tr>
                                                            <td>{{ $influencer->name }}</td>
                                                            <td>${{ number_format($influencer->purchase->where('status', 'complete')->sum('total_amount'), 2) }}
                                                            </td>
                                                            <td>{{ $influencer->completed_inperson_lessons }}</td>
                                                            <td>{{ $influencer->completed_online_lessons }}</td>
                                                            <td>{{ $influencer->pending_inperson_lessons }}</td>
                                                            <td>{{ $influencer->pending_online_lessons }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">
                                                                {{ __('No influencers available') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (Auth::user()->type == 'Follower')
                                @include('admin.influencers.common-profile')
                            @endif
                        </div>
                    </div>
                @endsection
                @push('css')
                    <link rel="stylesheet" type="text/css"
                        href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
                    @include('layouts.includes.datatable_css')
                @endpush
                @push('javascript')
                    @include('layouts.includes.datatable_js')
                    {{ $dataTable->scripts() }}
                    <script src="{{ asset('vendor/modules/moment.min.js') }}"></script>
                    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
                    <script src="{{ asset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
                    <script>
                        $(function() {
                            var start = moment().subtract(29, 'days');
                            var end = moment();

                            function cb(start, end) {
                                $('.chartRange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                                var start = start.format('YYYY-MM-DD');
                                var end = end.format('YYYY-MM-DD');
                                $.ajax({
                                    url: "{{ route('get.chart.data') }}",
                                    type: 'POST',
                                    data: {
                                        start: start,
                                        end: end,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(result) {
                                        chartFun(result.lable, result.value);
                                    },
                                    error: function(data) {
                                        return data.responseJSON;
                                    }
                                });
                            }

                            function chartFun(lable, value) {
                                var options = {
                                    chart: {
                                        height: 400,
                                        type: 'area',
                                        toolbar: {
                                            show: false,
                                        },
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        width: 2,
                                        curve: 'smooth'
                                    },
                                    series: [{
                                        name: 'Users',
                                        data: value
                                    }],
                                    xaxis: {
                                        categories: lable,
                                    },
                                    colors: ['{{ $chatcolor }}'],

                                    grid: {
                                        strokeDashArray: 4,
                                    },
                                    legend: {
                                        show: false,
                                    },
                                    markers: {
                                        size: 4,
                                        colors: ['{{ $chatcolor }}'],
                                        opacity: 0.9,
                                        strokeWidth: 2,
                                        hover: {
                                            size: 7,
                                        }
                                    },
                                    yaxis: {
                                        tickAmount: 3,
                                        min: 0,
                                    }
                                };
                                $("#users-chart").empty();
                                var chart = new ApexCharts(document.querySelector("#users-chart"), options);
                                chart.render();
                            }
                            $('.chartRange').daterangepicker({
                                startDate: start,
                                endDate: end,
                                ranges: {
                                    'Today': [moment(), moment()],
                                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                                        'month').endOf('month')],
                                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                                        'year').endOf('year')],
                                }
                            }, cb);
                            cb(start, end);
                        });
                    </script>
                @endpush
