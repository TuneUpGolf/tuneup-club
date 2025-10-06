@extends('layouts.main')
@section('title', __('Help'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Help') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    {{-- <div class="d-flex justify-end">
                        <a class="btn btn-default buttons-create btn-light-primary no-corner me-1 add_module" tabindex="0"
                            aria-controls="users-table" href="{{ route('help-section.create') }}">
                            <span>
                                <i class="fa fa-upload"></i> Upload
                            </span>
                        </a>
                    </div> --}}
                    <div class="flex justify-between">
                        <h4 class="my-2">
                            Uploaded help videos, files or images.
                        </h4>
                        @if ($role == 'Admin' || $role == 'Super Admin')
                            <a href="{{ route('help-section.create') }}" class="btn btn-primary"> <i class="fa fa-plus"></i>
                                Create</a>
                        @endif
                    </div>
                    <hr />

                    {{-- Dummy Cards --}}
                    <div class="row g-3 mt-2">
                        @forelse ($help_sections as $item)
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card shadow-sm " style="display: flex; flex-direction: column;">
                                    <div class="card-body text-center d-flex flex-column justify-content-between overflow-hidden mb-4"
                                        style="height: 250px;min-height: 229px;">
                                        <p class="mb-2 font-bold">{{ $item['title'] }}</p>

                                        <div class="mb-2 d-flex flex-column">
                                            @if ($item['type'] == 'video')
                                                <video class="w-100 rounded" style="max-height: 150px; object-fit: cover;"
                                                    controls>
                                                    <source src="{{ $item['url'] }}"
                                                        type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @elseif($item['type'] == 'image')
                                                <img src="{{ $item['url'] }}"
                                                    style="max-height: 150px; object-fit: cover;" alt="">
                                            @endif
                                        </div>

                                        {{-- Title --}}


                                        {{-- Buttons --}}
                                        <div class="d-flex justify-content-center gap-2 mt-2">
                                            <a href="{{$item['url'] }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @if (auth()->user()->hasAnyRole(['Admin', 'Super Admin']))
                                                <button class="btn btn-sm btn-danger ms-3 delete-btn"
                                                    data-id="{{ $item->id }}" data-type="{{ $item->type }}"
                                                    data-url="{{ $item->url }}">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-md-12">
                                <h2 class="text-center">No Help Video Found</h2>
                            </div>
                        @endforelse
                    </div>

                    {{-- Dummy Pagination --}}
                    <div class="d-flex justify-content-end mt-4">
                        <nav>
                            {{ $help_sections->links() }}

                            {{--  <ul class="pagination">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>  --}}
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const url = this.getAttribute('data-url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('{{ route('help-section.destroy', ':id') }}'.replace(':id', id), {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    type: type,
                                    url: url
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'The item has been deleted.',
                                        'success'
                                    ).then(() => {
                                        window.location
                                            .reload(); // Refresh to update the list
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message || 'Failed to delete the item.',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'An error occurred while deleting.',
                                    'error'
                                );
                                console.error(error);
                            });
                    }
                });
            });
        });
    </script>
@endpush
