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
                    <div class="profile-info-container flex">
                        <img alt="{{ $instructor->name }}""
                            src="{{ asset('/storage' . '/' . tenant('id') . '/' . $instructor?->logo) }}""
                            class="rounded-full align-middle border-1 profile-image">
                        <div class="flex flex-col">
                            <span class="font-medium text-3xl mb-2">{{ $instructor->name }}</span>
                            <div class="flex justify-center items-center divide-x divide-solid w-100 gap-2 text-gray-600">
                                <div class="text-sm leading-normal text-gray-600 uppercase">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $instructor->country }}
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
                    <div class="flex justify-between mt-4">
                        <div class="flex justify-center items-center divide-x divide-solid stats-container">
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $followers }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $subscribers }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Subscribers</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $totalPosts }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Posts</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $totalLessons }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Lessons</span>
                            </div>
                        </div>
                        <div class="mr-4">
                            {!! Form::open([
                                'route' => [
                                    'follow.instructor',
                                    [
                                        'influencer_id' => $instructor?->id,
                                        'follow' => $follow->where('follower_id', Auth::user()->id)?->first()?->active_status
                                            ? 'unfollow'
                                            : 'follow',
                                    ],
                                ],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                            {{ Form::button(__($follow->where('follower_id', Auth::user()->id)->first()?->active_status ? 'Unfollow' : 'Follow'), ['type' => 'submit', 'class' => 'follow-profile-btn']) }}
                            {!! Form::close() !!}
                        </div>
                    </div>
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
                        @if($isFollowing)
                            @if (!!$totalLessons)
                                <div id="blog" class="bg-gray-100 px-4 xl:px-4 py-14">
                                    <div class="mx-auto container">
                                        <div class="focus:outline-none mt-5 mb-5 lg:mt-24">
                                            <div class="infinity">
                                                <div class="flex flex-wrap w-100">
                                                    @foreach ($posts as $post)
                                                        @include('admin.posts.blog', ['post' => $post, 'isInfluencer' => $isInfluencer, 'isSubscribed' => $isSubscribed])
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
                        @else
                            <div class="flex flex-col justify-center items-center no-data gap-2 text-center">
                                <i class="fa fa-lock text-xl mb-2" aria-hidden="true"></i>
                                <span class="text-lg font-semibold">Follow to access this section</span>
                            </div>
                        @endif
                    </div>
                    <div id="Subscriptions" class="tabcontent">
                        @if($isFollowing)
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
                                                        <div class="mt-2 d-flex justify-content-center gap-3">
                                                            @if($plan->is_chat_enabled)
                                                                <div class="d-flex align-items-center">
                                                                    <i class="ti ti-message-circle text-success me-1"></i>
                                                                    <span>{{ __('Free Chat') }}</span>
                                                                </div>
                                                            @endif
                                                            @if($plan->is_feed_enabled)
                                                                <div class="d-flex align-items-center">
                                                                    <i class="ti ti-rss text-success me-1"></i>
                                                                    <span>{{ __('Free Feed') }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
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
                        @else
                            <div class="flex flex-col justify-center items-center no-data gap-2 text-center">
                                <i class="fa fa-lock text-xl mb-2" aria-hidden="true"></i>
                                <span class="text-lg font-semibold">Follow to access this section</span>
                            </div>
                        @endif
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
