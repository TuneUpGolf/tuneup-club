<div class="d-flex justify-content-end">
    {{-- View Button --}}
    <div class="action-btn bg-light-secondary ms-2">
        <a href="{{ route('announcements.show', $announcement->id) }}" 
           class="mx-3 btn btn-sm d-inline-flex align-items-center" 
           data-bs-toggle="tooltip" data-bs-placement="top" 
           title="{{ __('View') }}">
            <i class="ti ti-eye"></i>
        </a>
    </div>

    {{-- Edit Button --}}
    @can('edit-announcements')
    <div class="action-btn bg-light-primary ms-2">
        <a href="{{ route('announcements.edit', $announcement->id) }}" 
           class="mx-3 btn btn-sm d-inline-flex align-items-center" 
           data-bs-toggle="tooltip" data-bs-placement="top" 
           title="{{ __('Edit') }}">
            <i class="ti ti-edit"></i>
        </a>
    </div>
    @endcan

   {{-- Delete Button --}}
@can('delete-announcements')
<div class="action-btn bg-light-danger ms-2">
    <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="d-inline" id="delete-form-{{ $announcement->id }}">
        @csrf
        @method('DELETE')
        <button type="button" 
                class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" 
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Delete') }}"
                data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                data-form-id="delete-form-{{ $announcement->id }}">
            <i class="ti ti-trash"></i>
        </button>
    </form>
</div>
@endcan
</div>

@push('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    document.querySelectorAll('.show_confirm').forEach(button => {
        button.addEventListener('click', function() {
            const formId = this.getAttribute('data-form-id');
            const announcementId = this.getAttribute('data-announcement-id');
            const confirmMessage = this.getAttribute('data-confirm');
            const textMessage = this.getAttribute('data-text');
            
            console.log('Form ID:', formId);
            console.log('Announcement ID:', announcementId);
            
            Swal.fire({
                title: confirmMessage,
                text: textMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Submitting form:', formId);
                    const form = document.getElementById(formId);
                    console.log('Form found:', form);
                    
                    if (form) {
                        form.submit();
                    } else {
                        console.error('Form not found with ID:', formId);
                        Swal.fire('Error', 'Form not found!', 'error');
                    }
                }
            });
        });
    });
});
</script>
@endpush