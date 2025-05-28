@php
    use Carbon\Carbon;
    $user = Auth::user();
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
@section('title', __('Influencer Profile'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Influencer Profile') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="flex flex-col">
                <div class="profile-backdrop">
                    <div class="profile-info-container flex flex-wrap">
                        <img alt="{{ $influencer->name }}"
                            src="{{ $influencer?->logo }}"
                            class="rounded-full align-middle border-1 profile-image">
                        <div class="flex flex-col">
                            <span class="font-medium text-3xl mb-2">{{ $influencer->name }}</span>
                            <div class="flex justify-center items-center divide-x divide-solid w-100 gap-2 text-gray-600">
                                <div class="text-sm leading-normal text-gray-600 uppercase">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $influencer->country }}
                                </div>
                                <div class="text-sm leading-normal text-gray-600 uppercase">
                                    <i class="fas fa-user"></i>
                                    Influencer
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card min-h-screen">
                    <div class="tab">
                        <button class="tablinks active" onclick="openCity(event, 'Lessons')">Lessons</button>
                        <button class="tablinks" onclick="openCity(event, 'Posts')">Posts</button>
                        <button class="tablinks" onclick="openCity(event, 'Subscriptions')">Subscriptions</button>
                        </hr>
                    </div>
                    <div id="Lessons" class="tabcontent flex items-center">
                        @if (!!$totalLessons)
                            <livewire:lessons-grid-view />
                        @else
                            <div class='flex flex-col justify-center items-center no-data gap-2'><i
                                    class="fa fa-thumbs-down" aria-hidden="true"></i>There are no lessons from this
                                influencer yet
                            </div>
                        @endif
                    </div>
                    <div id="Posts" class="tabcontent">
                        @if (!!$totalLessons)
                            <div id="blog" class="">
                                <div class="">
                                    <div class="focus:outline-none mt-4 mb-5 lg:mt-24">
                                        <div class="infinity">
                                            <div class="flex flex-wrap w-100">
                                                @foreach ($posts as $post)
                                                    @php
                                                        $purchasePost = $post->purchasePost->firstWhere('follower_id', Auth::id());
                                                    @endphp
                                                    @include('admin.posts.blog', ['post' => $post, 'isInfluencer' => $isInfluencer, 'isSubscribed' => $isSubscribed, 'purchasePost' => $purchasePost])
                                                @endforeach
                                                {{ $posts->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class='flex flex-col justify-center items-center no-data gap-2'><i
                                    class="fa fa-thumbs-down" aria-hidden="true"></i>There are no posts from
                                this influencer yet</div>
                        @endif
                    </div>
                    <div id="Subscriptions" class="tabcontent">
                        <div class="row ">
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
                                                    
                                                    <ul class="mt-2 pl-0">
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endsection
        @push('css')
            @include('layouts.includes.datatable_css')
            <link rel="stylesheet"
                href="https://demos.creative-tim.com/notus-js/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
        @endpush
        @push('javascript')
            <script>
                document.getElementById('Lessons').style.display = "block";

                function openCity(evt, tabName) {
                    // Declare all variables
                    var i, tabcontent, tablinks;

                    // Get all elements with class="tabcontent" and hide them
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }

                    // Get all elements with class="tablinks" and remove the class "active"
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }

                    // Show the current tab, and add an "active" class to the button that opened the tab
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.className += " active";

                }
                $('ul.pagination').hide();
                $(function() {
                    $('.infinity').jscroll({
                        autoTrigger: true,
                        debug: false,
                        loadingHtml: '<img class="center-block" src="/images/loading.gif" alt="Loading..." />',
                        padding: 0,
                        nextSelector: '.pagination li.active + li a',
                        contentSelector: '.infinity',
                        callback: function() {
                            $('ul.pagination').remove();
                        }
                    });
                });
            </script>
        @endpush
