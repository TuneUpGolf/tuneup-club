@extends('layouts.main')

@section('title', __('User Detail'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Follower') }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Profile Card --}}

    <div class="col-xl-12">
        <div id="useradd-1" class="text-white card bg-primary mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png" class="img-user wid-80 rounded-circle" alt="Avatar">
                        </div>
                        <div>
                            <h4 class="mb-1 text-white">{{ $follower->name }}</h4>
                            <p class="mb-0 text-sm text-white-50">{{ $follower->email }}</p>
                            <p class="mb-0 text-sm text-white-50">{{ $follower->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchased plan section --}}
    @if (!empty($follower->plan_expired_date))
    <div class="row">
        <div class="col-xl-3 col-md-6 py-4">
            <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"  data-wow-delay="0.2s">
                <div class="rounded-lg shadow popular-wrap h-100">
                    <div class="px-3 pt-4 ">
                        <p class="text-2xl font-bold mb-1">{{ $follower->plan->name }}</p>
                        <div class="flex gap-2 items-center mt-2 ">
                            <p class=" text-6xl font-bold">{{ $currencySymbol . ' ' . $follower->plan->price }} /</p>
                            <p class="text-2xl text-gray-600">{{ $follower->plan->duration . ' ' . $follower->plan->durationtype }}</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="px-3 py-4">
                        <span
                            class="lesson-btn text-center font-bold text-lg mt-auto"
                            data-amount="{{ $follower->plan->price }}">{{ __('Expire at') }}
                            {{ \Carbon\Carbon::parse($follower->plan_expired_date)->format('d/m/Y') }}
                        </span>
                        <p class="font-semibold text-xl mb-2 mt-2">Features</p>
                        <p class="text-gray-600">
                            {!! $follower->plan->description !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Purchases Table --}}
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

{{-- Purchased plan section --}}
{{-- @if (!empty($follower->plan_expired_date))
<div class="row">
    <div class="col-xl-3 col-md-6 py-4">
        <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"  data-wow-delay="0.2s">
            <div class="rounded-lg shadow popular-wrap h-100">
                <div class="px-3 pt-4 ">
                    <p class="text-2xl font-bold mb-1">{{ $follower->plan->name }}</p>
                    <div class="flex gap-2 items-center mt-2 ">
                        <p class=" text-6xl font-bold">{{ $currencySymbol . ' ' . $follower->plan->price }} /</p>
                        <p class="text-2xl text-gray-600">{{ $follower->plan->duration . ' ' . $follower->plan->durationtype }}</p>
                    </div>
                </div>
                <div class="border-t border-gray-300"></div>
                <div class="px-3 py-4">
                    <span
                        class="lesson-btn text-center font-bold text-lg mt-auto"
                        data-amount="{{ $follower->plan->price }}">{{ __('Expire at') }}
                        {{ \Carbon\Carbon::parse($follower->plan_expired_date)->format('d/m/Y') }}
                    </span>
                    <p class="font-semibold text-xl mb-2 mt-2">Features</p>
                    <p class="text-gray-600">
                        {!! $follower->plan->description !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif --}}

{{-- Chat Section --}}
@if($follower->chat_status == 1)
    @include('admin.chat.chat', ['user' => $follower->name])
@endif
@endsection

@push('css')
    {{-- Profile-specific styles --}}
    <link rel="stylesheet" href="{{ asset('vendor/croppie/css/croppie.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
    {{-- Purchases table styles --}}
    @include('layouts.includes.datatable_css')

@endpush

@push('javascript')
    {{-- Profile JS --}}
    <script src="{{ asset('vendor/croppie/js/croppie.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

    {{-- Purchases table JS --}}
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}

    {{-- Profile cropper and phone logic --}}
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 200
        });

        $(document).ready(function() {
            $image_crop = $(".image-preview").croppie({
                enableExif: !0,
                enforceBoundary: !1,
                enableOrientation: !0,
                viewport: {
                    width: 200,
                    height: 200,
                    type: "square"
                },
                boundary: {
                    width: 300,
                    height: 300
                }
            });

            $("#avatarCrop").change(function() {
                $("#avatar-holder").addClass("d-none");
                $("#avatar-updater").removeClass("d-none");
                var reader = new FileReader();
                reader.onload = function(e) {
                    $image_crop.croppie("bind", { url: e.target.result });
                };
                reader.readAsDataURL(this.files[0]);
            });

            $("#rotate-image").click(function() {
                $image_crop.croppie("rotate", 90);
            });

            $("#crop_image").click(function() {
                $image_crop.croppie("result", {
                    type: "canvas",
                    size: "viewport"
                }).then(function(result) {
                    var postUrl = $("input[name=avatar-url]").val();
                    var token = $('meta[name="csrf-token"]').attr("content");

                    $("#crop_image").html("Saving Avatar...").attr("disabled", true);

                    $.ajaxSetup({ headers: { "X-CSRF-TOKEN": token } });

                    $.post(postUrl, { avatar: result, _token: token }, function(response) {
                        new swal({ text: response, icon: "success" }).then(() => {
                            location.reload();
                        });
                    });
                });
            });

            $("#avatar-cancel-btn").click(function() {
                $("#avatar-holder").removeClass("d-none");
                $("#avatar-updater").addClass("d-none");
            });

            $("#phone").intlTelInput({
                geoIpLookup: function(callback) {
                    $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                        callback('{{ $follower->country_code }}');
                    });
                },
                initialCountry: "auto",
                separateDialCode: true,
            });

            $('#phone').on('countrychange', function() {
                $(this).val('');
                var selectedCountry = $(this).intlTelInput('getSelectedCountryData');
                var dialCode = selectedCountry.dialCode;
                var maskNumber = intlTelInputUtils.getExampleNumber(selectedCountry.iso2, 0, 0);
                maskNumber = intlTelInputUtils.formatNumber(maskNumber, selectedCountry.iso2, 2);
                maskNumber = maskNumber.replace('+' + dialCode + ' ', '');
                var mask = maskNumber.replace(/[0-9+]/ig, '0');
                $('input[name="country_code"]').val(selectedCountry.iso2);
                $('input[name="dial_code"]').val(dialCode);
                $('#phone').mask(mask, { placeholder: maskNumber });
            });
        });
    </script>
    <script>
        window.chatConfig = {
            senderId : "{{ auth()->user()->chat_user_id }}",
            senderImage : "{{ auth()->user()->avatar }}",
            groupId : "{{ $follower->group_id }}",
            recieverImage : "{{ $follower->dp }}",
            token : "{{ $token }}",
        }
        window.chatBaseUrl = "{{ config('services.chat.base_url') }}";
        window.s3BaseUrl = "{{ config('services.aws.base_url') }}";
    </script>
    <script src="{{ asset('assets/custom-js/chat.js') }}"></script>
@endpush