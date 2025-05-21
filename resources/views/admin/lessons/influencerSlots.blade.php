@extends('layouts.main') @section('title', __('All Slots')) @section('breadcrumb') <li class="breadcrumb-item"><a
        href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
<li class="breadcrumb-item">{{ __('All Slots') }}</li>
@endsection
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div>
                        <div class="flex justify-between items-center card-header  w-100">
                            <h5>{{ __('All Slots') }}</h5>
                            <form>
                                <select name="lesson_id" id="lesson_id" class="form-select w-full"
                                    onchange="this.form.submit()">
                                    <option value="" disabled selected>Select Lesson</option>
                                    <option value="-1">All</option>
                                    @foreach ($lessons as $lesson)
                                        <option value="{{ $lesson->id }}"
                                            {{ request()->input('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                            {{ ucfirst($lesson->lesson_name) }}</option>
                                    @endforeach
                                </select>
                            </form>
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

            </div>
            <div id="calendar"></div>
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
        var followers = @json($followers);
        var payment_method = @json($payment_method);
        var isMobile = window.innerWidth <= 768;
        var initialCalendarView = isMobile ? 'listWeek' : 'timeGridWeek';
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            eventMinHeight: 20,
            eventShortHeight: 45,
            nowIndicator: true,
            slotMinTime: '5:00:00',
            slotMaxTime: '20:00:00',
            events: @json($events),
            eventClick: function(info) {
                const slot_id = info?.event?.extendedProps?.slot_id;
                const isBooked = !!info?.event?.extendedProps?.is_follower_assigned;
                const isCompleted = !!info.event?.extendedProps?.is_completed;
                const availableSeats = info.event.extendedProps.available_seats;
                const slot = info.event.extendedProps.slot;
                const paymentMethod = slot.lesson.payment_method;
                const follower = info.event.extendedProps.follower;
                const lesson = info.event.extendedProps.lesson;
                const influencer = info.event.extendedProps.influencer;
                const isPackageLesson = !!lesson.is_package_lesson;
                const formattedTime = new Date(slot.date_time.replace(/-/g, "/"))
                    .toLocaleTimeString([], {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                if (isBooked && type == 'influencer' && isPackageLesson) {
                    // Extract list of booked followers
                    let bookedFollowersHtml =
                        "<strong style='display: block; text-align: left; margin-bottom: 5px;'>📋 Booked Followers:</strong>";
                    bookedFollowersHtml += "<ol style='text-align: left; padding-left: 20px;'>";

                    if (follower.length > 0) {
                        follower.forEach((follower, index) => {
                            let followerName = follower.pivot.isFriend ?
                                `${follower.pivot.friend_name} (Friend: ${follower.name})` :
                                follower.name;

                            bookedFollowersHtml += `<li>${index + 1}. ${followerName}</li>`;
                        });
                    } else {
                        bookedFollowersHtml += "<li>No followers booked yet.</li>";
                    }

                    bookedFollowersHtml += "</ol>";

                    Swal.fire({
                        title: "Confirm Slot Completion",
                        html: `
            <p style="text-align: left;">Are you sure you want to complete this lesson slot?</p>
            ${bookedFollowersHtml}
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


                if (!isCompleted && (type == 'Admin' || type == 'influencer')) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success",
                            cancelButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });

                    if (!!lesson.is_package_lesson) {
                        Swal.fire('Error',
                            'Sorry, influencers can\'t book package lesson slots for followers.',
                            'error');
                        return; // Stop further execution
                    }


                    let completeSlotButtonHtml = '';
                    if (isBooked)
                        completeSlotButtonHtml = `
                    <button id="completeSlotBtn" type="button" class="swal2-confirm btn btn-success">Complete Slot</button>
                                                `;

                    let bookedFollowersHtml = follower.length ?
                        `<ul>${follower.map(follower => 
        `<li>${follower?.pivot.friend_name ? follower.pivot.friend_name : follower.name} ${follower.isGuest ? "(Guest)" : ""}</li>`
    ).join('')}</ul>` :
                        "<p class='text-muted'>No followers booked yet.</p>";

                    let unbookButtonHtml = "";
                    if (isBooked) {
                        unbookButtonHtml =
                            `<button id="unbookBtn" type="button" class="swal2-confirm btn btn-warning">Unbook Followers</button>`;
                    }

                    swalWithBootstrapButtons.fire({
                        title: "Book Slot",
                        html: `
    <div style="text-align: left; font-size: 14px; margin-bottom: 10px;">
        <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
        <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
        <span><strong>influencer:</strong> ${influencer.name}</span><br/>
        <span><strong>Location:</strong> ${slot.location}</span><br/>
        <span><strong>Available Spots:</strong> <strong>${availableSeats}</strong></span><br/>
        <label><strong>Booked Followers:</strong></label>
        ${bookedFollowersHtml}
    </div>

    <form id="swal-form">
        <div class="form-group" id="follower-form">
            <div class="flex justify-start">
                <label class="mb-1"><strong>Select Followers</strong></label>
            </div>
            <select name="follower_id[]" id="follower_id" class="form-select w-full" multiple>
                @foreach ($followers as $follower)
                    <option value="{{ $follower->id }}">
                        {{ ucfirst($follower->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
    <div class="flex justify-center gap-4">
            ${unbookButtonHtml} ${completeSlotButtonHtml}
        </div>
`,

                        showCancelButton: true,
                        confirmButtonText: "Book",
                        cancelButtonText: 'Close',
                        reverseButtons: true,
                        didRender: () => {
                            document.getElementById("unbookBtn")?.addEventListener(
                                "click",
                                function() {
                                    openManageSlotPopup(slot_id, follower,
                                        formattedTime, lesson, influencer, slot,
                                        availableSeats);
                                });
                            document.getElementById("completeSlotBtn")
                                ?.addEventListener("click", function() {
                                    completeSlot(slot_id, paymentMethod,
                                        influencer);
                                });
                        },
                        preConfirm: () => {
                            const followerSelect = document.getElementById('follower_id');
                            const follower_ids = [...followerSelect.selectedOptions].map(
                                opt => opt.value);

                            if (follower_ids.length > availableSeats)
                                Swal.showValidationMessage(
                                    `You can only select up to ${availableSeats} followers.`
                                );

                            return {
                                follower_ids,
                            };
                        }
                    }).then((result) => {
                        if (result.value) {
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
                        }
                    });
                }
            },
            headerToolbar: {
                right: 'customDayButton today prev,next', // Add custom button here
                center: 'title',
                left: 'timeGridWeek,listWeek', // Built-in views
            },
            customButtons: {
                customDayButton: {
                    text: 'Day View', // Button label
                    click: function() {
                        calendar.changeView('timeGridDay'); // Change to day view
                    },
                },
            },
        });

        calendar.render();
    });

    function openManageSlotPopup(slot_id, follower, formattedTime, lesson, influencer, slot, availableSeats) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger",
            },
            buttonsStyling: false,
        });

        let followersHtml = `
        <label for="unbookFollowers"><strong>Select Followers to Unbook:</strong></label>
        <select id="unbookFollowers" class="form-select w-full" multiple>
    `;

        if (Array.isArray(follower) && follower.length > 0) {
            followersHtml += follower.map(s =>
                `<option value="${s.id}">${s.isGuest ? `${s.name} (Guest)` : s.name}</option>`
            ).join('');
        }
        followersHtml += `</select>`;

        swalWithBootstrapButtons.fire({
            title: "Manage Slot",
            html: `
            <div style="text-align: left; font-size: 14px;">
                <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
                <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                <span><strong>influencer:</strong> ${influencer.name}</span><br/>
                <span><strong>Location:</strong> ${slot.location}</span><br/>
                <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                ${followersHtml}<br/>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: "Unbook",
            cancelButtonText: "Cancel",
            reverseButtons: true,
            showCloseButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const selectedFollowers = Array.from(document.getElementById('unbookFollowers').selectedOptions)
                    .map(option => option.value);

                if (selectedFollowers.length === 0) {
                    Swal.showValidationMessage("Please select at least one follower to unbook.");
                    return false;
                }

                $.ajax({
                    url: "{{ route('slot.update') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        unbook: 1,
                        slot_id: slot_id,
                        follower_ids: selectedFollowers,
                        redirect: 1,
                    },
                    success: function(response) {
                        Swal.fire('Success', 'Followers unbooked successfully!', 'success');
                        window.location.reload();
                    },
                    error: function(error) {
                        Swal.fire('Error', 'There was a problem processing the request.', 'error');
                        console.log(error);
                    }
                });
            }
        });
    }

    function completeSlot(slot_id, paymentMethod, influencer) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-primary",
            },
            buttonsStyling: false,
        });
        if (paymentMethod == 'both') {
            console.log(influencer.is_stripe_connected);
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
                        if (influencer.is_stripe_connected) {
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
    }
</script>
<script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
</script>
@endpush
