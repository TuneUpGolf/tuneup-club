<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm chat-module-wrapper">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Chat {{isset($user)?"with ". $user:''}}</h5>
            </div>
            <div class="card-body chat-box p-4">
            </div>
            <div class="card-footer">
                <form id="chatForm" class="d-flex align-items-center">
                    <input type="file" id="mediaInput" accept="image/*,video/*,audio/*"
                        class="form-control me-2 upload-files" />
                    <input type="text" id="chatInput" class="form-control me-2" placeholder="Type your message..." />
                    <button type="button" id="emoji-toggle" class="btn btn-light-secondary me-2">😊</button>
                    <button id="sendButton" class="btn btn-primary" type="button"><i class="ti ti-send"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('css')
    <style>
        .chat-box .rounded-circle {
            min-width: 40px;
            width: 40px;
            height: 40px;
            object-fit: cover;

        }

        .card-body {
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
        }

        .upload-files {
            min-width: 105px;
            max-width: 105px;
        }
        .chat-msg-wrap {
            word-break: break-all;
        }

        @media (max-width: 768px) {
            .chat-box .rounded-circle {
                min-width: 35px;
                width: 35px;
                height: 35px;
            }

            .card-body {
                max-height: 300px;
            }

            .chat-module-wrapper .card-footer .btn {
                padding: 5px 7px;
                height: 41px;
            }

            .upload-files {
                min-width: 34px;
                max-width: 34px;
                font-size: 0;
                height: 41px;
                background-image: url('{{ asset('assets/images/upload.svg') }}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 20px;
                padding: 0;
                filter: opacity(0.5);
            }

            .chat-box .form-control {
                padding: 0.5rem 0.75rem;

            }
            .chat-box audio {
                max-width: 260px;
            }
        }
    </style>
@endpush
