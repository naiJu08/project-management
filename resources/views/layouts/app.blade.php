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
<script>
    // Check for HTTPS before proceeding
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        console.warn('⚠️ WebRTC requires HTTPS. Video calls may not work on HTTP.');
    }
</script>
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    // ================= GLOBAL VIDEO CALL LISTENER =================
    const globalVideoSocket = io("https://pm.inovace.in");
    const myGlobalVideoUserId = {{ auth()->id() }};
    
    globalVideoSocket.on("connect", () => {
        console.log("✅ Global video socket connected");
        globalVideoSocket.emit("join-user", myGlobalVideoUserId);
        console.log("📡 Joined user room:", `user-${myGlobalVideoUserId}`);
    });
    
    globalVideoSocket.on("disconnect", () => {
        console.log("❌ Global video socket disconnected");
    });

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
            <button onclick="acceptGlobalVideoCall()" style="flex:1; background:#10b981; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Accept</button>
            <button onclick="declineGlobalVideoCall()" style="flex:1; background:#ef4444; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Decline</button>
        </div>
    `;
    document.body.appendChild(incomingVideoUI);
    
    // Make incomingVideoUI globally accessible
    window.incomingVideoUI = incomingVideoUI;

    let pendingGlobalVideoOffer = null;
    let pendingGlobalVideoCallerId = null;
    let pendingGlobalVideoCallerName = null;

    // Global call state management
    window.isInCall = false;
    window.currentCallUserId = null;

    // Clean up any existing call state on page load
    function cleanupCallState() {
        console.log("🧹 Cleaning up call state on page load");
        window.isInCall = false;
        window.currentCallUserId = null;
        pendingGlobalVideoOffer = null;
        pendingGlobalVideoCallerId = null;
        pendingGlobalVideoCallerName = null;
        incomingVideoUI.style.display = "none";
        sessionStorage.removeItem('pendingVideoCall');
        sessionStorage.removeItem('isInCall');
        sessionStorage.removeItem('currentCallUserId');
    }

    // Call cleanup on page load
    cleanupCallState();

    // Listen for call state changes from chat component
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'call-state-change') {
            console.log("📞 Received call state change:", event.data);
            window.isInCall = event.data.isInCall;
            window.currentCallUserId = event.data.currentCallUserId;
            
            // Update sessionStorage for persistence across page reloads
            sessionStorage.setItem('isInCall', window.isInCall);
            sessionStorage.setItem('currentCallUserId', window.currentCallUserId || '');
            
            // Hide incoming call UI if we're now in a call
            if (window.isInCall && incomingVideoUI.style.display === 'block') {
                console.log("🔄 Hiding incoming call UI - now in call");
                incomingVideoUI.style.display = 'none';
                pendingGlobalVideoOffer = null;
                pendingGlobalVideoCallerId = null;
                pendingGlobalVideoCallerName = null;
            }
        }
    });

    globalVideoSocket.on("offer", async (data) => {
        console.log("🔥 GLOBAL VIDEO OFFER RECEIVED", data);
        
        // Restore call state from sessionStorage if available
        if (sessionStorage.getItem('isInCall') === 'true') {
            window.isInCall = true;
            window.currentCallUserId = sessionStorage.getItem('currentCallUserId');
        }
        
        // Ignore if we are already in the chat video flow or already in a call
        if (window.peerConnection || 
            (document.getElementById("videoCallContainer") && document.getElementById("videoCallContainer").style.display === "block") || 
            window.isInCall) {
            console.log("⚠️ Ignoring offer - already in video call or call state");
            // Clear the pending offer if we're ignoring it to prevent stale data
            pendingGlobalVideoOffer = null;
            pendingGlobalVideoCallerId = null;
            pendingGlobalVideoCallerName = null;
            return;
        }

        console.log("🔥 Processing new global video offer");
        pendingGlobalVideoOffer = data.offer;
        pendingGlobalVideoCallerId = data.callerUserId || data.targetUserId || data.room;
        pendingGlobalVideoCallerName = `User ${pendingGlobalVideoCallerId}`;

        document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${pendingGlobalVideoCallerName}`;
        incomingVideoUI.style.display = "block";
        
        // Auto-hide after 30 seconds if no response
        setTimeout(() => {
            if (incomingVideoUI.style.display === "block" && !window.isInCall) {
                console.log("⏰ Auto-hiding incoming call UI after 30 seconds");
                incomingVideoUI.style.display = "none";
                pendingGlobalVideoOffer = null;
                pendingGlobalVideoCallerId = null;
                pendingGlobalVideoCallerName = null;
            }
        }, 30000);
    });

    window.acceptGlobalVideoCall = async function() {
        console.log("📱 Global accept button clicked");
        if (!pendingGlobalVideoOffer) {
            console.log("❌ No pending offer");
            return;
        }

        // Set call state immediately to prevent duplicate popups
        window.isInCall = true;
        window.currentCallUserId = pendingGlobalVideoCallerId;
        sessionStorage.setItem('isInCall', 'true');
        sessionStorage.setItem('currentCallUserId', pendingGlobalVideoCallerId);
        
        incomingVideoUI.style.display = "none";
        pendingGlobalVideoOffer = null;
        pendingGlobalVideoCallerId = null;
        pendingGlobalVideoCallerName = null;

        // If we're on the chat page, trigger the offer handling
        if (window.location.pathname.includes('/chat')) {
            console.log("📱 Using chat page accept handler");
            // Call the handleChatVideoOffer function directly with the pending offer
            if (window.handleChatVideoOffer && window.pendingChatVideoOffer) {
                await window.handleChatVideoOffer(window.pendingChatVideoOffer);
            } else {
                console.log("❌ handleChatVideoOffer not available, waiting...");
                // Wait a bit and try again
                setTimeout(async () => {
                    if (window.handleChatVideoOffer && window.pendingChatVideoOffer) {
                        await window.handleChatVideoOffer(window.pendingChatVideoOffer);
                    } else {
                        // If still not available, redirect
                        window.location.href = `/chat?selectUser=${window.currentCallUserId}&videoCall=true`;
                    }
                }, 500);
            }
            return;
        }

        // Otherwise redirect to chat page with the caller selected
        window.location.href = `/chat?selectUser=${window.currentCallUserId}&videoCall=true`;
    };

    window.declineGlobalVideoCall = function() {
        console.log("📱 Global decline button clicked");
        incomingVideoUI.style.display = "none";
        
        // Send decline signal to caller
        if (pendingGlobalVideoCallerId) {
            globalVideoSocket.emit("decline-call", {
                targetUserId: pendingGlobalVideoCallerId
            });
        }
        
        // Clear all call state
        pendingGlobalVideoOffer = null;
        pendingGlobalVideoCallerId = null;
        pendingGlobalVideoCallerName = null;
        window.isInCall = false;
        window.currentCallUserId = null;
        sessionStorage.removeItem('isInCall');
        sessionStorage.removeItem('currentCallUserId');
        sessionStorage.removeItem('pendingVideoCall');
    };

    // Global function to end call from anywhere
    window.endGlobalCall = function() {
        console.log("🛑 Ending global call");
        window.isInCall = false;
        window.currentCallUserId = null;
        sessionStorage.removeItem('isInCall');
        sessionStorage.removeItem('currentCallUserId');
        sessionStorage.removeItem('pendingVideoCall');
        
        if (incomingVideoUI) {
            incomingVideoUI.style.display = "none";
        }
        
        pendingGlobalVideoOffer = null;
        pendingGlobalVideoCallerId = null;
        pendingGlobalVideoCallerName = null;
    };
</script>
@endif

</body>
</html>