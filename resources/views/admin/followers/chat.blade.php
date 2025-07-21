@push('css')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
    <style>
        .card-body{
            min-height: calc(100vh - 360px)
           }
    </style>
    <style>
        .chat-box .rounded-circle {
            min-width: 40px;
            width: 40px;
            height: 40px;
            object-fit: cover;

        }

        .card-body {
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
        }

        .upload-files {
            min-width: 105px;
            max-width: 105px;
        }
        .chat-msg-wrap {
            word-break: break-all;
        }

        @media (max-width: 768px) {
            .chat-box .rounded-circle {
                min-width: 35px;
                width: 35px;
                height: 35px;
            }

            .card-body {
                max-height: 300px;
            }

            .chat-module-wrapper .card-footer .btn {
                padding: 5px 7px;
                height: 41px;
            }

            .upload-files {
                min-width: 34px;
                max-width: 34px;
                font-size: 0;
                height: 41px;
                background-image: url('{{ asset('assets/images/upload.svg') }}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 20px;
                padding: 0;
                filter: opacity(0.5);
            }

            .chat-box .form-control {
                padding: 0.5rem 0.75rem;

            }
            .chat-box audio {
                max-width: 260px;
            }
        }
    </style>
@endpush
@include('admin.chat.chat', ['user' => $influencer->name])
@push('javascript')
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
        window.chatConfig = {
            senderId : "{{ auth()->user()->chat_user_id }}",
            senderImage : "{{ auth()->user()->dp }}",
            groupId : "{{ auth()->user()->group_id }}",
            recieverImage : "{{ $influencer->avatar }}",
            token : "{{ $token }}",
        }
        window.chatBaseUrl = "{{ config('services.chat.base_url') }}";
        window.s3BaseUrl = "{{ config('services.aws.base_url') }}";
    </script>
    <script src="{{ asset('assets/custom-js/chat.js') }}"></script>
@endpush
