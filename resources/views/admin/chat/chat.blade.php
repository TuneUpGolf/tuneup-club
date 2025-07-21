{{-- <div class="row">
    <div class="col-xl-12"> --}}
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
    {{-- </div>
</div> --}}