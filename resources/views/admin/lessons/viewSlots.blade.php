 @extends('layouts.main') @section('title', __('Manage Slots')) @section('breadcrumb') <li class="breadcrumb-item"><a
         href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
 <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
 <li class="breadcrumb-item">{{ __('Manage Slots') }}</li>
@endsection
@section('content')
 <div class="main-content">
     <section class="section">
         <div class="section-body">
             <div class="m-auto col-lg-3 col-md-8 col-xxl-4">

                 <div class="card card-width-manageSlots">
                     <div class="flex justify-between items-center card-header  w-100 ">
                         @if (Auth::user()->type === 'Student')
                             <h5>{{ __('Book Slot') }}</h5>
                             <p>{{ __('Click on an avaialble slot to book') }}</p>
                         @else
                             <div>
                                 <h5>{{ __('Manage Slots') }}</h5>
                                 <div class="pt-2">
                                     <p class="mb-0 text-muted">Name: <strong>{{ $lesson->lesson_name }}</strong>
                                     </p>
                                     @if ($lesson->is_package_lesson)
                                         <span class="badge bg-primary">Package Lesson</span>
                                     @endif
                                 </div>
                             </div>
                             <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                 <a class="text-white" href="{{ route('slot.create', ['lesson_id' => $lesson_id]) }}">
                                     {{ __('Add New Slot') }}
                                 </a>
                             </button>
                         @endif
                     </div>
                     <div class="flex justify-center my-2 gap-2">
                         <div class="flex gap-1 items-center">
                             <div class="completed-key"></div>
                             <span>Completed</span>
                         </div>
                         <div class="flex gap-1 items-center">
                             <div class="booked-key"></div>
                             <span>Booked</span>
                         </div>
                         <div class="flex gap-1 items-center">
                             <div class="avaialable-key"></div>
                             <span>Available</span>
                         </div>
                     </div>
                 </div>

             </div>
             <div id="calendar"></div>
         </div>
     </section>
     {{ Form::open([
         'route' => ['slot.complete', ['redirect' => 1]],
         'method' => 'POST',
         'data-validate',
         'id' => 'form',
     ]) }}
     <input type="hidden" id="slot_id" name="slot_id" value="" />
     <input type="hidden" id="payment_method" name="payment_method" value="" />
     {{ Form::close() }}
     {{ Form::open([
         'route' => ['slot.book', ['redirect' => 1]],
         'method' => 'POST',
         'data-validate',
         'id' => 'form-book',
     ]) }}
     <input type="hidden" id="slot" name="slot_id" value="" />
     {{ Form::close() }}
 </div>
 @stack('scripts')
@endsection
@push('css')
 <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
 <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet" />
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         var calendarEl = document.getElementById('calendar');
         var type = @json($type);
         var authId = @json($authId);
         var students = @json($students);
         var isMobile = window.innerWidth <= 768;
         var initialCalendarView = isMobile ? 'listWeek' : 'timeGridWeek';
         var calendar = new FullCalendar.Calendar(calendarEl, {
             initialView: initialCalendarView,
             eventShortHeight: 45,
             nowIndicator: true,
             slotMinTime: '5:00:00',
             slotMaxTime: '20:00:00',
             events: @json($events),
             eventClick: function(info) {
                 const slot_id = info?.event?.extendedProps?.slot_id;
                 const slot = info.event.extendedProps.slot;
                 const paymentMethod = slot.lesson.payment_method;
                 const isFullyBooked = info?.event?.extendedProps?.isFullyBooked;
                 const availableSeats = info.event.extendedProps.available_seats;
                 const student = info?.event?.extendedProps?.student;
                 const lesson = slot.lesson;
                 const instructor = info.event.extendedProps.instructor;
                 const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
                 const isCompleted = !!info.event?.extendedProps?.is_completed;
                 const isAuthStudentBooked = !!(student.some(obj => obj.id == authId));
                 const isPackageLesson = !!lesson.is_package_lesson;

                 const formattedTime = new Date(slot.date_time.replace(/-/g, "/"))
                     .toLocaleTimeString([], {
                         weekday: 'long', // Full day name
                         year: 'numeric',
                         month: 'long', // Full month name
                         day: 'numeric',
                         hour: '2-digit',
                         minute: '2-digit',
                         hour12: true // 12-hour format with AM/PM
                     });

                 if (type == 'Instructor' && !isBooked && !isCompleted) {
                     const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                             confirmButton: "btn btn-success",
                             cancelButton: "btn btn-danger",
                         },
                         buttonsStyling: false,
                     });

                     if (!!lesson.is_package_lesson) {
                         Swal.fire('Error',
                             'Instructors can\'t book package lesson slots for students.',
                             'error');
                         return; // Stop further execution
                     }

                     swalWithBootstrapButtons
                         .fire({
                             title: "Book Slot",
                             html: `
                        <form id="swal-form">
                        <div class="flex justify-start mb-1">
                        <span>Available Spots: <strong>${availableSeats}</strong></span>
                        </div>
                        <div class="form-group" id="student-form">
                             <div class="flex justify-start">
                            <label class="mb-1">Select Students</label>
                            </div>
                            <select name="follower_id[]" id="follower_id" class="form-select w-full" multiple>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ ucfirst($student->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        </form>`,
                             preConfirm: () => {
                                 const studentSelect = document.getElementById('follower_id');
                                 const follower_ids = [...studentSelect.selectedOptions].map(
                                     opt => opt.value);

                                 if (follower_ids.length > availableSeats)
                                     Swal.showValidationMessage(
                                         `You can only select up to ${availableSeats} students.`
                                     );

                                 return {
                                     follower_ids,
                                 };
                             },
                             showCancelButton: true,
                             confirmButtonText: "Book",
                             reverseButtons: true,
                         })
                         .then((result) => {
                             const formData = result.value;
                             $.ajax({
                                 url: "{{ route('slot.admin') }}",
                                 type: 'POST',
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     isGuest: false,
                                     follower_ids: formData.follower_ids,
                                     slot_id: slot_id,
                                     redirect: 1,
                                 },
                                 success: function(response) {
                                     Swal.fire('Success',
                                         'Form submitted successfully!',
                                         'success');
                                     window.location.reload();
                                 },
                                 error: function(error) {
                                     Swal.fire('Error',
                                         'There was a problem submitting the form.',
                                         error);
                                     console.log(error);
                                 }

                             });

                         });
                 }
                 if (type == 'Instructor' && isBooked && !isCompleted) {
                     const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                             confirmButton: "btn btn-success",
                             cancelButton: "btn btn-danger",
                         },
                         buttonsStyling: false,
                     });

                     if (isPackageLesson) {
                         // Extract list of booked students
                         let bookedStudentsHtml =
                             "<strong style='display: block; text-align: left; margin-bottom: 5px;'>📋 Booked Students:</strong>";
                         bookedStudentsHtml += "<ol style='text-align: left; padding-left: 20px;'>";

                         if (slot.student.length > 0) {
                             slot.student.forEach((student, index) => {
                                 let studentName = student.pivot.isFriend ?
                                     `${student.pivot.friend_name} (Friend: ${student.name})` :
                                     student.name;

                                 bookedStudentsHtml +=
                                     `<li>${index + 1}. ${studentName}</li>`;
                             });
                         } else {
                             bookedStudentsHtml += "<li>No students booked yet.</li>";
                         }

                         bookedStudentsHtml += "</ol>";

                         Swal.fire({
                             title: "Confirm Slot Completion",
                             html: `
            <p style="text-align: left;">Are you sure you want to complete this lesson slot?</p>
            ${bookedStudentsHtml}
        `,
                             icon: "warning",
                             showCancelButton: true,
                             confirmButtonText: "Confirm",
                             cancelButtonText: "Cancel",
                         }).then((result) => {
                             if (result.isConfirmed) {
                                 // Send AJAX request to complete slot
                                 $.ajax({
                                     url: "{{ route('slot.complete') }}",
                                     type: "POST",
                                     data: {
                                         _token: $('meta[name="csrf-token"]').attr(
                                             'content'),
                                         payment_method: "online",
                                         slot_id: slot_id,
                                         redirect: 1,
                                     },
                                     success: function(response) {
                                         Swal.fire("Success!",
                                                 "Lesson slot has been completed.",
                                                 "success")
                                             .then(() => {
                                                 location
                                                     .reload(); // Reload page after success
                                             });
                                     },
                                     error: function(xhr) {
                                         Swal.fire("Error!",
                                             "Something went wrong. Please try again.",
                                             "error");
                                     }
                                 });
                             }
                         });

                         return;
                     }


                     let studentsHtml = `
                     <label for="unbookStudents"><strong>Select Students to Unbook:</strong></label>
                     <select id="unbookStudents" class="form-select w-full" multiple>
                    `;

                     if (Array.isArray(student) && student.length > 0) {
                         studentsHtml += student.map(student =>
                             `<option value="${student.id}">${student.isGuest ? `${student.name} (Guest)` : student.name}</option>`
                         ).join('');
                     }
                     studentsHtml += `</select>`;
                     swalWithBootstrapButtons
                         .fire({
                             title: "Manage Slot",
                             html: `
                    <div style="text-align: left; font-size: 14px;">
                    <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
                    <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                    <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
                    <span><strong>Location:</strong> ${slot.location}</span><br/>
                    <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                    ${studentsHtml}<br/>
                     </div>
                            `,
                             showCancelButton: true,
                             confirmButtonText: "Complete",
                             cancelButtonText: "Unreserve",
                             reverseButtons: true,
                             showCloseButton: true,
                         })
                         .then((result) => {

                             if (!!result.isConfirmed) {

                                 const swalWithBootstrapButtons = Swal.mixin({
                                     customClass: {
                                         confirmButton: "btn btn-success",
                                         cancelButton: "btn btn-primary",
                                     },
                                     buttonsStyling: false,
                                 });
                                 if (paymentMethod == 'both') {
                                     console.log(instructor.is_stripe_connected);
                                     swalWithBootstrapButtons
                                         .fire({
                                             title: "Choose Payment Method",
                                             text: "Please select from the following payment options before completing slot as complete",
                                             icon: "warning",
                                             showCancelButton: true,
                                             confirmButtonText: "Online",
                                             cancelButtonText: "Cash",
                                             reverseButtons: true,
                                         })
                                         .then((result) => {
                                             if (result.isConfirmed) {
                                                 if (instructor.is_stripe_connected) {
                                                     $("#slot_id").val(slot_id);
                                                     $("#payment_method").val('online');
                                                     $("#form").submit()
                                                 } else {
                                                     Swal.fire({
                                                         title: "Stripe Setup Required",
                                                         text: "Please set up Stripe integration to proceed.",
                                                         icon: "warning",
                                                         confirmButtonText: "OK"
                                                     });
                                                 }
                                             } else {
                                                 $("#slot_id").val(slot_id);
                                                 $("#payment_method").val('cash');
                                                 $("#form").submit()
                                             }
                                         });
                                 }
                                 if (paymentMethod == 'online') {
                                     $("#slot_id").val(slot_id);
                                     $("#payment_method").val('online');
                                     $("#form").submit()
                                 }
                                 if (paymentMethod == 'cash') {
                                     $("#slot_id").val(slot_id);
                                     $("#payment_method").val('cash');
                                     $("#form").submit()
                                 }
                             } else if (result.dismiss == 'cancel') {
                                 const selectedStudents = Array.from(document
                                         .getElementById(
                                             'unbookStudents').selectedOptions)
                                     .map(option => option.value);

                                 if (selectedStudents.length === 0) {
                                     Swal.showValidationMessage(
                                         "Please select at least one student to unbook."
                                     );
                                 }
                                 $.ajax({
                                     url: "{{ route('slot.update') }}",
                                     type: 'POST',
                                     data: {
                                         _token: $('meta[name="csrf-token"]').attr(
                                             'content'),
                                         unbook: 1,
                                         slot_id: slot_id,
                                         follower_ids: selectedStudents,
                                         redirect: 1,
                                     },
                                     success: function(response) {
                                         Swal.fire('Success',
                                             'Form submitted successfully!',
                                             'success');
                                         window.location.reload();
                                     },
                                     error: function(error) {
                                         Swal.fire('Error',
                                             'There was a problem submitting the form.',
                                             error);
                                         console.log(error);
                                     }

                                 });
                             }
                         });
                 }
                 if (type == 'Student' && !isAuthStudentBooked && !isFullyBooked && !isCompleted) {
                     let studentsHtml = `
        <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
        <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
    `;
                     Swal.fire({
                         title: "Slot Details",
                         html: `
                    <div style="text-align: left; font-size: 14px;">
                    <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
                    <span><strong>Location:</strong> ${slot.location}</span><br/>
                    <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                    <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
                    <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                         ${studentsHtml}
                    </div>
                         `,
                         showCancelButton: true,
                         confirmButtonText: "Book Slot",
                         cancelButtonText: "Cancel",
                         preConfirm: () => {
                             const friendNames = document.getElementById(
                                 'studentFriends')?.value.trim();
                             const friendNamesArray = friendNames ? friendNames
                                 .split(',').map(name => name.trim()) : [];
                             return {
                                 friendNames: friendNamesArray
                             };
                         }
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $.ajax({
                                 url: "{{ route('slot.book') }}",
                                 type: "POST",
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     slot_id: slot_id,
                                     friend_names: result.value.friendNames || "",
                                     redirect: 1,
                                 },
                                 success: function(response) {
                                     Swal.fire({
                                         icon: 'success',
                                         title: 'Booking Successful',
                                         text: response.message ||
                                             'You have successfully booked the slot.',
                                     }).then(() => {
                                         location
                                             .reload(); // Reload page to update UI
                                     });
                                 },
                                 error: function(xhr) {
                                     Swal.fire({
                                         icon: 'error',
                                         title: 'Booking Failed',
                                         text: xhr.responseJSON
                                             ?.message ||
                                             'An error occurred while booking the slot.',
                                     });
                                 }
                             });
                         }
                     });
                 }
                 if (type == 'Student' && isAuthStudentBooked) {
                     Swal.fire({
                         title: "Unbook Slot",
                         text: "Are you sure you want to unbook this slot?",
                         icon: "warning",
                         showCancelButton: true,
                         confirmButtonText: "Yes, unbook",
                         cancelButtonText: "Cancel",
                         reverseButtons: true,
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $.ajax({
                                 url: "{{ route('slot.update') }}",
                                 type: "POST",
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     unbook: 1,
                                     slot_id: slot_id,
                                     follower_ids: [authId],
                                     redirect: 1,
                                 },
                                 success: function(response) {
                                     Swal.fire("Success",
                                         "Slot unbooked successfully!",
                                         "success");
                                     window.location.reload();
                                 },
                                 error: function(error) {
                                     Swal.fire("Error",
                                         "Failed to unbook the slot.",
                                         "error");
                                     console.log(error);
                                 }
                             });
                         }
                     });
                 }
             },
         });

         calendar.render();

     });
 </script>
 <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
 </script>
@endpush
