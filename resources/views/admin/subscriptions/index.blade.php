@extends('layouts.main')
@section('title', __('Student Subscriptions'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Subscriptions') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="">
                        <table class="table table-bordered data-table w-100" id="subscriptionsTable">
                            <thead id="subscriptionsTableHead">
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Plan Name</th>
                                    <th class="mobile-hide">Status</th>
                                    <th class="">Start Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    @include('layouts.includes.datatable_css')
    <style>
        .dataTables_filter {
            display: flex !important;
        }

        table.dataTable td {
            white-space: normal !important;
            word-wrap: break-word;
        }

        table.dataTable {
            table-layout: auto !important;
            width: 100% !important;
        }

        @media (max-width: 768px) {
            .card-body {
                max-height: unset !important;
            }
        }

        @media (max-width: 768px) {
            #subscriptionsTable .mobile-hide {
                display: none !important;
            }
        }

        @media (max-width: 1399px) {
            .tb-search input {
                width: unset !important;
            }
        }

        @media (max-width: 768px) {
            .mobile-hide {
                display: none !important;
            }
        }

        .my-responsive-class {
            width: 100%;
        }

        @media (min-width: 768px) {
            .my-responsive-class {
                width: 50%;
                justify-content: end;
            }

            .dataTables_filter {
                justify-content: end !important;
            }
        }

        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .badge.bg-success {
            background-color: #4AD991 !important;
        }

        .badge.bg-warning {
            background-color: #FEC53D !important;
        }

        .badge.bg-danger {
            background-color: #FF0000 !important;
        }

        .badge.bg-secondary {
            background-color: #6c757d !important;
        }
    </style>
@endpush

@push('javascript')
    @include('layouts.includes.datatable_js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#subscriptionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('purchase.subscription') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'student_name',
                        name: 'student_name'
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'created_at_formatted',
                        name: 'created_at'
                    }
                ],
                order: [[4, 'desc']],
                columnDefs: [{
                        responsivePriority: 1,
                        targets: 0
                    },
                    {
                        responsivePriority: 2,
                        targets: 1
                    },
                    {
                        responsivePriority: 3,
                        targets: 4
                    },
                    {
                        responsivePriority: 10000,
                        targets: [2, 3]
                    }
                ],
                dom: `<'dataTable-top row'
                        <'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
                        <'dataTable-search dataTable-search tb-search d-flex justify-content-end'f>
                    >
                    <'dataTable-container'<'col-sm-12'tr>>
                    <'dataTable-bottom row'
                        <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                        <'col-sm-7'p>
                    >`,
                responsive: true,
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        });
    </script>
@endpush