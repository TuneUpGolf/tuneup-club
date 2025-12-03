@extends('layouts.main')
@section('title', __('Album Ordering'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.manage') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Album Ordering') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Reorder Albums') }}</h5>
                    <small class="text-muted">{{ __('Drag and drop albums to reorder them') }}</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        {{ __('Drag the handle (⋮⋮) on the left to reorder albums. Changes are saved automatically.') }}
                    </div>

                    <div class="table-responsive" style="margin-top: 25px !important">
                        <table class="table table-bordered data-table w-100" id="albumOrderTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"></th> <!-- Reorder handle -->
                                    <th style="width: 60px;">#</th>
                                    <th>{{ __('Album Name') }}</th>
                                    <th>{{ __('Media Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                </tr>
                            </thead>
                            <tbody id="sortable">
                                @foreach ($albums as $album)
                                    <tr data-id="{{ $album->id }}">
                                        <td class="reorder-handle text-center" style="cursor: move;">
                                            <span class="drag-handle">⋮⋮</span>
                                        </td>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $album->title }}</td>
                                        <td>
                                            <span class="badge bg-{{ $album->file_type == 'image' ? 'success' : 'info' }}">
                                                {{ ucfirst($album->file_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $album->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($album->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $album->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- <div class="mt-3">
                        <a href="{{ route('blogs.manage') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back to Posts') }}
                        </a>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        #sortable tr {
            cursor: move;
        }

        .drag-handle {
            color: #6c757d;
            font-size: 18px;
            cursor: move;
            user-select: none;
        }

        .drag-handle:hover {
            color: #007bff;
        }

        .ui-sortable-helper {
            background-color: #f8f9fa;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            display: table;
        }

        .ui-sortable-placeholder {
            background-color: #e9ecef;
            visibility: visible !important;
            height: 50px;
        }
    </style>
@endpush

@push('javascript')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(function() {
            // Initialize sortable
            $("#sortable").sortable({
                handle: ".reorder-handle",
                placeholder: "ui-sortable-placeholder",
                update: function(event, ui) {
                    updateOrder();
                }
            });

            function updateOrder() {
                let order = [];
                let position = 1;

                $("#sortable tr").each(function() {
                    const albumId = $(this).data('id');
                    if (albumId) {
                        order.push({
                            id: albumId,
                            position: position
                        });
                        position++;
                    }
                });

                // Update serial numbers
                $("#sortable tr").each(function(index) {
                    $(this).find('td:eq(1)').text(index + 1);
                });

                // Send AJAX request to update order
                $.ajax({
                    url: "{{ route('album.category.album-reorder', ['id' => $id]) }}",
                    method: "POST",
                    data: {
                        order: order,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', '{{ __('Success') }}',
                                '{{ __('Album order updated successfully!') }}');
                        } else {
                            showToast('error', '{{ __('Error') }}', response.message ||
                                '{{ __('Failed to update order') }}');
                        }
                    },
                    error: function(xhr) {
                        showToast('error', '{{ __('Error') }}',
                            '{{ __('Something went wrong. Please try again.') }}');
                    }
                });
            }

            function showToast(type, title, message) {
                // You can use your existing toast notification system
                // For example, if using Toastr:
                show_toastr(title, message, type);

                // if (typeof toastr !== 'undefined') {
                //     toastr[type](message, title);
                //             show_toastr(message, type, type);

                // } else {
                //     // Fallback alert
                //     alert(title + ': ' + message);
                //     toastr[type](message, title);
                // }
            }
        });
    </script>
@endpush
