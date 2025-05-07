@php
    $languages = \App\Facades\UtilityFacades::languages();
    $currency = tenancy()->central(function ($tenant) {
    return Utility::getsettings('currency_symbol');
    });
@endphp
@extends('layouts.main-landing')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css" />
<link rel="stylesheet" href="http://parth.tc.localhost/projects/tuneup_club/vendor/tailwind.css" />
<link rel="stylesheet" href="https://demo.collegegolfrecruitingportal.com/assets/css/customizer.css">
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-white px-0 py-3">
        <div class="container ctm-container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="...">
            </a>
            <button class="request-text border-0 rounded-pill demo px-4 py-2 bg-primary">
                <a class="text-white font-bold" href="{{ route('login') }}" style="text-decoration: none">
                    Login/Signup</a>
            </button>
        </div>
    </nav>
</header>

<section class="landing-hero">
    <div class="hero-sec">
        <img class="w-full" src="{{ $influencerDetails->banner_image }}" alt="hero-banner">
    </div>
</section>
<section class="lession-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">{{ $influencerDetails->name }}</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl">{{ $influencerDetails->bio }}</p>
    </div>
    <div class="container-fluid lessions-slider pt-5">
        @if(!$influencerDetails->lessons->isEmpty())
            @foreach ($influencerDetails->lessons as $lesson)
                <div class="px-3 py-4">
                    <div class=" bg-white rounded-lg shadow   flex flex-col">
                        <div class="relative text-center p-3 flex gap-3">
                            <img src="{{ $influencerDetails->avatar }}"
                                alt="{{ $influencerDetails->name }}"
                                class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                            <div class="text-left">
                                <a class="font-bold text-dark text-xl"
                                    href="{{ route('login') }}">
                                    {{ $influencerDetails->name }}
                                </a>
                                <div class="text-lg font-bold tracking-tight text-primary">
                                    {{ $currency}} {{ $lesson->lesson_price }} (USD)
                                </div>
                                <div class="text-sm font-medium text-gray-500 italic">
                                    <span class="">({!! \App\Models\Purchase::where('lesson_id', $lesson->id)->where('status', 'complete')->count() !!} Purchased)</span>
                                </div>
                            </div>
                        </div>

                        <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                            <span class="text-xl font-semibold text-dark">{{ $lesson->lesson_name }}</span>
                            <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                                {{ $lesson->lesson_description }}
                            </p>

                            <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
                                <div class="text-center w-50">
                                    <span class="text-xl font-bold">{{ $lesson->lesson_quantity }}</span>
                                    <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                                </div>
                                <div class="text-center w-50">
                                    <span class="text-xl font-bold">{{ $lesson->required_time }} Days</span>
                                    <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                </div>
                            </div>
                            <div class="w-100 mt-3">
                                {{-- <form method="POST"
                                    action="{{ route('login') }}"
                                    accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                    data-validate="" novalidate="true"><input name="_token" type="hidden"
                                        value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                    <button type="submit" class="lesson-btn py-2">Purchase</button>
                                </form> --}}
                                <a href="{{ route('login') }}">
                                    <button type="submit" class="lesson-btn py-2">Purchase</button>
                                </a>
                            </div>
                        </div>
                        <form id="bookingForm" method="POST"
                            action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                            <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                                type="hidden" id="slotIdInput" name="slot_id">
                            <input type="hidden" id="friendNamesInput" name="friend_names">

                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</section>

<section class="lession-sec subscription-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Subscription Plans</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <div class="subscription-slider pt-5">
            @if(!$plans->isEmpty())
                @foreach($plans as $plan)
                    <div class="px-3 py-4">
                        <div class="bg-white rounded-lg shadow popular-wrap position-relative">
                            @if($plan->is_chat_enabled && $plan->is_feed_enabled)
                                <div class="rounded-pill px-4 py-2 popular-plan w-auto bg-primary text-white font-bold position-absolute" style="top: -15px; left: 50%; transform: translateX(-50%);">
                                    POPULAR
                                </div>
                            @endif
                            <div class="relative p-3">
                                
                                <p class="text-2xl font-semibold mb-2">{{ $plan->name }}</p>
                                <p class="text-gray-600">
                                    {{ $plan->description }}
                                </p>
                                <div class="flex gap-2 items-center my-3">
                                    <h2 class="text-6xl font-bold">{{ $currency . ' ' . $plan->price }}</h2>
                                    <div>
                                        <p class="text-gray-600">{{ $plan->duration . ' ' . $plan->durationtype }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('login') }}">
                                    <button type="submit" class="lesson-btn font-bold text-lg">Purchase</button>
                                </a>
                            </div>
                            <div class="border-t border-gray-300"></div>
                            <div class="p-3">
                                <p class="font-semibold text-xl">Features</p>
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
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<section class="lession-sec feed-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Feed</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

        <div class="flex flex-wrap gap-5 w-100">
            @if(!$influencerDetails->post->isEmpty())
                @foreach ($influencerDetails->post as $post)
                    <div class="max-w-sm w-full">
                        <div class="shadow rounded-2 overflow-hidden position-relative">
                            @if($post->paid && !isset($purchasePost))
                                <?php $cls  = 'p-3 position-absolute left-0 top-0 z-10 w-full'; ?>
                            @else
                                <?php $cls  = 'p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient'; ?>
                            @endif
                            <div class="{{ $cls }}">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center gap-3">
                                        <img class="w-16 h-16 rounded-full"
                                            src="{{ $influencerDetails->avatar }}"
                                            alt="Profile">
                                        <div>
                                            <p class="text-xl text-white font-bold mb-0 leading-tight">
                                                {{ $influencerDetails->name }}
                                            </p>
                                            <span class="text-md text-white">
                                                {{ $influencerDetails->type}}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                        {{-- <form method="POST"
                                            action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=9"
                                            accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                                type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                            <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                                    class="text-2xl lh-sm ti ti-heart"></i><span> {{ $post->likePost->count() }} Likes</span></button>
                                        </form> --}}
                                        <a href="{{ route('login') }}">
                                            <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                                    class="text-2xl lh-sm ti ti-heart"></i><span> {{ $post->likePost->count() }} Likes</span></button>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <div class="relative paid-post-wrap">
                                <img class=" w-full post-thumbnail"
                                    src="{{ $post->file }}"
                                    alt="Post Image">
                                @if($post->paid)
                                    <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                                        <div
                                            class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                            <i class="ti ti-lock-open"></i>
                                        </div>
                                        {{-- <form method="POST"
                                            action="https://demo.collegegolfrecruitingportal.com/purchase/post/instructor?post_id=9"
                                            accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                                type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                            <div
                                                class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                                    {{ $currency . ' ' . $post->price }}</button>
                                            </div>
                                        </form> --}}
                                        <a href="{{ route('login') }}">
                                            <div
                                                class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                                    {{ $currency . ' ' . $post->price }}</button>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            </div>


                            <div class="px-4 py-2 border-t border-gray-500">
                                <div class="text-md italic text-gray-500">
                                    {{ $post->created_at }}
                                </div>
                                <h1 class="text-xl font-bold truncate">
                                    {{ $post->title }}
                                </h1>

                                @php
                                    $description = strip_tags($post->description);
                                    $shortDescription = \Illuminate\Support\Str::limit($description, 50, '');
                                @endphp
                                <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                                    <span class="short-text">{{ $shortDescription }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<footer class="foot mt-0">
    <div class="text-center container ctm-container footer-one">
        <div class="flex justify-center">
            <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="img-fluid" alt="" />
        </div>

        <p class="mt-3 fot-golf word-spacing-5">
            Trusted by Industry leading Golf Club, Instructors, Acadmies, and
            Teaching Professionals.
        </p>
    </div>
</footer>
<footer class="foot-two">
    <div class="flex justify-content-sm-between justify-center align-items-center container footer-two">
        <div class="text-white m-0">
            <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
        </div>
        <div class="icon flex mt-2 sm-mt-0 text-3xl flex gap-3">
            <a href="{{ $influencerDetails->social_url_fb }}" class="text-gray-800"><i class="ti ti-brand-facebook"></i></a>
            <a href="{{ $influencerDetails->social_url_x }}" class="text-gray-800"><i class="ti ti-brand-twitter"></i></a>
            <a href="{{ $influencerDetails->social_url_ig }}" class="text-gray-800"><i class="ti ti-brand-instagram"></i></a>
            <a href="{{ $influencerDetails->social_url_yt }}" class="text-gray-800"><i class="ti ti-brand-youtube"></i></a>
            <a href="{{ $influencerDetails->social_url_ln }}" class="text-gray-800"><i class="ti ti-brand-linkedin"></i></a>
        </div>
    </div>
</footer>
@endsection
