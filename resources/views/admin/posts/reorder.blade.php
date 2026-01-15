@extends('layouts.main')
@section('title', __('Reorder Posts'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('blogs.manage') }}">{{ __('Posts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reorder Posts') }}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Reorder Posts') }}</h5>
                <p class="text-muted mb-0">{{ __('Drag and drop posts to reorder them. Click "Save Order" when done.') }}</p>
            </div>
            <div class="card-body">
                @if($posts->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> {{ __('No posts found to reorder.') }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> {{ __('Drag the handle (⋮⋮) on the left to reorder posts. The order will be saved automatically.') }}
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="reorderTable">
                            <thead>
                                <tr>
                                    <th width="50">{{ __('Order') }}</th>
                                    <th width="50"></th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                </tr>
                            </thead>
                            <tbody id="sortable">
                                @foreach($posts as $post)
                                <tr data-id="{{ $post->id }}">
                                    <td class="text-center">
                                        <span class="badge bg-primary order-badge">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="drag-handle" style="cursor: move; font-size: 18px;">⋮⋮</span>
                                    </td>
                                    <td>{{ $post->title }}</td>
                                    <td>
                                        @if($post->album_category_id)
                                            <span class="badge bg-info">{{ __('Album') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Post') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($post->status == 'active')
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \App\Facades\UtilityFacades::date_time_format($post->created_at) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" id="saveOrderBtn" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> {{ __('Save Order') }}
                        </button>
                        <a href="{{ route('blogs.manage') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> {{ __('Back to Posts') }}
                        </a>
                        <span id="saveStatus" class="ms-3"></span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    #sortable tr {
        cursor: move;
    }
    
    .drag-handle {
        color: #6c757d;
        user-select: none;
    }
    
    .drag-handle:hover {
        color: #0d6efd;
    }
    
    .ui-sortable-helper {
        display: table;
        background-color: rgba(13, 110, 253, 0.1);
        border: 2px dashed #0d6efd !important;
    }
    
    .order-badge {
        font-size: 12px;
        min-width: 30px;
    }
    
    #saveStatus {
        font-size: 14px;
    }
    
    .success-message {
        color: #198754;
    }
    
    .error-message {
        color: #dc3545;
    }
</style>
@endpush

@push('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        // Make table rows sortable
        $("#sortable").sortable({
            handle: '.drag-handle',
            placeholder: "ui-state-highlight",
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
            },
            update: function(event, ui) {
                updateOrderNumbers();
            }
        });
        $("#sortable").disableSelection();
        
        // Update order numbers
        function updateOrderNumbers() {
            $('#sortable tr').each(function(index) {
                $(this).find('.order-badge').text(index + 1);
            });
        }
        
        // Save order button click
        $('#saveOrderBtn').click(function() {
            saveOrder();
        });
        
        // Save order function
        function saveOrder() {
            let order = [];
            $('#sortable tr').each(function(index) {
                let postId = $(this).data('id');
                order.push({
                    id: postId,
                    position: index + 1
                });
            });
            
            $('#saveStatus').html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...').removeClass('success-message error-message');
            
            $.ajax({
                url: "{{ route('post.reorder.updatee') }}",
                method: "POST",
                data: {
                    order: order,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('#saveStatus').html('<i class="fas fa-check-circle me-2"></i> ' + response.message).addClass('success-message');
                        
                        // Update badges with new order from database if needed
                        if (response.new_order) {
                            response.new_order.forEach(function(item) {
                                $('tr[data-id="' + item.id + '"]').find('.order-badge').text(item.column_order);
                            });
                        }
                    } else {
                        $('#saveStatus').html('<i class="fas fa-exclamation-circle me-2"></i> ' + (response.message || 'Failed to save order')).addClass('error-message');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#saveStatus').html('<i class="fas fa-exclamation-circle me-2"></i> Error saving order. Please try again.').addClass('error-message');
                }
            });
        }
        
        // Auto-save on reorder (optional)
        let autoSaveTimer;
        $("#sortable").on("sortstop", function(event, ui) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                // Uncomment the line below to enable auto-save on reorder
                // saveOrder();
            }, 1000);
        });
    });
</script>
@endpush