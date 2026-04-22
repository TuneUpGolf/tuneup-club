@php
    // Check if this is an album post
    $isAlbumPost = !empty($post->album_category_id);

    // Get album category if this is an album post
    $albumCategory = $isAlbumPost ? \App\Models\AlbumCategory::find($post->album_category_id) : null;

    // Determine if post is paid (for album posts, check album_category)
    $isPaid = $isAlbumPost ? $albumCategory && $albumCategory->payment_mode == 'paid' : $post->paid;

    // Get price (for album posts, get from album_category)
    $price = $isAlbumPost ? ($albumCategory ? $albumCategory->price : '0.00') : $post->price;

    // Check if purchased (for album posts, check PurchaseAlbum)
    if ($isAlbumPost) {
        $isPurchased = \App\Models\PurchaseAlbum::where('student_id', auth()->guard('follower')->id())
            ->where('album_category_id', $post->album_category_id)
            ->where('active_status', 1)
            ->exists();
    } else {
        $isPurchased = $purchasePost;
    }
@endphp

<div class="focus:outline-none w-full md:w-1/2 lg:w-1/3 py-3 p-sm-3 max-w-md">
    <div class="shadow rounded-2 overflow-hidden position-relative">
        @if ($isPaid && !$isPurchased && !$isInfluencer && !$isSubscribed)
            <?php $cls = 'p-3 position-absolute left-0 top-0 z-1 w-full'; ?>
        @else
            <?php $cls = 'p-3 position-absolute left-0 top-0 z-1 w-full custom-gradient'; ?>
        @endif

        <div class="{{ $cls }}">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center gap-3">
                    <img class="w-16 h-16 rounded-full" src="{{ $post?->influencer?->logo }}" alt="Profile" />
                    <div>
                        <p class="text-xl text-white font-bold mb-0 leading-tight">
                            {{ ucfirst($post->isFollowerPost ? $post?->follower->name : $post?->influencer?->name) }}
                        </p>
                    </div>
                </div>
                @if ($isPaid && $isPurchased)
                    <div class="bg-green-500 py-2 px-3 rounded-3xl shadow">
                        <span> Purchased</span>
                    </div>
                @endif
            </div>
        </div>

        @if ($post->file_type == 'image')
            @if ($isPaid && !$isPurchased && !$isInfluencer && !$isSubscribed)
                @php
                    $lockedPreview = $post->locked_thumbnail ?: $post->file;
                @endphp
                <div class="relative paid-post-wrap">
                    <img class="w-full post-thumbnail" src="{{ $lockedPreview }}" />
                    <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                        <div
                            class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                            <i class="ti ti-lock-open"></i>
                        </div>

                        @if ($isAlbumPost)
                            {!! Form::open([
                                'route' => ['album.category.purchase.album.index', ['post_id' => $post->id]],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                        @else
                            {!! Form::open([
                                'route' => ['purchase.post.index', ['post_id' => $post->id]],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                        @endif

                        <div
                            class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                            <i class="ti ti-lock-open text-2xl lh-sm"></i>
                            {{ Form::button(__('Unlock for - $' . $price), ['type' => 'submit', 'class' => 'btn p-0 pl-1 text-white border-0']) }}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            @else
                <img class="w-full post-thumbnail open-full-thumbnail" src="{{ $post->file }}" />
                <div id="imageModal" class="modal">
                    <span class="close" id="closeBtn">&times;</span>
                    <img class="modal-content" id="fullImage">
                </div>
            @endif
        @else
            @if ($isPaid && !$isPurchased && !$isInfluencer && !$isSubscribed)
                <div class="relative paid-post-wrap">
                    @if (!empty($post->locked_thumbnail))
                        <img class="w-full post-thumbnail" src="{{ $post->locked_thumbnail }}" alt="Locked Video" />
                    @else
                        <video class="w-full post-thumbnail pointer-events-none opacity-50">
                            <source src="{{ $post->file }}#t=0.001">
                        </video>
                    @endif
                    <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                        <div
                            class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                            <i class="ti ti-lock-open"></i>
                        </div>

                        @if ($isAlbumPost)
                            {!! Form::open([
                                'route' => ['album.category.purchase.album.index', ['post_id' => $post->id]],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                        @else
                            {!! Form::open([
                                'route' => ['purchase.post.index', ['post_id' => $post->id]],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                        @endif

                        <div
                            class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                            <i class="ti ti-lock-open text-2xl lh-sm"></i>
                            {{ Form::button(__('Unlock for - $' . $price), ['type' => 'submit', 'class' => 'btn p-0 pl-1 text-white border-0']) }}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            @else
                <video controls class="w-full post-thumbnail">
                    <source src="{{ $post->file }}#t=0.001">
                </video>
            @endif
        @endif

        <div class="px-4 py-2">
            <h1 class="text-xl font-bold truncate">
                {{ $post->title }}
            </h1>

            @php
                $description = strip_tags($post->description);
                $shortDescription = \Illuminate\Support\Str::limit($description, 80, '');
            @endphp

            <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                <a href="javascript:void(0);" class="text-blue-600 font-semibold underline" data-bs-toggle="modal"
                    data-bs-target="#descriptionModal{{ $post->id }}">
                    View Description
                </a>
            </p>
        </div>
    </div>
</div>

<style>
    .longDescContentTipandDrills li{
        list-style: disc;
    }
    .longDescContentTipandDrills{
        margin-left: 10px;
    }
</style>

<!-- Bootstrap Modal -->
<div class="modal fade" id="descriptionModal{{ $post->id }}" tabindex="-1" role="dialog"
    aria-labelledby="descriptionModalLabel{{ $post->id }}" aria-hidden="true" style="z-index: 1072;">
    <div class="modal-dialog modal-dialog-centered custom-modal-width" role="document" >
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header flex justify-between items-center">
                <h1 class="modal-title font-bold text-lg" id="descriptionModalLabel{{ $post->id }}">
                    {{ $post->title }}
                </h1>
                <button type="button"
                    class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                    data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                {{-- Full Description --}}
                <div class="longDescContentTipandDrills">
                    {!! $post->description !!}
                </div>

                {{-- Breaker Line --}}
                @if ($post->description && $post->short_description)
                    <div class="my-3  border-b border-gray-300">
                        <div class="flex items-center justify-center">
                            {{-- <span class="text-sm font-medium text-gray-600 uppercase tracking-wider">
                                ┄┄┄┄┄┄┄┄┄┄┄┄┄┄
                            </span> --}}
                        </div>
                    </div>
                @endif

                {{-- Short Description --}}
                @if ($post->short_description)
                    <div class="longDescContentTipandDrills">
                        {!! $post->short_description !!}
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="lesson-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('css')
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 90%;
            max-height: 80%;
            width: auto;
            height: auto;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #0071ce;
        }

        body.modal-open {
            overflow-y: hidden;
        }

        body.modal-open .dash-container {
            z-index: 99999;
        }
    </style>
@endpush

@push('javascript')
    <script>
        function toggleDescription(button) {
            let parent = button.closest('.description');
            let shortText = parent.querySelector('.short-text');
            let fullText = parent.querySelector('.full-text');

            if (shortText.classList.contains('hidden')) {
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "Read More >>";
            } else {
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "<< Read Less";
            }
        }
    </script>
@endpush
