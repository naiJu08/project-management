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
        style="position:absolute; bottom:20px; right:20px; width:200px; border-radius:10px; background:#000;"></video>

    <video id="remoteVideo" autoplay playsinline
        style="width:100%; height:100%; object-fit:cover; background:#000;"></video>

    <button onclick="endCall()" style="position:absolute; bottom:20px; left:50%; transform:translateX(-50%);
                   background:red; color:white; padding:10px 20px; border-radius:50px;">
        End Call
    </button>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>

<script>
    // Check browser compatibility
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('❌ Browser does not support WebRTC');
        alert('Your browser does not support video calls. Please use a modern browser like Chrome, Firefox, or Safari.');
    }

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

    // Initialize Pusher
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

    const socket = io("https://pm.inovace.in");
    const myVideoUserId = {{ auth()->id() }};

    socket.emit("join-user", myVideoUserId);

    document.addEventListener("DOMContentLoaded", () => {

        const myId = myVideoUserId;
        const selectedUser = @this.get('selectedUser');

        if (selectedUser) {
            const room = "room-" + Math.min(myId, selectedUser) + "-" + Math.max(myId, selectedUser);
            socket.emit("join-room", room);
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

    const iceServers = [
        // STUN servers for NAT discovery
        { urls: "stun:stun.l.google.com:19302" },
        { urls: "stun:stun1.l.google.com:19302" },
        { urls: "stun:stun2.l.google.com:19302" },
        { urls: "stun:stun3.l.google.com:19302" },
        { urls: "stun:stun4.l.google.com:19302" },

        // Your own TURN server (now properly configured)
        {
            urls: [
                "turn:pm.inovace.in:3478?transport=udp",
                "turn:pm.inovace.in:3478?transport=tcp"
            ],
            username: "webrtcuser",
            credential: "strongpassword123"
        },

        // Public TURN servers as backup
        {
            urls: [
                "turn:openrelay.metered.ca:80",
                "turn:openrelay.metered.ca:443",
                "turn:openrelay.metered.ca:443?transport=tcp"
            ],
            username: "openrelayproject",
            credential: "openrelayproject"
        },
        {
            urls: [
                "turn:turn.anyfirewall.com:3478?transport=udp",
                "turn:turn.anyfirewall.com:3478?transport=tcp"
            ],
            username: "anyfirewall",
            credential: "anyfirewall"
        }
    ];

    const config = {
        iceServers: iceServers,
        iceCandidatePoolSize: 10,
        iceTransportPolicy: "all",
        sdpSemantics: "unified-plan",
        bundlePolicy: "max-bundle",
        rtcpMuxPolicy: "require"
    };

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

    function attachPeerConnectionListeners() {
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                socket.emit("ice-candidate", {
                    room: currentRoom,
                    candidate: event.candidate
                });
            }
        };

        peerConnection.ontrack = (event) => {
            console.log("📡 Received remote track:", event.track.kind, event.streams.length, "streams");
            
            if (event.streams && event.streams[0]) {
                const remoteVideo = document.getElementById("remoteVideo");
                const stream = event.streams[0];
                
                // Only set srcObject if it's different to prevent AbortError
                if (remoteVideo.srcObject !== stream) {
                    console.log("🎬 Setting new remote stream");
                    remoteVideo.srcObject = stream;
                    
                    // Wait for metadata to load before playing
                    remoteVideo.onloadedmetadata = () => {
                        console.log("🎬 Metadata loaded, playing video");
                        remoteVideo.play().catch(e => console.error("❌ Remote video play error:", e));
                    };
                }
                
                // Log audio track details
                const audioTracks = stream.getAudioTracks();
                if (audioTracks.length > 0) {
                    console.log("🎤 Remote audio track received:", audioTracks[0].label, "enabled:", audioTracks[0].enabled);
                    // Ensure remote audio is not muted
                    remoteVideo.muted = false;
                }
                
                const videoTracks = stream.getVideoTracks();
                console.log("✅ Remote stream attached with", audioTracks.length, "audio tracks and", videoTracks.length, "video tracks");
            }
        };

        peerConnection.onconnectionstatechange = () => {
            console.log("Connection state:", peerConnection.connectionState);
            if (peerConnection.connectionState === "disconnected" || 
                peerConnection.connectionState === "failed" || 
                peerConnection.connectionState === "closed") {
                endCall();
            }
        };
    }

    async function startVideoCall(userId) {
        // Check if media devices are available
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Video calls are not supported in this browser or on insecure connections (HTTP). Please use HTTPS and a modern browser.');
            return;
        }

        currentRoom = "room-" + Math.min(myVideoUserId, userId) + "-" + Math.max(myVideoUserId, userId);
        document.getElementById("videoCallContainer").style.display = "block";

        socket.emit("join-room", currentRoom);

        if (peerConnection) {
            peerConnection.close();
        }
        pendingCandidates = [];
        isRemoteDescriptionSet = false;

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 44100,
                    channelCount: 2,
                    latency: 0
                }
            });
            
            console.log("✅ Media stream obtained:", localStream.getTracks().length, "tracks");
            
            // Ensure audio tracks are enabled and log details
            localStream.getAudioTracks().forEach(track => {
                track.enabled = true;
                console.log("🎤 Audio track enabled:", track.label, "enabled:", track.enabled, "state:", track.readyState, "settings:", track.getSettings());
            });
            
            // Ensure video tracks are enabled and log details
            localStream.getVideoTracks().forEach(track => {
                track.enabled = true;
                console.log("📹 Video track enabled:", track.label, "enabled:", track.enabled, "state:", track.readyState);
            });
            
        } catch (e) {
            console.error("❌ Media access error:", e);
            alert("Camera/Microphone access denied. Please allow permissions and try again.\n\nError: " + e.message);
            return;
        }

        document.getElementById("localVideo").srcObject = localStream;
        
        // Ensure local video is muted to prevent echo
        document.getElementById("localVideo").muted = true;
        
        // Ensure remote video is not muted for audio
        document.getElementById("remoteVideo").muted = false;

        peerConnection = new RTCPeerConnection(config);
        attachPeerConnectionListeners();

        // ✅ ADD ALL TRACKS (AUDIO AND VIDEO) TO PEER CONNECTION
        localStream.getTracks().forEach(track => {
            console.log("📡 Adding track to peer connection:", track.kind, track.label, "enabled:", track.enabled, "settings:", track.getSettings());
            peerConnection.addTrack(track, localStream);
        });

        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);

        socket.emit("offer", {
            room: currentRoom,
            targetUserId: userId,
            offer: offer,
            callerName: "{{ auth()->user()->name }}",
            callerId: myVideoUserId
        });
    }

    // RECEIVE OFFER
    socket.on("offer", async (data) => {

        console.log("🔥 OFFER RECEIVED");
        console.log("📩 Incoming video offer from user:", data.targetUserId);

        // If global UI exists, use it instead of starting video immediately
        if (window.incomingVideoUI) {
            console.log("📱 Using global incoming UI");
            window.incomingVideoUI.style.display = "block";
            
            // Get caller name from the data or use fallback
            const callerName = data.callerName || `User ${data.targetUserId}` || 'Unknown User';
            document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${callerName}`;
            
            // Store offer for when user accepts
            window.pendingChatVideoOffer = data;
            
            // Find and override the accept button click handler
            const acceptBtn = window.incomingVideoUI.querySelector("button");
            if (acceptBtn) {
                // Remove existing onclick
                acceptBtn.removeAttribute('onclick');
                // Add new click listener
                acceptBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    console.log("📱 Accept button clicked in chat");
                    window.incomingVideoUI.style.display = "none";
                    await handleChatVideoOffer(data);
                });
            }
            return;
        }

        // Fallback: handle immediately if no global UI
        (async () => {
            await handleChatVideoOffer(data);
        })();
    });

    async function handleChatVideoOffer(data) {
        // ✅ JOIN ROOM (IMPORTANT FIX)
        socket.emit("join-room", data.room);

        currentRoom = data.room;

        if (peerConnection) {
            peerConnection.close();
        }
        pendingCandidates = [];
        isRemoteDescriptionSet = false;

        // ✅ SHOW VIDEO UI
        document.getElementById("videoCallContainer").style.display = "block";

        // ✅ GET CAMERA WITH AUDIO
        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 44100,
                    channelCount: 2,
                    latency: 0
                }
            });
            
            console.log("✅ Receiver media stream obtained:", localStream.getTracks().length, "tracks");
            
            // Ensure audio tracks are enabled and log details
            localStream.getAudioTracks().forEach(track => {
                track.enabled = true;
                console.log("🎤 Receiver audio track enabled:", track.label, "enabled:", track.enabled, "state:", track.readyState, "settings:", track.getSettings());
            });
            
            // Ensure video tracks are enabled and log details
            localStream.getVideoTracks().forEach(track => {
                track.enabled = true;
                console.log("📹 Receiver video track enabled:", track.label, "enabled:", track.enabled, "state:", track.readyState);
            });
            
        } catch (e) {
            console.error("❌ Receiver media access error:", e);
            alert("Camera/Microphone access required for video calls. Please allow permissions and try again.\n\nError: " + e.message);
            return;
        }

        document.getElementById("localVideo").srcObject = localStream;

        peerConnection = new RTCPeerConnection(config);
        attachPeerConnectionListeners();

        // ✅ ADD ALL TRACKS (AUDIO AND VIDEO) TO PEER CONNECTION
        localStream.getTracks().forEach(track => {
            console.log("📡 Adding receiver track to peer connection:", track.kind, track.label, "enabled:", track.enabled, "settings:", track.getSettings());
            peerConnection.addTrack(track, localStream);
        });
        
        // Ensure local video is not muted
        document.getElementById("localVideo").muted = true; // Keep local video muted to avoid echo
        
        // Ensure remote video is not muted
        document.getElementById("remoteVideo").muted = false;

        await peerConnection.setRemoteDescription(data.offer);
        isRemoteDescriptionSet = true;
        await flushPendingCandidates();

        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);

        socket.emit("answer", {
            room: currentRoom,
            answer: answer
        });
    }

    // Make handleChatVideoOffer globally available
    window.handleChatVideoOffer = handleChatVideoOffer;

    // RECEIVE ANSWER
    socket.on("answer", async (data) => {

        console.log("✅ ANSWER RECEIVED");

        if (!peerConnection) {
            console.log("⚠️ PeerConnection not ready yet");
            return;
        }

        try {
            await peerConnection.setRemoteDescription(data.answer);
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
        console.log("📞 Ending call...");
        
        document.getElementById("videoCallContainer").style.display = "none";
        
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
            console.log("✅ PeerConnection closed");
        }
        
        if (localStream) {
            localStream.getTracks().forEach(track => {
                track.stop();
                console.log("🛑 Stopped track:", track.kind);
            });
            localStream = null;
        }
        
        document.getElementById("localVideo").srcObject = null;
        document.getElementById("remoteVideo").srcObject = null;
        pendingCandidates = [];
        currentRoom = null;
        isRemoteDescriptionSet = false;
        
        console.log("✅ Call ended and resources cleaned up");
    }

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