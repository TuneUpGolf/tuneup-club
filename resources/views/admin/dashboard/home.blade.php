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

@endphp
@extends('layouts.main')
@section('title', __('Dashboard'))
@section('content')
<div class="row">
    <div class="col-xxl-12">
        <div class="row">
            @can('manage-lessons')
            <div class="col-lg-3 col-md-6 col-6 pb-3">
                <div class="relative flex flex-col bg-white rounded-lg w-96">
                    <div class="p-2 p-sm-3 flex flex-col">
                        <div class="flex flex-row flex-wrap items-center gap-3">
                            <div class="bg-card1 p-2 rounded">
                                <svg width="28" height="28" viewBox="0 0 35 35" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M4.5727 30.357H32.906C33.9492 30.357 34.7949 31.2027 34.7949 32.2459C34.7949 33.2891 33.9492 34.1348 32.906 34.1348H2.68381C1.64061 34.1348 0.794922 33.2891 0.794922 32.2459V2.02365C0.794922 0.98045 1.64061 0.134766 2.68381 0.134766C3.72702 0.134766 4.5727 0.98045 4.5727 2.02365V30.357Z"
                                        fill="#4AD991" />
                                    <path opacity="0.5"
                                        d="M11.6204 22.2038C10.9069 22.9648 9.71158 23.0034 8.95052 22.2899C8.18947 21.5764 8.15091 20.381 8.8644 19.62L15.9477 12.0644C16.6378 11.3284 17.7844 11.2646 18.5519 11.9195L24.1425 16.6901L31.4265 7.46364C32.0729 6.64485 33.2607 6.50511 34.0795 7.15153C34.8983 7.79794 35.038 8.98573 34.3916 9.80452L25.8916 20.5712C25.2277 21.4122 23.998 21.5331 23.183 20.8376L17.4709 15.9633L11.6204 22.2038Z"
                                        fill="#4AD991" />
                                </svg>
                            </div>
                            <div class="order-3 order-sm-2">
                                <p class="font-sans font-thin mb-0">{{ __('Online') }}</p>
                                <span class="font-roboto font-semibold"> {{ __('Completed Lessons') }} </span>
                            </div>
                            <p class="order-2 order-sm-3 mb-0 font-sans  bg-card-text text-2xl ml-auto">
                                {{ $purchaseComplete }} </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6 pb-3">
                <div class="relative flex flex-col bg-white rounded-lg w-96">
                    <div class="p-2 p-sm-3 flex flex-col">
                        <div class="flex flex-row flex-wrap items-center gap-3">
                            <div class="bg-card4 p-2 rounded">
                                <svg width="28" height="28" viewBox="0 0 33 35" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.48" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M14.87 10.6994C14.89 10.4389 15.1072 10.2378 15.3685 10.2378H16.0146C16.2714 10.2378 16.4865 10.4324 16.5121 10.688L17.2478 18.0456L22.4614 21.0248C22.6172 21.1139 22.7133 21.2795 22.7133 21.459V22.0757C22.7133 22.4054 22.3999 22.6449 22.0818 22.5581L14.5234 20.4967C14.292 20.4336 14.138 20.2151 14.1564 19.976L14.87 10.6994Z"
                                        fill="#FEC53D" />
                                    <path opacity="0.901274" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.83134 0.313688C7.56724 -0.00106104 7.0573 0.119411 6.96197 0.519073L4.98275 8.81675C4.90507 9.1424 5.16314 9.4509 5.49739 9.43196L14.0333 8.94824C14.4442 8.92496 14.6526 8.44293 14.388 8.12765L12.1686 5.48265C13.5334 5.01605 14.9807 4.77234 16.4668 4.77234C23.7975 4.77234 29.7402 10.715 29.7402 18.0457C29.7402 25.3764 23.7975 31.319 16.4668 31.319C9.13614 31.319 3.19345 25.3764 3.19345 18.0457C3.19345 16.8151 3.36011 15.6097 3.68505 14.4513L0.677972 13.6078C0.282053 15.0193 0.0703125 16.5077 0.0703125 18.0457C0.0703125 27.1012 7.41127 34.4422 16.4668 34.4422C25.5223 34.4422 32.8633 27.1012 32.8633 18.0457C32.8633 8.99016 25.5223 1.6492 16.4668 1.6492C14.1883 1.6492 12.0184 2.11394 10.0467 2.9538L7.83134 0.313688Z"
                                        fill="#FEC53D" />
                                </svg>
                            </div>
                            <div class="order-3 order-sm-2">
                                <p class="font-sans font-thin mb-0">{{ __('Online') }}</p>
                                <span class="font-roboto font-semibold"> {{ __('Lessons Pending') }} </span>
                            </div>

                            <p class="order-2 order-sm-3 mb-0 font-sans bg-card4-text text-2xl ml-auto">
                                {{ $purchaseInprogress }} </p>

                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('manage-followers')
            <div class="col-lg-3 col-md-6 col-6 pb-3">
                <div class="relative flex flex-col bg-white rounded-lg w-96">
                    <div class="p-2 p-sm-3 flex flex-col">
                        <div class="flex flex-row flex-wrap items-center gap-3">
                            <div class="bg-card2 p-2 rounded">
                                <svg width="28" height="28" viewBox="0 0 31 34" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.441406 11.9137L13.3399 19.3607C13.4788 19.4409 13.6244 19.4988 13.7727 19.5356V33.9786L1.36133 26.6335C0.791136 26.2961 0.441406 25.6827 0.441406 25.0202V11.9137ZM30.4377 11.7158V25.0202C30.4377 25.6828 30.088 26.2961 29.5178 26.6336L17.1064 33.9786V19.4091C17.1367 19.394 17.1667 19.3779 17.1963 19.3608L30.4377 11.7158Z"
                                        fill="#2DBCFF" />
                                    <path opacity="0.3992" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.84668 8.29887C1.00423 8.09992 1.20309 7.93177 1.43493 7.80828L14.5579 0.818699C15.1088 0.525278 15.7696 0.525278 16.3205 0.818699L29.4435 7.80828C29.6222 7.90347 29.7813 8.0252 29.917 8.16713L15.5291 16.474C15.4345 16.5286 15.3473 16.5911 15.2678 16.6602C15.1883 16.5911 15.101 16.5286 15.0064 16.474L0.84668 8.29887Z"
                                        fill="#2DBCFF" />
                                </svg>
                            </div>
                            <div class="order-3 order-sm-2">
                                <p class="font-sans font-thin mb-0">{{ __('Total') }}</p>
                                <span class="font-roboto font-semibold">{{ __('Followers') }}</span>
                            </div>
                            <p class="order-2 order-sm-3 mb-0 font-sans bg-card2-text text-2xl ml-auto">
                                {{ $followers }} </p>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @if (Auth::user()->type == 'Admin' || Auth::user()->type == 'Influencer')
            <div class="col-lg-3 col-md-6 col-6 pb-3">
                <div class="relative flex flex-col bg-white rounded-lg w-96">
                    <div class="p-2 p-sm-3 flex flex-col">
                        <div class="flex flex-row flex-wrap items-center gap-3">
                            <div class="bg-card3 p-2 rounded">
                                <svg width="28" height="28" viewBox="0 0 35 35" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_176_931)">
                                        <path
                                            d="M18.0657 18.1301V0.134766C8.42653 0.640362 0.575909 8.49092 0.0703125 18.1301H18.0657ZM14.0815 15.142H12.0894V13.1499H14.0815V15.142ZM8.10514 9.16565H10.0972V11.1578H8.10514V9.16565ZM13.0854 8.75321L14.4939 10.1617L9.10119 15.5544L7.69271 14.1459L13.0854 8.75321Z"
                                            fill="#FF0000" fill-opacity="0.25" />
                                        <path
                                            d="M18.066 20.1221H6.15137C6.63678 26.47 11.7182 31.5514 18.0661 32.0368V20.1221H18.066Z"
                                            fill="#FF0000" />
                                        <path
                                            d="M20.0576 4.11914C20.0576 4.33309 20.0576 34.3687 20.0576 34.1335C27.8316 33.6172 34.0688 26.9295 34.0688 19.0269C34.0688 11.1236 27.8335 4.6337 20.0576 4.11914ZM26.0339 18.1303C27.6817 18.1303 29.0221 19.4707 29.0221 21.1184C29.0221 22.4153 28.1869 23.5106 27.03 23.9231V26.0987H25.0379V23.9231C23.881 23.5106 23.0458 22.4153 23.0458 21.1184H25.0379C25.0379 21.668 25.4844 22.1145 26.0339 22.1145C26.5835 22.1145 27.03 21.668 27.03 21.1184C27.03 20.5689 26.5835 20.1224 26.0339 20.1224C24.3862 20.1224 23.0458 18.782 23.0458 17.1342C23.0458 15.8374 23.881 14.7421 25.0379 14.3295V12.154H27.03V14.3295C28.1869 14.7421 29.0221 15.8374 29.0221 17.1342H27.03C27.03 16.5847 26.5835 16.1382 26.0339 16.1382C25.4844 16.1382 25.0379 16.5847 25.0379 17.1342C25.0379 17.6838 25.4844 18.1303 26.0339 18.1303Z"
                                            fill="#FF0000" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_176_931">
                                            <rect width="34" height="34" fill="white"
                                                transform="translate(0.0693359 0.134766)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </div>
                            <div class="order-3 order-sm-2">
                                <p class="font-sans font-thin mb-0">{{ __('Total') }}</p>
                                <span class="font-roboto font-semibold">{{ __('Earnings') }}</span>
                            </div>
                            <p class="order-2 order-sm-3 mb-0 font-sans bg-card3-text text-2xl ml-auto">
                                {{ Utility::amount_format($earning) }} </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if (Auth::user()->type == 'Influencer' && !$users->is_stripe_connected)
        <div class="col-lg-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-sm">
                            <h2 class="text-white ">{{ 'Connect Stripe' }}</h2>
                            <p class="text-white">
                                {{ __('Inorder to recieve payments for your lessons and subscriptions you need to connect stripe.') }}<br/>
                                *{{ __('Make sure you have enabled the payouts in your stripe account.') }}
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

        @if (Auth::user()->type == 'Influencer' || Auth::user()->type == 'Follower')
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
                                <td colspan="5" class="text-center">{{ __('No influencers available') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endsection
    @push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
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
    {{-- {{ $dataTable->scripts() }} --}}
    <script type="text/javascript">
    $(document).ready(function() {
        var html =
            $('.dataTable-title').html(
                "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>My online Submissions</span></div>"
            );
    });
    </script>
    @endpush