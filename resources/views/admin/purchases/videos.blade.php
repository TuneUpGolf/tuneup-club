@extends('layouts.main')
@php
    $title = $purchase->lesson->type == 'online' ? 'Online Submission Details' : 'In-Person Lesson Details';
@endphp
@section('title', $title)
@section('content')
    @php
        $purchaseVideo = $purchase->videos->first();
        $purchaseVideo2Url = $purchaseVideo->video_url_2 ?? '';
    @endphp

    <div class="flex flex-col md:flex-row gap-6 bg-white p-4 rounded-lg">

        <!-- Video Section -->
        @if ($purchase->lesson->type == 'online')
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
                            <!-- Analyze Button -->
                            <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchaseVideo2Url }}"
                                class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                                <i class="ti ti-search text-xl"></i> Analyze
                            </a>

                            <!-- Download Button -->
                            <a href="{{ route('streamM3U8ToMov2', $purchase->id) }}"
                                class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                                <i class="ti ti-download text-xl"></i> Download
                            </a>
                        </div>
                    @endif
                @endif

            </div>
        @endif

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

                    // Helper function to detect file type
                    function getMediaType($url)
                    {
                        $url = strtolower($url);

                        // Check for HLS playlist
                        if (str_contains($url, '.m3u8')) {
                            return 'hls';
                        }

                        // Check for images
                        if (preg_match('/\.(jpeg|jpg|png|gif|webp|bmp|svg)$/i', $url)) {
                            return 'image';
                        }

                        // Check for regular videos
                        if (preg_match('/\.(mp4|mov|avi|webm|mkv|flv|wmv|mpeg|mpg)$/i', $url)) {
                            return 'video';
                        }

                        // Default
                        return 'unknown';
                    }
                @endphp

                {{-- SINGLE FEEDBACK MODE (old data) --}}
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
                            @php
                                $url = $vid['url'] ?? $vid;
                                $mediaType = getMediaType($url);
                            @endphp
                            <div class="inline-block m-2">
                                <div class="relative cursor-pointer mb-2" onclick="openMediaModal('{{ $i }}')">
                                    @if ($mediaType === 'image')
                                        <img class="w-24 h-16 sm:w-32 sm:h-20 rounded object-cover"
                                            src="{{ $url }}" alt="Feedback image" data-type="image"
                                            data-src="{{ $url }}">
                                    @elseif ($mediaType == 'unknown')
                                    @else
                                        <div class="relative" data-type="{{ $mediaType }}"
                                            data-src="{{ $url }}">
                                            <img class="w-24 h-16 sm:w-32 sm:h-20 rounded object-cover"
                                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}"
                                                alt="Video thumbnail">
                                            <div
                                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded">
                                                <i class="ti ti-player-play text-white text-3xl"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Download button for video files --}}
                                @if ($mediaType === 'video' || $mediaType === 'hls')
                                    <a href="{{ $url }}" download
                                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-1 px-2 rounded text-xs sm:text-sm transition-colors duration-200"
                                        title="Download video">
                                        <i class="ti ti-download text-sm mr-1"></i> Download
                                    </a>
                                @endif

                                {{-- Download button for images --}}
                                @if ($mediaType === 'image')
                                    <a href="{{ $url }}" download
                                        class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-1 px-2 rounded text-xs sm:text-sm transition-colors duration-200"
                                        title="Download image">
                                        <i class="ti ti-download text-sm mr-1"></i> Download
                                    </a>
                                @endif

                                <div id="mediaModal{{ $i }}" class="modal">
                                    <span class="close" onclick="closeMediaModal('{{ $i }}')">&times;</span>
                                    <div class="modal-content">
                                        {{-- Modal content will be dynamically loaded --}}
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
                            @php
                                $url = $vid['url'] ?? '';
                                $mediaType = getMediaType($url);
                                $feedbackNote = $feedbackArray[$i] ?? '';
                            @endphp
                            <div class="feedback-item border-b border-gray-200 pb-4 last:border-b-0">
                                {{-- Media Thumbnail and Download Button Container --}}
                                <div class="flex items-start gap-4 mb-2">
                                    {{-- Media Thumbnail --}}
                                    <div class="relative cursor-pointer" onclick="openMediaModal('{{ $i }}')">
                                        @if ($mediaType === 'image')
                                            <img class="w-24 h-16 sm:w-32 sm:h-20 rounded object-cover"
                                                src="{{ $url }}" alt="Feedback image" data-type="image"
                                                data-src="{{ $url }}">
                                                @elseif($mediaType === 'unknown')
                                        @else
                                            <div class="relative" data-type="{{ $mediaType }}"
                                                data-src="{{ $url }}">
                                                <img class="w-24 h-16 sm:w-32 sm:h-20 rounded object-cover"
                                                    src="{{ asset('assets/images/video-thumbanail.jpeg') }}"
                                                    alt="Video thumbnail">
                                                <div
                                                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded">
                                                    <i class="ti ti-player-play text-white text-3xl"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Download Button --}}
                                    @if ($url && ($mediaType === 'video' || $mediaType === 'hls' || $mediaType === 'image'))
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ $url }}" download
                                                class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors duration-200 shadow-sm"
                                                title="Download {{ $mediaType === 'image' ? 'image' : 'video' }}">
                                                <i class="ti ti-download text-lg mr-2"></i>
                                                Download {{ $mediaType === 'image' ? 'Image' : 'Video' }}
                                            </a>

                                            {{-- Alternative for HLS --}}
                                            @if ($mediaType === 'hls')
                                                <p class="text-xs text-gray-500 max-w-xs">
                                                    HLS video - download may require special tools or you can use browser
                                                    developer tools.
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Note for this video --}}
                                <p class="text-lg sm:text-xl font-semibold break-words mt-2">
                                    {{ $feedbackNote }}
                                </p>
                            </div>

                            {{-- Modal --}}
                            <div id="mediaModal{{ $i }}" class="modal">
                                <span class="close" onclick="closeMediaModal('{{ $i }}')">&times;</span>
                                <div class="modal-content">
                                    {{-- Modal content will be dynamically loaded by JavaScript --}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (auth()->user()->type == 'Influencer')
                    @php
                        if (trim($purchaseVideo?->feedback)) {
                            $routeName = 'purchase.feedback.edit';
                        } else {
                            $routeName = 'purchase.feedback.create';
                        }
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-2 mt-4">
                        <a href="{{ route($routeName, ['purchase_id' => $purchase->id]) }}"
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
            padding: 20px;
            width: 95%;
            max-width: 700px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            min-height: 200px;
        }

        .modal-content video {
            width: 100%;
            height: auto;
            max-height: 80vh;
            border-radius: 10px;
            background-color: #000;
        }

        .modal-content img {
            width: 100%;
            height: auto;
            max-height: 80vh;
            border-radius: 10px;
            object-fit: contain;
        }

        .hls-player-container {
            width: 100%;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .hls-video {
            width: 100%;
            height: auto;
            max-height: 80vh;
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
            z-index: 100000;
        }

        .feedback-item {
            transition: all 0.3s ease;
        }

        .feedback-item:hover {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 8px;
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

            .modal-content {
                width: 98%;
                padding: 10px;
                margin-top: 20px;
            }

            .close {
                top: 5px;
                right: 10px;
            }
        }
    </style>
@endpush

@push('javascript')
    <!-- Include HLS.js library -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <script>
        // Object to store HLS player instances
        const hlsPlayers = {};
        const regularVideoPlayers = {};

        // Function to detect if browser is Chrome
        function isChrome() {
            return /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
        }

        // Function to check if MOV can be played in current browser
        function canPlayMov(videoElement) {
            const canPlay = videoElement.canPlayType('video/quicktime');
            return canPlay === 'probably' || canPlay === 'maybe';
        }

        // Function to check if it's a .mov file
        function isMovFile(url) {
            return url.toLowerCase().endsWith('.mov');
        }

        // Function to show download fallback for unsupported files
        function showDownloadFallback(mediaSrc, modalContent) {
            modalContent.innerHTML = `
                <div style="padding: 30px; text-align: center;">
                    <div style="font-size: 48px; color: #f59e0b; margin-bottom: 20px;">
                        <i class="ti ti-download"></i>
                    </div>
                    <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 10px;">
                        Video Download Required
                    </h3>
                    <p style="margin-bottom: 15px; color: #666;">
                        This video format (.mov) requires download to play in your browser.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 10px; align-items: center;">
                        <a href="${mediaSrc}" download 
                           class="btn btn-primary" 
                           style="padding: 12px 24px; text-decoration: none; color: white; border-radius: 5px; background-color: #0071ce; font-weight: bold;">
                            <i class="ti ti-download"></i> Download Video
                        </a>
                        <p style="font-size: 14px; color: #888; max-width: 400px; margin-top: 10px;">
                            After downloading, open the file with VLC, QuickTime Player, or Windows Media Player.
                        </p>
                    </div>
                </div>
            `;
        }

        // Function to try playing MOV with workarounds
        function tryPlayMovWorkaround(videoElement, mediaSrc, index, modalContent) {
            // Test 1: Try with video/mp4 type (some .mov files might work)
            videoElement.innerHTML = '';
            const source = document.createElement('source');
            source.src = mediaSrc;
            source.type = 'video/mp4'; // Try mp4 type

            videoElement.appendChild(source);

            // Add error handler
            videoElement.addEventListener('error', function(e) {
                console.log('MP4 type failed, trying QuickTime type');

                // Test 2: Try with video/quicktime type
                videoElement.innerHTML = '';
                const source2 = document.createElement('source');
                source2.src = mediaSrc;
                source2.type = 'video/quicktime';
                videoElement.appendChild(source2);

                // Load again
                videoElement.load();

                // Add another error handler
                videoElement.addEventListener('error', function(e2) {
                    console.log('QuickTime type also failed, showing download option');

                    // Both methods failed, show download option
                    setTimeout(() => {
                        showDownloadFallback(mediaSrc, modalContent);
                    }, 100);
                });

                videoElement.load();
            });

            videoElement.load();
        }

        function openMediaModal(index) {
            const modal = document.getElementById('mediaModal' + index);
            const modalContent = modal.querySelector('.modal-content');

            // Get the clicked thumbnail
            const thumbnail = document.querySelector(`[onclick="openMediaModal('${index}')"]`);
            let mediaType, mediaSrc;

            // Check if it's an image or video container
            if (thumbnail.querySelector('img[data-type]')) {
                const imgElement = thumbnail.querySelector('img[data-type]');
                mediaType = imgElement.getAttribute('data-type');
                mediaSrc = imgElement.getAttribute('data-src');
            } else if (thumbnail.querySelector('div[data-type]')) {
                const divElement = thumbnail.querySelector('div[data-type]');
                mediaType = divElement.getAttribute('data-type');
                mediaSrc = divElement.getAttribute('data-src');
            }

            // Clear previous content
            modalContent.innerHTML = '';

            // Create appropriate player based on media type
            if (mediaType === 'image') {
                const img = document.createElement('img');
                img.src = mediaSrc;
                img.className = 'img-fluid';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '80vh';
                img.style.margin = '0 auto';
                img.style.display = 'block';
                img.style.objectFit = 'contain';
                modalContent.appendChild(img);
            } else if (mediaType === 'hls') {
                const container = document.createElement('div');
                container.className = 'hls-player-container';

                const video = document.createElement('video');
                video.id = 'hlsPlayer' + index;
                video.controls = true;
                video.className = 'hls-video';
                video.style.width = '100%';
                video.style.height = 'auto';
                video.style.maxHeight = '80vh';
                video.style.borderRadius = '10px';

                container.appendChild(video);
                modalContent.appendChild(container);

                // Initialize HLS player
                initializeHlsPlayer(index, video, mediaSrc);
            } else if (mediaType === 'video') {
                // Create video element
                const video = document.createElement('video');
                video.id = 'videoPlayer' + index;
                video.controls = true;
                video.preload = 'metadata';
                video.style.width = '100%';
                video.style.height = 'auto';
                video.style.maxHeight = '80vh';
                video.style.borderRadius = '10px';
                video.style.backgroundColor = '#000';

                // Check if it's a .mov file
                if (isMovFile(mediaSrc)) {
                    console.log('MOV file detected:', mediaSrc);

                    // Create a test video element to check browser capability
                    const testVideo = document.createElement('video');

                    if (isChrome()) {
                        console.log('Chrome browser detected for MOV file');
                        // Chrome has limited MOV support, try workarounds
                        tryPlayMovWorkaround(video, mediaSrc, index, modalContent);
                    } else {
                        // For non-Chrome browsers, try normal playback
                        const source = document.createElement('source');
                        source.src = mediaSrc;
                        source.type = 'video/quicktime';
                        video.appendChild(source);

                        // Add fallback message
                        const fallbackMsg = document.createElement('p');
                        fallbackMsg.textContent =
                            'Your browser may not support .mov files. If video doesn\'t play, please download it.';
                        fallbackMsg.style.color = 'white';
                        fallbackMsg.style.textAlign = 'center';
                        fallbackMsg.style.padding = '20px';
                        video.appendChild(fallbackMsg);

                        // Add error handler
                        video.addEventListener('error', function(e) {
                            console.log('MOV playback failed, showing download option');
                            showDownloadFallback(mediaSrc, modalContent);
                        });

                        video.load();
                    }
                } else {
                    // For non-MOV files, use normal logic
                    const source = document.createElement('source');
                    source.src = mediaSrc;

                    // Set MIME type based on file extension
                    const srcLower = mediaSrc.toLowerCase();
                    if (srcLower.endsWith('.mp4')) {
                        source.type = 'video/mp4';
                    } else if (srcLower.endsWith('.webm')) {
                        source.type = 'video/webm';
                    } else if (srcLower.endsWith('.ogg') || srcLower.endsWith('.ogv')) {
                        source.type = 'video/ogg';
                    } else if (srcLower.endsWith('.avi')) {
                        source.type = 'video/x-msvideo';
                    } else {
                        source.type = 'video/mp4'; // default
                    }

                    video.appendChild(source);

                    // Add fallback message
                    const fallbackMsg = document.createElement('p');
                    fallbackMsg.textContent = 'Your browser does not support the video tag.';
                    fallbackMsg.style.color = 'white';
                    fallbackMsg.style.textAlign = 'center';
                    fallbackMsg.style.padding = '20px';
                    video.appendChild(fallbackMsg);

                    video.load();
                }

                // Add to modal
                modalContent.appendChild(video);

                // Store reference
                regularVideoPlayers[index] = video;

                // Try to play after video is loaded (for non-MOV files)
                if (!isMovFile(mediaSrc)) {
                    video.addEventListener('loadeddata', function() {
                        setTimeout(() => {
                            video.play().catch(e => {
                                console.log('Autoplay prevented, user must click play:', e);
                            });
                        }, 100);
                    });
                }
            } else {
                const errorMsg = document.createElement('p');
                errorMsg.textContent = 'Unsupported file type: ' + mediaType;
                errorMsg.style.color = 'red';
                errorMsg.style.textAlign = 'center';
                errorMsg.style.padding = '20px';
                modalContent.appendChild(errorMsg);
            }

            modal.style.display = 'block';

            // Prevent body scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function closeMediaModal(index) {
            const modal = document.getElementById('mediaModal' + index);
            modal.style.display = 'none';

            // Destroy HLS player if it exists
            if (hlsPlayers[index]) {
                hlsPlayers[index].destroy();
                delete hlsPlayers[index];
            }

            // Pause regular video if it exists
            if (regularVideoPlayers[index]) {
                regularVideoPlayers[index].pause();
                regularVideoPlayers[index].currentTime = 0;
                delete regularVideoPlayers[index];
            }

            // Clear the modal content
            const modalContent = modal.querySelector('.modal-content');
            modalContent.innerHTML = '';

            // Restore body scrolling
            document.body.style.overflow = 'auto';
        }

        function initializeHlsPlayer(index, videoElement, videoSrc) {
            if (!videoSrc) {
                console.error('No video source found for HLS player');
                const errorMsg = document.createElement('p');
                errorMsg.textContent = 'Video source not available';
                errorMsg.style.color = 'red';
                errorMsg.style.textAlign = 'center';
                errorMsg.style.padding = '20px';
                videoElement.parentElement.appendChild(errorMsg);
                return;
            }

            if (Hls.isSupported()) {
                const hls = new Hls({
                    debug: false,
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90,
                    autoStartLoad: true
                });

                hls.loadSource(videoSrc);
                hls.attachMedia(videoElement);

                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    videoElement.play().catch(e => {
                        console.log('Autoplay prevented for HLS:', e);
                    });
                });

                hls.on(Hls.Events.ERROR, function(event, data) {
                    console.error('HLS error:', data);
                    if (data.fatal) {
                        switch (data.type) {
                            case Hls.ErrorTypes.NETWORK_ERROR:
                                hls.startLoad();
                                break;
                            case Hls.ErrorTypes.MEDIA_ERROR:
                                hls.recoverMediaError();
                                break;
                            default:
                                hls.destroy();
                                break;
                        }
                    }
                });

                hlsPlayers[index] = hls;
            } else if (videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                // For Safari (native HLS support)
                videoElement.src = videoSrc;
                videoElement.addEventListener('loadedmetadata', function() {
                    videoElement.play().catch(e => {
                        console.log('Autoplay prevented for native HLS:', e);
                    });
                });
            } else {
                console.error('HLS not supported by this browser');
                videoElement.innerHTML =
                    '<p style="padding:20px;color:white;text-align:center;">Your browser does not support HLS video playback.</p>';
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            document.querySelectorAll('.modal').forEach((modal) => {
                if (event.target === modal) {
                    const modalId = modal.id;
                    const index = modalId.replace('mediaModal', '');
                    closeMediaModal(index);
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach((modal) => {
                    if (modal.style.display === 'block') {
                        const modalId = modal.id;
                        const index = modalId.replace('mediaModal', '');
                        closeMediaModal(index);
                    }
                });
            }
        });

        // Clean up all players when page is unloaded
        window.addEventListener('beforeunload', function() {
            Object.keys(hlsPlayers).forEach(index => {
                hlsPlayers[index].destroy();
            });

            Object.keys(regularVideoPlayers).forEach(index => {
                regularVideoPlayers[index].pause();
                regularVideoPlayers[index].currentTime = 0;
            });
        });

        // Initialize tooltips if any
        document.addEventListener('DOMContentLoaded', function() {
            // If using tooltip library, initialize here
            if (typeof tippy !== 'undefined') {
                tippy('[data-tippy-content]', {
                    placement: 'top',
                    animation: 'shift-away',
                    theme: 'light'
                });
            }
        });
    </script>
@endpush
