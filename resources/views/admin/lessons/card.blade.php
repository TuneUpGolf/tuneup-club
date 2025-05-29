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
    <div class="relative text-center p-3 flex gap-3">
        {{-- <img src="{{ $image }}" alt="{{ $image }}"
        class="hover:shadow-lg cursor-pointer rounded-xl h-56 w-full object-cover"> --}}
        <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
        <div class="text-left">
            <a class="font-bold text-dark text-xl"
                href="{{ route('influencer.profile', ['influencer_id' => $model?->user?->id]) }}">
                {!! \Illuminate\Support\Str::limit(ucfirst($model?->user?->name), 40, '...') !!}
            </a>
            <div class="text-lg font-bold tracking-tight text-primary">
                {!! $subtitle !!}
            </div>
            {{-- <div class="text-sm font-medium text-gray-500 italic">
                <span class="">({!! \App\Models\Purchase::where('lesson_id', $model->id)->where('status',
                    'complete')->count() !!} Purchased)</span>
            </div> --}}
        </div>

    </div>

    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
        <div class="flex flex-row justify-between">
            <!-- <div class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                {!! $subtitle !!}
            </div> -->
            @if ($model->is_package_lesson)
            <div class="bg-green-500 text-white text-sm font-bold px-2 py-1 rounded-full">
                Package
                Lesson
            </div>
            @endif
        </div>
       
        <span class="text-xl font-semibold text-dark">{!! $title !!}</span>
        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
            {!! \Illuminate\Support\Str::limit($description, 177,
            '<a onClick="javascript:showMore(this);" class="read-more" href="javascript:void(0);">...Read more >></a>'
            ).
            '<span class="more-content hidden">'.substr($description, 177).
            '<a onClick="javascript:showLess(this);" href="javascript:void(0);"> << Read less</a>'
            ."</span>" !!}
        </p>

        <div class="mt-auto bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
            <div class="text-center w-50">
                <span class="text-xl font-bold">{!! $model->lesson_quantity !!}</span>
                <div class="text-sm rtl:space-x-reverse">Number of <br> Lessons</div>

            </div>
            <div class="text-center w-50">
                <span class="text-xl font-bold">{!! $model->required_time !!} Days</span>
                <div class="text-sm rtl:space-x-reverse">Expected Response <br> Time</div>
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
            return $slot->follower->contains(Auth::id());
            });
            @endphp

            @if ($isAlreadyBooked)
            <button class="lesson-btn opacity-50 cursor-not-allowed" disabled>
                Already Enrolled
            </button>
            @else
            <button class="lesson-btn" onclick="openBookingPopup({{ json_encode($allSlots) }})">
                Purchase
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
                    <button class="lesson-btn">Purchase</button>
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

    const availableSeats = firstSlot.lesson.max_followers - firstSlot.follower.length;
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
            <label for="followerFriends"><strong>Book for Friends (Optional):</strong></label>
            <input type="text" id="followerFriends" class="form-control" placeholder="Enter friend names, separated by commas">
        </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Book Slot",
        cancelButtonText: "Cancel",
        preConfirm: () => {
            const friendNames = document.getElementById('followerFriends')?.value.trim();
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