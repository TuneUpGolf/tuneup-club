@extends('layouts.main')

@section('title', __('Purchase Detail'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase') }}</li>
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
                            <img src="{{ $purchase->follower->dp }}" class="img-user wid-80 rounded-circle" alt="Avatar">
                        </div>
                        <div>
                            <h4 class="mb-1 text-white">{{ $purchase->follower->name }}</h4>
                            <p class="mb-0 text-sm text-white-50">{{ $purchase->follower->email}}</p>
                            <p class="mb-0 text-sm text-white-50">{{ $purchase->follower->name}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

{{-- Chat Section --}}
@if($isSubscribed)
    @include('admin.chat.chat')
@endif
@endsection

@push('css')
    {{-- Profile-specific styles --}}
    <link rel="stylesheet" href="{{ asset('vendor/croppie/css/croppie.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
    {{-- Purchases table styles --}}
    @include('layouts.includes.datatable_css')

    <style>
        .chat-box .rounded-circle {
            min-width: 40px;
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        .card-body{
            max-height: 400px;
            overflow-y: auto;
        }
        .upload-files {
            width:105px;
        }
    </style>
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
    <script>
        $(document).ready(function() {
            $('.dataTable-title').html(
                "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>All Purchases</span></div>"
            );
        });
    </script>
    <script>
        window.chatConfig = {
            senderId : "{{ auth()->user()->chat_user_id }}",
            senderImage : "{{ auth()->user()->avatar }}",
            groupId : "{{ $purchase->follower->group_id }}",
            recieverImage : "{{ $purchase->follower->dp }}",
            token : "{{ $token }}",
        }
        window.chatBaseUrl = "{{ config('services.chat.base_url') }}";
        window.s3BaseUrl = "{{ config('services.aws.base_url') }}";
    </script>
    <script src="{{ asset('assets/custom-js/chat.js') }}"></script>

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
@endpush