@extends('layouts.main')
@section('title', __('Influencers'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Influencers') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card px-4">
                <livewire:instructor-grid-view />
            </div>
        </div>
    @endsection
    @push('css')
        @include('layouts.includes.datatable_css')
    @endpush
    @push('javascript')
        @include('layouts.includes.datatable_js')
    @endpush
