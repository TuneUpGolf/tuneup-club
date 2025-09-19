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


        @php
            $description1 = html_entity_decode($short_description);

            $cleanDescription = strip_tags($description1, '<ul><ol><li><span><a><strong><em><b><i>');
            $cleanShortDescription = strip_tags($description1, '<ul><ol><li><strong><b><i>');
            $shortDescription = \Illuminate\Support\Str::limit($cleanShortDescription, 80, '...');
        @endphp
        <div class="text-gray-500 text-md description font-medium ctm-min-h p-2">
            <div class="short-text clamp-text font-thin text-gray-600 mb-2">
                {!! $shortDescription !!}
            </div>
            @if (!empty($description1) && strlen(strip_tags($description1)) > 80)
                <div class="hidden full-text text-gray-600"
                    style="font-size: 15px; max-height: auto; overflow-y: auto;">
                    {!! $short_description !!}
                </div>
                <a href="javascript:void(0);" style="font-size: 15px"
                    class="text-blue-600 toggle-read-more font-semibold" onclick="toggleDescription(this, event)">View
                    Lesson Description</a>
            @else
                <div class="hidden full-text text-gray-600"
                    style="font-size: 15px; max-height: auto; overflow-y: auto;">
                    {!! $short_description !!}
                </div>
            @endif
        </div>

        <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
            @if (!empty($description))
                <div class="description-wrapper relative">
                    <div class="hidden long-text text-gray-600" style="font-size: 15px; max-height: 100px; ">
                        {!! $description !!}
                    </div>
                    <a href="javascript:void(0)" style="font-size: 15px" data-long_description="{!! $description !!}"
                        class="text-blue-600 font-medium mt-1 inline-block viewDescription" tabindex="0">View
                        Description</a>
                </div>
            @endif
        </div>

        <div class="mt-auto bg-gray-200 gap-1 rounded-lg px-4 py-3">
            <div class="text-center">
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
<div class="modal" id="longDescModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title font-bold" style="font-size: 20px">Long Description</h1>
                <button type="button"
                    class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                    onclick="closeLongDescModal()" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="word-break: break-all;">
                <div class="longDescContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
            </div>
        </div>
    </div>
</div>
@push('css')
    <style>
        .lessions-slider .slick-track {
            display: flex !important;
        }

        .lessions-slider .slick-slide {
            height: inherit !important;
        }

        .read-more-btn {
            transition: all 0.3s ease;
        }

        .read-more-btn:hover {
            transform: translateY(-1px);
        }

        #instructorPopup {
            transition: opacity 0.3s ease;
        }

        #instructorPopup.show {
            opacity: 1;
        }

        .description ul,
        .description ol {
            list-style-type: disc;
            margin-left: 20px;
            padding-left: 20px;
        }

        .description li {
            display: list-item;
            margin-bottom: 5px;
        }

        .description b,
        .description strong {
            font-weight: bold;
        }

        .description i,
        .description em {
            font-style: italic;
        }

        .description {
            display: block !important;
        }

        .hidden {
            display: none;
        }

        .longDescContent ul {
            list-style: disc;
            padding-left: 1.5rem;
        }
    </style>
@endpush
@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleDescription(button, event) {
            event.stopPropagation();
            // match the wrapper class used in HTML
            const parent = button.closest('.description');
            const shortText = parent.querySelector('.short-text');
            const fullText = parent.querySelector('.full-text');

            if (!shortText || !fullText) {
                console.error('Short text or full text element not found', {
                    parent,
                    shortText,
                    fullText
                });
                return;
            }

            if (shortText.classList.contains('hidden')) {
                // show short text, hide full text
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "View Lesson Description";
            } else {
                // hide short text, show full text
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "Show Less";
            }
        }
        $(document).on('click', '.viewDescription', function() {
            const desc = $(this).siblings('.long-text').html();
            $('#longDescModal').modal('show');
            $('.longDescContent').html(desc);
        })

        function closeLongDescModal() {
            $('#longDescModal').modal('hide');
        }

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
