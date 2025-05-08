@extends('layouts.main')
@section('title', __('Users'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Users') }}</li>
@endsection
@section('content')
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
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
@endpush
@push('javascript')
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}
    <script type="text/javascript">
        $(document).ready(function() {
            var html =
                $('.dataTable-title').html(
                    "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>All Users</span></div>"
                );
        });
    </script>
@endpush
