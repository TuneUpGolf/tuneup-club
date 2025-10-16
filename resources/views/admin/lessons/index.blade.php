@extends('layouts.main')
@section('title', __('Lessons'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Lessons') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-bordered data-table w-100">
                            <thead>
                                <tr>
                                    <th id="icon12"></th> <!-- ✅ Responsive control column -->
                                    <th></th> <!-- ✅ Reorder handle column -->
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Created At</th>
                                    <th>Type</th>
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

@endsection

@push('css')
    @include('layouts.includes.datatable_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

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
    </style>
@endpush

@push('javascript')
    @include('layouts.includes.datatable_js')

    <!-- ✅ DataTables Extensions (latest compatible versions) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <!-- ✅ Export dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables core -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <!-- DataTables extensions -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>

    <script>
        $(function() {
            let createUrl = "{{ route('lesson.create', ['type' => 'online']) }}";

            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lesson.index') }}",
                columns: [{
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    }, {
                        data: null,
                        className: 'reorder-handle',
                        orderable: false,
                        searchable: false,
                        render: () => '<span class="drag-handle">⋮⋮</span>'
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'lesson_name',
                        name: 'lesson_name'
                    },
                    {
                        data: 'lesson_price',
                        name: 'lesson_price'
                    },
                    {
                        data: 'lesson_quantity',
                        name: 'lesson_quantity'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                rowReorder: {
                    selector: 'td.reorder-handle',
                    update: false
                },

                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.childRow, // show arrow icon
                        renderer: function(api, rowIdx, columns) {
                            let data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function(i, col) {

                                data.append(
                                    '<tr>' +
                                    '<td><strong>' + col.title + '</strong></td>' +
                                    '<td>' + col.data + '</td>' +
                                    '</tr>'
                                );
                            });
                            return data;
                        }
                    }
                },
                order: [
                    [2, 'asc']
                ],
                dom: "<'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'<'custom-title'>>" +
                    "<'dataTable-botton table-btn col-lg-6 col-sm-12'B>" +
                    "<'dataTable-search tb-search col-lg-3 col-sm-12'f>>" +
                    "<'dataTable-container'<'col-sm-12'tr>>" +
                    "<'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l><'col-sm-7'p>>",
                buttons: [{
                        text: '<i class="fa fa-plus"></i> Add Lesson',
                        className: 'btn btn-light-primary no-corner me-1 add_module',
                        action: function() {
                            window.location.href = createUrl;
                        }
                    },
                    {
                        extend: 'collection',
                        text: '<i class="ti ti-download"></i> Export',
                        className: 'btn btn-light-secondary me-1 dropdown-toggle',
                        buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Print',
                                className: 'btn btn-light text-primary dropdown-item'
                            },
                            {
                                extend: 'csv',
                                text: '<i class="fas fa-file-csv"></i> CSV',
                                className: 'btn btn-light text-primary dropdown-item'
                            },
                            {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel"></i> Excel',
                                className: 'btn btn-light text-primary dropdown-item'
                            },
                            {
                                extend: 'pdf',
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                className: 'btn btn-light text-primary dropdown-item'
                            },
                        ],
                        popoverTitle: ''
                    },
                    {
                        text: '<i class="fa fa-sync"></i> Reset',
                        className: 'btn btn-light-danger me-1',
                        action: function(e, dt) {
                            dt.search('').columns().search('').draw();
                        }
                    },
                    {
                        text: '<i class="fa fa-rotate"></i> Reload',
                        className: 'btn btn-light-warning',
                        action: function(e, dt) {
                            dt.ajax.reload();
                        }
                    }
                ],
                initComplete: function() {
                    var table = this;
                    var tableContainer = $(table.api().table().container());

                    // Customize search input
                    var searchInput = $('#' + table.api().table().container().id +
                        ' label input[type="search"]');
                    searchInput.removeClass('form-control form-control-sm').addClass('dataTable-input');

                    // Customize length selector
                    $(table.api().table().container()).find(".dataTables_length select")
                        .removeClass('custom-select custom-select-sm form-control form-control-sm')
                        .addClass('dataTable-selector');

                    // Custom table title
                    tableContainer.find(".dataTable-title").html(
                        $("<div>").addClass("flex justify-start items-center").append(
                            $("<div>").addClass("custom-table-header"),
                            $("<span>").addClass("font-medium text-2xl pl-4").text("All Lessons")
                        )
                    );
                }
            });

            // Smooth reorder handler
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
                    url: "{{ route('lesson.reorder') }}",
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

            function handleResponsiveColumn(table) {
                if (window.innerWidth <= 1300) {
                    // Show responsive icon column
                    $('#icon12').show();
                    table.column(0).visible(true);
                } else {
                    // Hide responsive icon column
                    $('#icon12').hide();
                    table.column(0).visible(false);
                }
            }

            // Run on load and resize
            handleResponsiveColumn(table);
            $(window).on('resize', function() {
                handleResponsiveColumn(table);
            });
        });
    </script>
@endpush
