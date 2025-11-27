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
    $isChatTab = isset($token) ? true : false;
@endphp
<style>
    .cancel-btn {
        background-color: #ff3a6e !important;
        transition: color 0.2s ease !important;
    }

    .cancel-btn:hover {
        background-color: #d9315c !important;
    }

    .lesson-btn:disabled {
        background: rgba(0, 113, 206, 0.5);
        /* faded version */
        cursor: not-allowed;
        opacity: 0.6;
    }



    @media (min-width: 787px) {
        .text-3xl-c {
            font-size: 1.5rem !important;
        }
    }

    .text-3xl-c {
        font-size: 1.3rem !important;
    }
</style>
<div class="flex flex-col">
    <div class="profile-backdrop">
        <div class="profile-info-container flex flex-wrap">
            <img alt="{{ $influencer->name }}" src="{{ $influencer?->logo }}"
                class="rounded-full align-middle border-1 profile-image">
            <div class="flex flex-col">
                <span class="font-medium text-3xl-c mb-2">{{ $influencer->name }}</span>
                <div class="flex divide-x divide-solid w-100 gap-2 text-gray-600">
                    <div class="text-sm leading-normal text-gray-600 uppercase">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $influencer->country }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card min-h-screen">
        <div class="tab">
            <button class="tablinks {{ $tab == 'lessons' ? 'active' : '' }}"
                onclick="window.location.href='home?tab=lessons'">Offerings</button>
            <button class="tablinks {{ $tab == 'posts' ? 'active' : '' }}"
                onclick="window.location.href='home?tab=posts'">Tips & Drills</button>
            <button class="tablinks {{ $tab == 'subscriptions' ? 'active' : '' }}"
                onclick="window.location.href='home?tab=subscriptions'">Subscriptions</button>
            <button class="tablinks {{ $isChatTab ? 'active' : '' }}"
                onclick="window.location.href='home?tab=chat'">Chat</button>
            </hr>
        </div>
        @if ($tab == 'lessons')
            <div id="Lessons" class="tabcontent flex items-center block">
                @if (!!$totalLessons)
                    <livewire:lessons-grid-view />
                @else
                    <div class='flex flex-col justify-center items-center no-data gap-2'><i class="fa fa-thumbs-down"
                            aria-hidden="true"></i>There are no lessons from this
                        influencer yet
                    </div>
                @endif
            </div>
        @endif
        @if ($tab == 'posts')
            <div id="Posts" class="tabcontent block">
                @if (!!$totalLessons)
                    <div id="blog" class="">
                        <div class="dropdown dash-h-item drp-company mt-3">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="hide-mob ms-2 text-lg">Filter</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown">
                                <a href="{{ route('home', ['filter' => 'all', 'tab' => 'posts']) }}"
                                    class="dropdown-item {{ request()->query('filter') === 'all' ? 'active' : '' }}">
                                    <span>{{ __('All') }}</span>
                                </a>
                                <a href="{{ route('home', ['filter' => 'free', 'tab' => 'posts']) }}"
                                    class="dropdown-item  {{ request()->query('filter') === 'free' ? 'active' : '' }}">
                                    <span>{{ __('Free') }}</span>
                                </a>
                                <a href="{{ route('home', ['filter' => 'paid', 'tab' => 'posts']) }}"
                                    class="dropdown-item {{ request()->query('filter') === 'paid' ? 'active' : '' }}">
                                    <span>{{ __('Paid') }}</span>
                                </a>
                            </div>
                        </div>
                        <div class="dataTable-top row">

                            <div class="col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block"></div>
                            <div class="tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex">
                                <select id="album-category" class="form-select"
                                    style="margin-left:auto; max-width: 12.5rem;">
                                    <option value="" disabled>
                                        - Select Category -
                                    </option>
                                    <option value=""
                                        {{ request()->query('category') === ' ' ? 'selected' : '' }}>
                                        View Individual Tips/Drills
                                    </option>
                                    <option value="all_category"
                                        {{ request()->query('category') === 'all_category' ? 'selected' : '' }}>
                                        View Categories
                                    </option>
                                    {{-- @foreach ($album_categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ request()->query('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->title }}
                                                    </option>
                                                @endforeach --}}
                                </select>
                            </div>
                            <div class="">
                                <div class="focus:outline-none mt-4 mb-5 lg:mt-24">
                                    @if (request()->query('category_album'))
                                        <div class="infinity">
                                            @include('admin.posts.album-view', [
                                                'albums' => $albumcategories,
                                            ])
                                        @elseif (request()->query('category') == 'all_category')
                                            <div class="infinity">
                                                @include('admin.posts.album-category-view', [
                                                    'albums' => $albums,
                                                ])
                                            </div>
                                        @else
                                            <div class="infinity">
                                                <div class="flex flex-wrap w-100">
                                                    @if (request()->query('category') == 'all_category')
                                                    @endif
                                                    @foreach ($posts as $post)
                                                        @php
                                                            $purchasePost = $post->purchasePost->firstWhere(
                                                                'follower_id',
                                                                Auth::id(),
                                                            );
                                                            $purchasePost = $purchasePost->active_status ?? false;
                                                        @endphp
                                                        @include('admin.posts.blog', [
                                                            'post' => $post,
                                                            'isInfluencer' => $isInfluencer,
                                                            'isSubscribed' => $isSubscribed,
                                                            'purchasePost' => $purchasePost,
                                                        ])
                                                    @endforeach
                                                </div>
                                                <div class="float-end">
                                                    {{ $posts->withQueryString()->links('pagination::bootstrap-4') }}
                                                </div>
                                            </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class='flex flex-col justify-center items-center no-data gap-2'><i
                                class="fa fa-thumbs-down" aria-hidden="true"></i>There are no posts from
                            this influencer yet
                        </div>
                @endif
            </div>
        @endif
        @if ($tab == 'subscriptions')
            <div id="Subscriptions" class="tabcontent block">
                <div class="row gy-4 gx-3 ">
                    @foreach ($plans as $plan)
                        @if ($plan->active_status == 1)
                            {{-- <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 d-flex align-items-stretch">
                                <div class="card price-card price-1 wow animate__fadeInUp ani-fade w-100 h-100"
                                    data-wow-delay="0.2s">
                                    <div
                                        class="rounded-lg shadow popular-wrap d-flex flex-column justify-content-between h-100">

                                        <!-- Plan Header -->
                                        <div class="px-4 pt-4 text-center">
                                            <p class="text-2xl font-bold mb-1">{{ $plan->name }}</p>
                                            <div class="d-flex justify-content-center align-items-end flex-wrap mt-2">
                                                <p class="h1 fw-bold mb-0">{{ $currency_symbol . ' ' . $plan->price }}
                                                </p>
                                                <p class="text-muted fs-5 mb-1 ms-1">
                                                    /{{ $plan->duration . ' ' . $plan->durationtype }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="border-top my-3"></div>

                                        <!-- Plan Body -->
                                        <div class="px-4 pb-4 d-flex flex-column flex-grow-1 justify-content-between">


                                            <!-- Fixed Button Section -->
                                            <div class="mt-4">
                                                @if ($plan->id != 1)
                                                    @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                                        <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                                            class="w-100 btn btn-secondary fw-bold py-2 rounded-pill mt-auto"
                                                            data-amount="{{ $plan->price }}">
                                                            {{ __('Expire at') }}
                                                            {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                            class="w-100 btn btn-primary fw-bold py-2 rounded-pill mt-auto">
                                                            @if ($plan->id == $user->plan_id)
                                                                {{ __('Renew') }}
                                                            @else
                                                                {{ __('Buy Plan') }}
                                                            @endif
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                            <div>
                                                <p class="fw-semibold fs-5 mb-2">Features</p>
                                                <div class="text-muted small">{!! $plan->description !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-xl-3 col-md-6 py-4">
                                <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"
                                    data-wow-delay="0.2s">
                                    <div class="rounded-lg shadow popular-wrap h-100">
                                        <div class="px-3 pt-4 ">
                                            <p class="text-2xl font-bold mb-1">
                                                {{ $plan->name }}
                                            </p>

                                            <span class="text-gray-600"><strong>Influencer:
                                                    {{ $plan->influencer->name }}</strong></span>
                                            <br>
                                            <span class="text-gray-600"><strong>Total Duration:
                                                    {{ $plan->duration . ' ' . $plan->durationtype }}
                                                </strong></span>
                                            <br>
                                            @if ($plan->lesson_limit != 0)
                                                <span class="text-gray-600"><strong>Online Lesson Limit:
                                                        {{ $plan->lesson_limit_label }}
                                                    </strong></span>
                                            @endif
                                            <div class="flex gap-1 items-center mt-2 ">
                                                <p class="text-4xl font-bold">
                                                    {{ '$' . $plan->price }}/</p>
                                                <p class="text-2xl text-gray-600">
                                                    Month
                                                </p>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-300"></div>
                                        <div class="px-3 py-4">
                                            @if ($plan->id != 1)
                                                {{-- @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                                                        <a href="javascript:void(0)"
                                                                            data-id="{{ $plan->id }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                            data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                                            {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                                                    @else
                                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            @if ($plan->id == $user->plan_id)
                                                                                {{ __('Renew') }}
                                                                            @else
                                                                                {{ __('Buy Plan') }}
                                                                            @endif
                                                                        </a>
                                                                    @endif --}}
                                                {{-- @dd(auth('student')->user()) --}}
                                                {{-- @if (auth('student')->user())
                                                            @if (auth('student')->user()->plan_id != null)
                                                                @if ($plan->id == auth('student')->user()->plan_id)
                                                                    @if (!empty(auth('student')->user()->plan_expired_date) && \Carbon\Carbon::parse(auth('student')->user()->plan_expired_date)->gte(now()))
                                                                        <a href="javascript:void(0)"
                                                                            data-id="{{ $plan->id }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                            data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                                            {{ \Carbon\Carbon::parse(auth('student')->user()->plan_expired_date)->format('d/m/Y') }}</a>
                                                                        <a href="{{ route('plans.cancel', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn">Cancel
                                                                            Plan</a>
                                                                    @else
                                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            @if ($plan->id == auth('student')->user()->plan_id)
                                                                                {{ __('Renew') }}
                                                                            @else
                                                                                {{ __('Buy Plan') }}
                                                                            @endif
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                  

                                                                    <button disabled
                                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                        {{ __('Buy Plan') }}
                                                                    </button>
                                                                @endif
                                                            @else
                                                                <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    @if ($plan->id == auth('student')->user()->plan_id)
                                                                        {{ __('Renew') }}
                                                                    @else
                                                                        {{ __('Buy Plan') }}
                                                                    @endif
                                                                </a>
                                                            @endif
                                                        @else
                                                            @if (auth('web')->user() || auth('instructors')->user())
                                                                <button disabled
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </button>
                                                            @else
                                                                <a href="{{ route('login') }}"
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </a>
                                                            @endif
                                                        @endif --}}

                                                @php
                                                    $follower = auth('follower')->user();
                                                    $webUser = auth('web')->user();
                                                    $influencer = auth('influencers')->user();

                                                    $hasStudent = !is_null($follower);
                                                    $hasPlan = $hasStudent && !is_null($follower->plan_id);
                                                    $isCurrentPlan = $hasPlan && $plan->id == $follower->plan_id;
                                                    $isActive =
                                                        $isCurrentPlan &&
                                                        !empty($follower->plan_expired_date) &&
                                                        \Carbon\Carbon::parse($follower->plan_expired_date)->gte(now());
                                                @endphp

                                                @if ($hasStudent)
                                                    @if ($isCurrentPlan)
                                                        @if ($isActive)
                                                            {{-- ✅ Current active plan --}}
                                                            <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                                                class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                data-amount="{{ $plan->price }}">
                                                                {{ __('Expire at') }}
                                                                {{ \Carbon\Carbon::parse($follower->plan_expired_date)->format('d/m/Y') }}
                                                            </a>
                                                            <a href="{{ route('plans.cancel', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn">
                                                                {{ __('Cancel Plan') }}
                                                            </a>
                                                        @else
                                                            {{-- 🔁 Expired plan → Renew --}}
                                                            <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                {{ __('Renew') }}
                                                            </a>
                                                        @endif
                                                    @elseif ($hasPlan)
                                                        {{-- 🚫 User has another plan --}}
                                                        <button disabled
                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                            {{ __('Buy Plan') }}
                                                        </button>
                                                    @else
                                                        {{-- 🛒 No plan yet --}}
                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                            {{ __('Buy Plan') }}
                                                        </a>
                                                    @endif
                                                @elseif ($webUser || $influencer)
                                                    {{-- 🚷 Logged in as non-student --}}
                                                    <button disabled
                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                        {{ __('Buy Plan') }}
                                                    </button>
                                                @else
                                                    {{-- 🔐 Guest user --}}
                                                    <a href="{{ route('login') }}"
                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                        {{ __('Buy Plan') }}
                                                    </a>
                                                @endif
                                            @endif
                                            <p class="font-semibold text-xl mb-2 mt-2">Includes:</p>
                                            <p class="text-gray-600">
                                                {!! $plan->description !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        @endif
        @if ($isChatTab)

            <div id="Chat" class="tabcontent block">
                <div class="row">
                    @if ($chatEnabled)
                        @include('admin.followers.chat', ['token' => $token, 'influencer' => $influencer])
                    @else
                        @foreach ($plans as $plan)
                            @if ($plan->active_status == 1 && $plan->is_chat_enabled == 1)
                                <div class="col-xl-3 col-md-6 py-4">
                                    <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"
                                        data-wow-delay="0.2s">
                                        <div class="rounded-lg shadow popular-wrap h-100">
                                            <div class="px-3 pt-4 ">
                                                <p class="text-2xl font-bold mb-1">{{ $plan->name }}</p>
                                                <div class="flex gap-2 items-center mt-2 ">
                                                    <p class=" text-6xl font-bold">
                                                        {{ $currency_symbol . ' ' . $plan->price }} /</p>
                                                    <p class="text-2xl text-gray-600">
                                                        {{ $plan->duration . ' ' . $plan->durationtype }}</p>
                                                </div>
                                            </div>
                                            <div class="border-t border-gray-300"></div>
                                            <div class="px-3 py-4">
                                                @if ($plan->id != 1)
                                                    @if (
                                                        $plan->id == $user->plan_id &&
                                                            !empty($user->plan_expired_date) &&
                                                            Carbon::parse($user->plan_expired_date)->gte(now()))
                                                        <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                            data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                            {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                                    @else
                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                            @if ($plan->id == $user->plan_id)
                                                                {{ __('Renew') }}
                                                            @else
                                                                {{ __('Buy Plan') }}
                                                            @endif
                                                        </a>
                                                    @endif
                                                @endif
                                                <p class="font-semibold text-xl mb-2 mt-2">Features</p>
                                                <p class="text-gray-600">
                                                    {!! $plan->description !!}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@push('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.3.7/jquery.jscroll.min.js"></script>
    <script>
        document.getElementById('album-category').addEventListener('change', function() {
            let categoryId = this.value;

            // Only redirect if current view is 'posts' (or not in-person/online)
            const url = new URL(window.location.href);
            const currentView = url.searchParams.get('tab') || 'posts';


            if (currentView === 'posts') {
                url.searchParams.set('category', categoryId);
                url.searchParams.delete('category_album');
                window.location.href = url.toString();
            }
        });
        document.getElementById('Lessons').style.display = "{{ $isChatTab ? 'hidden' : 'block' }}";

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
