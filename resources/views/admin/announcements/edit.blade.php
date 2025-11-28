@extends('layouts.main')
@section('title', __('Edit Announcement'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">{{ __('Announcements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Announcement') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Edit Announcement') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" id="announcementForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $announcement->title) }}" 
                               placeholder="{{ __('Enter announcement title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-3">
                        <label for="content" class="form-label">{{ __('Content') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="8" 
                                  placeholder="{{ __('Enter announcement content') }}" required>{{ old('content', $announcement->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-3">
                        <label class="form-label">{{ __('Send To') }} <span class="text-danger">*</span></label>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input recipient-type" type="radio" name="recipient_type" 
                                   id="recipient_all" value="all" {{ old('recipient_type', $announcement->recipient_type) == 'all' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="recipient_all">
                                <strong>{{ __('All Students') }}</strong>
                                <small class="text-muted d-block">({{ $students->count() }} students)</small>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input recipient-type" type="radio" name="recipient_type" 
                                   id="recipient_specific" value="specific" {{ old('recipient_type', $announcement->recipient_type) == 'specific' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="recipient_specific">
                                <strong>{{ __('Specific Students') }}</strong>
                            </label>
                        </div>
                    </div>

                    <!-- Student Selection -->
                    <div class="form-group mt-3" id="studentSelection" style="display: none;">
                        <label class="form-label">{{ __('Select Students') }} <span class="text-danger">*</span></label>
                        
                        <div class="mb-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" id="selectAllStudents">
                                    <i class="ti ti-check me-1"></i>{{ __('Select All') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="deselectAllStudents">
                                    <i class="ti ti-x me-1"></i>{{ __('Deselect All') }}
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1" id="selectedCount">0 students selected</small>
                        </div>

                        <div class="student-list border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            @forelse($students as $student)
                            <div class="form-check mb-2">
                                <input class="form-check-input student-checkbox" type="checkbox" 
                                       name="recipient_students[]" value="{{ $student->id }}" 
                                       id="student_{{ $student->id }}"
                                       {{ in_array($student->id, old('recipient_students', $announcement->recipients->pluck('student_id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="student_{{ $student->id }}">
                                    <strong>{{ $student->name }}</strong>
                                    <small class="text-muted d-block">{{ $student->email }}</small>
                                </label>
                            </div>
                            @empty
                            <div class="text-center text-muted py-3">
                                <i class="ti ti-users-off ti-lg mb-2"></i>
                                <p>{{ __('No students found.') }}</p>
                            </div>
                            @endforelse
                        </div>
                        
                        @error('recipient_students')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- <div class="form-group mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('Active Announcement') }}
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('Inactive announcements will not be visible to users.') }}
                        </small>
                    </div> -->

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-2"></i>{{ __('Update Announcement') }}
                        </button>
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-2"></i>{{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .form-text {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recipientAll = document.getElementById('recipient_all');
    const recipientSpecific = document.getElementById('recipient_specific');
    const studentSelection = document.getElementById('studentSelection');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const selectAllBtn = document.getElementById('selectAllStudents');
    const deselectAllBtn = document.getElementById('deselectAllStudents');
    const selectedCount = document.getElementById('selectedCount');

    // Toggle student selection visibility
    function toggleStudentSelection() {
        if (recipientSpecific.checked) {
            studentSelection.style.display = 'block';
            updateSelectedCount();
        } else {
            studentSelection.style.display = 'none';
        }
    }

    // Update selected count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCount.textContent = `${selected} students selected`;
        
        // Update validation
        if (recipientSpecific.checked && selected === 0) {
            selectedCount.classList.add('text-danger');
        } else {
            selectedCount.classList.remove('text-danger');
        }
    }

    // Select all students
    selectAllBtn.addEventListener('click', function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    });

    // Deselect all students
    deselectAllBtn.addEventListener('click', function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // Event listeners
    recipientAll.addEventListener('change', toggleStudentSelection);
    recipientSpecific.addEventListener('change', toggleStudentSelection);
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Form validation
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
        if (recipientSpecific.checked) {
            const selected = document.querySelectorAll('.student-checkbox:checked').length;
            if (selected === 0) {
                e.preventDefault();
                alert('Please select at least one student.');
                return false;
            }
        }
    });

    // Initialize
    toggleStudentSelection();
});
</script>
@endpush