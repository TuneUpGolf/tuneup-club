@extends('layouts.main')
@section('title', __('Start Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Start Lesson') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="tab">
            <button id='onlineTab' class="tablinks active">Online</button>
            </hr>
        </div>
        <div class="flex flex-col justify-center items-center w-100">
            <livewire:lessons-grid-view />
        </div>
    </div>

@endsection
