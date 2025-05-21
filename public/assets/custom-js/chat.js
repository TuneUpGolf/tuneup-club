document.addEventListener("DOMContentLoaded", async () => {
    const {
        senderId,
        senderImage,
        groupId,
        recieverImage,
        token
    } = window.chatConfig;

    const socket = io(`${window.chatBaseUrl}`, {
        query: { senderid: senderId },
        transports: ["polling", "websocket"],
        forceNew: true,
        transportOptions: {
            polling: {
                extraHeaders: {
                    Authorization: `Bearer ${token}`,
                }
            }
        }
    });

    socket.on("connect", () => {

        socket.emit("join", {
            groupId: groupId,
            senderId: senderId
        });
    });

    //fetch intial messages
    loadMessages();

    socket.on("connect_error", (err) => {
        console.error("❌ Connection failed:", err.message);
        console.error("Full error:", err);
    });

    socket.on("message", (data) => {
    });

    socket.on("disconnect", () => {
        console.log("Disconnected from socket.");
    });

    function sendMessage() {
        const messageInput = document.getElementById('chatInput');
        const message = messageInput.value.trim();

        if (!message) return;

        socket.emit("chatMessage", {
            senderId: senderId,
            groupId: groupId,
            msg: message,
            parentId: null,
            type: "onetoone"
        });

        messageInput.value = "";
    }

    document.getElementById('sendButton').addEventListener('click', function (e) {
        e.preventDefault();
        sendMessage();
    });

    document.getElementById('chatInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

    socket.on("received", (newMessage) => {
        const msgHtml = createMessageHTML(newMessage);
        chatBox.insertAdjacentHTML("beforeend", msgHtml);
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    // 🔹 load chat messages as per page
    async function loadMessages(page = 1) {
        const response = await fetch(`${window.chatBaseUrl}/brainvire-chat-base-app/api/v1/chat/list`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                groupId,
                userType: "onetoone",
                perPage: 15,
                page: page
            })
        });
    
        if (!response.ok) return;
    
        const data = await response.json();
    
        if (data.status === 'Success' && Array.isArray(data.data) && data.data[0]?.data) {
            let messages = data.data[0].data;

            if (page === 1) {
                messages = messages.reverse();
            }

            renderMessages(messages, page !== 1);
        }
    }

    // 🔹 Emoji picker initialization
    const emojiToggle = document.getElementById('emoji-toggle');
    const chatInput = document.getElementById('chatInput');
    const chatBody = document.querySelector('.chat-box');
    const emojiPicker = document.createElement('emoji-picker');

    // Picker style
    emojiPicker.style.position = 'absolute';
    emojiPicker.style.bottom = '60px';
    emojiPicker.style.right = '90px';
    emojiPicker.style.zIndex = '1000';
    emojiPicker.style.display = 'none';

    chatBody.appendChild(emojiPicker);

    emojiToggle.addEventListener('click', () => {
        emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
    });

    emojiPicker.addEventListener('emoji-click', event => {
        chatInput.value += event.detail.unicode;
        emojiPicker.style.display = 'none';
        chatInput.focus();
    });

    // 🔹 File/media upload
    document.getElementById("mediaInput").addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const maxSizeMB = 2;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;

        if (file.size > maxSizeBytes) {
            Swal.fire({
                icon: 'error',
                title: 'File too large!',
                text: `Please select a file smaller than ${maxSizeMB}MB.`,
            });
            event.target.value = "";
            return;
        }

        uploadChatMedia({
            groupId: groupId,
            senderId: senderId,
            file,
        });

        event.target.value = "";
    });

    // 🔹 create message to addd in chat box
    function createMessageHTML(message) {
        const isSender = message.senderId === senderId;
    
        let mediaContent = "";
        if (message.isFile && message.fileName && message.filePath && message.fileType) {
            const fileUrl = `${window.s3BaseUrl}${message.fileName}`;
            const ext = message.fileType.toLowerCase();
    
            if (['.png', '.jpg', '.jpeg', '.gif', '.webp'].includes(ext)) {
                mediaContent = `<img src="${fileUrl}" alt="sent image" style="max-width: 200px; border-radius: 8px;" />`;
            } else if (['.mp4', '.webm', '.ogg'].includes(ext)) {
                mediaContent = `<video controls style="max-width: 200px; border-radius: 8px;">
                                    <source src="${fileUrl}" type="video/${ext.replace('.', '')}">
                                    Your browser does not support the video tag.
                                </video>`;
            } else if (['.mp3', '.wav', '.aac'].includes(ext)) {
                mediaContent = `<audio controls>
                                    <source src="${fileUrl}" type="audio/${ext.replace('.', '')}">
                                    Your browser does not support the audio element.
                                </audio>`;
            } else {
                mediaContent = `<a href="${fileUrl}" target="_blank" download>${message.fileName}</a>`;
            }
        }
    
        return `
            <div class="d-flex mb-3 ${isSender ? "flex-row-reverse" : ""}">
                <img src="${isSender ? senderImage : recieverImage}"
                    alt="avatar" class="rounded-circle ${isSender ? 'ms-2' : 'me-2'}" width="40" height="40">
                <div class="${isSender ? 'text-end' : ''}">
                    <div class="chat-msg-wrap ${isSender ? ' bg-primary text-white' : 'bg-light'} rounded px-3 py-2 mb-1">
                        ${message.message || ""}
                        ${mediaContent}
                    </div>
                    <small class="text-muted">${new Date(message.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
    }

    // 🔹 Render all messages (initial load)
    function renderMessages(messages, prepend = false) {
        const chatBox = document.querySelector(".chat-box");
    
        messages.forEach(message => {
            const msgHtml = createMessageHTML(message);
            if (prepend) {
                chatBox.insertAdjacentHTML("afterbegin", msgHtml);
            } else {
                chatBox.insertAdjacentHTML("beforeend", msgHtml);
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    }

    // 🔹 Upload media to server
    async function uploadChatMedia({ groupId, senderId, file }) {
        const formData = new FormData();
        formData.append("groupId", groupId);
        formData.append("senderId", senderId);
        formData.append("image", file);

        try {
            const response = await fetch(`${window.chatBaseUrl}/brainvire-chat-base-app/api/v1/chat/image`, {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${token}`
                },
                body: formData,
            });

            const result = await response.json();
            if (response.ok) {
                console.log("Upload successful:", result);
            } else {
                console.error("Upload failed:", result);
            }
        } catch (error) {
            console.error("Error uploading media:", error);
        }
    }

    let currentPage = 1;
    let isLoading = false;

    const chatBox = document.querySelector(".chat-box");

    chatBox.addEventListener("scroll", async function () {
        if (chatBox.scrollTop === 0 && !isLoading) {
            isLoading = true;
            currentPage++;

            const oldScrollHeight = chatBox.scrollHeight;

            await loadMessages(currentPage);

            const newScrollHeight = chatBox.scrollHeight;
            chatBox.scrollTop = newScrollHeight - oldScrollHeight;

            isLoading = false;
        }
    });
});