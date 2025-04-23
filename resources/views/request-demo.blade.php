@php
    $languages = \App\Facades\UtilityFacades::languages();
    $currency = tenancy()->central(function ($tenant) {
        return Utility::getsettings('currency_symbol');
    });
@endphp
@extends('layouts.main-landing')
@section('content')
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-white px-0 py-3">
            <div class="container-xl">
                <!-- Logo -->
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="..." />
                </a>
                <!-- Navbar toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <img class="navbar-toggler-icon"
                            src="{{ asset('assets/images/landing-page-images/icon-1.png') }}"alt="" />
                    </span>
                </button>
                <!-- Collapse -->
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <!-- Nav -->
                    <div class="navbar-nav mx-lg-auto nav" id="myDIV">
                        <a class="nav-item nav-link item-custom anker" href="/" aria-current="page">Why
                            TuneUp?</a>
                        <a class="nav-item nav-link item-custom anker" href="./Course.html">Golf Course Benefits</a>
                        <a class="nav-item nav-link item-custom anker" href="./Instructor.html">Instructors</a>
                        <a class="nav-item nav-link item-custom anker" href="./Golfer.html">Golfers</a>
                        <!-- <a class="nav-item nav-link  item-custom anker" href="./">Our Partners</a> -->
                        <a class="nav-item nav-link item-custom anker" href="./About.html">About Us</a>
                    </div>


                    <!-- Action -->
                    <div class="d-flex align-items-lg-center mt-3 mt-lg-0">
                        <button class="request-text border-0 rounded-pill demo" style="background-color: #0033a1">
                            Request a Demo
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container mt-5">
            <div class="card p-4">
                <h1 class="card-title text-center mb-4">Request a Demo</h1>
                {!! Form::open([
                    'route' => 'request.submit',
                    'method' => 'Post',
                    'class' => 'p-4',
                ]) !!}
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-6">
                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                        {!! Form::text('name', null, ['placeholder' => __('Enter name'), 'class' => 'form-control', 'required']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                        {!! Form::email('email', null, [
                            'class' => 'form-control',
                            'placeholder' => __('email@example.com'),
                            'required',
                        ]) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                        {!! Form::text('phone', null, [
                            'placeholder' => __('Enter Phone Number'),
                            'class' => 'form-control',
                            'required',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="clubName"> Club Name</label>
                        <input type="text" id="clubName" name="clubName" required class="form-control"
                            placeholder="Your Club Name" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="message"> Message</label>
                    <textarea id="message" name="message" rows="4" required class="form-control" placeholder="Your Message"></textarea>
                </div>
                <div class="text-center mt-2">
                    <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div>

                    @error('g-recaptcha-response')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="container d-flex align-items-lg-center justify-content-center mt-3 mt-lg-0">
                    <button type="submit" id="btn-end" class="btn btn-primary w-25">
                        Submit
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </main>

    <footer class="foot mt-5">
        <div class="d-flex justify-content-between container footer-one">
            <div class="d-flex flex-column gap-4">
                <div>
                    <img src="{{ asset('assets/images/landing-page-images/logo (5).png') }}" alt="" />
                </div>
                <p class="text-white fot-golf">
                    Lorem ipsum dolor sit amet consectetur. Velit <br />
                    morbi hac sit elementum nascetur.
                </p>
                <div class="d-flex footer-item">
                    <a class="text-white anker-2" href="">How It Works</a>
                    <a class="text-white anker-1" href="">About</a>
                    <a class="text-white anker-1" href="">Help</a>
                    <a class="text-white anker-1" href="">Privacy</a>
                </div>
            </div>
            <div class="d-flex flex-column gap-4 google">
                <div>
                    <img src="{{ asset('assets/images/landing-page-images/Google Play.png') }}" alt="" />
                </div>
                <div>
                    <img src="{{ asset('assets/images/landing-page-images/App Store.png') }}" alt="" />
                </div>
            </div>
        </div>
    </footer>
    <footer class="foot-two">
        <div class="d-flex justify-content-between align-items-center container footer-two">
            <div class="text-white m-0">
                <p class="fot-p">© 2024 Tuneup. All rights reserved.</p>
            </div>
            <div class="icon">
                <img src="{{ asset('assets/images/landing-page-images/Facebook.png') }}" alt="" />
                <img src="{{ asset('assets/images/landing-page-images/Twitter.png') }}" alt="" />
                <img src="{{ asset('assets/images/landing-page-images/Instagram.png') }}" alt="" />
                <img src="{{ asset('assets/images/landing-page-images/Github.png') }}" alt="" />
                <img src="{{ asset('assets/images/landing-page-images/LinkedIn.png') }}" alt="" />
            </div>
        </div>
    </footer>
@endsection
@push('javascript')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const navLinks = document.querySelectorAll("#mynav .nav-link");

            navLinks.forEach((link) => {
                link.addEventListener("click", () => {
                    navLinks.forEach((nav) => nav.classList.remove("active"));
                    link.classList.add("active");
                });
            });
        });
    </script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ env('NOCAPTCHA_SITEKEY') }}', {
                action: 'submit'
            }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
@endpush
@push('css')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .hero-sec {
            height: 55vh !important;
            background-image: url(imges/banner1.png);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        main {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #343a40;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }

        .form-group label {
            font-weight: 500;
        }

        @media (max-width: 767px) {
            #btn-end {
                font-size: 10px;
            }
        }
    </style>
@endpush
