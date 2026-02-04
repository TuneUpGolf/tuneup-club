@extends('layouts.main')
@section('title', __('Subscribed Students'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Subscribed Students') }}</li>
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
                                    <th>Duration</th>
                                    <th>Next Payment</th>
                                    <th>Remaining Lessons</th>
                                    <th class="mobile-hide">Status</th>
                                    <th class="">History</th>
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

        .subscription-history-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .subscription-history-item {
            padding: 4px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .subscription-history-item:last-child {
            border-bottom: none;
        }

        .history-date {
            font-weight: 500;
            color: #333;
        }

        .history-price {
            font-weight: 500;
            color: #28a745;
        }
        
        /* Status badge responsiveness */
        .badge {
            white-space: nowrap;
        }
        
        /* Remaining lessons badge styles */
        .remaining-lessons-badge {
            display: inline-block;
            padding: 0.25em 0.65em;
            font-size: 0.75em;
            font-weight: 500;
            border-radius: 9999px;
        }
        .remaining-lessons-green {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .remaining-lessons-blue {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .remaining-lessons-gray {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        
        /* Status filter styles */
        .dataTables_filter {
            gap: 15px;
            align-items: center;
        }
        
        .status-filter-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
        }
        
        .status-filter-label {
            font-weight: 500;
            white-space: nowrap;
            font-size: 14px;
            color: #495057;
        }
        
        .status-filter-select {
            min-width: 120px;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: white;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .dataTables_filter {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            
            .status-filter-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .status-filter-select {
                flex: 1;
                max-width: 150px;
            }
            
            .tb-search {
                width: 100%;
            }
            
            .tb-search input {
                width: 100% !important;
            }
        }
        
        @media (min-width: 769px) and (max-width: 992px) {
            .status-filter-container {
                margin-right: 0;
            }
            
            .status-filter-label {
                display: none;
            }
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
                ajax: {
                    url: "{{ route('purchase.subscription') }}",
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'student_name',
                        name: 'student_name',
                        width: '12%'
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name',
                        width: '12%'
                    },
                    {
                        data: 'duration',
                        name: 'duration',
                        width: '8%',
                        render: function(data) {
                            if (!data || data === 'N/A') return 'N/A';
                            return data.charAt(0).toUpperCase() + data.slice(1);
                        }
                    },
                    {
                        data: 'next_payment_date',
                        name: 'next_payment_date',
                        width: '12%',
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: 'remaining_lessons',
                        name: 'remaining_lessons',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        width: '8%',
                        className: 'mobile-hide'
                    },
                    {
                        data: 'subscription_history',
                        name: 'subscription_history',
                        orderable: false,
                        searchable: false,
                        width: '33%'
                    }
                ],
                order: [],
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
                        targets: 7 // History column
                    },
                    {
                        responsivePriority: 4,
                        targets: 4 // Next Payment column
                    },
                    {
                        responsivePriority: 5,
                        targets: 5 // Remaining Lessons column
                    },
                    {
                        responsivePriority: 6,
                        targets: 2 // Plan Name column
                    },
                    {
                        responsivePriority: 7,
                        targets: 3 // Duration column
                    },
                    {
                        responsivePriority: 10000,
                        targets: 6 // Status column (lowest priority for mobile)
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
                initComplete: function() {
                    // Add status filter to the search container
                    var statusFilter = `
                        <div class="status-filter-container">
                            <label class="status-filter-label">Status:</label>
                            <select class="status-filter-select" id="statusFilter">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    `;
                    
                    // Insert status filter before the search input
                    $('.dataTables_filter').prepend(statusFilter);
                    
                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    
                    // Status filter change event
                    $('#statusFilter').on('change', function() {
                        table.ajax.reload();
                    });
                },
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        });
    </script>
@endpush