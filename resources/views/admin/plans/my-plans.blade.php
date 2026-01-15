@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')
@section('title', __('My Plans'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('My Plans') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-bordered data-table nowrap w-100">
                            <thead>
                                <tr>
                                    <th id="icon12"></th>
                                    <th></th>
                                    <!-- <th>NO</th> -->
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th># OF Purchase</th>
                                    <th>Created at</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buyers Modal -->
    <div class="modal fade" id="buyersModal" tabindex="-1" aria-labelledby="buyersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buyersModalLabel">Followers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Plan Expiry</th>
                                </tr>
                            </thead>
                            <tbody id="buyersTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">No data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')

    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        .drag-handle {
            cursor: grab;
            font-size: 18px;
            color: #6c757d;
            user-select: none;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .drag-handle:active {
            cursor: grabbing;
            color: #000;
            transform: scale(1.2);
        }

        tbody tr {
            transition: transform 0.25s ease, background-color 0.25s ease;
        }

        tbody tr.dt-rowReorder-moving {
            background-color: #f1f3f5 !important;
        }

        /* ✅ Make DataTable buttons responsive */
        div.dt-buttons.btn-group {
            flex-wrap: wrap !important;
            gap: 5px;
        }

        div.dt-buttons.btn-group>.btn {
            flex: 0 0 auto !important;
        }

        /* ✅ Make table more mobile friendly */
        table.dataTable {
            width: 100% !important;
        }
    </style>
@endpush

@push('javascript')
    @include('layouts.includes.datatable_js')

    <!-- ✅ jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ DataTables Core -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- ✅ Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <!-- ✅ RowReorder + Responsive -->
    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>


    <script>
        $(function() {
            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('plans.myplan.data') }}",
                columns: [{
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: null,
                        className: 'reorder-handle',
                        orderable: false,
                        searchable: false,
                        render: () => '<span class="drag-handle">⋮⋮</span>'
                    },
                    // {
                    //     data: 'DT_RowIndex',
                    //     name: 'DT_RowIndex',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'purchases_count',
                        name: 'purchases_count',
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'active_status',
                        name: 'active_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                rowReorder: {
                    selector: 'td.reorder-handle',
                    update: false
                },
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.childRow,
                        renderer: function(api, rowIdx, columns) {
                            let data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function(i, col) {
                                if (i === 0 || i === 1) return;
                                data.append('<tr><td><strong>' + col.title +
                                    '</strong></td><td>' + (col.data ?? '') + '</td></tr>');
                            });
                            return data;
                        }
                    }
                },
                order: [
                    [2, 'asc']
                ],

                dom: `
            <'dataTable-top row'
                <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'dataTable-botton table-btn col-lg-6 col-sm-12'B>
                <'dataTable-search tb-search col-lg-3 col-sm-12'f>
            >
            <'dataTable-container'<'col-sm-12'tr>>
            <'dataTable-bottom row'
                <'col-sm-5'i>
                <'col-sm-7'p>
            >
        `,

                buttons: [{
                    text: '➕ Create Plan',
                    className: 'btn btn-light-primary no-corner me-1 add_module',
                    action: function(e, dt, node, config) {
                        window.location = "{{ route('plans.createmyplan') }}";
                    }
                }],

                initComplete: function() {
                    var table = this;
                    var searchInput = $('#' + table.api().table().container().id +
                        ' label input[type="search"]');
                    searchInput.removeClass('form-control form-control-sm').addClass('dataTable-input');
                    var select = $(table.api().table().container())
                        .find(".dataTables_length select")
                        .removeClass('custom-select custom-select-sm form-control form-control-sm')
                        .addClass('dataTable-selector');
                }
            });

            // Handle row reorder
            table.on('row-reorder', function(e, diff, edit) {
                if (diff.length === 0) return;
                let order = [];
                diff.forEach(function(move) {
                    let rowData = table.row(move.node).data();
                    order.push({
                        id: rowData.id,
                        position: move.newPosition + 1
                    });
                });

                $.ajax({
                    url: "{{ route('plan.reorder') }}",
                    method: "POST",
                    data: {
                        order: order,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                    },
                    error: function(err) {
                        console.error('Reorder failed:', err);
                    }
                });
            });

            // Responsive column toggle
            function handleResponsiveColumn(table) {
                if (window.innerWidth <= 1300) {
                    $('#icon12').show();
                    table.column(0).visible(true);
                } else {
                    $('#icon12').hide();
                    table.column(0).visible(false);
                }
            }
            handleResponsiveColumn(table);
            $(window).on('resize', function() {
                handleResponsiveColumn(table);
            });
        });
    </script>

    <script>
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.classList.contains('js-plan-buyers')) {
                e.preventDefault();
                var url = target.getAttribute('data-url');

                // Clear table body and show loading
                var tbody = document.getElementById('buyersTableBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(resp) {
                        return resp.json();
                    })
                    .then(function(json) {
                        var rows = '';
                        if (json && Array.isArray(json.data) && json.data.length) {
                            json.data.forEach(function(buyer, idx) {
                                rows += '<tr>' +
                                    '<td>' + (idx + 1) + '</td>' +
                                    '<td>' + (buyer.name ?? '-') + '</td>' +
                                    '<td>' + (buyer.email ?? '-') + '</td>' +
                                    '<td>' + (buyer.plan_expired_date ?? '-') + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            rows = '<tr><td colspan="4" class="text-center">No follower found</td></tr>';
                        }
                        tbody.innerHTML = rows;
                        var modal = new bootstrap.Modal(document.getElementById('buyersModal'));
                        modal.show();
                    })
                    .catch(function() {
                        tbody.innerHTML =
                            '<tr><td colspan="4" class="text-center text-danger">Failed to load follower</td></tr>';
                        var modal = new bootstrap.Modal(document.getElementById('buyersModal'));
                        modal.show();
                    });
            }
        });
    </script>
@endpush
