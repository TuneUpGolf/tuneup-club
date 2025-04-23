@extends('layouts.main')
@section('title', __('Start Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Start Lesson') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="tab">
            <button id='inPersonTab' class="tablinks" onclick="openCity(event, 'inPerson')">In Person</button>
            <button id='onlineTab' class="tablinks" onclick="openCity(event, 'online')">Online</button>
            </hr>
        </div>
        <div class="flex flex-col justify-center items-center w-100">
            <livewire:lessons-grid-view />
        </div>
    </div>

@endsection
@push('javascript')
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const myParam = urlParams.get('type');

        var i, tabcontent, tablinks;

        if (myParam == 'online') {
            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById('onlineTab').classList.add("active");
        }
        if (myParam == 'inPerson') {
            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById('inPersonTab').classList.add("active");
        }

        function openCity(evt, tabName) {
            // Declare all variables
            if (tabName == 'online') {
                urlParams.set('type', 'online');
            } else
                urlParams.set('type', 'inPerson');

            window.location.search = urlParams;
        }
    </script>
@endpush
