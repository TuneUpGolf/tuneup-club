@props([
    'image' => '',
    'title' => '',
    'subtitle' => '',
    'description' => '',
    'withBackground' => false,
    'model',
    'actions' => [],
    'hasDefaultAction' => false,
    'selected' => false,
])

<div
    class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
    <div class="relative text-center p-2">
        <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-xl h-56 w-full object-cover">
        <div class="text-bottom-img">
            <span> By:
                <a class="text-white" href="{{ route('instructor.profile', ['influencer_id' => $model?->user?->id]) }}">
                    {!! \Illuminate\Support\Str::limit(ucfirst($model?->user?->name), 40, '...') !!}
                </a>
            </span>
        </div>
    </div>

    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
        <div class="flex flex-row justify-between">
            <div class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                {!! $subtitle !!}
            </div>
            @if ($model->is_package_lesson)
                <div class="bg-green-500 text-white text-sm font-bold px-2 py-1 rounded-full">
                    Package
                    Lesson
                </div>
            @endif
        </div>
        <br>
        <span class="text-xl">{!! $title !!}</span>
        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
            {!! \Illuminate\Support\Str::limit($description, 180, '...') !!}
        </p>

        <div class="mt-auto">
            <div class="flex justify-between mb-1">
                <div class="flex items-center space-x-1 rtl:space-x-reverse">Number of Lessons</div>
                <span class="">{!! $model->lesson_quantity !!}</span>
            </div>
            <div class="flex justify-between mt-2.5 mb-2">
                <div class="flex items-center space-x-1 rtl:space-x-reverse">Expected Response Time</div>
                <span class="">{!! $model->required_time !!} Days</span>
            </div>
            <div class="flex justify-between mb-1">
                <div class="flex items-center space-x-1 rtl:space-x-reverse">Purchases</div>
                <span class="">{!! \App\Models\Purchase::where('lesson_id', $model->id)->where('status', 'complete')->count() !!}</span>
            </div>
        </div>

        <div class="w-100 mt-3">
            @if ($model->type === 'online')
                {!! Form::open([
                    'route' => ['purchase.store', ['lesson_id' => $model->id]],
                    'method' => 'Post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'data-validate',
                ]) !!}
                {{ Form::button(__('Purchase'), ['type' => 'submit', 'class' => 'lesson-btn']) }}
                {!! Form::close() !!}
            @endif

            @if ($model->type === 'inPerson')
                @if ($model->is_package_lesson)
                    @php
                        $firstSlot = $model->slots->first();
                        $allSlots = $model->slots;
                    @endphp
                    @if ($firstSlot && !$firstSlot->isFullyBooked())
                        @php
                            $isAlreadyBooked = $model->slots->contains(function ($slot) {
                                return $slot->student->contains(Auth::id());
                            });
                        @endphp

                        @if ($isAlreadyBooked)
                            <button class="lesson-btn opacity-50 cursor-not-allowed" disabled>
                                Already Enrolled
                            </button>
                        @else
                            <button class="lesson-btn" onclick="openBookingPopup({{ json_encode($allSlots) }})">
                                Book Lesson
                            </button>
                        @endif
                    @else
                        <button class="lesson-btn opacity-50 cursor-not-allowed" disabled>
                            No Slots Available
                        </button>
                    @endif
                @else
                    <div>
                        <a href="{{ route('slot.view', ['lesson_id' => $model->id]) }}">
                            <button class="lesson-btn">Book Slot</button>
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
    <form id="bookingForm" method="POST" action="{{ route('slot.book', ['redirect' => 1]) }}">
        @csrf
        <input type="hidden" id="slotIdInput" name="slot_id">
        <input type="hidden" id="friendNamesInput" name="friend_names">

    </form>
</div>

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openBookingPopup(allSlots) {
            if (!allSlots || allSlots.length === 0) {
                console.error("No slots available!");
                return;
            }

            const firstSlot = allSlots[0]; // Extract first slot dynamically
            document.getElementById('slotIdInput').value = firstSlot.id;

            const availableSeats = firstSlot.lesson.max_students - firstSlot.student.length;
            const lesson = firstSlot.lesson;

            // Format All Slots' Date & Time
            let slotDetailsHtml = "";
            allSlots.forEach((s, index) => {
                const formattedTime = new Intl.DateTimeFormat('en-US', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                }).format(new Date(s.date_time.replace(/-/g, "/")));

                slotDetailsHtml += `
            <div class="slot-item">
                <span><strong>Slot ${index + 1}:</strong> ${formattedTime}</span><br/>
            </div>
        `;
            });

            Swal.fire({
                title: "Slot Details",
                html: `
        <div style="text-align: left; font-size: 14px;">
            <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
            <span><strong>Location:</strong> ${firstSlot.location}</span><br/>
            <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
            <div class="slot-list">
                <h6 class="mt-2"><strong>Slots Available:</strong></h6>
                ${slotDetailsHtml}
            </div>
            <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
            <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
        </div>
        `,
                showCancelButton: true,
                confirmButtonText: "Book Slot",
                cancelButtonText: "Cancel",
                preConfirm: () => {
                    const friendNames = document.getElementById('studentFriends')?.value.trim();
                    const friendNamesArray = friendNames ? friendNames.split(',').map(name => name.trim()) : [];

                    // Ensure it's passed as an array
                    document.getElementById("friendNamesInput").value = JSON.stringify(friendNamesArray);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Processing...",
                        text: "Please wait while we confirm your booking...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the hidden form
                    document.getElementById("bookingForm").submit();
                }
            });
        }
    </script>
@endpush
