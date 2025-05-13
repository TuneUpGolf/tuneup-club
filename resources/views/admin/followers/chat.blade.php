@extends('layouts.main')
@section('title', __('Follower Chat'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Chat') }}</li>
@endsection
@section('content')
    @if($isSubscribed)
        @include('admin.chat.chat')
    @else
    <div class="flex flex-col justify-center items-center no-data gap-2 text-center vh-100">
        <i class="fa fa-lock text-xl mb-2" aria-hidden="true"></i>
        <span class="text-lg font-semibold">Purchase Subscription with chat enabled to access this section</span>
    </div>
    @endif
@endsection
@push('css')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
    <style>
        .card-body{
            min-height: calc(100vh - 360px)
           }
    </style>
@endpush
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
    </script>
    <script src="{{ asset('assets/js/chat.js') }}"></script>
@endpush
