@extends('layouts.main')
@section('title', __('Purchase User Details'))

@section('content')
    <div class="flex items-start justify-between border-b border-gray-400 pb-4 mb-5"></div>

    @php
        $purchaseVideo = $purchase->videos->first();
        $purchaseVideo2Url = $purchaseVideo->video_url_2 ?? '';
    @endphp

    <div class="bg-white p-4 rounded-lg flex flex-col lg:flex-row gap-6">
        {{-- Left Column (Videos + Info) --}}
        <div class="flex flex-col lg:flex-row gap-6 w-full lg:w-1/2">
            <div class="video-wrap border-b lg:border-b-0 lg:border-r border-gray-300 pb-4 lg:pb-0 lg:pr-4">
                <video controls autoplay loop muted src="{{ $purchase->videos->first()->video_url }}"
                    class="w-full h-auto rounded-lg object-cover mb-4"></video>

                @if ($purchaseVideo2Url)
                    <video controls autoplay loop muted src="{{ $purchaseVideo2Url }}"
                        class="w-full h-auto rounded-lg object-cover mb-4"></video>
                @endif

                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideo->video_url]) }}"
                            class="btn btn-warning rounded-full px-4 py-2 text-white font-bold flex items-center gap-2">
                            <i class="ti ti-notebook text-2xl"></i> Feedback
                        </a>
                        <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchase->videos->first()->video_url }}"
                            class="btn btn-danger rounded-full px-4 py-2 text-white font-bold flex items-center gap-2">
                            <i class="ti ti-search text-2xl"></i> Analyze
                        </a>
                    </div>
                @endif
            </div>

            {{-- Lesson Details --}}
            <div class="w-full lg:w-1/2">
                <ul class="space-y-4">
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Lesson Name:</p>
                        <p class="text-lg sm:text-xl font-semibold break-words">{{ $purchase->lesson->lesson_name }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Date Submitted:</p>
                        <p class="text-lg sm:text-xl font-semibold">{{ $purchase->lesson->created_at }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Lesson Number:</p>
                        <p class="text-lg sm:text-xl font-semibold">{{ $purchase->lesson->id }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Follower Name:</p>
                        <p class="text-lg sm:text-xl font-semibold">{{ $purchase->follower->name }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Payment:</p>
                        <p class="text-lg sm:text-xl font-semibold">${{ $purchase->lesson->lesson_price }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm sm:text-base">Payment Status:</p>
                        <div
                            class="btn btn-success rounded-full px-4 py-2 text-white font-bold inline-flex items-center gap-1">
                            <i class="ti ti-check text-2xl"></i>
                            <span>{{ $purchase->lesson->payment_method }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Right Column (Feedback Section) --}}
        <div class="feedback-sec border border-gray-300 rounded-lg p-4 w-full lg:w-1/2">
            <h2 class="font-bold text-2xl sm:text-3xl mb-3 border-b border-gray-400 pb-2">Feedback Provided</h2>
            <p class="text-lg sm:text-2xl text-gray-700 font-bold">{{ $purchase->lesson->created_at->format('F j, Y') }}
            </p>
            <p class="text-gray-500">
                {{ auth()->user()->name == $purchase->follower->name ? 'Your Note' : 'Note by ' . $purchase->follower->name }}:
            </p>
            <p class="text-lg sm:text-xl font-semibold break-words">{{ $purchaseVideo->note }}</p>

            @if ($purchaseVideo->feedback)
                <br>
                <p class="text-gray-500">{{ auth()->user()->type == 'Influencer' ? 'Your Feedback' : 'Feedback' }}</p>
                <p class="text-lg sm:text-xl font-semibold break-words">{{ $purchaseVideo->feedback }}</p>
            @endif

            {{-- Feedback Video and Actions --}}
            <div class="flex flex-wrap items-start gap-4 mt-4">
                @if ($purVid = $purchase->videos->first())
                    @if ($vid = $purVid->feedbackContent->first())

                        @php
                            $videos = $purVid->feedbackContent->first()?->url;

                            if ($videos) {
                                // Check if JSON
                                $decoded = json_decode($videos, true);
                                $videos = is_array($decoded) ? $decoded : [['url' => $videos, 'type' => 'video']];
                            } else {
                                $videos = [];
                            }
                        @endphp

                        @foreach ($videos as $index => $vid)
                            <div class="inline-block m-2">
                                <!-- Thumbnail -->
                                <img class="w-24 h-16 sm:w-32 sm:h-20 rounded cursor-pointer"
                                    src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                    data-video="{{ $vid['url'] ?? ($vid['url'] ?? '') }}"
                                    onclick="openVideoModal('{{ $index }}')">

                                <!-- Modal -->
                                <div id="videoModal{{ $index }}" class="modal">
                                    <span class="close" onclick="closeVideoModal('{{ $index }}')">&times;</span>
                                    <div class="modal-content">
                                        <video id="videoPlayer{{ $index }}" controls>
                                            <source src="{{ $vid['url'] ?? ($vid['url'] ?? '') }}" type="video/mp4">
                                            Your browser does not support HTML5 video.
                                        </video>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endif

                {{-- Action Buttons --}}
                @if (auth()->user()->type == 'Influencer')
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideo->video_url]) }}"
                            class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm sm:text-base">
                            @if (trim($purchaseVideo->feedback))
                                <i class="ti ti-pencil text-2xl"></i> Edit Feedback
                            @else
                                <i class="ti ti-plus text-2xl"></i> Provide Feedback
                            @endif
                        </a>
                        @if (trim($purchaseVideo->feedback))
                            <form action="{{ route('purchase.feedback.delete', $purchaseVideo->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm sm:text-base">
                                    <i class="ti ti-trash text-2xl"></i> Delete
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
        #videoThumbnail {
            cursor: pointer;
        }

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
            max-width: 800px;
            background-color: #fff;
            border-radius: 10px;
            max-height: 80vh;
            overflow: hidden;
        }

        .modal-content video {
            width: 100%;
            height: auto;
            max-height: 80vh;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            height: 30px;
            width: 30px;
            border-radius: 100px;
            background-color: #0071ce;
            text-align: center;
            line-height: 30px;
        }

        .close:hover {
            color: #000;
        }

        /* Responsive Tweaks */
        @media (max-width: 1024px) {
            .video-wrap {
                border-right: none !important;
                border-bottom: 1px solid #ccc !important;
                padding-bottom: 1rem;
            }
        }

        @media (max-width: 768px) {
            .feedback-sec {
                margin-top: 1.5rem;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.4rem 0.8rem;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.4rem;
            }

            p,
            span {
                font-size: 0.9rem;
            }

            .btn {
                font-size: 0.8rem;
            }
        }
    </style>
@endpush

@push('javascript')
    <script>
        function openVideoModal(index) {
            document.getElementById('videoModal' + index).style.display = 'block';
        }

        function closeVideoModal(index) {
            const modal = document.getElementById('videoModal' + index);
            const video = document.getElementById('videoPlayer' + index);
            modal.style.display = 'none';
            video.pause(); // stop video when modal closes
        }

        // Optional: click outside modal to close
        window.onclick = function(event) {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = "none";
                    const vid = modal.querySelector('video');
                    if (vid) vid.pause();
                }
            });
        }
    </script>
    <script>
        const modal = document.getElementById("videoModal");
        const thumbnail = document.getElementById("videoThumbnail");
        const closeBtn = document.querySelector(".close");
        const video = document.getElementById("videoPlayer");

        if (thumbnail) {
            thumbnail.onclick = function() {
                modal.style.display = "block";
                video.play();
            }
        }

        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = "none";
                video.pause();
                video.currentTime = 0;
            }
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
                video.pause();
                video.currentTime = 0;
            }
        }
    </script>
@endpush
