@php
$users = \Auth::user();
$currantLang = $users->currentLanguage();
$languages = Utility::languages();
@endphp
<nav class="dash-sidebar light-sidebar {{ Utility::getsettings('transparent_layout') == 1 ? 'transprent-bg' : '' }}">
    <div class="navbar-wrapper navbar-border">
        <div class="m-header justify-content-center header-set">
            <a href="{{ route('home') }}" class="text-center b-brand header-image-set">
                <!-- ========   change your logo hear   ============ -->
                @if ($users->dark_layout == 1)
                <img src="{{ Utility::getsettings('app_logo') ? Utility::getpath('logo/app-logo.png') : asset('assets/images/app-logo.png') }}"
                    alt="footer-logo" class="footer-light-logo">
                @else
                <img src="{{ Utility::getsettings('app_dark_logo') ? Utility::getpath('logo/app-dark-logo.png') : asset('assets/images/app-dark-logo.png') }}"
                    alt="footer-logo" class="footer-dark-logo">
                @endif
            </a>
        </div>
        <div class="navbar-content flex flex-col justify-between">

            <ul class="dash-navbar">
                <li class="dash-item dash-hasmenu">
                    <a href="{{ route('home') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="dash-mtext">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if ($users->type == 'Super Admin')
                <li
                    class="dash-item dash-hasmenu {{ request()->is('users*') || request()->is('roles*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-user"></i></span>
                        <span class="dash-mtext">{{ __('User Management') }}</span>
                        <span class="dash-arrow">
                            <i data-feather="chevron-right"></i>
                        </span>
                    </a>

                    <ul class="dash-submenu">
                        @can('manage-user')
                        <li class="dash-item {{ request()->is('users*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('users.index') }}">{{ __('Golf Courses') }}</a>
                        </li>
                        @endcan
                        @can('manage-role')
                        <li class="dash-item {{ request()->is('roles*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('roles.index') }}">{{ __('Roles') }}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <li
                    class="dash-item dash-hasmenu {{ request()->is('request-domain*') || request()->is('change-domain*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-lock"></i></span><span
                            class="dash-mtext">{{ __('Domains') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('manage-domain-request')
                        <li class="dash-item {{ request()->is('request-domain*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('request.domain.index') }}">{{ __('Domain Requests') }}</a>
                        </li>
                        @endcan
                        @can('manage-domain-request')
                        <li class="dash-item {{ request()->is('change-domain*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('changedomain') }}">{{ __('Change Domain') }}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <li
                    class="dash-item dash-hasmenu {{ request()->is('coupon*') || request()->is('plans*') || request()->is('payment*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-gift"></i></span><span
                            class="dash-mtext">{{ __('Subscription') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('manage-coupon')
                        <li class="dash-item {{ request()->is('coupon*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('coupon.index') }}">{{ __('Coupons') }}</a>
                        </li>
                        @endcan
                        @can('manage-plan')
                        <li
                            class="dash-item {{ request()->is('plans*') || request()->is('payment*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('plans.index') }}">{{ __('Plans') }}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <li class="dash-item dash-hasmenu">
                    <a href="{{ route('request-demo-view') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-database"></i></span>
                        <span class="dash-mtext">{{ __('Request Demo') }}</span>
                    </a>
                </li>
                {{-- <li
                    class="dash-item dash-hasmenu {{ request()->is('Offline*') || request()->is('sales*') ? 'active dash-trigger' : 'collapsed' }}">
                <a href="#!" class="dash-link"><span class="dash-micon"><i
                            class="ti ti-clipboard-check"></i></span><span
                        class="dash-mtext">{{ __('Payment') }}</span><span class="dash-arrow"><i
                            data-feather="chevron-right"></i></span></a>
                <ul class="dash-submenu">
                    <li class="dash-item {{ request()->is('Offline*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('offline.index') }}">{{ __('Offline Payments') }}</a>
                    </li>
                    <li class="dash-item {{ request()->is('sales*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('sales.index') }}">{{ __('Transactions') }}</a>
                    </li>
                </ul>
                </li> --}}
                {{-- <li
                        class="dash-item dash-hasmenu {{ request()->is('support-ticket*') ? 'active dash-trigger' : 'collapsed' }}">
                <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-database"></i></span><span
                        class="dash-mtext">{{ __('Support') }}</span><span class="dash-arrow"><i
                            data-feather="chevron-right"></i></span></a>
                <ul class="dash-submenu">
                    <li class="dash-item {{ request()->is('support-ticket*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('support-ticket.index') }}">{{ __('Support Tickets') }}</a>
                    </li>
                </ul>
                </li> --}}
                {{-- <li
                        class="dash-item dash-hasmenu {{ request()->is('landingpage-setting*') ||
                        request()->is('faqs*') ||
                        request()->is('testimonial*') ||
                        request()->is('pagesetting*')
                            ? 'active dash-trigger'
                            : 'collapsed' }}">
                <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-table"></i></span><span
                        class="dash-mtext">{{ __('Frontend Setting') }}</span><span class="dash-arrow"><i
                            data-feather="chevron-right"></i></span></a>
                <ul class="dash-submenu">
                    @can('manage-landingpage')
                    <li class="dash-item {{ request()->is('landingpage-setting*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('landingpage.setting') }}">{{ __('Landing Page') }}</a>
                    </li>
                    @endcan
                    @can('manage-faqs')
                    <li class="dash-item {{ request()->is('faqs*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('faqs.index') }}">{{ __('Faqs') }}</a>
                    </li>
                    @endcan
                    @can('manage-testimonial')
                    <li class="dash-item {{ request()->is('testimonial*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('testimonial.index') }}">{{ __('Testimonials') }}</a>
                    </li>
                    @endcan
                    @can('manage-page-setting')
                    <li class="dash-item {{ request()->is('pagesetting*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('pagesetting.index') }}">{{ __('Page Settings') }}</a>
                    </li>
                    @endcan
                </ul>
                </li> --}}
                <li
                    class="dash-item dash-hasmenu {{ request()->is('email-template*') || request()->is('manage-language*') || request()->is('sms-template*') || request()->is('settings*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-apps"></i></span><span
                            class="dash-mtext">{{ __('Account Setting') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('manage-email-template')
                        <li class="dash-item {{ request()->is('email-template*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('email-template.index') }}">{{ __('Email Templates') }}</a>
                        </li>
                        @endcan
                        @can('manage-sms-template')
                        <li class="dash-item {{ request()->is('sms-template*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('sms-template.index') }}">{{ __('Sms Templates') }}</a>
                        </li>
                        @endcan
                        @can('manage-langauge')
                        <li class="dash-item {{ request()->is('manage-language*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('manage.language', [$currantLang]) }}">{{ __('Manage Languages') }}</a>
                        </li>
                        @endcan
                        @can('manage-setting')
                        <li class="dash-item {{ request()->is('settings*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('settings') }}">{{ __('Settings') }}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endif
                @if ($users->type != 'Super Admin')
                @canany(['manage-user', 'manage-role', 'manage-followers'])
                <li
                    class="dash-item dash-hasmenu {{ request()->is('follower*') || request()->is('users*') || request()->is('roles*') || request()->is('influencer*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-user-plus"></i></span>
                        <span class="dash-mtext">{{ __('Users') }}</span>

                        <span class="dash-arrow">
                            <i data-feather="chevron-right"></i>
                        </span>

                    </a>
                    <ul class="dash-submenu">
                        @can('manage-user')
                        <li class="dash-item {{ request()->is('users*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('users.index') }}">{{ __('Users') }}</a>
                        </li>
                        @endcan
                        @can('manage-user')
                        <li class="dash-item {{ request()->is('influencer*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('influencer.index') }}">{{ __('Influencer') }}</a>
                        </li>
                        @endcan
                        @can('manage-followers')
                        <li class="dash-item {{ request()->is('follower*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('follower.index') }}">{{ __('Follower') }}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @if (Auth::user()->type != 'Follower')
                <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                    @can('manage-lessons')
                <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                    <a href="#!" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-notebook"></i></span>
                        <span class="dash-mtext">{{ __('Lessons') }}</span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i>
                        </span>
                    </a>
                    <ul class="dash-submenu">
                        <li class="dash-item {{ request()->is('lesson') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('lesson.index') }}">{{ __('Manage Lessons') }}</a>
                        </li>
                        @if (Auth::user()->type === 'Admin')
                        <li class="dash-item {{ request()->is('lesson/manage/slot') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('slot.manage') }}">{{ __('Admin Bookings') }}</a>
                        </li>
                        @endif
                        <li class="dash-item {{ request()->is('lesson/create?type=online') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('lesson.create', ['type' => 'online']) }}">{{ __('Create Lesson') }}</a>
                        </li>
                    </ul>
                </li>
                @endcan
                @endif
                @if (Auth::user()->type == 'Follower')
                <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('lesson.available', ['type' => 'online']) }}">
                        <span class="dash-micon"><i class="ti ti-school"></i></span>
                        <span class="dash-mtext">{{ __('Start Lesson') }}</span>
                    </a>
                </li>
                @endif

                @can('manage-purchases')
                <li class="dash-item dash-hasmenu {{ request()->is('purchase*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('purchase.index') }}">
                        <span class="dash-micon"><i class="ti ti-messages"></i></span>
                        <span class="dash-mtext">{{ __('Purchased Lessons') }}</span>
                    </a>
                </li>
                @endcan
                @if (Auth::user()->type === 'Follower')
                <li class="dash-item dash-hasmenu {{ request()->is('influencer*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('influencer.profiles') }}">
                        <span class="dash-micon"><i class="ti ti-user"></i></span>
                        <span class="dash-mtext">{{ __('Influencers') }}</span>
                    </a>

                </li>
                @endif
                @if (Auth::user()->type === 'influencer')
                <li class="dash-item dash-hasmenu">
                    <a class="dash-link" rel="noopener noreferrer"
                        href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid }}" target="_blank">
                        <span class="dash-micon"><i class="ti ti-picture-in-picture"></i></span>
                        <span class="dash-mtext">{{ __('Annotation Tool') }}</span>
                    </a>
                </li>
                @endif
                @canany(['manage-blog', 'manage-category'])
                <li
                    class="dash-item dash-hasmenu {{ request()->is('blogs*') || request()->is('category*') ? 'active dash-trigger' : 'collapsed' }}">
                    <a href="#!" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-server"></i></span>
                        <span class="dash-mtext">{{ __('Post') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @if (Auth::user()->type === 'Influencer' || Auth::user()->type === 'Admin')
                        @can('create-blog')
                        <li class="dash-item {{ request()->is('blogs/create') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('blogs.create') }}">{{ __('Create New Post') }}</a>
                        </li>
                        @endcan
                        @endif
                        @can('manage-blog')
                        <li class="dash-item {{ request()->is('blogs') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('blogs.index') }}">{{ __('Feed') }}</a>
                        </li>
                        @endcan
                        @if (Auth::user()->type === 'Influencer' || Auth::user()->type === 'Admin')
                        @can('manage-blog')
                        <li class="dash-item {{ request()->is('blogs/manage/posts') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('blogs.manage') }}">{{ __('Manage Posts') }}</a>
                        </li>
                        @endcan
                        @endif
                        @if ($users->type == 'Admin')
                        @can('manage-blog')
                        <li class="dash-item {{ request()->is('blogs/manage/report') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('blogs.report') }}">{{ __('Reported Posts') }}</a>
                        </li>
                        @endcan
                        @endif
                        {{-- @can('manage-category')
                                    <li class="dash-item {{ request()->is('category*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('category.index') }}">{{ __('Categories') }}</a>
                </li>
                @endcan --}}
            </ul>
            </li>
            @endcanany
            @canany(['manage-coupon', 'manage-plan'])
            <li
                class="dash-item dash-hasmenu {{ request()->is('coupon*') || request()->is('plans*') || request()->is('myplan*') || request()->is('payment*') ? 'active dash-trigger' : 'collapsed' }}">
                <a href="#!" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-currency-dollar"></i></span>
                    <span class="dash-mtext">{{ __('Subscription') }}</span><span class="dash-arrow"><i
                            data-feather="chevron-right"></i></span></a>
                <ul class="dash-submenu">
                    @can('manage-coupon')
                    <li class="dash-item {{ request()->is('coupon*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('coupon.index') }}">{{ __('Coupons') }}</a>
                    </li>
                    @endcan
                    @can('manage-plan')
                    <li class="dash-item {{ request()->is('plans*') || request()->is('payment*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('plans.index') }}">{{ __('Your Subscription Plan') }}</a>
                    </li>
                    @endcan
                    @if ($users->type != 'Follower')
                    <li class="dash-item {{ request()->is('myplan*') ? 'active' : '' }}">
                        <a class="dash-link"
                            href="{{ route('plans.myplan') }}">{{ __('Manage Subscription Plans') }}</a>
                    </li>
                    @endif
                    @if ($users->type === 'Follower')
                    <li class="dash-item {{ request()->is('follow*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('follow.subsctiptions') }}">{{ __('My Subscriptions') }}</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endcanany
            @if ($users->type == 'Admin')
            {{-- <li
                            class="dash-item dash-hasmenu {{ request()->is('Offline*') || request()->is('sales*') ? 'active dash-trigger' : 'collapsed' }}">
            <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-clipboard-check"></i></span><span
                    class="dash-mtext">{{ __('Payment') }}</span><span class="dash-arrow"><i
                        data-feather="chevron-right"></i></span></a>
            <ul class="dash-submenu">
                <li class="dash-item {{ request()->is('sales*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('sales.index') }}">{{ __('Transactions') }}</a>
                </li>
            </ul>
            </li> --}}
            {{-- <li
                            class="dash-item dash-hasmenu {{ request()->is('chat*') || request()->is('support-ticket*') ? 'active dash-trigger' : 'collapsed' }}">
            <a href="#!" class="dash-link">
                <span class="dash-micon"><i class="ti ti-database"></i></span><span
                    class="dash-mtext">{{ __('Support') }}</span><span class="dash-arrow"><i
                        data-feather="chevron-right"></i></span></a>
            <ul class="dash-submenu">
                @if (Utility::getsettings('pusher_status') == '1')
                <li class="dash-item {{ request()->is('chat*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('chat') }}">{{ __('Chats') }}</a>
                </li>
                @endif
                <li class="dash-item {{ request()->is('support-ticket*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('support-ticket.index') }}">{{ __('Support Tickets') }}</a>
                </li>
            </ul>
            </li> --}}
            {{-- <li
                            class="dash-item dash-hasmenu {{ request()->is('landingpage-setting*') ||
                            request()->is('faqs*') ||
                            request()->is('testimonial*') ||
                            request()->is('pagesetting*')
                                ? 'active dash-trigger'
                                : 'collapsed' }}">
            <a href="#!" class="dash-link"><span class="dash-micon">
                    <i class="ti ti-table"></i></span><span class="dash-mtext">{{ __('Frontend Setting') }}</span><span
                    class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
            <ul class="dash-submenu">
                @can('manage-landingpage')
                <li class="dash-item {{ request()->is('landingpage-setting*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('landingpage.setting') }}">{{ __('Landing Page') }}</a>
                </li>
                @endcan
                @can('manage-faqs')
                <li class="dash-item {{ request()->is('faqs*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('faqs.index') }}">{{ __('Faqs') }}</a>
                </li>
                @endcan
                @can('manage-testimonial')
                <li class="dash-item {{ request()->is('testimonial*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('testimonial.index') }}">{{ __('Testimonials') }}</a>
                </li>
                @endcan
                <li class="dash-item {{ request()->is('pagesetting*') ? 'active' : '' }}">
                    <a class="dash-link" href="{{ route('pagesetting.index') }}">{{ __('Page Settings') }}</a>
                </li>
            </ul>
            </li> --}}
            @endif
            @if (Auth::user()->type == 'Admin')
            <li
                class="dash-item dash-hasmenu {{ request()->is('email-template*') || request()->is('sms-template*') || request()->is('settings*') ? 'active dash-trigger' : 'collapsed' }}">

                <a href="#!" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-settings"></i></span>
                    <span class="dash-mtext">{{ __('Account Setting') }}</span><span class="dash-arrow"><i
                            data-feather="chevron-right"></i></span>
                </a>
                <ul class="dash-submenu">
                    @can('manage-email-template')
                    <li class="dash-item {{ request()->is('email-template*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('email-template.index') }}">{{ __('Email Templates') }}</a>
                    </li>
                    @endcan
                    @can('manage-sms-template')
                    <li class="dash-item {{ request()->is('sms-template*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('sms-template.index') }}">{{ __('Sms Templates') }}</a>
                    </li>
                    @endcan
                    @can('manage-setting')
                    <li class="dash-item {{ request()->is('settings*') ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('settings') }}">{{ __('Settings') }}</a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif
            @if(Auth::user()->type == "Follower" && Auth::user()->group_id)
            <li class="dash-item dash-hasmenu {{ request()->is('chat*') ? 'active' : '' }}">
                <a class="dash-link" href="{{ route('follower.chat') }}">
                    <span class="dash-micon"><i class="ti ti-message-circle\"></i></span>
                    <span class="dash-mtext">{{ __('Chat') }}</span>
                </a>
            </li>
            @endif
            @endif
            </ul>
            <div class="flex flex-col">

                <div class="flex justify-center items-center mb-8">
                    <a href="javascript:void(0)" class="font-thin text-black"
                        onclick="document.getElementById('logout-form').submit()">
                        <i class="ti ti-power text-xl"></i>
                        <span class="text-l font-thin pl-2">{{ __('Logout') }}</span>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form"> @csrf </form>
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>