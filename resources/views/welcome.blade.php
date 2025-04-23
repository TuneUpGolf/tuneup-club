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
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="...">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <img src="{{ asset('assets/images/landing-page-images/icon-1.png') }}" class="navbar-toggler-icon"
                            alt="...">
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav mx-lg-auto" id="mynav">
                        <a class="nav-item nav-link item-custom anker active" href="/" aria-current="page">Why
                            TuneUp?</a>
                        <a class="nav-item nav-link item-custom anker" href="./Course.html">Golf Course Benefits</a>
                        <a class="nav-item nav-link item-custom anker" href="./Instructor.html">Instructors</a>
                        <a class="nav-item nav-link item-custom anker" href="./Golfer.html">Golfers</a>
                        <a class="nav-item nav-link item-custom anker" href="./About.html">About Us</a>
                    </div>

                    <div class="d-flex align-items-lg-center mt-3 mt-lg-0">
                        <button class="request-text border-0 rounded-pill demo" style="background-color: #0033a1">
                            <a class="text-white" href="{{ url('request-demo') }}" style="text-decoration: none">
                                Request a Demo</a>
                        </button>
                        <button class="request-text border-0 rounded-pill demo mx-2" style="background-color: #0033a1">
                            <a class="text-white" href="{{ route('login') }}" style="text-decoration: none">
                                Login</a>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <section>
        <div class="hero-sec d-flex gap-5 flex-column flex-lg-row">
            <div class="master">
                <h4 id="hero-top" class="text-white">GET STARTED WITH TUNEUP</h4>
                <h1 id="hero-heading" class="fw-bold text-white time">
                    Technology To Advance Your Operation
                </h1>
                <p id="hero-para" class="text-white golf hero-features mt-4">
                    Trusted by industry leading Golf Clubs, Instructors, Academies, and
                    Teaching Professionals Worldwide.
                </p>
                <button id="hero-btn" class="request-text border-0 rounded-pill demo" style="background-color: #ffffff">
                    <a href="{{ url('request-demo') }}" style="text-decoration: none">
                        Request a Demo</a>
                </button>
            </div>
        </div>
    </section>

    <section>
        <div class="container mt-5 management">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <img src="{{ asset('assets/images/landing-page-images/effortless.png') }}" class="img-fluid"
                        alt="" style="height: 451px" />
                </div>
                <div class="col-lg-7">
                    <h1 class="fw-bold mt-3 lesson-h" style="color: #0033a1">
                        Effortless Lesson Management
                    </h1>
                    <p class="golf-p features mt-2" style="line-height: 37px">
                        TuneUp streamlines your lesson workflow. Access online
                        submissions, track pending lessons, monitor revenue, and review
                        student activity—all from one intuitive dashboard. Deliver a
                        seamless experience for you and your members.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container mt-5 management">
            <div class="row align-items-center gap-4">
                <div class="col-lg-7">

                    <h1 class="fw-bold mt-3 lesson-h" style="color: #0033a1">
                        Convenient Access to Your Coach
                    </h1>
                    <p class="golf-p features">
                        TuneUp makes booking in-person lessons simple. View your golf
                        professional’s availability and reserve your spot in seconds—no
                        more back-and-forth scheduling.
                    </p>
                </div>
                <div class="col-lg-4">
                    <img src="{{ asset('assets/images/landing-page-images/convenient.png') }}" class="img-fluid"
                        alt="" style="max-height: 451px" />
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container mt-5 management">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <img style="max-height: 500px" class="iphone-15 img-fluid"
                        src="{{ asset('assets/images/landing-page-images/annotationimg.png') }}" alt="" />
                </div>
                <div class="col-lg-7">
                    <h1 class="fw-bold mt-3 lesson-h" style="color: #0033a1">
                        Advanced Coaching Tools for Instant Feedback
                    </h1>
                    <p class="text-body-emphasis features golf-p">
                        TuneUp’s advanced annotation features empower coaches to provide
                        instant, precise instruction—whether in person or online. Utilize
                        powerful tools like video overlays, screen recording, and our
                        intuitive annotation tool to analyze swings and deliver impactful
                        feedback.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section class="access-wrapper">
        <div class="access-content flex-column">
            <h1 class="text-white text-center fw-bold">
                Stay Ahead of the Game with TuneUp
            </h1>
            <p class="with text-white golf-with">
                TuneUp equips golf courses, instructors, and golfers with the tools
                they need to succeed. Whether you're a course looking to enhance
                member engagement, an instructor streamlining lessons, or a golfer
                seeking personalized feedback—TuneUp delivers powerful solutions for
                everyone.
            </p>
            <button class="request-text border-0 rounded-pill demo last-but" style="background-color: #576974">
                <a class="text-white" href="request-demo" style="text-decoration: none">
                    Request a Demo</a>
            </button>
        </div>
    </section>

    <footer class="foot mt-0">
        <div class="d-flex justify-content-between container footer-one">
            <div class="d-flex flex-column gap-4">
                <div>

                    <img src="{{ asset('assets/images/landing-page-images/logo (5).png') }}" alt="" />
                </div>
                <p class="text-white fot-golf word-spacing-5">
                    Trusted by Industry leading Golf Club, Instructors, Acadmies, and
                    Teaching Professionals.
                </p>
            </div> -->
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
                <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
@endpush
