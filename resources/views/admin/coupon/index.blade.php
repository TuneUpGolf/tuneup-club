@extends('layouts.main')
@section('title', __('Coupons'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Coupons') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <div class="d-flex align-items-center">
            @if (\Auth::user()->can('mass-create-coupon'))
                <a href="{{ route('coupon.mass.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                    data-bs-placement="bottom" class="btn btn-sm btn-primary"
                    data-bs-original-title="{{ __('Mass Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endif
            @if (\Auth::user()->can('upload-coupon'))
                <a href="javascript:void(0);" data-url="{{ route('coupon.upload') }}" data-ajax-popup="true"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" class="mx-1 btn btn-sm btn-primary upload_csv"
                    data-bs-original-title="{{ __('Upload') }}">
                    <i class="ti ti-upload"></i>
                </a>
            @endif
        </div>
    </div>
@endsection
@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card comp-card h-100">
                <div class="card-body d-flex flex-column h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="me-2">
                            <h6 class="mb-0 text-muted fw-normal">{{ __('Total Coupon') }}</h6>
                        </div>
                        <div class="icon-wrapper rounded-circle bg-primary p-1">
                            <i class="ti ti-discount text-white fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <h3 class="mb-0 text-primary">{{ $totalCoupon }}</h3>
                        <small class="text-muted">{{ __('All time') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card comp-card h-100">
                <div class="card-body d-flex flex-column h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="me-2">
                            <h6 class="mb-0 text-muted fw-normal">{{ __('Total Expired Coupon') }}</h6>
                        </div>
                        <div class="icon-wrapper rounded-circle bg-danger p-1">
                            <i class="ti ti-user-exclamation text-white fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <h3 class="mb-0 text-danger">{{ $expieredCoupon }}</h3>
                        <small class="text-muted">{{ __('No longer valid') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card comp-card h-100">
                <div class="card-body d-flex flex-column h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="me-2">
                            <h6 class="mb-0 text-muted fw-normal">{{ __('Total Used Coupon') }}</h6>
                        </div>
                        <div class="icon-wrapper rounded-circle bg-success p-1">
                            <i class="ti ti-user-check text-white fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <h3 class="mb-0 text-success">{{ $totalUsedCoupon }}</h3>
                        <small class="text-muted">{{ __('Redeemed by customers') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card comp-card h-100">
                <div class="card-body d-flex flex-column h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="me-2">
                            <h6 class="mb-0 text-muted fw-normal">{{ __('Total Discounted Amount') }}</h6>
                        </div>
                        <div class="icon-wrapper rounded-circle bg-warning p-1">
                            <i class="ti ti-currency-dollar text-white fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <h3 class="mb-0 text-warning">
                            {{ Utility::getsettings('currency_symbol') }}{{ $totalUseAmount }}
                        </h3>
                        <small class="text-muted">{{ __('Total savings') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <script>
        $(document).ready(function() {
            $('body').on('click', '.upload_csv', function() {
                var action = $(this).data('url');
                var modal = $('#common_modal');
                $.get(action, function(response) {
                    modal.find('.modal-title').html('{{ __('Upload Coupon') }}');
                    modal.find('.body').html(response);
                    modal.modal('show');
                })
            });
        });
    </script>
@endpush
