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
                    <a href="{{ route('home') }}" class="dash-link py-3 px-4">
                        <svg width="24" height="24" viewBox="0 0 18 17" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M3.15625 2.94922C4.78906 1.31641 6.73698 0.5 9 0.5C11.263 0.5 13.1966 1.31641 14.8008 2.94922C16.4336 4.55339 17.25 6.48698 17.25 8.75C17.25 11.013 16.4336 12.9609 14.8008 14.5938C13.1966 16.1979 11.263 17 9 17C6.73698 17 4.78906 16.1979 3.15625 14.5938C1.55208 12.9609 0.75 11.013 0.75 8.75C0.75 6.48698 1.55208 4.55339 3.15625 2.94922ZM13.8555 3.89453C12.5091 2.54818 10.8906 1.875 9 1.875C7.10938 1.875 5.49089 2.54818 4.14453 3.89453C2.79818 5.24089 2.125 6.85938 2.125 8.75C2.125 10.6406 2.79818 12.2591 4.14453 13.6055C5.49089 14.9518 7.10938 15.625 9 15.625C10.8906 15.625 12.5091 14.9518 13.8555 13.6055C15.2018 12.2591 15.875 10.6406 15.875 8.75C15.875 6.85938 15.2018 5.24089 13.8555 3.89453ZM8.48438 2.77734C8.6276 2.63411 8.79948 2.5625 9 2.5625C9.20052 2.5625 9.35807 2.63411 9.47266 2.77734C9.61589 2.89193 9.6875 3.04948 9.6875 3.25C9.6875 3.45052 9.61589 3.6224 9.47266 3.76562C9.35807 3.88021 9.20052 3.9375 9 3.9375C8.79948 3.9375 8.6276 3.88021 8.48438 3.76562C8.36979 3.6224 8.3125 3.45052 8.3125 3.25C8.3125 3.04948 8.36979 2.89193 8.48438 2.77734ZM4.61719 4.41016C4.76042 4.26693 4.91797 4.19531 5.08984 4.19531C5.29036 4.19531 5.46224 4.26693 5.60547 4.41016C5.7487 4.52474 5.82031 4.68229 5.82031 4.88281C5.82031 5.05469 5.7487 5.21224 5.60547 5.35547C5.46224 5.4987 5.29036 5.57031 5.08984 5.57031C4.91797 5.57031 4.76042 5.4987 4.61719 5.35547C4.5026 5.21224 4.44531 5.05469 4.44531 4.88281C4.44531 4.68229 4.5026 4.52474 4.61719 4.41016ZM12.3945 4.36719L13.3828 5.35547L10.332 8.40625C10.3607 8.52083 10.375 8.63542 10.375 8.75C10.375 9.1224 10.2318 9.45182 9.94531 9.73828C9.6875 9.99609 9.3724 10.125 9 10.125C8.6276 10.125 8.29818 9.99609 8.01172 9.73828C7.75391 9.45182 7.625 9.1224 7.625 8.75C7.625 8.3776 7.75391 8.0625 8.01172 7.80469C8.29818 7.51823 8.6276 7.375 9 7.375C9.11458 7.375 9.22917 7.38932 9.34375 7.41797L12.3945 4.36719ZM2.98438 8.27734C3.1276 8.13411 3.29948 8.0625 3.5 8.0625C3.70052 8.0625 3.85807 8.13411 3.97266 8.27734C4.11589 8.39193 4.1875 8.54948 4.1875 8.75C4.1875 8.95052 4.11589 9.1224 3.97266 9.26562C3.85807 9.38021 3.70052 9.4375 3.5 9.4375C3.29948 9.4375 3.1276 9.38021 2.98438 9.26562C2.86979 9.1224 2.8125 8.95052 2.8125 8.75C2.8125 8.54948 2.86979 8.39193 2.98438 8.27734ZM13.9844 8.27734C14.1276 8.13411 14.2995 8.0625 14.5 8.0625C14.7005 8.0625 14.8581 8.13411 14.9727 8.27734C15.1159 8.39193 15.1875 8.54948 15.1875 8.75C15.1875 8.95052 15.1159 9.1224 14.9727 9.26562C14.8581 9.38021 14.7005 9.4375 14.5 9.4375C14.2995 9.4375 14.1276 9.38021 13.9844 9.26562C13.8698 9.1224 13.8125 8.95052 13.8125 8.75C13.8125 8.54948 13.8698 8.39193 13.9844 8.27734ZM4.61719 12.1445C4.76042 12.0013 4.91797 11.9297 5.08984 11.9297C5.29036 11.9297 5.46224 12.0013 5.60547 12.1445C5.7487 12.2878 5.82031 12.4596 5.82031 12.6602C5.82031 12.832 5.7487 12.9896 5.60547 13.1328C5.46224 13.2474 5.29036 13.3047 5.08984 13.3047C4.91797 13.3047 4.76042 13.2474 4.61719 13.1328C4.5026 12.9896 4.44531 12.832 4.44531 12.6602C4.44531 12.4596 4.5026 12.2878 4.61719 12.1445ZM12.3945 12.1445C12.5378 12.0013 12.6953 11.9297 12.8672 11.9297C13.0677 11.9297 13.2253 12.0013 13.3398 12.1445C13.4831 12.2878 13.5547 12.4596 13.5547 12.6602C13.5547 12.832 13.4831 12.9896 13.3398 13.1328C13.2253 13.2474 13.0677 13.3047 12.8672 13.3047C12.6953 13.3047 12.5378 13.2474 12.3945 13.1328C12.2513 12.9896 12.1797 12.832 12.1797 12.6602C12.1797 12.4596 12.2513 12.2878 12.3945 12.1445Z"
                                fill="{{ request()->is('home*') ? 'white' : 'black' }}" />
                        </svg>
                        <span class="pl-6 text-l font-thin">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if ($users->type == 'Super Admin')
                    <li
                        class="dash-item dash-hasmenu {{ request()->is('users*') || request()->is('roles*') ? 'active dash-trigger' : 'collapsed' }}">
                        <a href="#!" class="dash-link py-3 px-4">
                            <svg width="21" height="13" viewBox="0 0 21 13" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.57812 1.76562C3.38021 0.963542 4.35417 0.5625 5.5 0.5625C6.64583 0.5625 7.61979 0.963542 8.42188 1.76562C9.22396 2.56771 9.625 3.54167 9.625 4.6875C9.625 6.09115 9.05208 7.20833 7.90625 8.03906C8.96615 8.55469 9.76823 9.29948 10.3125 10.2734C10.8568 9.29948 11.6589 8.55469 12.7188 8.03906C11.5729 7.20833 11 6.09115 11 4.6875C11 3.54167 11.401 2.56771 12.2031 1.76562C13.0052 0.963542 13.9792 0.5625 15.125 0.5625C16.2708 0.5625 17.2448 0.963542 18.0469 1.76562C18.849 2.56771 19.25 3.54167 19.25 4.6875C19.25 6.09115 18.6771 7.20833 17.5312 8.03906C18.4766 8.4974 19.2214 9.17057 19.7656 10.0586C20.3385 10.918 20.625 11.8776 20.625 12.9375H19.25C19.25 11.7917 18.849 10.8177 18.0469 10.0156C17.2448 9.21354 16.2708 8.8125 15.125 8.8125C13.9792 8.8125 13.0052 9.21354 12.2031 10.0156C11.401 10.8177 11 11.7917 11 12.9375H9.625C9.625 11.7917 9.22396 10.8177 8.42188 10.0156C7.61979 9.21354 6.64583 8.8125 5.5 8.8125C4.35417 8.8125 3.38021 9.21354 2.57812 10.0156C1.77604 10.8177 1.375 11.7917 1.375 12.9375H0C0 11.8776 0.272135 10.918 0.816406 10.0586C1.38932 9.17057 2.14844 8.4974 3.09375 8.03906C1.94792 7.20833 1.375 6.09115 1.375 4.6875C1.375 3.54167 1.77604 2.56771 2.57812 1.76562ZM7.43359 2.75391C6.91797 2.20964 6.27344 1.9375 5.5 1.9375C4.72656 1.9375 4.06771 2.20964 3.52344 2.75391C3.00781 3.26953 2.75 3.91406 2.75 4.6875C2.75 5.46094 3.00781 6.11979 3.52344 6.66406C4.06771 7.17969 4.72656 7.4375 5.5 7.4375C6.27344 7.4375 6.91797 7.17969 7.43359 6.66406C7.97786 6.11979 8.25 5.46094 8.25 4.6875C8.25 3.91406 7.97786 3.26953 7.43359 2.75391ZM17.0586 2.75391C16.543 2.20964 15.8984 1.9375 15.125 1.9375C14.3516 1.9375 13.6927 2.20964 13.1484 2.75391C12.6328 3.26953 12.375 3.91406 12.375 4.6875C12.375 5.46094 12.6328 6.11979 13.1484 6.66406C13.6927 7.17969 14.3516 7.4375 15.125 7.4375C15.8984 7.4375 16.543 7.17969 17.0586 6.66406C17.6029 6.11979 17.875 5.46094 17.875 4.6875C17.875 3.91406 17.6029 3.26953 17.0586 2.75391Z"
                                    fill= "{{ request()->is('student*') || request()->is('users*') || request()->is('roles*') || request()->is('instructor*') ? 'white' : 'black' }}" />
                            </svg>
                            <span class="pl-6 text-l font-thin">{{ __('User Management') }}</span>

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
                        <a href="#!" class="dash-link"><span class="dash-micon"><i
                                    class="ti ti-lock"></i></span><span
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
                        <a href="#!" class="dash-link"><span class="dash-micon"><i
                                    class="ti ti-gift"></i></span><span
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
                            <a class="dash-link"
                                href="{{ route('offline.index') }}">{{ __('Offline Payments') }}</a>
                        </li>
                        <li class="dash-item {{ request()->is('sales*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('sales.index') }}">{{ __('Transactions') }}</a>
                        </li>
                    </ul>
                </li> --}}
                    {{-- <li
                        class="dash-item dash-hasmenu {{ request()->is('support-ticket*') ? 'active dash-trigger' : 'collapsed' }}">
                        <a href="#!" class="dash-link"><span class="dash-micon"><i
                                    class="ti ti-database"></i></span><span
                                class="dash-mtext">{{ __('Support') }}</span><span class="dash-arrow"><i
                                    data-feather="chevron-right"></i></span></a>
                        <ul class="dash-submenu">
                            <li class="dash-item {{ request()->is('support-ticket*') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('support-ticket.index') }}">{{ __('Support Tickets') }}</a>
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
                        <a href="#!" class="dash-link"><span class="dash-micon"><i
                                    class="ti ti-table"></i></span><span
                                class="dash-mtext">{{ __('Frontend Setting') }}</span><span class="dash-arrow"><i
                                    data-feather="chevron-right"></i></span></a>
                        <ul class="dash-submenu">
                            @can('manage-landingpage')
                                <li class="dash-item {{ request()->is('landingpage-setting*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('landingpage.setting') }}">{{ __('Landing Page') }}</a>
                                </li>
                            @endcan
                            @can('manage-faqs')
                                <li class="dash-item {{ request()->is('faqs*') ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('faqs.index') }}">{{ __('Faqs') }}</a>
                                </li>
                            @endcan
                            @can('manage-testimonial')
                                <li class="dash-item {{ request()->is('testimonial*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('testimonial.index') }}">{{ __('Testimonials') }}</a>
                                </li>
                            @endcan
                            @can('manage-page-setting')
                                <li class="dash-item {{ request()->is('pagesetting*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('pagesetting.index') }}">{{ __('Page Settings') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li> --}}
                    <li
                        class="dash-item dash-hasmenu {{ request()->is('email-template*') || request()->is('manage-language*') || request()->is('sms-template*') || request()->is('settings*') ? 'active dash-trigger' : 'collapsed' }}">
                        <a href="#!" class="dash-link"><span class="dash-micon"><i
                                    class="ti ti-apps"></i></span><span
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
                                    <a class="dash-link"
                                        href="{{ route('sms-template.index') }}">{{ __('Sms Templates') }}</a>
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
                    @canany(['manage-user', 'manage-role', 'manage-students'])
                        <li
                            class="dash-item dash-hasmenu {{ request()->is('student*') || request()->is('users*') || request()->is('roles*') || request()->is('instructor*') ? 'active dash-trigger' : 'collapsed' }}">
                            <a href="#!" class="dash-link py-3 px-4">
                                <svg width="21" height="13" viewBox="0 0 21 13" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.57812 1.76562C3.38021 0.963542 4.35417 0.5625 5.5 0.5625C6.64583 0.5625 7.61979 0.963542 8.42188 1.76562C9.22396 2.56771 9.625 3.54167 9.625 4.6875C9.625 6.09115 9.05208 7.20833 7.90625 8.03906C8.96615 8.55469 9.76823 9.29948 10.3125 10.2734C10.8568 9.29948 11.6589 8.55469 12.7188 8.03906C11.5729 7.20833 11 6.09115 11 4.6875C11 3.54167 11.401 2.56771 12.2031 1.76562C13.0052 0.963542 13.9792 0.5625 15.125 0.5625C16.2708 0.5625 17.2448 0.963542 18.0469 1.76562C18.849 2.56771 19.25 3.54167 19.25 4.6875C19.25 6.09115 18.6771 7.20833 17.5312 8.03906C18.4766 8.4974 19.2214 9.17057 19.7656 10.0586C20.3385 10.918 20.625 11.8776 20.625 12.9375H19.25C19.25 11.7917 18.849 10.8177 18.0469 10.0156C17.2448 9.21354 16.2708 8.8125 15.125 8.8125C13.9792 8.8125 13.0052 9.21354 12.2031 10.0156C11.401 10.8177 11 11.7917 11 12.9375H9.625C9.625 11.7917 9.22396 10.8177 8.42188 10.0156C7.61979 9.21354 6.64583 8.8125 5.5 8.8125C4.35417 8.8125 3.38021 9.21354 2.57812 10.0156C1.77604 10.8177 1.375 11.7917 1.375 12.9375H0C0 11.8776 0.272135 10.918 0.816406 10.0586C1.38932 9.17057 2.14844 8.4974 3.09375 8.03906C1.94792 7.20833 1.375 6.09115 1.375 4.6875C1.375 3.54167 1.77604 2.56771 2.57812 1.76562ZM7.43359 2.75391C6.91797 2.20964 6.27344 1.9375 5.5 1.9375C4.72656 1.9375 4.06771 2.20964 3.52344 2.75391C3.00781 3.26953 2.75 3.91406 2.75 4.6875C2.75 5.46094 3.00781 6.11979 3.52344 6.66406C4.06771 7.17969 4.72656 7.4375 5.5 7.4375C6.27344 7.4375 6.91797 7.17969 7.43359 6.66406C7.97786 6.11979 8.25 5.46094 8.25 4.6875C8.25 3.91406 7.97786 3.26953 7.43359 2.75391ZM17.0586 2.75391C16.543 2.20964 15.8984 1.9375 15.125 1.9375C14.3516 1.9375 13.6927 2.20964 13.1484 2.75391C12.6328 3.26953 12.375 3.91406 12.375 4.6875C12.375 5.46094 12.6328 6.11979 13.1484 6.66406C13.6927 7.17969 14.3516 7.4375 15.125 7.4375C15.8984 7.4375 16.543 7.17969 17.0586 6.66406C17.6029 6.11979 17.875 5.46094 17.875 4.6875C17.875 3.91406 17.6029 3.26953 17.0586 2.75391Z"
                                        fill= "{{ request()->is('student*') || request()->is('users*') || request()->is('roles*') || request()->is('instructor*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Users') }}</span>

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
                                    <li class="dash-item {{ request()->is('instructor*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('instructor.index') }}">{{ __('Instructor') }}</a>
                                    </li>
                                @endcan
                                @can('manage-students')
                                    <li class="dash-item {{ request()->is('student*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('student.index') }}">{{ __('Student') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @if (Auth::user()->type != 'Student')
                        <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                            @can('manage-lessons')
                            <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                                <a href="#!" class="dash-link py-3 px-4">
                                    <svg width="24" height="24" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M0.4375 0.1875H1.125H14.875H15.5625V0.875V14.625V15.3125H14.875H1.125H0.4375V14.625V0.875V0.1875ZM1.8125 1.5625V7.0625H7.3125V1.5625H1.8125ZM8.6875 1.5625V7.0625H14.1875V1.5625H8.6875ZM1.8125 8.4375V13.9375H7.3125V8.4375H1.8125ZM8.6875 8.4375V13.9375H14.1875V8.4375H8.6875Z"
                                            fill="{{ request()->is('lesson*') ? 'white' : 'black' }}" />
                                    </svg>
                                    <span class="pl-6 text-l font-thin">{{ __('Lessons') }}</span>
                                    <span class="dash-arrow"><i data-feather="chevron-right"></i>
                                    </span>
                                </a>
                                <ul class="dash-submenu">
                                    <li class="dash-item {{ request()->is('lesson') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('lesson.index') }}">{{ __('Manage Lessons') }}</a>
                                    </li>
                                    @if (Auth::user()->type === 'Admin')
                                        <li class="dash-item {{ request()->is('lesson/manage/slot') ? 'active' : '' }}">
                                            <a class="dash-link"
                                                href="{{ route('slot.manage') }}">{{ __('Admin Bookings') }}</a>
                                        </li>
                                    @else
                                        <li class="dash-item {{ request()->is('lesson/manage/slot') ? 'active' : '' }}">
                                            <a class="dash-link"
                                                href="{{ route('slot.manage') }}">{{ __('All Slots') }}</a>
                                        </li>
                                    @endif
                                    <li
                                        class="dash-item {{ request()->is('lesson/create?type=online') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('lesson.create', ['type' => 'online']) }}">{{ __('Create Lesson') }}</a>
                                    </li>
                                    <li
                                        class="dash-item {{ request()->is('lesson/create?type=inPerson') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('lesson.create', ['type' => 'inPerson']) }}">{{ __('Create In-Person Lesson') }}</a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                    @endif
                    @if (Auth::user()->type == 'Student')
                        <li class="dash-item dash-hasmenu {{ request()->is('lesson*') ? 'active' : '' }}">
                            <a class="dash-link py-3 px-4"
                                href="{{ route('lesson.available', ['type' => 'inPerson']) }}">
                                <svg width="24" height="24" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0.4375 0.1875H1.125H14.875H15.5625V0.875V14.625V15.3125H14.875H1.125H0.4375V14.625V0.875V0.1875ZM1.8125 1.5625V7.0625H7.3125V1.5625H1.8125ZM8.6875 1.5625V7.0625H14.1875V1.5625H8.6875ZM1.8125 8.4375V13.9375H7.3125V8.4375H1.8125ZM8.6875 8.4375V13.9375H14.1875V8.4375H8.6875Z"
                                        fill="{{ request()->is('lesson*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Start Lesson') }}</span>
                            </a>
                        </li>
                    @endif

                    @can('manage-purchases')
                        <li class="dash-item dash-hasmenu {{ request()->is('purchase*') ? 'active' : '' }}">
                            <a class="dash-link py-3 px-4" href="{{ route('purchase.index') }}">
                                <svg width="24" height="24" viewBox="0 0 20 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0.375 0.1875H1.0625H13.4375H14.125V0.875V10.5V11.1875H13.4375H7.50781L4.24219 13.7656L3.125 14.7109V13.25V11.1875H1.0625H0.375V10.5V0.875V0.1875ZM1.75 1.5625V9.8125H3.8125H4.5V10.5V11.7891L6.82031 9.98438L6.99219 9.8125H7.25H12.75V1.5625H1.75ZM15.5 2.9375H19.625V13.9375H16.875V17.4609L12.4922 13.9375H6.13281L7.85156 12.5625H13.0078L15.5 14.5391V12.5625H18.25V4.3125H15.5V2.9375Z"
                                        fill="{{ request()->is('purchase*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Purchased Lessons') }}</span>
                            </a>
                        </li>
                    @endcan
                    @if (Auth::user()->type === 'Student')
                        <li class="dash-item dash-hasmenu {{ request()->is('instructor*') ? 'active' : '' }}">
                            <a class="dash-link py-3 px-4" href="{{ route('instructor.profiles') }}">
                                <svg width="21" height="13" viewBox="0 0 21 13" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.57812 1.76562C3.38021 0.963542 4.35417 0.5625 5.5 0.5625C6.64583 0.5625 7.61979 0.963542 8.42188 1.76562C9.22396 2.56771 9.625 3.54167 9.625 4.6875C9.625 6.09115 9.05208 7.20833 7.90625 8.03906C8.96615 8.55469 9.76823 9.29948 10.3125 10.2734C10.8568 9.29948 11.6589 8.55469 12.7188 8.03906C11.5729 7.20833 11 6.09115 11 4.6875C11 3.54167 11.401 2.56771 12.2031 1.76562C13.0052 0.963542 13.9792 0.5625 15.125 0.5625C16.2708 0.5625 17.2448 0.963542 18.0469 1.76562C18.849 2.56771 19.25 3.54167 19.25 4.6875C19.25 6.09115 18.6771 7.20833 17.5312 8.03906C18.4766 8.4974 19.2214 9.17057 19.7656 10.0586C20.3385 10.918 20.625 11.8776 20.625 12.9375H19.25C19.25 11.7917 18.849 10.8177 18.0469 10.0156C17.2448 9.21354 16.2708 8.8125 15.125 8.8125C13.9792 8.8125 13.0052 9.21354 12.2031 10.0156C11.401 10.8177 11 11.7917 11 12.9375H9.625C9.625 11.7917 9.22396 10.8177 8.42188 10.0156C7.61979 9.21354 6.64583 8.8125 5.5 8.8125C4.35417 8.8125 3.38021 9.21354 2.57812 10.0156C1.77604 10.8177 1.375 11.7917 1.375 12.9375H0C0 11.8776 0.272135 10.918 0.816406 10.0586C1.38932 9.17057 2.14844 8.4974 3.09375 8.03906C1.94792 7.20833 1.375 6.09115 1.375 4.6875C1.375 3.54167 1.77604 2.56771 2.57812 1.76562ZM7.43359 2.75391C6.91797 2.20964 6.27344 1.9375 5.5 1.9375C4.72656 1.9375 4.06771 2.20964 3.52344 2.75391C3.00781 3.26953 2.75 3.91406 2.75 4.6875C2.75 5.46094 3.00781 6.11979 3.52344 6.66406C4.06771 7.17969 4.72656 7.4375 5.5 7.4375C6.27344 7.4375 6.91797 7.17969 7.43359 6.66406C7.97786 6.11979 8.25 5.46094 8.25 4.6875C8.25 3.91406 7.97786 3.26953 7.43359 2.75391ZM17.0586 2.75391C16.543 2.20964 15.8984 1.9375 15.125 1.9375C14.3516 1.9375 13.6927 2.20964 13.1484 2.75391C12.6328 3.26953 12.375 3.91406 12.375 4.6875C12.375 5.46094 12.6328 6.11979 13.1484 6.66406C13.6927 7.17969 14.3516 7.4375 15.125 7.4375C15.8984 7.4375 16.543 7.17969 17.0586 6.66406C17.6029 6.11979 17.875 5.46094 17.875 4.6875C17.875 3.91406 17.6029 3.26953 17.0586 2.75391Z"
                                        fill= "{{ request()->is('instructor*') || request()->is('users*') || request()->is('roles*') || request()->is('instructor*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Instructors') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Auth::user()->type === 'Instructor')
                        <li class="dash-item dash-hasmenu">
                            <a class="dash-link py-3 px-4" rel="noopener noreferrer"
                                href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid }}"
                                target="_blank">
                                <svg width="24" height="24" viewBox="0 0 18 19" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.06641 0.8125H7.625H10.375H10.9336L11.0625 1.37109L11.4492 3.39062C12.0794 3.64844 12.6667 4.00651 13.2109 4.46484L15.2305 3.77734L15.7891 3.60547L16.0469 4.07812L17.4219 6.48438L17.6797 6.95703L17.293 7.34375L15.7461 8.67578C15.832 9.19141 15.875 9.54948 15.875 9.75C15.875 9.95052 15.832 10.3086 15.7461 10.8242L17.293 12.1562L17.6797 12.543L17.4219 13.0156L16.0469 15.4219L15.7891 15.8945L15.2305 15.7227L13.2109 15.0352C12.6667 15.4935 12.0794 15.8516 11.4492 16.1094L11.0625 18.1289L10.9336 18.6875H10.375H7.625H7.06641L6.9375 18.1289L6.55078 16.1094C5.92057 15.8516 5.33333 15.4935 4.78906 15.0352L2.76953 15.7227L2.21094 15.8945L1.95312 15.4219L0.578125 13.0156L0.320312 12.543L0.707031 12.1562L2.25391 10.8242C2.16797 10.3086 2.125 9.95052 2.125 9.75C2.125 9.54948 2.16797 9.19141 2.25391 8.67578L0.707031 7.34375L0.320312 6.95703L0.578125 6.48438L1.95312 4.07812L2.21094 3.60547L2.76953 3.77734L4.78906 4.46484C5.33333 4.00651 5.92057 3.64844 6.55078 3.39062L6.9375 1.37109L7.06641 0.8125ZM8.18359 2.1875L7.83984 3.99219L7.75391 4.37891L7.36719 4.50781C6.59375 4.76562 5.90625 5.16667 5.30469 5.71094L4.96094 5.96875L4.61719 5.88281L2.85547 5.28125L2.03906 6.65625L3.41406 7.85938L3.75781 8.11719L3.62891 8.54688C3.54297 8.91927 3.5 9.32031 3.5 9.75C3.5 10.1797 3.54297 10.5807 3.62891 10.9531L3.75781 11.3828L3.41406 11.6406L2.03906 12.8438L2.85547 14.2188L4.61719 13.6172L4.96094 13.5312L5.30469 13.7891C5.90625 14.3333 6.59375 14.7344 7.36719 14.9922L7.75391 15.1211L7.83984 15.5078L8.18359 17.3125H9.81641L10.1602 15.5078L10.2461 15.1211L10.6328 14.9922C11.4062 14.7344 12.0938 14.3333 12.6953 13.7891L13.0391 13.5312L13.3828 13.6172L15.1445 14.2188L15.9609 12.8438L14.5859 11.6406L14.2852 11.3828L14.3711 10.9531C14.457 10.5807 14.5 10.1797 14.5 9.75C14.5 9.32031 14.457 8.91927 14.3711 8.54688L14.2422 8.11719L14.5859 7.85938L15.9609 6.65625L15.1445 5.28125L13.3828 5.88281L13.0391 5.96875L12.6953 5.71094C12.0938 5.16667 11.4062 4.76562 10.6328 4.50781L10.2461 4.37891L10.1602 3.99219L9.81641 2.1875H8.18359ZM6.55078 7.34375C7.23828 6.65625 8.05469 6.3125 9 6.3125C9.94531 6.3125 10.7474 6.65625 11.4062 7.34375C12.0938 8.0026 12.4375 8.80469 12.4375 9.75C12.4375 10.6953 12.0938 11.5117 11.4062 12.1992C10.7474 12.8581 9.94531 13.1875 9 13.1875C8.05469 13.1875 7.23828 12.8581 6.55078 12.1992C5.89193 11.5117 5.5625 10.6953 5.5625 9.75C5.5625 8.80469 5.89193 8.0026 6.55078 7.34375ZM10.4609 8.28906C10.0599 7.88802 9.57292 7.6875 9 7.6875C8.42708 7.6875 7.9401 7.88802 7.53906 8.28906C7.13802 8.6901 6.9375 9.17708 6.9375 9.75C6.9375 10.3229 7.13802 10.8099 7.53906 11.2109C7.9401 11.612 8.42708 11.8125 9 11.8125C9.57292 11.8125 10.0599 11.612 10.4609 11.2109C10.862 10.8099 11.0625 10.3229 11.0625 9.75C11.0625 9.17708 10.862 8.6901 10.4609 8.28906Z"
                                        fill="black" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Annotation Tool') }}</span>
                            </a>
                        </li>
                    @endif
                    @canany(['manage-blog', 'manage-category'])
                        <li
                            class="dash-item dash-hasmenu {{ request()->is('blogs*') || request()->is('category*') ? 'active dash-trigger' : 'collapsed' }}">
                            <a href="#!" class="dash-link py-3 px-4">
                                <svg width="24" height="24" viewBox="0 0 15 17" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0.4375 0.5H1.125H13.5H14.1875V1.1875V16.3125V17H13.5H1.125H0.4375V16.3125V1.1875V0.5ZM1.8125 1.875V5.3125H12.8125V1.875H1.8125ZM1.8125 6.6875V10.8125H12.8125V6.6875H1.8125ZM1.8125 12.1875V15.625H12.8125V12.1875H1.8125Z"
                                        fill="{{ request()->is('blogs*') || request()->is('category*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Post') }}</span><span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @can('create-blog')
                                    <li class="dash-item {{ request()->is('blogs/create') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('blogs.create') }}">{{ __('Create New Post') }}</a>
                                    </li>
                                @endcan
                                @can('manage-blog')
                                    <li class="dash-item {{ request()->is('blogs') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('blogs.index') }}">{{ __('Feed') }}</a>
                                    </li>
                                @endcan
                                @can('manage-blog')
                                    <li class="dash-item {{ request()->is('blogs/manage/posts') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('blogs.manage') }}">{{ __('Manage Posts') }}</a>
                                    </li>
                                @endcan
                                @if ($users->type == 'Admin')
                                    @can('manage-blog')
                                        <li class="dash-item {{ request()->is('blogs/manage/report') ? 'active' : '' }}">
                                            <a class="dash-link"
                                                href="{{ route('blogs.report') }}">{{ __('Reported Posts') }}</a>
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
                            <a href="#!" class="dash-link py-3 px-4">
                                <svg width="24" height="24" viewBox="0 0 18 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.25 0.1875C7.16667 0.1875 8.02604 0.746094 8.82812 1.86328C8.82812 1.89193 8.88542 1.99219 9 2.16406C9.11458 1.99219 9.17188 1.89193 9.17188 1.86328C9.97396 0.746094 10.8333 0.1875 11.75 0.1875C12.3229 0.1875 12.8099 0.388021 13.2109 0.789062C13.612 1.1901 13.8125 1.67708 13.8125 2.25C13.8125 2.47917 13.7695 2.70833 13.6836 2.9375H16.5625H17.25V3.625V6.375V7.0625H16.5625V15.3125V16H15.875H2.125H1.4375V15.3125V7.0625H0.75V6.375V3.625V2.9375H1.4375H4.31641C4.23047 2.70833 4.1875 2.47917 4.1875 2.25C4.1875 1.67708 4.38802 1.1901 4.78906 0.789062C5.1901 0.388021 5.67708 0.1875 6.25 0.1875ZM6.25 1.5625C5.79167 1.5625 5.5625 1.79167 5.5625 2.25C5.5625 2.70833 5.79167 2.9375 6.25 2.9375H7.83984C7.8112 2.90885 7.78255 2.86589 7.75391 2.80859C7.72526 2.7513 7.69661 2.69401 7.66797 2.63672C7.15234 1.92057 6.67969 1.5625 6.25 1.5625ZM11.75 1.5625C11.3203 1.5625 10.8477 1.92057 10.332 2.63672C10.3034 2.69401 10.2747 2.73698 10.2461 2.76562C10.2461 2.76562 10.2318 2.79427 10.2031 2.85156C10.2031 2.88021 10.1888 2.90885 10.1602 2.9375H11.75C12.2083 2.9375 12.4375 2.70833 12.4375 2.25C12.4375 1.79167 12.2083 1.5625 11.75 1.5625ZM2.125 4.3125V5.6875H8.3125V5H9.6875V5.6875H15.875V4.3125H9H7.96875H2.125ZM2.8125 7.0625V14.625H8.3125V7.75H9.6875V14.625H15.1875V7.0625H2.8125Z"
                                        fill="{{ request()->is('coupon*') || request()->is('plans*') || request()->is('myplan*') || request()->is('payment*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Subscription') }}</span><span
                                    class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
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
                                @if ($users->type == 'Admin')
                                    <li class="dash-item {{ request()->is('myplan*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('plans.myplan') }}">{{ __('My Plans') }}</a>
                                    </li>
                                @endif
                                @if ($users->type === 'Student')
                                    <li class="dash-item {{ request()->is('follow*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('follow.subsctiptions') }}">{{ __('My Subscriptions') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endcanany
                    @if ($users->type == 'Admin')
                        {{-- <li
                            class="dash-item dash-hasmenu {{ request()->is('Offline*') || request()->is('sales*') ? 'active dash-trigger' : 'collapsed' }}">
                            <a href="#!" class="dash-link"><span class="dash-micon"><i
                                        class="ti ti-clipboard-check"></i></span><span
                                    class="dash-mtext">{{ __('Payment') }}</span><span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li class="dash-item {{ request()->is('sales*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('sales.index') }}">{{ __('Transactions') }}</a>
                                </li>
                            </ul>
                        </li> --}}
                        {{-- <li
                            class="dash-item dash-hasmenu {{ request()->is('chat*') || request()->is('support-ticket*') ? 'active dash-trigger' : 'collapsed' }}">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon"><i
                                        class="ti ti-database"></i></span><span
                                    class="dash-mtext">{{ __('Support') }}</span><span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @if (Utility::getsettings('pusher_status') == '1')
                                    <li class="dash-item {{ request()->is('chat*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('chat') }}">{{ __('Chats') }}</a>
                                    </li>
                                @endif
                                <li class="dash-item {{ request()->is('support-ticket*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('support-ticket.index') }}">{{ __('Support Tickets') }}</a>
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
                                <i
                                        class="ti ti-table"></i></span><span
                                    class="dash-mtext">{{ __('Frontend Setting') }}</span><span
                                    class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @can('manage-landingpage')
                                    <li class="dash-item {{ request()->is('landingpage-setting*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('landingpage.setting') }}">{{ __('Landing Page') }}</a>
                                    </li>
                                @endcan
                                @can('manage-faqs')
                                    <li class="dash-item {{ request()->is('faqs*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('faqs.index') }}">{{ __('Faqs') }}</a>
                                    </li>
                                @endcan
                                @can('manage-testimonial')
                                    <li class="dash-item {{ request()->is('testimonial*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('testimonial.index') }}">{{ __('Testimonials') }}</a>
                                    </li>
                                @endcan
                                <li class="dash-item {{ request()->is('pagesetting*') ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('pagesetting.index') }}">{{ __('Page Settings') }}</a>
                                </li>
                            </ul>
                        </li> --}}
                    @endif
                    @if (Auth::user()->type == 'Admin')
                        <li
                            class="dash-item dash-hasmenu {{ request()->is('email-template*') || request()->is('sms-template*') || request()->is('settings*') ? 'active dash-trigger' : 'collapsed' }}">

                            <a href="#!" class="dash-link py-3 px-4">
                                <svg width="24" height="24" viewBox="0 0 18 19" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.06641 0.8125H7.625H10.375H10.9336L11.0625 1.37109L11.4492 3.39062C12.0794 3.64844 12.6667 4.00651 13.2109 4.46484L15.2305 3.77734L15.7891 3.60547L16.0469 4.07812L17.4219 6.48438L17.6797 6.95703L17.293 7.34375L15.7461 8.67578C15.832 9.19141 15.875 9.54948 15.875 9.75C15.875 9.95052 15.832 10.3086 15.7461 10.8242L17.293 12.1562L17.6797 12.543L17.4219 13.0156L16.0469 15.4219L15.7891 15.8945L15.2305 15.7227L13.2109 15.0352C12.6667 15.4935 12.0794 15.8516 11.4492 16.1094L11.0625 18.1289L10.9336 18.6875H10.375H7.625H7.06641L6.9375 18.1289L6.55078 16.1094C5.92057 15.8516 5.33333 15.4935 4.78906 15.0352L2.76953 15.7227L2.21094 15.8945L1.95312 15.4219L0.578125 13.0156L0.320312 12.543L0.707031 12.1562L2.25391 10.8242C2.16797 10.3086 2.125 9.95052 2.125 9.75C2.125 9.54948 2.16797 9.19141 2.25391 8.67578L0.707031 7.34375L0.320312 6.95703L0.578125 6.48438L1.95312 4.07812L2.21094 3.60547L2.76953 3.77734L4.78906 4.46484C5.33333 4.00651 5.92057 3.64844 6.55078 3.39062L6.9375 1.37109L7.06641 0.8125ZM8.18359 2.1875L7.83984 3.99219L7.75391 4.37891L7.36719 4.50781C6.59375 4.76562 5.90625 5.16667 5.30469 5.71094L4.96094 5.96875L4.61719 5.88281L2.85547 5.28125L2.03906 6.65625L3.41406 7.85938L3.75781 8.11719L3.62891 8.54688C3.54297 8.91927 3.5 9.32031 3.5 9.75C3.5 10.1797 3.54297 10.5807 3.62891 10.9531L3.75781 11.3828L3.41406 11.6406L2.03906 12.8438L2.85547 14.2188L4.61719 13.6172L4.96094 13.5312L5.30469 13.7891C5.90625 14.3333 6.59375 14.7344 7.36719 14.9922L7.75391 15.1211L7.83984 15.5078L8.18359 17.3125H9.81641L10.1602 15.5078L10.2461 15.1211L10.6328 14.9922C11.4062 14.7344 12.0938 14.3333 12.6953 13.7891L13.0391 13.5312L13.3828 13.6172L15.1445 14.2188L15.9609 12.8438L14.5859 11.6406L14.2852 11.3828L14.3711 10.9531C14.457 10.5807 14.5 10.1797 14.5 9.75C14.5 9.32031 14.457 8.91927 14.3711 8.54688L14.2422 8.11719L14.5859 7.85938L15.9609 6.65625L15.1445 5.28125L13.3828 5.88281L13.0391 5.96875L12.6953 5.71094C12.0938 5.16667 11.4062 4.76562 10.6328 4.50781L10.2461 4.37891L10.1602 3.99219L9.81641 2.1875H8.18359ZM6.55078 7.34375C7.23828 6.65625 8.05469 6.3125 9 6.3125C9.94531 6.3125 10.7474 6.65625 11.4062 7.34375C12.0938 8.0026 12.4375 8.80469 12.4375 9.75C12.4375 10.6953 12.0938 11.5117 11.4062 12.1992C10.7474 12.8581 9.94531 13.1875 9 13.1875C8.05469 13.1875 7.23828 12.8581 6.55078 12.1992C5.89193 11.5117 5.5625 10.6953 5.5625 9.75C5.5625 8.80469 5.89193 8.0026 6.55078 7.34375ZM10.4609 8.28906C10.0599 7.88802 9.57292 7.6875 9 7.6875C8.42708 7.6875 7.9401 7.88802 7.53906 8.28906C7.13802 8.6901 6.9375 9.17708 6.9375 9.75C6.9375 10.3229 7.13802 10.8099 7.53906 11.2109C7.9401 11.612 8.42708 11.8125 9 11.8125C9.57292 11.8125 10.0599 11.612 10.4609 11.2109C10.862 10.8099 11.0625 10.3229 11.0625 9.75C11.0625 9.17708 10.862 8.6901 10.4609 8.28906Z"
                                        fill=" {{ request()->is('email-template*') || request()->is('sms-template*') || request()->is('settings*') ? 'white' : 'black' }}" />
                                </svg>
                                <span class="pl-6 text-l font-thin">{{ __('Account Setting') }}</span><span
                                    class="dash-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="dash-submenu">
                                @can('manage-email-template')
                                    <li class="dash-item {{ request()->is('email-template*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('email-template.index') }}">{{ __('Email Templates') }}</a>
                                    </li>
                                @endcan
                                @can('manage-sms-template')
                                    <li class="dash-item {{ request()->is('sms-template*') ? 'active' : '' }}">
                                        <a class="dash-link"
                                            href="{{ route('sms-template.index') }}">{{ __('Sms Templates') }}</a>
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
