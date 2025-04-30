@extends('layouts.main')
@section('title', __('Posts'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Posts') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div id="blog" class="bg-gray-100 px-4 xl:px-4 py-14">
                    <div class="dropdown dash-h-item drp-company mt-8">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                            href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="hide-mob ms-2 text-lg">Filter</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown">
                            <a href="{{ route('blogs.index', ['filter' => 'all']) }}"
                                class="dropdown-item {{ request()->query('filter') === 'all' ? 'active' : '' }}">
                                <span>{{ __('All') }}</span>
                            </a>
                            <a href="{{ route('blogs.index', ['filter' => 'free']) }}"
                                class="dropdown-item  {{ request()->query('filter') === 'free' ? 'active' : '' }}">
                                <span>{{ __('Free') }}</span>
                            </a>
                            <a href="{{ route('blogs.index', ['filter' => 'paid']) }}"
                                class="dropdown-item {{ request()->query('filter') === 'paid' ? 'active' : '' }}">
                                <span>{{ __('Paid') }}</span>
                            </a>
                            <a href="{{ route('blogs.index', ['filter' => 'instructor']) }}"
                                class="dropdown-item {{ request()->query('filter') === 'instructor' ? 'active' : '' }}">
                                <span>{{ __('Instructor') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="mx-auto container">
                        <div class="focus:outline-none mt-5 mb-5 lg:mt-24">
                            <div class="infinity">
                                <div class="flex flex-col justify-center items-center w-100">
                                    @if ($posts->count() > 0)
                                        @each('admin.posts.blog', $posts, 'post')
                                        {{ $posts->links('pagination::bootstrap-4') }}
                                    @else
                                        <p class="text-gray-500 text-lg mt-5">No posts available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('javascript')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.3.7/jquery.jscroll.min.js"></script>
        <script type="text/javascript">
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
        @include('layouts.includes.datatable_js')
    @endpush
