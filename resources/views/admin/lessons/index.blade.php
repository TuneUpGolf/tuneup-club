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
                                    <th></th>
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

    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>

    <script>
        $(function() {
            let createUrl = "{{ route('lesson.create', ['type' => 'online']) }}";

            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lesson.index') }}",
                columns: [{
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
                    update: false // disable automatic reordering on the client
                },
                responsive: true,
                order: [
                    [1, 'asc']
                ],
                dom: "<'dataTable-top row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'dataTable-container'tr>" +
                    "<'dataTable-bottom row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        text: '<i class="fa fa-plus"></i> Add Lesson',
                        className: 'btn btn-light-primary me-1',
                        action: function() {
                            window.location.href = createUrl;
                        }
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
                ]
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
        });
    </script>
@endpush
