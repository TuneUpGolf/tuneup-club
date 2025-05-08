@section('title')
    {{ __('Blog Details') }}
@endsection
<!DOCTYPE html>
<html lang="en">
@include('layouts.front_header')
<header id="home" class="bg-primary blog_detail">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-5">
                <h1 class="text-white mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.2s">
                    {{ __('Blog Details') }}
                </h1>
            </div>
        </div>
    </div>
</header>
<div>
    <div class="container">
        <div class="row">
            <!-- [ sample-page ] start -->
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @if ($post)
                                <div class="col-lg-5">
                                    <div class="position-relative product-slider">
                                        <div id="carouselExampleCaptions" class="carousel slide carousel-fade"
                                            data-bs-ride="carousel">
                                            <div class="carousel-inner custom-carousel-inner">
                                                <div class="carousel-item active ">
                                                    @if ($post->photo)
                                                        <img src="{{ Illuminate\Support\Facades\File::exists(Storage::path($post->photo)) ? Storage::url(tenant('id') . '/' . $post->photo) : Storage::url('seeder-image/350x250.png') }}"
                                                            class="d-block w-100">
                                                    @else
                                                        <img src="{{ Storage::url('seeder-image/350x250.png') }}"
                                                            class="d-block w-100">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <h2 class="mt-0">{{ $post->title }}</h2>
                                    <div class="mt-4">
                                        <h3>{{ $post->short_description }}</h3>
                                    </div>
                                    <div class="mt-4">
                                        <h6>{{ __('Description') }}</h6>
                                        <p class="text-muted text-sm mb-0">{!! $post->description !!}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mt-3">
                            <div class="main_content_wrapper">
                                <div class="container">
                                    <div class="row">
                                        @foreach ($randomPosts as $randomPost)
                                            <div class="col-lg-3 col-md-6">
                                                <div class="card mb-3">
                                                    @if ($randomPost->photo)
                                                        <img src="{{ Illuminate\Support\Facades\File::exists(Storage::path($randomPost->photo)) ? Storage::url(tenant('id') . '/' . $randomPost->photo) : Storage::url('seeder-image/350x250.png') }}"
                                                            class="img-fluid card-img-top card-img-custom">
                                                    @else
                                                        <img src="{{ Storage::url('seeder-image/350x250.png') }}"
                                                            class="img-fluid card-img-top card-img-custom">
                                                    @endif
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $randomPost->title }}</h5>
                                                        <p class="card-text ">
                                                            {{ substr($randomPost->short_description, 0, 75) . (strlen($randomPost->short_description) > 75 ? '...' : '') }}
                                                        </p>
                                                        <div class="article-cta">
                                                            <a href="{{ route('post.details', $randomPosts->slug) }}">{{ __('Read More') }}<i class="fas fa-chevron-right"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ sample-page ] end -->
</div>
@include('layouts.front_footer')
</body>

</html>
