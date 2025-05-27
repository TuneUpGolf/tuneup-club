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
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="videoModal" class="modal">
        <span class="close" style="position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer;">&times;</span>
        <div class="modal-content" style="margin:10% auto; width:80%; text-align:center;">
            <video id="videoPlayer" width="100%" controls>
                <source src="" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
        </div>
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
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
        width: 90%;
        max-width: 100%;
        background-color: #fff;
        border-radius: 10px;
        max-height: calc(100vh - 200px);
        overflow: hidden;
    }

    .modal-content video {
        width: 100%;
        height: auto;
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
</style>
@endpush
@push('javascript')
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById("videoModal");
        const closeBtn = document.querySelector(".close");
        const video = document.getElementById("videoPlayer");

        // Delegate click event to dynamically created .video-thumbnail elements
        document.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('video-thumbnail')) {
                    const videoSrc = e.target.getAttribute('data-video');
                    video.querySelector('source').src = videoSrc;
                    video.load(); // refresh video source
                    modal.style.display = "block";
                    video.play();
                }
            });

            closeBtn.onclick = function () {
                modal.style.display = "none";
                video.pause();
                video.currentTime = 0;
            };

            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                    video.pause();
                    video.currentTime = 0;
                }
            };
        });
    </script>
@endpush
