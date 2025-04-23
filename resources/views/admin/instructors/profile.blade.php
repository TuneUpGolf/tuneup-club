@extends('layouts.main')
@section('title', __('Instructor Profile'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Instructor Profile') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="flex flex-col">
                <div class="profile-backdrop">
                    <div class="profile-info-container flex">
                        <img alt="{{ $instructor->name }}""
                            src="{{ asset('/storage' . '/' . tenant('id') . '/' . $instructor?->logo) }}""
                            class="rounded-full align-middle border-1 profile-image">
                        <div class="flex flex-col">
                            <span class="font-medium text-3xl mb-2">{{ $instructor->name }}</span>
                            <div class="flex justify-center items-center divide-x divide-solid w-100 gap-2 text-gray-600">
                                <div class="text-sm leading-normal text-gray-600 uppercase">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $instructor->country }}
                                </div>
                                <div class="text-sm leading-normal text-gray-600 uppercase">
                                    <i class="fas fa-user"></i>
                                    Instructor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card min-h-screen">
                    <div class="flex justify-between mt-4">
                        <div class="flex justify-center items-center divide-x divide-solid stats-container">
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $followers }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $subscribers }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Subscribers</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $totalPosts }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Posts</span>
                            </div>
                            <div class="flex flex-col justify-center items-center w-100">
                                <span>{{ $totalLessons }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Lessons</span>
                            </div>
                        </div>
                        <div class="mr-4">
                            {!! Form::open([
                                'route' => [
                                    'follow.instructor',
                                    [
                                        'influencer_id' => $instructor?->id,
                                        'follow' => $follow->where('student_id', Auth::user()->id)?->first()?->active_status
                                            ? 'unfollow'
                                            : 'follow',
                                    ],
                                ],
                                'method' => 'Post',
                                'data-validate',
                            ]) !!}
                            {{ Form::button(__($follow->where('student_id', Auth::user()->id)->first()?->active_status ? 'Unfollow' : 'Follow'), ['type' => 'submit', 'class' => 'follow-profile-btn']) }}
                            {!! Form::close() !!}
                        </div>
                    </div>
                    <div class="tab">
                        <button class="tablinks active" onclick="openCity(event, 'Lessons')">Lessons</button>
                        <button class="tablinks" onclick="openCity(event, 'Posts')">Posts</button>
                        </hr>
                    </div>
                    <div id="Lessons" class="tabcontent flex items-center">
                        @if (!!$totalLessons)
                            <livewire:lessons-grid-view />
                        @else
                            <div class='flex flex-col justify-center items-center no-data gap-2'><i
                                    class="fa fa-thumbs-down" aria-hidden="true"></i>There are no lessons from this
                                instructor yet
                            </div>
                        @endif
                    </div>
                    <div id="Posts" class="tabcontent">
                        @if (!!$totalLessons)
                            <div id="blog" class="bg-gray-100 px-4 xl:px-4 py-14">
                                <div class="mx-auto container">
                                    <div class="focus:outline-none mt-5 mb-5 lg:mt-24">
                                        <div class="infinity">
                                            <div class="flex flex-col justify-center items-center w-100">
                                                @each('admin.posts.blog', $posts, 'post')
                                                {{ $posts->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class='flex flex-col justify-center items-center no-data gap-2'><i
                                    class="fa fa-thumbs-down" aria-hidden="true"></i>There are no posts from
                                this instructor yet</div>
                        @endif
                    </div>
                </div>
            </div>
        @endsection
        @push('css')
            @include('layouts.includes.datatable_css')
            <link rel="stylesheet"
                href="https://demos.creative-tim.com/notus-js/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
        @endpush
        @push('javascript')
            <script>
                document.getElementById('Lessons').style.display = "block";

                function openCity(evt, tabName) {
                    // Declare all variables
                    var i, tabcontent, tablinks;

                    // Get all elements with class="tabcontent" and hide them
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }

                    // Get all elements with class="tablinks" and remove the class "active"
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }

                    // Show the current tab, and add an "active" class to the button that opened the tab
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.className += " active";

                }
                $('ul.pagination').hide();
                $(function() {
                    $('.infinity').jscroll({
                        autoTrigger: true,
                        debug: false,
                        loadingHtml: '<img class="center-block" src="/images/loading.gif" alt="Loading..." />',
                        padding: 0,
                        nextSelector: '.pagination li.active + li a',
                        contentSelector: '.infinity',
                        callback: function() {
                            $('ul.pagination').remove();
                        }
                    });
                });
            </script>
        @endpush
