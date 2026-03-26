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
    const globalVideoSocket = io("https://pm.inovace.in");
    const myGlobalVideoUserId = {{ auth()->id() }};
    globalVideoSocket.emit("join-user", myGlobalVideoUserId);

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

    let pendingGlobalVideoOffer = null;
    let pendingGlobalVideoCallerId = null;
    let pendingGlobalVideoCallerName = null;

    globalVideoSocket.on("offer", async (data) => {
        console.log("🔥 GLOBAL VIDEO OFFER RECEIVED", data);
        // Ignore if we are already in the chat video flow
        if (window.peerConnection || (document.getElementById("videoCallContainer") && document.getElementById("videoCallContainer").style.display === "block")) {
            console.log("Ignoring offer - already in video call");
            return;
        }

        console.log("🔥 GLOBAL VIDEO OFFER RECEIVED");
        pendingGlobalVideoOffer = data.offer;
        pendingGlobalVideoCallerId = data.targetUserId || data.room;
        pendingGlobalVideoCallerName = `User ${pendingGlobalVideoCallerId}`;

        document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${pendingGlobalVideoCallerName}`;
        incomingVideoUI.style.display = "block";
    });

    window.acceptGlobalVideoCall = async function() {
        if (!pendingGlobalVideoOffer) return;

        incomingVideoUI.style.display = "none";

        // Redirect to chat page with the caller selected
        window.location.href = `/chat?selectUser=${pendingGlobalVideoCallerId}`;
    };

    window.declineGlobalVideoCall = function() {
        incomingVideoUI.style.display = "none";
        pendingGlobalVideoOffer = null;
        pendingGlobalVideoCallerId = null;
        pendingGlobalVideoCallerName = null;
    };
</script>
@endif

</body>
</html>