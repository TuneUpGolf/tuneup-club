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

<div class="w-full bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 p-4">
    <div class="flex items-center py-1">
        <img class="w-12 h-12 rounded-full" src="{{ $image }}" alt="{{ $image }}" />
        <div class="flex flex-col pl-4 justify-start items-start">
            <div class="text-xl font-medium text-gray-900 dark:text-white"><a class="text-black hover:text-blue-500"
                    href="{{ route('instructor.profile', ['influencer_id' => $model->id]) }}">{!! \Illuminate\Support\Str::limit(ucfirst($model->name), 40, '...') !!}</a>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Instructor</span>
        </div>
    </div>
    <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">
    <div class="flex justify-center items-center divide-x divide-solid w-100 pb-2">
        <div class="flex flex-col justify-center items-center w-100">
            <span>{{ $followers }}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
        </div>
        <div class="flex flex-col justify-center items-center w-100">
            <span>{{ $subscribers }}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400">Subscribers</span>
        </div>
        <div class="flex flex-col justify-center items-center w-100">
            <span>{{ $model->lessons->count() }}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400">Lessons</span>
        </div>
    </div>
    <div class="w-100 mt-1">
        @if (!request()->is('purchase*'))
            {!! Form::open([
                'route' => [
                    'follow.instructor',
                    [
                        'influencer_id' => $model?->id,
                        'follow' => $follow ? 'unfollow' : 'follow',
                    ],
                ],
                'method' => 'Post',
                'data-validate',
            ]) !!}
            {{ Form::button(__($follow ? 'Unfollow' : 'Follow'), ['type' => 'submit', 'class' => 'follow-btn w-100']) }}
            {!! Form::close() !!}
        @endif
        {{-- <a
            href="{{ route('instructor.profile', array_filter(['influencer_id' => $model->id, 'section' => request()->is('purchase*') ? 'lessons' : null])) }}"><button
                type="button"
                class="py-2 px-4 ms-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">{{ __(request()->is('purchase*') ? 'Select Coach' : 'View Profile') }}</button></a> --}}
    </div>
</div>
