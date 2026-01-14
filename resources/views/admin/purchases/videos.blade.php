{{-- @extends('layouts.main')
@section('title', __('Purchase User Details'))
@section('content')
    <div class="flex flex-col md:flex-row md:items-start justify-between border-b border-gray-400 pb-4 mb-5">
    </div>

    @php
        $purchaseVideo = $purchase->videos->first();
        $purchaseVideo2Url = $purchaseVideo->video_url_2 ?? '';
    @endphp

    <div class="flex flex-col xl:flex-row gap-6 bg-white p-4 rounded-lg">
        <!-- Video Section -->
        <div class="flex flex-col lg:flex-row gap-4 w-full xl:w-2/3">
             <div class="video-wrap lg:pr-4 lg:border-r border-gray-300 flex flex-col items-center lg:items-start">
                <video controls autoplay loop muted src="{{ $purchase->videos->first()->video_url }}"
                    class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg shadow"></video>

                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                        <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchase->videos->first()->video_url }}"
                            class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                            <i class="ti ti-search text-xl"></i> Analyze
                        </a>
                        

                     
                    </div>
                @endif

                @if ($purchaseVideo2Url)
                    <video controls autoplay loop muted src="{{ $purchaseVideo2Url }}"
                        class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg mt-4 shadow"></video>

                    @if (auth()->user()->type == 'Influencer')
                        <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                            <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchaseVideo2Url }}"
                                class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                                <i class="ti ti-search text-xl"></i> Analyze
                            </a>

                         
                        </div>
                    @endif
                @endif

            </div>

            <!-- Details Section -->
            <div class="mt-6 lg:mt-0 w-full lg:w-auto">
                <ul>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Lesson Name:</p>
                        <p class="text-lg font-semibold break-words">{{ $purchase->lesson->lesson_name }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Date Submitted:</p>
                        <p class="text-lg font-semibold">{{ $purchase->lesson->created_at }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Lesson Number:</p>
                        <p class="text-lg font-semibold">{{ $purchase->lesson->id }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Student Name:</p>
                        <p class="text-lg font-semibold">{{ $purchase->student->name }}</p>
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
        </div>

        <!-- Feedback Section -->
        <div class="feedback-sec border hidden border-gray-300 rounded-lg p-3 w-full xl:w-1/3 mt-6 xl:mt-0">
            <h2 class="font-bold text-2xl md:text-3xl mb-3 border-b border-gray-400 pb-2">Feedback Provided</h2>
            <div>
                <p class="text-lg text-gray-700 font-bold">{{ $purchase->lesson->created_at->format('F j, Y') }}</p>
                <p class="text-gray-500">
                    {{ auth()->user()->name == $purchase->student->name ? 'Your Note' : 'Note by ' . $purchase->student->name }}:
                </p>
                <p class="text-base md:text-lg font-semibold break-words">{{ $purchaseVideo->note }}</p>

                @if ($purchaseVideo->feedback)
                    <br>
                    <p class="text-gray-500">{{ auth()->user()->type == 'Influencer' ? 'Your Feedback' : 'Feedback' }}</p>
                    <p class="text-base md:text-lg font-semibold break-words">{{ $purchaseVideo->feedback }}</p>
                @endif

                <div class="flex flex-wrap items-start gap-3 mt-4">
                    @php
                        $feedbackContents = $purchase->videos->first()->feedbackContent ?? collect();
                    @endphp

                    @foreach ($feedbackContents as $index => $vid)
                        @if ($vid->url)
                            <img class="video-thumbnail w-32 h-20 object-cover rounded cursor-pointer border border-gray-300"
                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                data-target="#videoModal{{ $index }}">

                            <!-- Modal -->
                            <div id="videoModal{{ $index }}" class="modal">
                                <span class="close">&times;</span>
                                <div class="modal-content">
                                    <video class="video-player" controls>
                                        <source src="{{ $vid->url }}" type="video/mp4">
                                        Your browser does not support HTML5 video.
                                    </video>
                                </div>
                            </div>
                        @endif

                        @if ($vid->url_2)
                            <img class="video-thumbnail w-32 h-20 object-cover rounded cursor-pointer border border-gray-300"
                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                data-target="#videoModal{{ $index }}_2">

                            <!-- Modal -->
                            <div id="videoModal{{ $index }}_2" class="modal">
                                <span class="close">&times;</span>
                                <div class="modal-content">
                                    <video class="video-player" controls>
                                        <source src="{{ $vid->url_2 }}" type="video/mp4">
                                        Your browser does not support HTML5 video.
                                    </video>
                                </div>
                            </div>
                        @endif
                    @endforeach


                    @if (auth()->user()->type == 'Influencer')
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('purchase.feedback.create', ['purchase_id' => $purchase->id]) }}"
                                class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm md:text-base">
                                @if (trim($purchaseVideo->feedback))
                                    <i class="ti ti-pencil text-xl"></i> Edit Feedback
                                @else
                                    <i class="ti ti-plus text-xl"></i> Provide Feedback
                                @endif
                            </a>
                            @if (trim($purchaseVideo->feedback))
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
    </div>
@endsection

@push('css')
    <style>
        /* Mobile modal optimization */
        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            margin: auto;
            padding: 0;
            width: 95%;
            max-width: 700px;
            background-color: #fff;
            border-radius: 10px;
        }

        .modal-content video {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background-color: #0071ce;
            text-align: center;
            line-height: 28px;
        }

        @media (max-width: 768px) {
            .feedback-sec {
                margin-top: 2rem;
            }
        }
    </style>
@endpush

@push('javascript')
    <script>
        // const modal = document.getElementById("videoModal");
        // const modal2 = document.getElementById("videoModal2");

        // const thumbnail = document.getElementById("videoThumbnail");
        // const thumbnail2 = document.getElementById("videoThumbnail2");
        // const closeBtn = document.querySelector(".close");
        // const closeBtn2 = document.querySelector(".close2");
        // const video = document.getElementById("videoPlayer");
        // const video2 = document.getElementById("videoPlayer2");

        // if (thumbnail) {
        //     thumbnail.onclick = function() {
        //         modal.style.display = "block";
        //         video.play();
        //     }
        // }

        // if (thumbnail2) {
        //     thumbnail2.onclick = function() {
        //         modal2.style.display = "block";
        //         video2.play();
        //     }
        // }

        // if (closeBtn) {
        //     closeBtn.onclick = function() {
        //         modal.style.display = "none";
        //         video.pause();
        //         video.currentTime = 0;
        //     }
        // }

        // if (closeBtn2) {
        //     closeBtn2.onclick = function() {
        //         modal2.style.display = "none";
        //         video2.pause();
        //         video2.currentTime = 0;
        //     }
        // }

        // window.onclick = function(event) {
        //     if (event.target === modal) {
        //         modal.style.display = "none";
        //         video.pause();
        //         video.currentTime = 0;
        //     }

        //     if (event.target === modal2) {
        //         modal2.style.display = "none";
        //         video2.pause();
        //         video2.currentTime = 0;
        //     }
        // }

        document.querySelectorAll('.video-thumbnail').forEach(thumbnail => {
            const targetModalSelector = thumbnail.dataset.target;
            const modal = document.querySelector(targetModalSelector);
            const video = modal.querySelector('.video-player');
            const closeBtn = modal.querySelector('.close');

            // Open modal and play video
            thumbnail.addEventListener('click', () => {
                modal.style.display = 'block';
                video.play();
            });

            // Close modal on close button click
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                video.pause();
                video.currentTime = 0;
            });

            // Close modal when clicking outside the modal content
            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    video.pause();
                    video.currentTime = 0;
                }
            });
        });
    </script>
@endpush --}}


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
            <video controls autoplay loop muted src="{{ $purchase->videos->first()?->video_url }}"
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
                <video controls autoplay loop muted src="{{ $purchaseVideo2Url }}"
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

                {{-- @if (@$purchaseVideo->feedback)
                    <br>
                    <p class="text-gray-500">{{ auth()->user()->type == 'Influencer' ? 'Your Feedback' : 'Feedback' }}</p>
                    <p class="text-base md:text-lg font-semibold break-words">{{ @$purchaseVideo->feedback }}</p>
                @endif

                <div class="flex flex-wrap items-start gap-3 mt-4">
                    @php
                        $feedbackContents = $purchase->videos->first()->feedbackContent ?? collect();
                    @endphp

                    @foreach ($feedbackContents as $index => $vid)
                        @if ($vid->url)
                            <img class="video-thumbnail w-32 h-20 object-cover rounded cursor-pointer border border-gray-300"
                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                data-target="#videoModal{{ $index }}">

                            <div id="videoModal{{ $index }}" class="modal">
                                <span class="close">&times;</span>
                                <div class="modal-content">
                                    <video class="video-player" controls>
                                        <source src="{{ $vid->url }}" type="video/mp4">
                                        Your browser does not support HTML5 video.
                                    </video>
                                </div>
                            </div>
                        @endif

                        @if ($vid->url_2)
                            <img class="video-thumbnail w-32 h-20 object-cover rounded cursor-pointer border border-gray-300"
                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                data-target="#videoModal{{ $index }}_2">

                            <div id="videoModal{{ $index }}_2" class="modal">
                                <span class="close">&times;</span>
                                <div class="modal-content">
                                    <video class="video-player" controls>
                                        <source src="{{ $vid->url_2 }}" type="video/mp4">
                                        Your browser does not support HTML5 video.
                                    </video>
                                </div>
                            </div>
                        @endif
                    @endforeach --}}

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
                        // dd($purVid, $purVid->feedbackContent->first());
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

                    {{-- show all videos --}}
                    <div class="flex flex-wrap items-start gap-4 mt-4">
                        @foreach ($videos as $i => $vid)
                            <div class="inline-block m-2">
                                <img class="w-24 h-16 sm:w-32 sm:h-20 rounded cursor-pointer"
                                    src="{{ asset('assets/images/video-thumbanail.jpeg') }}"
                                    onclick="openVideoModal('{{ $i }}')">

                                @php
                                $test = 'https://tune-golf.nyc3.digitaloceanspaces.com/angusglen/71/jILOf9xGgVz9sVfMS2OfSS8gG.jpeg';
                                    $isImage = preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $test);
                                    // dd($isImage);
                                    $isVideo = preg_match('/\.(mp4|mov|avi|webm|mkv)$/i', $vid['url']);
                                @endphp

                                <div id="videoModal{{ $i }}" class="modal">
                                    <span class="close" onclick="closeVideoModal('{{ $i }}')">&times;</span>
                                    <div class="modal-content">
                                        @if ($isImage)
                                            <img src="{{ $vid['url'] }}" class="img-fluid" style="max-width:100%;">
                                        @elseif ($isVideo)
                                            <video id="videoPlayer{{ $i }}" controls>
                                                <source src="{{ $vid['url'] }}">
                                            </video>
                                        @else
                                            <p>Unsupported file type</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- MULTI FEEDBACK MODE (new data) --}}
                @if ($isMulti)
                    <div class="flex flex-col gap-6 mt-4">
                        @foreach ($videos as $i => $vid)
                            <div>
                                {{-- Video --}}
                                <img class="w-24 h-16 sm:w-32 sm:h-20 rounded cursor-pointer"
                                    src="{{ asset('assets/images/video-thumbanail.jpeg') }}"
                                    onclick="openVideoModal('{{ $i }}')">

                                {{-- Note for this video --}}
                                <p class="text-lg sm:text-xl font-semibold break-words mt-2">
                                    {{ $feedbackArray[$i] ?? '' }}
                                </p>
                                @php
                                // $test = 'https://tune-golf.nyc3.digitaloceanspaces.com/angusglen/71/jILOf9xGgVz9sVfMS2OfSS8gG.jpeg';
                                    // $isImage = preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $test);
                                    // dd($isImage);
                                    $isImage = preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $vid['url']);
                                    $isVideo = preg_match('/\.(mp4|mov|avi|webm|mkv)$/i', $vid['url']);
                                    // dd($vid["url"], $videos);
                                @endphp
                                {{-- Modal --}}
                                <div id="videoModal{{ $i }}" class="modal">
                                    <span class="close" onclick="closeVideoModal('{{ $i }}')">&times;</span>
                                    <div class="modal-content">
                                        @if ($isImage)
                                            <img src="{{ $vid['url'] }}" class="img-fluid" style="max-width:100%;">
                                        @elseif ($isVideo)
                                            <video id="videoPlayer{{ $i }}" controls>
                                                <source src="{{ $vid['url'] }}">
                                            </video>
                                        @else
                                            <p>Unsupported file type</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif


                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-col sm:flex-row gap-2 mt-4">
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
    </div>
@endsection

@push('css')
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            margin: auto;
            padding: 0;
            width: 95%;
            max-width: 700px;
            background-color: #fff;
            border-radius: 10px;
        }

        .modal-content video {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background-color: #0071ce;
            text-align: center;
            line-height: 28px;
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

{{-- @push('javascript')
    <script>
        document.querySelectorAll('.video-thumbnail').forEach(thumbnail => {
            const targetModalSelector = thumbnail.dataset.target;
            const modal = document.querySelector(targetModalSelector);
            const video = modal.querySelector('.video-player');
            const closeBtn = modal.querySelector('.close');

            thumbnail.addEventListener('click', () => {
                modal.style.display = 'block';
                video.play();
            });

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                video.pause();
                video.currentTime = 0;
            });

            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    video.pause();
                    video.currentTime = 0;
                }
            });
        });
    </script>
@endpush --}}

@push('javascript')
    <script>
        function openVideoModal(index) {
            document.getElementById('videoModal' + index).style.display = 'block';
        }

        function closeVideoModal(index) {
            const modal = document.getElementById('videoModal' + index);
            const video = document.getElementById('videoPlayer' + index);

            modal.style.display = 'none';
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = "none";
                    const video = modal.querySelector("video");
                    if (video) {
                        video.pause();
                        video.currentTime = 0;
                    }
                }
            });
        });
    </script>
@endpush
