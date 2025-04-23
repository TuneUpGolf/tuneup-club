@php
    $users = \Auth::user();
    $currantLang = $users->currentLanguage();
    $languages = Utility::languages();
@endphp
<header class="dash-header border-search {{ Utility::getsettings('transparent_layout') == 1 ? 'transprent-bg' : '' }}">
    <div class="header-wrapper">
        <div class="flex justify-between items-center">
            <div
                class="flex pl-3 py-2 pr-24 w-100 rounded-3xl border-search overflow-hidden max-w-md mx-auto font-sans search-bg ">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192.904 192.904" width="16px"
                    class="fill-gray-600 mr-3 rotate-90">
                    <path
                        d="m190.707 180.101-47.078-47.077c11.702-14.072 18.752-32.142 18.752-51.831C162.381 36.423 125.959 0 81.191 0 36.422 0 0 36.423 0 81.193c0 44.767 36.422 81.187 81.191 81.187 19.688 0 37.759-7.049 51.831-18.751l47.079 47.078a7.474 7.474 0 0 0 5.303 2.197 7.498 7.498 0 0 0 5.303-12.803zM15 81.193C15 44.694 44.693 15 81.191 15c36.497 0 66.189 29.694 66.189 66.193 0 36.496-29.692 66.187-66.189 66.187C44.693 147.38 15 117.689 15 81.193z">
                    </path>
                </svg>
                <input type="text" placeholder="Search"
                    class="w-full outline-none bg-transparent border-0 text-gray-600 text-sm" />
            </div>
            <div class="flex justify-center items-center">
                <div class="me-auto dash-mob-drp">
                    <ul class="list-unstyled">
                        <li class="dash-h-item mob-hamburger">
                            <a href="#!" class="dash-head-link" id="mobile-collapse">
                                <div class="hamburger hamburger--arrowturn">
                                    <div class="hamburger-box">
                                        <div class="hamburger-inner"></div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="dropdown dash-h-item drp-company">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                <span>
                                    <img alt="image" src="{{ Auth::user()->avatar_image }}"
                                        class="rounded-circle mr-1">
                                </span>
                                <span class="hide-mob ms-2">{{ __('Hi,') }} {{ Auth::user()->name }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown">
                                <a href="{{ route('profile.view') }}" class="dropdown-item">
                                    <i class="ti ti-user"></i>
                                    <span>{{ __('Profile') }}</span>
                                </a>
                                <a href="javascript:void(0)" class="dropdown-item"
                                    onclick="document.getElementById('logout-form').submit()">
                                    <i class="ti ti-power"></i>
                                    <span>{{ __('Logout') }}</span>
                                    <form action="{{ route('logout') }}" method="POST" id="logout-form"> @csrf </form>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="ms-auto">
                    <ul class="list-unstyled">
                        <li class="dropdown dash-h-item drp-language">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="ti ti-world nocolor"></i>
                                <span class="drp-text hide-mob">{{ Str::upper($currantLang) }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                @foreach ($languages as $language)
                                    <a class="dropdown-item @if ($language == $currantLang) text-danger @endif"
                                        href="{{ route('change.language', $language) }}">{{ Str::upper($language) }}</a>
                                @endforeach
                            </div>
                        </li>
                        <li class="dropdown dash-h-item drp-notification">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" id="kt_activities_toggle"
                                data-bs-toggle="dropdown" href="javascript:void(0);" role="button"
                                aria-haspopup="false" aria-expanded="false">
                                <i class="ti ti-bell"></i>
                                <span
                                    class="bg-danger dash-h-badge
                                    @if (auth()->user()->unreadnotifications->count()) dots @endif"><span
                                        class="sr-only"></span></span>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                <div class="noti-header">
                                    <h5 class="m-0">{{ __('Notification') }}</h5>
                                </div>
                                <div class="noti-body ps">
                                    @foreach (auth()->user()->notifications->where('read_at', '=', '') as $notification)
                                        <div class="d-flex align-items-start my-4">
                                            @if ($notification->type == 'App\Notifications\Superadmin\RegisterNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('New Domain Request') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('New') }}
                                                            {{ isset($notification->data['data']['domain']['email']) ? $notification->data['data']['domain']['email'] : '' }}{{ __(' User Create and') }}
                                                            {{ __('User Domain Name:') }}
                                                            {{ isset($notification->data['data']['domain']['domain_name']) ? $notification->data['data']['domain']['domain_name'] : '' }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (
                                                $notification->type == 'App\Notifications\Superadmin\ConatctNotification' ||
                                                    $notification->type == 'App\Notifications\Admin\ConatctNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('New Enquiry') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('New') }}
                                                            {{ isset($notification->data['data']['email']) ? $notification->data['data']['email'] : '' }}{{ __(' Enquiry Details') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($notification->type == 'App\Notifications\Superadmin\ApproveNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Domain Verified') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Domain') }}
                                                            {{ isset($notification->data['data']['alldata']['domain_name']) ? $notification->data['data']['alldata']['domain_name'] : '' }}{{ __(' is Verified By SuperAdmin') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($notification->type == 'App\Notifications\Superadmin\DisapprovedNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Domain Unverified') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Domain') }}
                                                            {{ isset($notification->data['data']['alldata']['domain_name']) ? $notification->data['data']['alldata']['domain_name'] : '' }}{{ __(' is not Verified By SuperAdmin') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (
                                                $notification->type == 'App\Notifications\Superadmin\ApproveOfflineNotification' ||
                                                    $notification->type == 'App\Notifications\Admin\ApproveOfflineNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Offline Payment Request Verified') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Plan Update Request') }}
                                                            {{ isset($notification->data['data']['alldata']['email']) ? $notification->data['data']['alldata']['email'] : '' }}{{ __(' is Verified By Super Admin') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (
                                                $notification->type == 'App\Notifications\Superadmin\DisapprovedOfflineNotification' ||
                                                    $notification->type == 'App\Notifications\Admin\DisapprovedOfflineNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Offline Payment Request Unverified') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Request Payment') }}
                                                            {{ isset($notification->data['data']['alldata']['email']) ? $notification->data['data']['alldata']['email'] : '' }}{{ __(' is Disapprove By Super Admin') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($notification->type == 'App\Notifications\Superadmin\SupportTicketNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('New Ticket Opened') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('New') }}
                                                            {{ isset($notification->data['data']['alldata']['ticket_id']) ? $notification->data['data']['alldata']['ticket_id'] : '' }}{{ __(' Ticket Opened') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($notification->type == 'App\Notifications\Superadmin\ReceiveTicketReplyNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Received Ticket Reply') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Ticket id') }}
                                                            {{ isset($notification->data['data']['alldata']['ticket_id']) ? $notification->data['data']['alldata']['ticket_id'] : '' }}{{ __(' New Reply') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($notification->type == 'App\Notifications\Superadmin\SupportTicketReplyNotification')
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-device-desktop"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <a href="javascript:void(0);">
                                                            <h6>{{ __('Send Ticket Reply') }}</h6>
                                                        </a>
                                                        <a href="javascript:void(0);" class="text-hover-danger"><i
                                                                class="ti ti-x"></i></a>
                                                    </div>
                                                    <div class="d-flex align-items-end justify-content-between">
                                                        <p class="mb-0 text-muted">
                                                            {{ __('Your Ticket id') }}
                                                            {{ isset($notification->data['data']['alldata']['ticket_id']) ? $notification->data['data']['alldata']['ticket_id'] : '' }}{{ __(' New Reply') }}
                                                        </p>
                                                        <span
                                                            class="text-sm ms-2 text-nowrap">{{ Utility::date_time_format($notification->created_at) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        @impersonating($guard = null)
                            <li class="dropdown dash-h-item drp-company">
                                <a class="btn btn-primary btn-active-color-primary btn-outline-secondary me-3"
                                    href="{{ route('impersonate.leave') }}"><i class="ti ti-ban"></i>
                                    {{ __('Exit Impersonation') }}
                                </a>
                            </li>
                        @endImpersonating
                    </ul>
                </div>
            </div>
        </div>

    </div>
</header>
@push('javascript')
    <script>
        $(document).on("click", "#kt_activities_toggle", function() {
            $.ajax({
                url: '{{ route('read.notification') }}',
                data: {
                    _token: $("meta[name='csrf-token']").attr('content')
                },
                method: 'post',
            }).done(function(data) {
                if (data.is_success) {
                    $("#kt_activities_toggle").find(".animation-blink").remove();
                }
            });
        });
    </script>
@endpush
