<!DOCTYPE html>
<html>
<head>
    <title>User Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @livewireStyles
</head>

<body>

@yield('content')

@livewireScripts

<!-- GLOBAL VIDEO CALL LISTENER (works on any page) -->
@if(auth()->check())
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    // ================= GLOBAL VIDEO CALL LISTENER =================
    // Use a single global socket to avoid conflicts
    if (!window.globalVideoSocket) {
        console.log("🔧 Initializing socket connection...");
        try {
            window.globalVideoSocket = io("https://pm.inovace.in", {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });
            console.log("📡 Attempting connection to: https://pm.inovace.in");
        } catch (error) {
            console.error("❌ Failed to connect to socket server:", error);
            // Fallback to localhost
            window.globalVideoSocket = io("http://localhost:3000", {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });
            console.log("📡 Trying fallback to: http://localhost:3000");
        }
    }
    const globalVideoSocket = window.globalVideoSocket;
    const myGlobalVideoUserId = {{ auth()->id() }};
    
    globalVideoSocket.on("connect", () => {
        console.log("✅ Global video socket connected");
        globalVideoSocket.emit("join-user", myGlobalVideoUserId);
        console.log("📡 Joined user room:", `user-${myGlobalVideoUserId}`);
        
        // Test connection by sending a ping
        setTimeout(() => {
            console.log("🔍 Testing connection - socket connected:", globalVideoSocket.connected);
            console.log("🔍 Socket ID:", globalVideoSocket.id);
        }, 1000);
    });
    
    globalVideoSocket.on("connect_error", (error) => {
        console.error("❌ Global video socket connection error:", error);
        console.log("🔄 Trying fallback connection...");
        // Try alternative connection
        try {
            window.globalVideoSocket = io("http://192.168.29.50:3000", {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });
            console.log("📡 Trying fallback to: http://192.168.29.50:3000");
        } catch (fallbackError) {
            console.error("❌ Fallback connection also failed:", fallbackError);
            console.log("⚠️ Please ensure the socket server is running on port 3000");
        }
    });
    
    globalVideoSocket.on("disconnect", () => {
        console.log("❌ Global video socket disconnected");
    });

    // Initialize global UI immediately
    document.addEventListener("DOMContentLoaded", function() {
        console.log("🔧 Initializing global video UI...");
        
        // Check if UI already exists (from Filament page)
        if (window.incomingVideoUI) {
            console.log("✅ Global video UI already exists");
            return;
        }
        
        // Incoming video call UI
        const incomingVideoUI = document.createElement("div");
        incomingVideoUI.id = "incomingVideoCallUI";
        incomingVideoUI.style.cssText = `
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 10000;
            min-width: 280px;
            font-family: system-ui;
        `;
        incomingVideoUI.innerHTML = `
            <div style="font-weight: 600; margin-bottom: 8px; color: #1f2937;">📹 Incoming Video Call</div>
            <div id="incomingVideoCallerName" style="color: #6b7280; margin-bottom: 12px;">Connecting...</div>
            <div style="display: flex; gap: 8px;">
                <button onclick="window.acceptGlobalVideoCall()" style="flex:1; background:#10b981; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Accept</button>
                <button onclick="window.declineGlobalVideoCall()" style="flex:1; background:#ef4444; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Decline</button>
            </div>
        `;
        document.body.appendChild(incomingVideoUI);
        incomingVideoUI.style.zIndex = "999999";
        window.incomingVideoUI = incomingVideoUI;
        
        console.log("✅ Global video UI initialized:", window.incomingVideoUI);
        
        // Test popup manually (remove this in production)
        window.testVideoCallPopup = function() {
            console.log("🧪 Testing video call popup...");
            const testData = {
                offer: { type: "offer", sdp: "test-sdp" },
                callerUserId: 999,
                targetUserId: myGlobalVideoUserId,
                room: "test-room",
                callerName: "Test User"
            };
            
            document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${testData.callerName}`;
            incomingVideoUI.style.display = "block";
            console.log("📱 Test popup displayed");
        };
        
        console.log("💡 Test function available: window.testVideoCallPopup()");
        
        // Now set up the offer listener after UI is ready
        setupGlobalVideoOfferListener();
    });
    
    function setupGlobalVideoOfferListener() {
        let pendingGlobalVideoOffer = null;
        let pendingGlobalVideoCallerId = null;
        let pendingGlobalVideoCallerName = null;

        globalVideoSocket.on("offer", async (data) => {
            console.log("🔥 GLOBAL VIDEO OFFER RECEIVED", data);
            console.log("🔍 Global UI available:", window.incomingVideoUI);
            
            // Ignore if we are already in a video call
            if (window.peerConnection || (document.getElementById("videoCallContainer") && document.getElementById("videoCallContainer").style.display === "block")) {
                console.log("Ignoring offer - already in video call");
                return;
            }

            console.log("🔥 Processing global video offer");
            pendingGlobalVideoOffer = data.offer;
            pendingGlobalVideoCallerId = data.callerUserId || data.targetUserId || data.room;
            
            // Try to get caller name - improved logic
            let callerName = `User ${pendingGlobalVideoCallerId}`;
            
            // Store the complete data for later use
            window.pendingGlobalVideoData = {
                offer: data.offer,
                callerUserId: data.callerUserId,
                targetUserId: data.targetUserId,
                room: data.room,
                callerName: callerName
            };

            document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${callerName}`;
            incomingVideoUI.style.display = "block";
            
            console.log("📱 Incoming call popup displayed for:", callerName);
        });

        window.acceptGlobalVideoCall = async function() {
            if (!pendingGlobalVideoOffer) return;

            console.log("🎯 Accepting global video call...");
            incomingVideoUI.style.display = "none";

            // Store the data for the chat page to use
            sessionStorage.setItem('pendingVideoCall', JSON.stringify(window.pendingGlobalVideoData));

            // Redirect to chat page with the caller selected
            window.location.href = `/user-chat?selectUser=${pendingGlobalVideoCallerId}&videoCall=true`;
        };

        window.declineGlobalVideoCall = function() {
            console.log("❌ Declining global video call");
            incomingVideoUI.style.display = "none";
            pendingGlobalVideoOffer = null;
            pendingGlobalVideoCallerId = null;
            pendingGlobalVideoCallerName = null;
            
            // Clean up stored data
            sessionStorage.removeItem('pendingVideoCall');
            window.pendingGlobalVideoData = null;
        };
    }
</script>
@endif

</body>
</html>