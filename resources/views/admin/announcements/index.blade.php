@extends('layouts.main')
@section('title', __('Announcements'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Announcements') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- <h5>{{ __('Announcements') }}</h5> -->
                        @can('create-announcements')
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-2"></i>{{ __('Add Announcement') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-lg" id="preSetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold text-lg">Lesson Students</h1>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="closeInstructorPopup()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x: auto;">
                        <table id="preSetTable" class="table table-bordered" style="min-width: 800px; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Instructor</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Payment Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn w-25" onclick="closeInstructorPopup()">Close</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('css')
    @include('layouts.includes.datatable_css')
    <style>
        .dataTables_filter {
            display: none;
        }

        table.dataTable td {
            white-space: normal !important;
            word-wrap: break-word;
        }

        table.dataTable {
            table-layout: auto !important;
            width: 100% !important;
        }

        /* th, td{
                                                                                                                                        display: inline-block !important;
                                                                                                                                    } */
    </style>
@endpush

@push('javascript')
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function() {
            $('.dataTables_filter').addClass('d-none');
        })
        $(document).on('click', '#preSetActionButton', function() {
            $("#preSetModal").modal('show');
            let lesson_id = $(this).attr('data-lesson_id');
            let slot_id = $(this).attr('data-slot_id');
            fetch(`{{ route('purchase.data') }}?lesson_id=${lesson_id} &slot_id=${slot_id}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#preSetTable tbody');
                    tbody.innerHTML = '';

                    if (!data || data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" class="text-center">No data found</td></tr>`;
                        return;
                    }

                    data.forEach(row => {
                        const badge =
                            row.status?.toLowerCase() === 'incomplete' ?
                            `<span style="background-color:#DC2626;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;">Incomplete</span>` :
                            `<span style="background-color:#16A34A;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;">${row.status ?? ''}</span>`;

                        let manageSlotsBtn = '';
                        if (row.status === 'complete') {
                            if (row.lesson_type != 'online') {
                                manageSlotsBtn = `
                        <a class="btn btn-sm small btn-info action-btn-fix"
                        href="{{ route('slot.view', ['lesson_id' => '__LESSON__']) }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        data-bs-original-title="{{ __('Manage Slots') }}">
                            <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon" version="1.1"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M864 512a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM881.92 389.44a23.68 23.68 0 0 0-5.76-2.88 19.84 19.84 0 0 0-6.08-1.92 32 32 0 0 0-28.8 8.64A32 32 0 0 0 832 416a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72z"
                                    fill="#FFFFFF" />
                                <path
                                    d="M800 128h-32a96 96 0 0 0-96-96H352a96 96 0 0 0-96 96H224a96 96 0 0 0-96 93.44v677.12A96 96 0 0 0 224 992h576a96 96 0 0 0 96-93.44V736a32 32 0 0 0-64 0v162.56a32 32 0 0 1-32 29.44H224a32 32 0 0 1-32-29.44V221.44A32 32 0 0 1 224 192h32a96 96 0 0 0 96 96h320a96 96 0 0 0 96-96h32a32 32 0 0 1 32 29.44V288a32 32 0 0 0 64 0V221.44A96 96 0 0 0 800 128z m-96 64a32 32 0 0 1-32 32H352a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32h320a32 32 0 0 1 32 32z"
                                    fill="#FFFFFF" />
                                <path
                                    d="M712.32 426.56L448 721.6l-137.28-136.32A32 32 0 0 0 265.6 630.4l160 160a32 32 0 0 0 22.4 9.6 32 32 0 0 0 23.04-10.56l288-320a32 32 0 0 0-47.68-42.88z"
                                    fill="#FFFFFF" />
                            </svg>
                        </a>`.replace('__LESSON__', row.lesson_id);
                            } else {
                                manageSlotsBtn = ``;
                            }
                        }

                        const feedbackBtn = `
                    <a class="btn btn-sm small btn-warning action-btn-fix"
                    href="{{ route('purchase.feedback.create', ['purchase_id' => '__PURCHASE__']) }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    data-bs-original-title="{{ __('Provide Feedback') }}">
                        <i class="ti ti-plus text-white"></i>
                    </a>`.replace('__PURCHASE__', row.id);

                        let viewFeedbackBtn = '';
                        if (['Student', 'Instructor'].includes(row.user_type) && row.has_video && row
                            .can_manage_purchases) {
                            viewFeedbackBtn = `
                        <a class="btn btn-sm small btn-warning action-btn-fix"
                        href="{{ route('purchase.feedback.index', ['purchase_id' => '__PURCHASE__']) }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        data-bs-original-title="{{ __('View Feedback') }}">
                            <i class="ti ti-eye text-white"></i>
                        </a>`.replace('__PURCHASE__', row.id);
                        }

                        tbody.innerHTML += `
                    <tr>
                        <td>${row.student_name ?? ''}</td>
                        <td>${row.name ?? ''}</td>
                        <td>${row.lesson_type == 'online' ? row.created_at.split('T')[0] : row.date_time ?? ''}</td>
                        <td>${row.lesson_type == 'online' ? 'Online' : row.location ?? ''}</td>
                        <td>${badge}</td>
                        <td>${manageSlotsBtn} ${feedbackBtn} ${viewFeedbackBtn}</td>
                    </tr>
                `;
                    });
                })
                .catch(err => console.error(err));
        });

        function closeInstructorPopup() {
            $("#preSetModal").modal('hide');
        }
    </script>
@endpush
