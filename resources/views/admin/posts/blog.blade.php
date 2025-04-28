<div class="focus:outline-none w-full sm:w-1/2 md:w-1/3 mb-8 shadow-md">
    <div class="bg-white px-4 py-2 rounded-tl-3xl rounded-tr-3xl">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-2">
                @if ($post->isFollowerPost)
                    <img class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->student?->dp) }}" alt="Profile" />
                @else
                    <img class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->instructor?->logo) }}"
                        alt="Profile" />
                @endif
                <div>
                    <p class="text-md text-gray-900 font-semibold mb-0 leading-tight">
                    {{ ucfirst($post->isFollowerPost ? $post?->follower?->name : $post?->instructor?->name) }}
                    </p>
                    <span class="text-xs italic text-gray-600">
                        {{ $post->isFollowerPost ? 'Follower' : 'Instructor' }}
                    </span>
                </div>
            </div>
            <div class="text-xs text-gray-500">
                {{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}
            </div>
        </div>
    </div>

    @if ($post->file_type == 'image')
        @if ($post->paid && !isset($purchasePost))
            <div class="relative bg-black">
                <img class="rounded-md w-full opacity-20"
                    src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}" alt="Post Image" />
                <div class="absolute inset-0 flex justify-center items-center">
                    {!! Form::open([
                        'route' => ['purchase.post.index', ['post_id' => $post->id]],
                        'method' => 'Post',
                        'data-validate',
                    ]) !!}
                    {{ Form::button(__('Purchase Post - $' . $post->price), ['type' => 'submit', 'class' => 'btn btn-primary bg-white text-black px-4 py-2 rounded-lg']) }}
                    {!! Form::close() !!}
                </div>
            </div>
        @else
            <img class="rounded-md w-full" src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}"
                alt="Post Image" />
        @endif
    @else
        @if ($post->paid && !isset($purchasePost))
            <div class="relative bg-black h-48 flex justify-center items-center">
                {!! Form::open([
                    'route' => ['purchase.post.index', ['post_id' => $post->id]],
                    'method' => 'Post',
                    'data-validate',
                ]) !!}
                {{ Form::button(__('Purchase Post - $' . $post->price), ['type' => 'submit', 'class' => 'btn btn-primary bg-white text-black px-4 py-2 rounded-lg']) }}
                {!! Form::close() !!}
            </div>
        @else
            <video controls class="w-full h-auto">
                <source src="{{ Storage::url(tenant('id') . '/' . $post?->file) }}" type="video/mp4">
            </video>
        @endif
    @endif

    <div class="flex justify-between px-4 py-2 bg-blue-600 rounded-bl-3xl rounded-br-3xl">
        <h1 class="text-lg text-white font-bold truncate">
            {{ $post->title }}
        </h1>
        <div class="flex items-center">
            {!! Form::open([
                'route' => ['purchase.like', ['post_id' => $post->id]],
                'method' => 'Post',
                'data-validate',
            ]) !!}
            {{ Form::button('<i class="fa fa-heart"></i> ' . $post->likePost->count(), ['type' => 'submit', 'class' => 'text-white text-lg font-bold flex items-center gap-1']) }}
            {!! Form::close() !!}
        </div>
    </div>

    <div class="bg-white px-4 py-2 rounded-xl">
        @php
            $description = strip_tags($post->description);
            $shortDescription = \Illuminate\Support\Str::limit($description, 50, '');
        @endphp

        <p class="text-gray-700 text-sm mt-2 description">
            <span class="short-text">{{ $shortDescription }}</span>
            @if (strlen($description) > 20)
                <span class="hidden full-text">{{ $description }}</span>
                <a href="javascript:void(0);" class="text-blue-500 toggle-read-more"
                    onclick="toggleDescription(this)">Read More</a>
            @endif
        </p>
    </div>
</div>
@push('javascript')
    <script>
        function toggleDescription(button) {
            let parent = button.closest('.description');
            let shortText = parent.querySelector('.short-text');
            let fullText = parent.querySelector('.full-text');

            if (shortText.classList.contains('hidden')) {
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "Read More";
            } else {
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "Show Less";
            }
        }
    </script>
@endpush
