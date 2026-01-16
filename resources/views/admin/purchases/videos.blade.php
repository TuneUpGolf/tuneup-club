@extends('layouts.main')
@section('title', __('Online Submission Details'))
@section('content')
    @php
        $purchaseVideo = $purchase->videos->first();
        $purchaseVideo2Url = $purchaseVideo->video_url_2 ?? '';
    @endphp

    <div class="flex flex-col md:flex-row gap-6 bg-white p-4 rounded-lg">

        <!-- Video Section -->
        <div class="video-wrap w-full md:w-1/4 lg:w-1/5 xl:w-1/5 !order-1">
            <video controls autoplay loop muted src="{{ $purchase->videos->first()?->video_url }}#t=0.001"
                class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg shadow"></video>

            @if (auth()->user()->type == 'Influencer')
                <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                    <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchase->videos->first()?->video_url }}"
                        class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                        <i class="ti ti-search text-xl"></i> Analyze
                    </a>

                     <a href="{{ route('streamM3U8ToMov', $purchase->id) }}"
                            class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                            <i class="ti ti-download text-xl"></i> Download
                    </a>

                   
                </div>
            @endif

            @if ($purchaseVideo2Url)
                <video controls autoplay loop muted src="{{ $purchaseVideo2Url }}#t=0.001"
                    class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg mt-4 shadow"></video>

                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                        <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchaseVideo2Url }}"
                            class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                            <i class="ti ti-search text-xl"></i> Analyze
                        </a>

                        <a href="{{ route('streamM3U8ToMov2', $purchase->id) }}"
                                class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                                <i class="ti ti-download text-xl"></i> Download
                        </a>
                    </div>
                @endif
            @endif
        </div>

        <!-- Lesson Details Section -->
        <div class="details-sec w-full md:w-5/12 lg:w-9/20 xl:w-9/20 !order-3 md:!order-2">
            <ul>
                <li class="mb-3">
                    <p class="text-gray-500 text-sm">Lesson Name:</p>
                    <p class="text-lg font-semibold break-words">{{ $purchase->lesson->lesson_name }}</p>
                </li>
                <li class="mb-3">
                    <p class="text-gray-500 text-sm">Date Submitted:</p>
                    <p class="text-lg font-semibold">{{ $purchase->created_at }}</p>
                </li>
                <li class="mb-3">
                    <p class="text-gray-500 text-sm">Lesson Number:</p>
                    <p class="text-lg font-semibold">{{ $purchase->lesson->id }}</p>
                </li>
                <li class="mb-3">
                    <p class="text-gray-500 text-sm">Student Name:</p>
                    <p class="text-lg font-semibold">{{ $purchase->follower->name }}</p>
                </li>
                <li class="mb-3">
                    <p class="text-gray-500 text-sm">Payment:</p>
                    <p class="text-lg font-semibold">${{ $purchase->lesson->lesson_price }}</p>
                </li>
                <li>
                    <p class="text-gray-500 text-sm">Payment Status:</p>
                    <div
                        class="rounded-full px-4 py-1 inline-flex items-center gap-1 bg-green-600 text-white font-semibold text-sm">
                        <i class="ti ti-check text-lg"></i>
                        <span>{{ $purchase->lesson->payment_method }}</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Feedback Section -->
        <div class="feedback-sec w-full !order-2 md:w-1/3 lg:w-7/20 xl:w-7/20 md:!order-3">
            <h2 class="font-bold text-2xl md:text-3xl mb-3 border-b border-gray-400 pb-2">Feedback Provided</h2>
            <div>
                <p class="text-lg text-gray-700 font-bold">{{ @$purchase->created_at->format('F j, Y') }}</p>
                <p class="text-gray-500">
                    {{ auth()->user()->name == $purchase->follower->name ? 'Your Note' : 'Note by ' . $purchase->follower->name }}:
                </p>
                <p class="text-base md:text-lg font-semibold break-words">{{ @$purchaseVideo->note }}</p>

               

                @php
                    $feedbackRaw = $purchaseVideo->feedback ?? null;
                    $feedbackArray = null;
                    $isMulti = false;

                    // Detect JSON array feedback
                    if ($feedbackRaw) {
                        $decoded = json_decode($feedbackRaw, true);
                        if (is_array($decoded)) {
                            $feedbackArray = $decoded;
                            $isMulti = true;
                        }
                    }

                    // Prepare videos
                    $videos = [];
                    if ($purVid = $purchase->videos->first()) {
                        if ($content = $purVid->feedbackContent->first()) {
                            $rawVideos = $content->url;
                            $decodedVideos = json_decode($rawVideos, true);

                            if (is_array($decodedVideos)) {
                                $videos = $decodedVideos; // array mode
                            } else {
                                $videos = [['url' => $rawVideos, 'type' => 'video']]; // single video mode
                            }
                        }
                    }
                @endphp

                {{-- SINGLE FEEDBACK MODE (old data) --}}
                @if (!$isMulti && $feedbackRaw)
                    <br>
                    <p class="text-gray-500">
                        {{ auth()->user()->type == 'Influencer' ? 'Your Feedback' : 'Feedback' }}
                    </p>
                    <p class="text-lg sm:text-xl font-semibold break-words">{{ $feedbackRaw }}</p>

                    {{-- show all videos directly --}}
                    <div class="mt-4 space-y-4">
                        @foreach ($videos as $i => $vid)
                            @php
                                $isImage = preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $vid['url']);
                                $isVideo = preg_match('/\.(mp4|mov|avi|webm|mkv)$/i', $vid['url']);
                            @endphp
                            
                            <div class="feedback-media-item">
                                @if ($isImage)
                                    <img src="{{ $vid['url'] }}" 
                                         class="w-full max-w-md rounded-lg shadow"
                                         alt="Feedback image">
                                @elseif ($isVideo)
                                    <video controls 
                                           class="w-full max-w-md rounded-lg shadow"
                                           src="{{ $vid['url'] }}#t=0.001">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <p class="text-red-500">Unsupported file type</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- MULTI FEEDBACK MODE (new data) --}}
                @if ($isMulti)
                    <div class="mt-4 space-y-6">
                        @foreach ($videos as $i => $vid)
                            @php
                                $isImage = preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $vid['url']);
                                $isVideo = preg_match('/\.(mp4|mov|avi|webm|mkv)$/i', $vid['url']);
                            @endphp
                            
                            <div class="feedback-item border-b border-gray-200 pb-4">
                                {{-- Video/Image displayed directly --}}
                                <div class="mb-2">
                                    @if ($isImage)
                                        <img src="{{ $vid['url'] }}" 
                                             class="w-full max-w-md rounded-lg shadow"
                                             alt="Feedback image">
                                    @elseif ($isVideo)
                                        <video controls 
                                               class="w-full max-w-md rounded-lg shadow"
                                               src="{{ $vid['url'] }}#t=0.001">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <p class="text-red-500">Unsupported file type</p>
                                    @endif
                                </div>
                                
                                {{-- Note for this video --}}
                                <p class="text-lg sm:text-xl font-semibold break-words mt-2">
                                    {{ $feedbackArray[$i] ?? '' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif


                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-col sm:flex-row gap-2 mt-6">
                        <a href="{{ route('purchase.feedback.edit', ['purchase_id' => $purchase->id]) }}"
                            class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm md:text-base">
                            @if (trim($purchaseVideo?->feedback))
                                <i class="ti ti-pencil text-xl"></i> Edit Feedback
                            @else
                                <i class="ti ti-plus text-xl"></i> Provide Feedback
                            @endif
                        </a>

                        @if (trim($purchaseVideo?->feedback))
                            <form action="{{ route('purchase.feedback.delete', $purchaseVideo) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm md:text-base">
                                    <i class="ti ti-trash text-xl"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Remove modal styles since we're showing videos directly */
        .feedback-media-item,
        .feedback-item {
            margin-bottom: 1.5rem;
        }
        
        .feedback-media-item video,
        .feedback-item video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .feedback-media-item img,
        .feedback-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .feedback-sec {
                order: 2;
                /* Feedback after videos on mobile */
            }

            .details-sec {
                order: 3;
                /* Lesson details at bottom on mobile */
            }
        }
    </style>
@endpush

{{-- Remove JavaScript since we don't need modal functionality anymore --}}
@push('javascript')
    <script>
        // No modal scripts needed since videos are displayed directly
        console.log('Feedback videos displayed inline');
    </script>
@endpush