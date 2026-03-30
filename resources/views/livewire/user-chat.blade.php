@php use Illuminate\Support\Str; @endphp
<div>
    <div class="flex flex-col overflow-hidden" style="height:800px;">

        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
            User Chat
        </h1>

        <div
            class="flex flex-1 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 h-full">

            <!-- USERS LIST -->
            <div wire:poll.5s class="w-72 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">

                <!-- USER SEARCH -->
                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Search user..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" />
                </div>

                @foreach($this->users as $user)
                    <div wire:click="selectUser({{ $user->id }})"
                        class="flex items-center gap-3 p-4 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800
                                                       @if($selectedUser == $user->id) bg-gray-100 dark:bg-gray-800 @endif">

                        <div class="relative">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @php $isOnline = $user->last_seen && $user->last_seen->gt(now()->subMinutes(2)); @endphp
                            <span class="absolute bottom-0 right-0 w-3 h-3 
                                                          {{ $isOnline ? 'bg-green-500' : 'bg-gray-400' }} 
                                                          border-2 border-white rounded-full"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">
                                {{ $user->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                @php
                                    $lastMsg = \App\Models\DirectMessage::where(function ($q) use ($user) {
                                        $q->where('sender_id', auth()->id())->where('receiver_id', $user->id);
                                    })->orWhere(function ($q) use ($user) {
                                        $q->where('sender_id', $user->id)->where('receiver_id', auth()->id());
                                    })->latest()->first();
                                @endphp
                                @if($lastMsg)
                                    @if($lastMsg->file)
                                        @if(Str::contains($lastMsg->file, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            📷 Photo
                                        @else
                                            📎 File
                                        @endif
                                    @else
                                        {{ Str::limit($lastMsg->message, 35) }}
                                    @endif
                                @else
                                    Start Chatting
                                @endif
                            </div>
                        </div>
                        @php
                            $unreadCount = \App\Models\DirectMessage::where('sender_id', $user->id)
                                ->where('receiver_id', auth()->id())
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full min-w-[20px] text-center">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- CHAT AREA -->
            <div class="flex flex-col flex-1 min-h-0 h-full">
                @if($selectedUser)
                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($this->selectedUserModel->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $this->selectedUserModel->name }}
                                </div>
                            </div>
                        </div>

                        <!-- HEADER ACTIONS -->
                        <div class="flex gap-3 text-gray-400">

                            <!-- Voice Call -->
                            <button onclick="openCall({{ $selectedUserModel->id }})"
                                class="hover:text-green-500 transition text-xl" title="Voice Call">
                                📞
                            </button>

                            <!-- Video Call -->
                            <button onclick="startVideoCall({{ $selectedUserModel->id }})"
                                class="hover:text-blue-500 transition text-xl" title="Video Call">
                                🎥
                            </button>

                        </div>
                    </div>
                @endif

                <!-- MESSAGES -->
                <div id="chatMessages" wire:poll.10s.keep-alive wire:key="chatMessages"
                    class="flex-1 overflow-y-auto px-6 pt-6 space-y-4 min-h-0">
                    @forelse($chatMessages as $index => $msg)
                        @if($msg->sender_id == auth()->id())
                            <!-- MY MESSAGE -->
                            <div class="flex justify-end">
                                <div class="max-w-md">
                                    <div
                                        class="relative bg-blue-600 text-white px-4 py-2 rounded-xl shadow-sm group break-words inline-block">
                                        <!-- HOVER TOOLBAR -->
                                        <div
                                            class="absolute -right-10 top-3 opacity-0 group-hover:opacity-100 transition duration-200">
                                            <div
                                                class="flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-full shadow px-2 py-[3px] text-[11px]">
                                                <button class="text-gray-500 hover:text-blue-500"
                                                    wire:click="editMessage({{ $msg->id }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5h2M4 20h4l10-10a2 2 0 10-4-4L4 16v4z" />
                                                    </svg>
                                                </button>
                                                <button class="text-gray-500 hover:text-red-500"
                                                    wire:click="deleteMessage({{ $msg->id }})"
                                                    onclick="return confirm('Delete this message?')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        @if($msg->file)
                                            <div
                                                class="mb-2 max-w-[140px] rounded-lg overflow-hidden border border-white/20 cursor-pointer transform hover:scale-105 transition duration-200">
                                                @if(Str::contains($msg->file, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $msg->file) }}" class="w-full h-auto object-cover">
                                                @else
                                                    <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                                        class="text-xs underline text-blue-200">
                                                        📎 {{ basename($msg->file) }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endif

                                        @if($msg->message)
                                            @if($editingMessageId === $msg->id)
                                                <div class="flex items-center gap-2">
                                                    <input type="text" wire:model.defer="editingText"
                                                        class="px-2 py-1 rounded border border-gray-400 w-full text-sm text-gray-900
                                                                                                                                                                              focus:outline-none focus:ring-2 focus:ring-blue-400
                                                                                                                                                                              dark:bg-gray-800 dark:text-white dark:border-gray-600">
                                                    <button wire:click="updateMessage"
                                                        class="text-xs bg-green-500 text-white px-2 py-1 rounded">Save</button>
                                                </div>
                                            @else
                                                <div class="text-[17px] leading-relaxed break-words font-medium">
                                                    {{ $msg->message }}
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Timestamp + Read status (inside bubble, bottom right) -->
                                        <div
                                            class="text-right text-[10px] mt-1 flex items-center justify-end gap-1 leading-tight opacity-80">
                                            <span>
                                                {{ $msg->created_at->timezone(config('app.timezone'))->format('h:i A') }}
                                            </span>

                                            @if($msg->read_at)
                                                <!-- Double tick (Seen) - RED -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                                    fill="#ffffff">
                                                    <path d="M1 14l5 5L15 6l-1.5-1.5L6 16 2.5 12.5z" />
                                                    <path d="M7 14l5 5L23 6l-1.5-1.5L12 16 8.5 12.5z" />
                                                </svg>
                                            @else
                                                <!-- Single tick (Sent) - WHITE -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                                    fill="#eb9b08">
                                                    <path d="M1 14l5 5L15 6l-1.5-1.5L6 16 2.5 12.5z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- OTHER USER MESSAGE (WhatsApp style) -->
                            <div class="flex items-start gap-3 justify-start">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($this->selectedUserModel->name, 0, 1)) }}
                                </div>
                                <div class="max-w-md relative">
                                    <div
                                        class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 rounded-xl shadow-sm">
                                        @if($msg->file)
                                            @if(Str::contains($msg->file, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <img src="{{ asset('storage/' . $msg->file) }}"
                                                    class="rounded-lg max-w-[250px] cursor-pointer transform hover:scale-100 transition duration-200 mb-2">
                                            @else
                                                <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                                    class="text-blue-500 underline text-xs">
                                                    📎 {{ basename($msg->file) }}
                                                </a>
                                            @endif
                                        @endif
                                        @if($msg->message)
                                            <div class="text-[20px] leading-relaxed break-words font-medium">
                                                {{ $msg->message }}
                                            </div>
                                        @endif

                                        <!-- Timestamp (inside bubble, bottom right) -->
                                        <div class="text-right text-[11px] mt-1 text-gray-500 dark:text-gray-400 leading-tight">
                                            {{ $msg->created_at->timezone(config('app.timezone'))->format('h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="mt-2 text-sm">No messages yet</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($selectedUser)
                    <!-- MESSAGE INPUT -->
                    <div
                        class="border-t border-gray-200 dark:border-gray-700 px-6 pt-6 pb-4 flex-shrink-0 sticky bottom-0 bg-white dark:bg-gray-900">
                        @if ($files)
                            @foreach($files as $index => $file)
                                <div class="mb-3 flex items-center gap-3 p-2 bg-gray-100 dark:bg-gray-800 rounded-lg w-fit">
                                    @if(str_contains($file->getMimeType(), 'image'))
                                        <img src="{{ $file->temporaryUrl() }}" class="w-16 h-16 rounded object-cover cursor-pointer">
                                    @else
                                        <div class="w-16 h-16 flex items-center justify-center bg-gray-300 rounded">📄</div>
                                    @endif
                                    <div class="text-sm">{{ $file->getClientOriginalName() }}</div>
                                    <button wire:click="$set('files', [])"
                                        class="text-red-500 hover:text-red-700 text-lg">✕</button>
                                </div>
                            @endforeach
                        @endif

                        <form wire:submit.prevent="sendMessage" class="flex items-center gap-3 w-full">
                            <label
                                class="cursor-pointer px-3 py-2 bg-gray-200 rounded-lg flex-shrink-0 hover:bg-gray-300 transition">
                                📎
                                <input type="file" wire:model="files" multiple class="hidden">
                            </label>
                            <textarea id="chatInput" wire:model.defer="message" rows="1"
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white
                                                                     focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 resize-none"
                                placeholder="Type your message..." @keydown.enter.prevent="
                                                                if(!event.shiftKey){
                                                                    let text = $event.target.value.trim();
                                                                    if(text.length > 0){
                                                                        $wire.sendMessage();
                                                                        $event.target.value = '';
                                                                    }
                                                                }"></textarea>
                            <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700
                                                                                  focus:outline-none focus:ring-2 focus:ring-blue-500 flex-shrink-0">
                                <svg class="w-5 h-5 transform rotate-90" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </form>
                        <p class="text-xs text-gray-400 mt-2">Press Enter to send, Shift+Enter for new line</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9);
         align-items:center; justify-content:center; z-index:9999;">
        <button id="closePreview" style="position:absolute; top:20px; right:30px; font-size:28px; color:white; 
                background:none; border:none; cursor:pointer;">✕</button>
        <img id="previewImage" style="max-width:90%; max-height:90%; border-radius:10px;">
    </div>
</div>

<!-- VIDEO CALL UI -->
<div id="videoCallContainer" style="display:none; position:fixed; inset:0; background:black; z-index:9999;">

    <video id="localVideo" autoplay muted playsinline
        style="position:absolute; bottom:20px; right:20px; width:200px; border-radius:10px;"></video>

    <video id="remoteVideo" autoplay playsinline
        style="width:100%; height:100%; object-fit:cover;"></video>

    <button onclick="endCall()" style="position:absolute; bottom:20px; left:50%; transform:translateX(-50%);
                   background:red; color:white; padding:10px 20px; border-radius:50px;">
        End Call
    </button>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>

<script>
    // Image preview
    document.addEventListener("click", function (e) {
        if (e.target.tagName === "IMG" && e.target.closest("#chatMessages")) {
            document.getElementById("previewImage").src = e.target.src;
            document.getElementById("imagePreviewModal").style.display = "flex";
        }
        if (e.target.id === "closePreview" || e.target.id === "imagePreviewModal") {
            document.getElementById("imagePreviewModal").style.display = "none";
        }
    });

    // Auto-scroll
    document.addEventListener("DOMContentLoaded", function () {
        const chat = document.getElementById("chatMessages");
        if (!chat) return;
        function scrollToBottom() { chat.scrollTop = chat.scrollHeight; }
        setTimeout(scrollToBottom, 300);
        const observer = new MutationObserver(scrollToBottom);
        observer.observe(chat, { childList: true, subtree: true });
    });

    // ==================== VOICE CALL FUNCTIONALITY ====================
    const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
    const PUSHER_CLUSTER = "ap2";
    const currentUserId = {{ auth()->id() }};

    // Track open call windows
    let callWindow = null;

    document.addEventListener("livewire:load", function () {
        console.log("✅ Initializing Pusher for voice calls...");

        try {
            const pusher = new Pusher(PUSHER_APP_KEY, {
                cluster: PUSHER_CLUSTER,
                forceTLS: true
            });

            const channel = pusher.subscribe('voice-call.' + currentUserId);

            channel.bind('subscription_succeeded', function () {
                console.log("✅ Subscribed to voice-call." + currentUserId);
            });

            // Handle incoming calls - AUTOMATICALLY OPEN RECEIVER WINDOW
            channel.bind('CallOffer', function (data) {
                console.log("📞 INCOMING CALL from user " + data.callerId + ": " + data.callerName);

                // Store call data
                const callData = {
                    offer: data.offer,
                    callerId: data.callerId,
                    callerName: data.callerName,
                    timestamp: Date.now()
                };
                sessionStorage.setItem('pendingCall', JSON.stringify(callData));

                // Check if call window is already open
                if (callWindow && !callWindow.closed) {
                    console.log("Call window already open, focusing it");
                    callWindow.focus();
                    // Send offer to existing window
                    setTimeout(() => {
                        try {
                            callWindow.postMessage({
                                type: 'incoming-offer',
                                offer: data.offer,
                                callerId: data.callerId,
                                callerName: data.callerName
                            }, '*');
                        } catch (e) {
                            console.error("Failed to send offer to existing window:", e);
                        }
                    }, 500);
                } else {
                    // Open new receiver window
                    console.log("Opening receiver call window");
                    callWindow = window.open(
                        "/voice-call/" + data.callerId,
                        "VoiceCallWindow",
                        "width=420,height=650,resizable=yes,scrollbars=yes"
                    );

                    if (!callWindow) {
                        console.error("Failed to open call window - popup blocked");
                        // Show fallback message
                        alert("Incoming call from " + data.callerName + "!\n\nPlease click OK to open the call window.");
                        window.open("/voice-call/" + data.callerId, "_blank");
                    }
                }
            });

            // Handle ICE candidates for multi-window support
            channel.bind('IceCandidate', function (data) {
                if (callWindow && !callWindow.closed) {
                    try {
                        callWindow.postMessage({
                            type: 'ice-candidate',
                            candidate: data.candidate,
                            senderId: data.senderId
                        }, '*');
                    } catch (e) {
                        console.error("Failed to forward ICE candidate:", e);
                    }
                }
            });

            // Handle call answers
            channel.bind('CallAnswer', function (data) {
                if (callWindow && !callWindow.closed) {
                    try {
                        callWindow.postMessage({
                            type: 'call-answer',
                            answer: data.answer,
                            callerId: data.callerId
                        }, '*');
                    } catch (e) {
                        console.error("Failed to forward call answer:", e);
                    }
                }
            });

        } catch (error) {
            console.error("❌ Failed to initialize Pusher:", error);
        }

        // Expose global UI reference for video calls
        if (window.incomingVideoUI) {
            console.log("✅ Global video UI detected");
        }
    });

    // Open call window (caller)
    function openCall(userId) {
        console.log("📞 Opening voice call to user:", userId);

        try {
            // Clear any pending calls
            sessionStorage.removeItem('pendingCall');

            // Close existing window if any
            if (callWindow && !callWindow.closed) {
                callWindow.close();
            }

            // Open caller window
            callWindow = window.open(
                "/voice-call/" + userId + "?mode=caller",
                "VoiceCallWindow",
                "width=420,height=650,resizable=yes,scrollbars=yes"
            );

            if (!callWindow) {
                alert("Please allow popups for this site to make calls.\n\nClick OK to open manually.");
                window.open("/voice-call/" + userId + "?mode=caller", "_blank");
            }
        } catch (error) {
            console.error("Failed to open call window:", error);
            alert("Failed to open call window. Please check your popup blocker.");
        }
    }

    // Make function globally available
    window.openCall = openCall;

    // ================= VIDEO CALL =================

    // Test camera function
    window.testCamera = async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 },
                    facingMode: "user"
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            });
            console.log("✅ Camera test successful");
            console.log("🎥 Video tracks:", stream.getVideoTracks());
            console.log("🎥 Audio tracks:", stream.getAudioTracks());
            
            // Test local video element
            const localVideo = document.getElementById("localVideo");
            if (localVideo) {
                localVideo.srcObject = stream;
                localVideo.style.display = "block";
                console.log("✅ Local video test - you should see yourself");
            }
            
            // Stop test after 5 seconds
            setTimeout(() => {
                stream.getTracks().forEach(track => track.stop());
                if (localVideo) localVideo.style.display = "none";
                console.log("🛑 Camera test stopped");
            }, 5000);
            
        } catch (error) {
            console.error("❌ Camera test failed:", error);
            alert("Camera test failed: " + error.message);
        }
    };

    // Use the global socket if it exists to avoid conflicts
    const socket = window.globalVideoSocket || (() => {
        try {
            return io("https://pm.inovace.in", {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });
        } catch (error) {
            console.error("❌ Failed to connect to socket server:", error);
            return io("http://localhost:3000", {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });
        }
    })();
    const myVideoUserId = {{ auth()->id() }};

    socket.emit("join-user", myVideoUserId);

    document.addEventListener("DOMContentLoaded", () => {

        const myId = myVideoUserId;
        const selectedUser = @this.get('selectedUser');

        if (selectedUser) {
            const room = "room-" + Math.min(myId, selectedUser) + "-" + Math.max(myId, selectedUser);
            socket.emit("join-room", room);
        }
        
        // Check if there's a pending video call from redirect
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('videoCall') === 'true') {
            const pendingCallData = sessionStorage.getItem('pendingVideoCall');
            if (pendingCallData) {
                console.log("🔄 Resuming pending video call after redirect");
                const data = JSON.parse(pendingCallData);
                setTimeout(() => {
                    const callerName = `User ${data.callerUserId || data.targetUserId || 'Unknown'}`;
                    const callerNameElement = document.getElementById("incomingVideoCallerName");
                    if (callerNameElement) {
                        callerNameElement.textContent = `Incoming video call from ${callerName}`;
                    }
                    window.pendingGlobalVideoData = data;
                    window.pendingChatVideoOffer = data;
                    window.acceptChatVideoCall = async function() {
                        sessionStorage.removeItem('pendingVideoCall');
                        if (window.incomingVideoUI) {
                            window.incomingVideoUI.style.display = "none";
                        }
                        await handleChatVideoOffer(data);
                    };
                    if (window.incomingVideoUI) {
                        window.incomingVideoUI.style.display = "block";
                    }
                }, 500);
            }
        }
    });

    // ✅ ADD THIS ALSO BELOW
    document.addEventListener("livewire:update", () => {

        const myId = myVideoUserId;
        const selectedUser = @this.get('selectedUser');

        if (selectedUser) {
            const room = "room-" + Math.min(myId, selectedUser) + "-" + Math.max(myId, selectedUser);
            socket.emit("join-room", room);
        }
    });

    let localStream;
    let peerConnection;
    let currentRoom = null;
    let pendingCandidates = [];
    let isRemoteDescriptionSet = false;
    let remoteStream = null;
    let isRelayFallbackEnabled = false;
    let isInCall = false;
    let currentCallUserId = null;

    const iceServers = [
        // Primary STUN servers (IPv4 only to avoid IPv6 issues)
        { urls: "stun:stun.l.google.com:19302" },
        { urls: "stun:stun1.l.google.com:19302" },
        { urls: "stun:stun2.l.google.com:19302" },
        
        // Additional reliable STUN servers
        { urls: "stun:stun.stunprotocol.org:3478" },
        { urls: "stun:stun.services.mozilla.com:3478" },

        // Your TURN server (updated with working configuration)
        {
            urls: [
                "turn:pm.inovace.in:3478?transport=udp",
                "turn:pm.inovace.in:3478?transport=tcp"
            ],
            username: "webrtcuser",
            credential: "strongpassword123"
        },

        // Backup TURN servers with verified credentials
        {
            urls: [
                "turn:openrelay.metered.ca:80",
                "turn:openrelay.metered.ca:443"
            ],
            username: "openrelayproject",
            credential: "openrelayproject"
        },
        {
            urls: "turn:numb.viagenie.ca:3478",
            username: "webrtc@live.com",
            credential: "muazkh"
        }
    ];

    function getPeerConfig(forceRelay = false) {
        const config = {
            iceServers: iceServers,
            iceCandidatePoolSize: 10,
            iceTransportPolicy: forceRelay ? "relay" : "all",
            bundlePolicy: "max-bundle",
            rtcpMuxPolicy: "require"
        };
        
        // Force IPv4 to avoid IPv6 connectivity issues
        config.iceServers = config.iceServers.map(server => {
            if (server.urls) {
                const urls = Array.isArray(server.urls) ? server.urls : [server.urls];
                const ipv4Urls = urls.filter(url => !url.includes('[')); // Filter IPv6
                return { ...server, urls: ipv4Urls };
            }
            return server;
        });
        
        return config;
    }

    async function flushPendingCandidates() {
        if (!peerConnection || !isRemoteDescriptionSet || !pendingCandidates.length) {
            return;
        }

        const queuedCandidates = [...pendingCandidates];
        pendingCandidates = [];

        for (const candidate of queuedCandidates) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (e) {
                console.error("Queued ICE error:", e);
            }
        }
    }

    async function enableRelayFallback() {
        if (!peerConnection || !currentRoom || isRelayFallbackEnabled) {
            return;
        }

        isRelayFallbackEnabled = true;

        try {
            console.warn("🔁 Switching WebRTC connection to TURN relay mode");
            
            // Create new peer connection with relay-only configuration
            const relayConfig = getPeerConfig(true);
            peerConnection.setConfiguration(relayConfig);
            
            // Restart ICE with relay-only
            const offer = await peerConnection.createOffer({ iceRestart: true });
            await peerConnection.setLocalDescription(offer);

            // Send new offer to remote peer
            socket.emit("offer", {
                room: currentRoom,
                targetUserId: null, // Will be determined by room
                callerUserId: myVideoUserId,
                offer: offer
            });
            
            console.log("✅ Relay fallback initiated successfully");
        } catch (error) {
            console.error("❌ Relay fallback failed:", error);
            // Try one more time with a completely new connection
            setTimeout(() => {
                if (currentRoom) {
                    console.log("🔄 Attempting complete connection restart");
                    startVideoCall(currentRoom.split('-')[1]);
                }
            }, 2000);
        }
    }

    function attachPeerConnectionListeners() {
        peerConnection.ontrack = event => {
            console.log("🎥 Remote track received:", event.track.kind);
            console.log("🎥 Incoming streams:", event.streams);
            const remoteVideo = document.getElementById("remoteVideo");
            
            if (!remoteVideo) {
                console.error("❌ Remote video element not found");
                return;
            }

            if (!remoteStream) {
                remoteStream = new MediaStream();
            }

            const trackAlreadyExists = remoteStream.getTracks().some(track => track.id === event.track.id);
            if (!trackAlreadyExists) {
                remoteStream.addTrack(event.track);
            }

            console.log("🎥 Remote stream tracks now:", remoteStream.getTracks());
            console.log("🎥 Remote video tracks:", remoteStream.getVideoTracks());
            console.log("🎥 Remote audio tracks:", remoteStream.getAudioTracks());
            
            // ✅ FIX: Proper stream handling without interruption
            // Clear any existing timeout to avoid conflicts
            if (remoteVideo.playTimeout) {
                clearTimeout(remoteVideo.playTimeout);
            }
            
            if (remoteVideo.srcObject !== remoteStream) {
                remoteVideo.srcObject = remoteStream;
            }
            remoteVideo.muted = false;
            remoteVideo.autoplay = true;
            remoteVideo.playsInline = true;
            remoteVideo.controls = false;
            remoteVideo.volume = 1;
            remoteVideo.setAttribute('autoplay', 'autoplay');
            remoteVideo.setAttribute('playsinline', 'playsinline');

            // ✅ FIX: Force playback immediately when tracks arrive
            const forcePlay = async () => {
                try {
                    await remoteVideo.play();
                    console.log("✅ Remote media playing immediately after track arrival");
                } catch (e) {
                    console.warn("⚠️ Immediate play failed, will retry:", e.name);
                    // Retry after a short delay
                    setTimeout(async () => {
                        try {
                            await remoteVideo.play();
                            console.log("✅ Remote media playing on retry");
                        } catch (e2) {
                            console.warn("⚠️ Retry failed, waiting for user interaction:", e2.name);
                        }
                    }, 300);
                }
            };
            forcePlay();

            event.track.onunmute = () => {
                console.log(`✅ Remote ${event.track.kind} track unmuted`);
                const playPromise = remoteVideo.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.error("❌ Remote video play error after unmute:", error);
                    });
                }
            };
            
            // ✅ FIX: Add user interaction fallback for autoplay policies
            const enablePlaybackWithInteraction = () => {
                const resumePlayback = async () => {
                    try {
                        await remoteVideo.play();
                        console.log("✅ Remote media started after user interaction");
                        document.removeEventListener('click', resumePlayback);
                        document.removeEventListener('touchstart', resumePlayback);
                    } catch (e) {
                        console.error("❌ Remote media still failed after interaction:", e);
                    }
                };
                document.addEventListener('click', resumePlayback, { once: true });
                document.addEventListener('touchstart', resumePlayback, { once: true });
                console.log("🔊 Click/tap anywhere to enable remote media if blocked");
            };

            // Use a single play attempt with proper error handling
            remoteVideo.playTimeout = setTimeout(() => {
                const playPromise = remoteVideo.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log("✅ Remote video playing successfully");
                    }).catch(error => {
                        if (error.name === 'AbortError') {
                            console.warn("⚠️ Video play was aborted, this is usually harmless");
                        } else if (error.name === 'NotAllowedError') {
                            console.warn("⚠️ Autoplay prevented, user interaction required");
                            enablePlaybackWithInteraction();
                        } else {
                            console.error("❌ Remote video play error:", error);
                        }
                    });
                }
            }, 100);
        };

        peerConnection.onicecandidate = event => {
            if (event.candidate) {
                const candidateStr = event.candidate.candidate;
                let type = "unknown";
                if (candidateStr.includes("typ host")) type = "host";
                else if (candidateStr.includes("typ srflx")) type = "srflx (STUN)";
                else if (candidateStr.includes("typ relay")) type = "relay (TURN)";
                console.log(`ICE candidate [${type}]:`, event.candidate);

                socket.emit("ice-candidate", {
                    room: currentRoom,
                    candidate: event.candidate
                });
            } else {
                console.log("ICE candidate gathering completed.");
            }
        };

        peerConnection.onconnectionstatechange = () => {
            const state = peerConnection.connectionState;
            console.log("WebRTC connection state:", state);
            if (state === 'failed' || state === 'disconnected' || state === 'closed') {
                console.error("❌ WebRTC connection failed - video won't work");
                if ((state === 'failed' || state === 'disconnected') && !isRelayFallbackEnabled) {
                    console.log("🔄 Attempting relay fallback due to connection failure");
                    enableRelayFallback();
                    return;
                }
                if (state === 'failed') {
                    alert("Video connection failed. Please check your network and try again.");
                }
            } else if (state === 'connected') {
                console.log("✅ WebRTC connection established successfully");
            }
        };

        peerConnection.oniceconnectionstatechange = () => {
            const state = peerConnection.iceConnectionState;
            console.log("WebRTC ICE state:", state);
            if (state === 'failed' || state === 'disconnected' || state === 'closed') {
                console.error("❌ ICE connection failed - video won't work");
                if ((state === 'failed' || state === 'disconnected') && !isRelayFallbackEnabled) {
                    console.log("🔄 Attempting relay fallback due to ICE failure");
                    enableRelayFallback();
                    return;
                }
                if (state === 'failed') {
                    alert("ICE connection failed. This might be due to network restrictions or firewall. Please try again.");
                }
            } else if (state === 'connected' || state === 'completed') {
                console.log("✅ ICE connection established successfully");
            }
        };

        peerConnection.onicecandidateerror = event => {
            // Suppress ICE candidate errors to reduce console noise
            // These are common and don't necessarily indicate connection failure
            if (event.errorCode === 701 || event.errorCode === 702) {
                // STUN/TURN server timeout - ignore silently
                return;
            }
            console.error("ICE candidate error:", event);
        };

        // Add more debugging
        peerConnection.onsignalingstatechange = () => {
            console.log("WebRTC signaling state:", peerConnection.signalingState);
        };

        peerConnection.onicegatheringstatechange = () => {
            console.log("WebRTC ICE gathering state:", peerConnection.iceGatheringState);
        };
    }

    async function startVideoCall(userId) {
        console.log("🎥 Starting video call to user:", userId);
        
        // Check if already in call
        if (isInCall) {
            console.log("⚠️ Already in a call, ending current call first");
            endCall();
        }
        
        isInCall = true;
        currentCallUserId = userId;
        currentRoom = "room-" + Math.min(myVideoUserId, userId) + "-" + Math.max(myVideoUserId, userId);
        document.getElementById("videoCallContainer").style.display = "block";

        // Join socket room first
        socket.emit("join-room", currentRoom);
        console.log("📡 Joined room:", currentRoom);

        // Clean up existing connection
        if (peerConnection) {
            peerConnection.ontrack = null;
            peerConnection.onicecandidate = null;
            peerConnection.onconnectionstatechange = null;
            peerConnection.oniceconnectionstatechange = null;
            peerConnection.close();
        }
        
        // Reset remote video
        remoteStream = new MediaStream();
        const remoteVideo = document.getElementById("remoteVideo");
        if (remoteVideo) {
            remoteVideo.pause();
            remoteVideo.srcObject = remoteStream;
            remoteVideo.load();
        }
        
        // Reset state variables
        pendingCandidates = [];
        isRemoteDescriptionSet = false;
        isRelayFallbackEnabled = false;

        // Get user media with enhanced error handling
        try {
            console.log("🎥 Requesting camera and microphone...");
            localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 },
                    facingMode: "user"
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            });
            console.log("✅ Local stream obtained:", localStream);
            console.log("🎥 Video tracks:", localStream.getVideoTracks());
            console.log("🎥 Audio tracks:", localStream.getAudioTracks());
        } catch (e) {
            console.warn("⚠️ Enhanced constraints failed, trying basic constraints");
            try {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                console.log("✅ Basic local stream obtained:", localStream);
            } catch (e2) {
                console.error("❌ Media access failed:", e2);
                alert("Camera/Microphone access is required for video calls. Please allow permissions and try again.");
                isInCall = false;
                currentCallUserId = null;
                endCall();
                return;
            }
        }

        // Setup local video
        const localVideo = document.getElementById("localVideo");
        if (localVideo.srcObject) {
            localVideo.pause();
            localVideo.srcObject = null;
        }
        setTimeout(() => {
            localVideo.srcObject = localStream;
            localVideo.muted = true; // Always mute local video to avoid echo
            localVideo.play().catch(e => console.error("🎥 Local video play error:", e));
        }, 50);

        // Create peer connection with optimized configuration
        peerConnection = new RTCPeerConnection(getPeerConfig());
        attachPeerConnectionListeners();

        // Add local tracks
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });

        // Create and send offer
        try {
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);

            socket.emit("offer", {
                room: currentRoom,
                targetUserId: userId,
                callerUserId: myVideoUserId,
                offer: offer
            });
            console.log("📤 Video call offer sent to user:", userId);
        } catch (error) {
            console.error("❌ Failed to create offer:", error);
            alert("Failed to initiate video call. Please try again.");
            isInCall = false;
            currentCallUserId = null;
            endCall();
        }
    }

    // RECEIVE OFFER
    socket.on("offer", async (data) => {
        console.log("🔥 CHAT OFFER RECEIVED", data);
        
        // Check if we're already in a call with this user
        if (isInCall && currentCallUserId === data.callerUserId) {
            console.log("⚠️ Already in call with this user, ignoring duplicate offer");
            return;
        }
        
        // Check if we're already in any call
        if (isInCall) {
            console.log("⚠️ Already in another call, declining new offer");
            socket.emit("decline-call", {
                room: data.room,
                targetUserId: data.callerUserId
            });
            return;
        }

        console.log("📩 Incoming video offer from user:", data.callerUserId);
        console.log("🔍 Checking for global UI:", window.incomingVideoUI);

        // Wait for global UI to be available (race condition fix)
        let attempts = 0;
        while (!window.incomingVideoUI && attempts < 50) {
            await new Promise(resolve => setTimeout(resolve, 50));
            attempts++;
        }
        
        console.log("🔍 Global UI after wait:", window.incomingVideoUI);

        // Mark that we have a pending call from this user
        currentCallUserId = data.callerUserId;

        // If global UI exists and is visible, let it handle the UI
        if (window.incomingVideoUI && window.incomingVideoUI.style.display === "block") {
            console.log("📱 Global UI is handling the offer, just storing data");
            // Store the data for when user accepts
            window.pendingGlobalVideoData = data;
            window.pendingChatVideoOffer = data;
            sessionStorage.setItem('pendingVideoCall', JSON.stringify(data));
            window.acceptChatVideoCall = async function() {
                if (isInCall) {
                    console.log("⚠️ Already in call, ignoring accept");
                    return;
                }
                sessionStorage.removeItem('pendingVideoCall');
                window.incomingVideoUI.style.display = "none";
                isInCall = true;
                await handleChatVideoOffer(data);
            };
            return;
        }

        // If global UI exists but not visible, show it
        if (window.incomingVideoUI && window.incomingVideoUI.style.display !== "block") {
            
            console.log("📱 Showing global UI for incoming call");
            
            const callerName = `User ${data.callerUserId || data.targetUserId || 'Unknown'}`;
            const callerNameElement = document.getElementById("incomingVideoCallerName");
            if (callerNameElement) {
                callerNameElement.textContent = `Incoming video call from ${callerName}`;
            }
            
            // Store the complete data
            window.pendingGlobalVideoData = data;
            window.pendingChatVideoOffer = data;
            sessionStorage.setItem('pendingVideoCall', JSON.stringify(data));
            
            window.acceptChatVideoCall = async function() {
                if (isInCall) {
                    console.log("⚠️ Already in call, ignoring accept");
                    return;
                }
                sessionStorage.removeItem('pendingVideoCall');
                window.incomingVideoUI.style.display = "none";
                isInCall = true;
                await handleChatVideoOffer(data);
            };
            
            window.incomingVideoUI.style.display = "block";
            return;
        }

        // Fallback: handle immediately if no global UI available
        console.log("📱 No global UI detected after waiting, storing pending call for explicit acceptance");
        console.log("🔍 window.incomingVideoUI:", window.incomingVideoUI);
        console.log("🔍 Attempts made:", attempts);
        sessionStorage.setItem('pendingVideoCall', JSON.stringify(data));
        window.pendingGlobalVideoData = data;
        window.pendingChatVideoOffer = data;
        alert("Incoming video call received. Please accept the call to allow camera and microphone access.");
    });

    async function handleChatVideoOffer(data) {
        console.log("🎥 Handling incoming video offer:", data);
        
        // Check if already in call
        if (isInCall) {
            console.log("⚠️ Already in a call, ignoring offer");
            return;
        }
        
        isInCall = true;
        currentCallUserId = data.callerUserId;
        
        // ✅ JOIN ROOM (IMPORTANT FIX)
        socket.emit("join-room", data.room);
        currentRoom = data.room;

        // Clean up existing connection
        if (peerConnection) {
            peerConnection.ontrack = null;
            peerConnection.onicecandidate = null;
            peerConnection.onconnectionstatechange = null;
            peerConnection.oniceconnectionstatechange = null;
            peerConnection.close();
        }
        
        // Reset remote video
        remoteStream = new MediaStream();
        const remoteVideo = document.getElementById("remoteVideo");
        if (remoteVideo) {
            remoteVideo.pause();
            remoteVideo.srcObject = remoteStream;
            remoteVideo.load();
        }
        
        // Reset state variables
        pendingCandidates = [];
        isRemoteDescriptionSet = false;
        isRelayFallbackEnabled = false;

        // ✅ SHOW VIDEO UI
        document.getElementById("videoCallContainer").style.display = "block";

        // ✅ GET CAMERA with enhanced error handling
        try {
            console.log("🎥 Requesting camera for receiver...");
            localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 },
                    facingMode: "user"
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            });
            console.log("✅ Receiver local stream obtained:", localStream);
            console.log("🎥 Receiver video tracks:", localStream.getVideoTracks());
            console.log("🎥 Receiver audio tracks:", localStream.getAudioTracks());
        } catch (e) {
            console.error("❌ Receiver media error:", e);
            alert("Camera/Microphone access is required to accept video calls. Please allow permissions and try again.");
            isInCall = false;
            currentCallUserId = null;
            endCall();
            return;
        }

        // ✅ FIX: Proper local video setup
        const localVideo = document.getElementById("localVideo");
        if (localVideo.srcObject) {
            localVideo.pause();
            localVideo.srcObject = null;
        }
        setTimeout(() => {
            localVideo.srcObject = localStream;
            localVideo.muted = true; // Always mute local video to avoid echo
            localVideo.play().catch(e => console.error("🎥 Local video play error:", e));
        }, 50);

        // Create peer connection
        peerConnection = new RTCPeerConnection(getPeerConfig());
        attachPeerConnectionListeners();

        // Add local tracks
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });

        // Handle the offer
        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
            isRemoteDescriptionSet = true;
            await flushPendingCandidates();

            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            socket.emit("answer", {
                room: currentRoom,
                answer: answer
            });
            console.log("📤 Video call answer sent");
        } catch (error) {
            console.error("❌ Failed to handle offer:", error);
            alert("Failed to accept video call. Please try again.");
            isInCall = false;
            currentCallUserId = null;
            endCall();
        }
    }

    // RECEIVE ANSWER
    socket.on("answer", async (data) => {
        console.log("✅ ANSWER RECEIVED");

        if (!peerConnection) {
            console.log("⚠️ PeerConnection not ready yet");
            return;
        }

        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            isRemoteDescriptionSet = true;
            await flushPendingCandidates();
        } catch (e) {
            console.error("Answer error:", e);
        }
    });

    // RECEIVE ICE CANDIDATE (🔥 FINAL FIX)
    socket.on("ice-candidate", async (data) => {

        if (!peerConnection) {
            console.log("📦 Storing ICE candidate");
            pendingCandidates.push(data.candidate);
            return;
        }

        if (!isRemoteDescriptionSet) {
            console.log("📦 Queueing ICE candidate until remote description is ready");
            pendingCandidates.push(data.candidate);
            return;
        }

        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
        } catch (e) {
            console.error("ICE error:", e);
        }
    });

    // END CALL
    function endCall() {
        console.log("🛑 Ending call");
        document.getElementById("videoCallContainer").style.display = "none";
        
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }
        
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        
        // ✅ FIX: Proper video cleanup
        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");
        
        if (localVideo) {
            localVideo.pause();
            localVideo.srcObject = null;
        }
        if (remoteVideo) {
            remoteVideo.pause();
            remoteVideo.srcObject = null;
        }
        
        // Reset call state
        pendingCandidates = [];
        currentRoom = null;
        isRemoteDescriptionSet = false;
        remoteStream = null;
        isRelayFallbackEnabled = false;
        isInCall = false;
        currentCallUserId = null;
        
        // Hide any incoming call UI
        if (window.incomingVideoUI) {
            window.incomingVideoUI.style.display = "none";
        }
        
        // Clear pending call data
        sessionStorage.removeItem('pendingVideoCall');
        window.pendingGlobalVideoData = null;
        window.pendingChatVideoOffer = null;
    }

    window.startVideoCall = startVideoCall;
    window.endCall = endCall;
</script>

<style>
    .chat-bubble-left:after {
        content: "";
        position: absolute;
        left: -6px;
        top: 10px;
        border-width: 6px;
        border-style: solid;
        border-color: transparent #e5e7eb transparent transparent;
    }

    .dark .chat-bubble-left:after {
        border-color: transparent #374151 transparent transparent;
    }
</style>