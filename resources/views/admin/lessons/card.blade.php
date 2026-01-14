<style>
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

    .longDescContent table {
        width: 100% !important;
    }

    .longDescContent table {
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
    }

    .longDescContent th,
    .longDescContent td {
        border: 1px solid #000;
        padding: 6px 10px;
        text-align: left;
    }

    .short-text ul {
        margin-bottom: 0px !important;
    }
</style>
@props([
    'image' => '',
    'title' => '',
    'subtitle' => '',
    'short_description' => '',
    'long_description' => '',
    'withBackground' => false,
    'model',
    'packages',
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
                {!! \Illuminate\Support\Str::limit(ucfirst($model?->user?->name), 80, '...') !!}
            </a>
            <div class="text-lg font-bold tracking-tight text-primary">
                {!! $subtitle !!}
            </div>
            <div class="text-sm font-medium text-gray-500 italic">
                
            </div>
        </div>
    </div>

    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">

        <span class="text-xl font-semibold text-dark mb-2">{!! $title !!}</span>

        @php
            $description1 = html_entity_decode($short_description);
            $cleanDescription = strip_tags($description1, '<ul><ol><li><span><a><strong><em><b><i>');
            $cleanShortDescription = strip_tags($description, '<ul><ol><li><strong><b><i>');
            $cleanShortDescription = \Illuminate\Support\Str::limit($cleanShortDescription, 80, '...');
        @endphp


        {{-- <div class="text-gray-500 text-md description font-medium ctm-min-h"> --}}
        @if (!empty($cleanDescription))
            <div class="hidden short-text text-gray-600">
                {!! $cleanDescription !!}
            </div>
        @endif
        
        <div class="description-wrapper relative expanded mb-2">
           
            <div class="hidden long-text text-gray-600" style="font-size: 15px; max-height: 100px; overflow-y: auto;">
                {!! $description !!}
            </div>
            <a href="javascript:void(0)" data-long_description="{{ e($description) }}"
                class="text-blue-600 font-medium mt-1 inline-block viewDescription" tabindex="0">
                View Description
            </a>
           

        </div>
    

        @if ($model->type == 'online')
            <div class="mt-auto bg-gray-200 gap-1 rounded-lg px-4 py-3">
                
                <div class="text-center">
                    <span class="text-xl font-bold">{!! $model->required_time !!} Days</span>
                    <div class="text-sm rtl:space-x-reverse">Expected Response <br> Time</div>

                </div>
            </div>
        @endif
    </div>
    <div class="w-100 px-3">
        @if ($model->type === 'online')
            {!! Form::open([
                'route' => ['purchase.store', ['lesson_id' => $model->id]],
                'method' => 'Post',
                'enctype' => 'multipart/form-data',
                'class' => 'form-horizontal',
                'data-validate',
            ]) !!}
            {{ Form::button(__('Purchase'), ['type' => 'submit', 'class' => 'lesson-btn mb-2']) }}
            {!! Form::close() !!}
        @endif
    </div>
   <form id="bookingForm" method="POST" action="{{ route('slot.book', ['redirect' => 1]) }}">
        @csrf
        <input type="hidden" id="slotIdInput" name="slot_id">
        <input type="hidden" id="friendNamesInput" name="friend_names">

    </form>

    <div class="modal" id="longDescModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold" style="font-size: 20px">Description</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeLongDescModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="longDescContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @dump($model->is_package_lesson) --}}
@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.viewDescription', function() {
            const parent = $(this).closest('.description-wrapper');
            const shortDesc = parent.closest('.w-full').find('.short-text').html() || '';
            const longDesc = parent.find('.long-text').html() || '';

            let modalHtml = '';

            if (shortDesc) {
            
                modalHtml += `
                    <div class="shortDescSection border-b mb-4">
                        
                        <div class="shortDesc text-gray-700" style="font-size:15px; line-height:1.6;">
                            ${shortDesc}
                        </div>
                    </div>
                `;
            }

            if (longDesc && longDesc.trim() !== '') {
             
                modalHtml += `
                    <div class="longDescSection mt-4">
                      
                        <div class="longDesc text-gray-800" style="font-size:15px; line-height:1.6;">
                            ${longDesc}
                        </div>
                    </div>
                `;
            }

            $('#longDescModal').modal('show');
            $('.longDescContent').html(modalHtml);
        });

        function closeLongDescModal() {
            $('#longDescModal').modal('hide');
        }

        function toggleDescription(button, event) {
            event.stopPropagation();
            let parent = button.closest('.description');
            let shortText = parent.querySelector('.short-text');
            let fullText = parent.querySelector('.full-text');

            parent.style.display = 'block';

            if (!shortText || !fullText) {
                console.error('Short text or full text element not found in .description', {
                    parent,
                    shortText,
                    fullText
                });
                return;
            }

            if (shortText.classList.contains('hidden')) {
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "View Lesson Description";
            } else {
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "Show Less";
            }
        }

      
        
    </script>
@endpush
