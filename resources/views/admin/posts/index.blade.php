@extends('layouts.main')
@section('title', __('Posts'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Posts') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <!-- Filter Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <label class="input-group-text" for="paymentFilter">Payment Type:</label>
                                <select class="form-select" id="paymentFilter">
                                    <option value="">All</option>
                                    <option value="paid">Paid</option>
                                    <option value="free">Free</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered data-table w-100">
                            <thead>
                                <tr>
                                    <th id="icon12"></th>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Paid</th>
                                    <th>Price</th>
                                    <th>Sales</th>
                                    <th>Photo</th>
                                    <th>Created At</th>
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
    <div id="videoModal" class="modal" style="">
        <span class="close"
            style="position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer;">&times;</span>
        <div class="modal-content" style="margin:3% auto; width:75%; text-align:center; max-height:none;">
            <video id="videoPlayer" width="100%" controls>
                <source src="" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
        </div>
    </div>
@endsection

@push('css')
    @include('layouts.includes.datatable_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        #videoThumbnail {
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            margin: auto;
            padding: 0;
            width: 90%;
            max-width: 100%;
            background-color: #fff;
            border-radius: 10px;
            max-height: calc(100vh - 200px);
            overflow: hidden;
        }

        .modal-content video {
            width: 100%;
            height: auto;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            height: 30px;
            width: 30px;
            border-radius: 100px;
            background-color: #0071ce;
            text-align: center;
            line-height: 30px;
        }

        .close:hover {
            color: #000;
        }

        div.dt-buttons.btn-group>.btn {
            flex: 0 0 auto !important;
        }
    </style>
@endpush

@push('javascript')
    @include('layouts.includes.datatable_js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(function() {
            let createUrl = "{{ route('blogs.create') }}";
            let reorderUrl = "{{ route('post.reorder') }}";
            
            // Initialize DataTable with custom filtering
            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('blogs.manage') }}",
                    data: function(d) {
                        // Add payment filter to request
                        d.payment_filter = $('#paymentFilter').val();
                    }
                },
                columns: [{
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'paid',
                        name: 'paid'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'sales',
                        name: 'sales'
                    },
                    {
                        data: 'photo',
                        name: 'photo'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.childRow,
                        renderer: function(api, rowIdx, columns) {
                            let data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function(i, col) {
                                if (i === 0) return;
                                data.append(
                                    '<tr>' +
                                    '<td><strong>' + col.title + '</strong></td>' +
                                    '<td>' + (col.data !== null ? col.data : '') + '</td>' +
                                    '</tr>'
                                );
                            });
                            return data;
                        }
                    }
                },
                order: [
                    [1, 'asc']
                ],
                dom: "<'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'<'custom-title'>>" +
                    "<'dataTable-botton table-btn col-lg-6 col-sm-12'B>" +
                    "<'dataTable-search tb-search col-lg-3 col-sm-12'f>>" +
                    "<'dataTable-container'<'col-sm-12'tr>>" +
                    "<'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l><'col-sm-7'p>>",
                buttons: [
                    {
                        text: '<i class="fa fa-plus"></i> Create',
                        className: 'btn btn-light-primary no-corner me-1 add_module',
                        action: function() {
                            window.location.href = createUrl;
                        }
                    },
                    {
                        text: '<i class="fa fa-sort"></i> Reorder Posts',
                        className: 'btn btn-light-info no-corner me-1 reorder-posts',
                        action: function() {
                            window.location.href = reorderUrl;
                        }
                    }
                ],
                initComplete: function() {
                    var table = this;
                    var tableContainer = $(table.api().table().container());

                    var searchInput = $('#' + table.api().table().container().id +
                        ' label input[type="search"]');
                    searchInput.removeClass('form-control form-control-sm').addClass('dataTable-input');

                    $(table.api().table().container()).find(".dataTables_length select")
                        .removeClass('custom-select custom-select-sm form-control form-control-sm')
                        .addClass('dataTable-selector');

                    tableContainer.find(".dataTable-title").html(
                        $("<div>").addClass("flex justify-start items-center").append(
                            $("<div>").addClass("custom-table-header"),
                            $("<span>").addClass("font-medium text-2xl pl-4").text("All Posts")
                        )
                    );
                }
            });

            // Filter change handler
            $('#paymentFilter').on('change', function() {
                table.ajax.reload();
            });

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
    
    <!-- Video Modal Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById("videoModal");
            const closeBtn = document.querySelector(".close");
            const video = document.getElementById("videoPlayer");

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('video-thumbnail')) {
                    const videoSrc = e.target.getAttribute('data-video');
                    video.querySelector('source').src = videoSrc;
                    video.load();
                    modal.style.display = "block";
                    video.play();
                }
            });

            closeBtn.onclick = function() {
                modal.style.display = "none";
                video.pause();
                video.currentTime = 0;
            };

            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                    video.pause();
                    video.currentTime = 0;
                }
            };
        });
    </script>
@endpush