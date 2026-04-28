@if(auth()->check())
<script>
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        console.warn('WebRTC requires HTTPS. Video calls may not work on HTTP.');
    }
</script>
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    if (!window.globalVideoCallListenerInitialized) {
        window.globalVideoCallListenerInitialized = true;

        console.log("Global video call script loading...");

        window.globalVideoSocket = window.globalVideoSocket || io("https://pm.inovace.in");
        const myGlobalVideoUserId = {{ auth()->id() }};

        console.log("Socket created, my ID:", myGlobalVideoUserId);

        window.globalVideoSocket.on("connect", () => {
            console.log("Global video socket connected");
            window.globalVideoSocket.emit("join-user", myGlobalVideoUserId);
            console.log("Joined user room:", `user-${myGlobalVideoUserId}`);
        });

        window.globalVideoSocket.on("disconnect", () => {
            console.log("Global video socket disconnected");
        });

        document.addEventListener("DOMContentLoaded", () => {
            console.log("Creating incoming video call UI");

            if (document.getElementById("incomingVideoCallUI")) {
                window.incomingVideoUI = document.getElementById("incomingVideoCallUI");
                return;
            }

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
                <div style="font-weight: 600; margin-bottom: 8px; color: #1f2937;">Incoming Video Call</div>
                <div id="incomingVideoCallerName" style="color: #6b7280; margin-bottom: 12px;">Connecting...</div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="acceptGlobalVideoCall()" style="flex:1; background:#10b981; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Accept</button>
                    <button onclick="declineGlobalVideoCall()" style="flex:1; background:#ef4444; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Decline</button>
                </div>
            `;
            document.body.appendChild(incomingVideoUI);

            window.incomingVideoUI = incomingVideoUI;
            console.log("Incoming video call UI created");
        });

        let pendingGlobalVideoOffer = null;
        let pendingGlobalVideoCallerId = null;
        let pendingGlobalVideoCallerName = null;
        let pendingGlobalVideoCallData = null;

        window.globalVideoSocket.on("offer", async (data) => {
            console.log("GLOBAL VIDEO OFFER RECEIVED", data);

            if (window.peerConnection || (document.getElementById("videoCallContainer") && document.getElementById("videoCallContainer").style.display === "block")) {
                console.log("Ignoring offer - already in video call");
                return;
            }

            if (window.showIncomingChatVideoCall && typeof window.showIncomingChatVideoCall === 'function') {
                console.log("Delegating incoming video call to chat popup");
                window.showIncomingChatVideoCall(data);
                return;
            }

            pendingGlobalVideoCallData = data;
            pendingGlobalVideoOffer = data.offer;
            pendingGlobalVideoCallerId = data.callerUserId || data.targetUserId || data.room;
            pendingGlobalVideoCallerName = data.callerName || `User ${pendingGlobalVideoCallerId}`;

            const showPopup = () => {
                if (window.incomingVideoUI) {
                    document.getElementById("incomingVideoCallerName").textContent = `Incoming video call from ${pendingGlobalVideoCallerName}`;
                    window.incomingVideoUI.style.display = "block";
                    console.log("Popup shown");
                } else {
                    console.log("UI not ready, retrying in 100ms...");
                    setTimeout(showPopup, 100);
                }
            };

            showPopup();
        });

        window.acceptGlobalVideoCall = async function() {
            console.log("Global accept button clicked");
            if (!pendingGlobalVideoOffer) {
                console.log("No pending offer");
                return;
            }

            window.incomingVideoUI.style.display = "none";

            if (window.acceptChatVideoCall && typeof window.acceptChatVideoCall === 'function') {
                console.log("Using inline chat video UI");
                await window.acceptChatVideoCall();
                return;
            }

            const callerId = pendingGlobalVideoCallData?.callerUserId || pendingGlobalVideoCallerId;
            const callData = {
                ...(pendingGlobalVideoCallData || {}),
                offer: pendingGlobalVideoOffer,
                callerUserId: callerId,
                targetUserId: pendingGlobalVideoCallData?.targetUserId || myGlobalVideoUserId,
                room: pendingGlobalVideoCallData?.room || `room-${Math.min(myGlobalVideoUserId, callerId)}-${Math.max(myGlobalVideoUserId, callerId)}`,
                callerName: pendingGlobalVideoCallerName
            };

            sessionStorage.setItem('pendingVideoCall', JSON.stringify(callData));

            const popupWindow = window.open(
                `/video-call/${callerId}`,
                "VideoCallWindow",
                "width=800,height=600,resizable=yes,scrollbars=yes"
            );

            if (!popupWindow) {
                console.error("Failed to open video call popup - popup blocked");
                window.location.href = `/user-chat?selectUser=${callerId}&videoCall=true`;
            }
        };

        window.declineGlobalVideoCall = function() {
            if (window.incomingVideoUI) {
                window.incomingVideoUI.style.display = "none";
            }
            pendingGlobalVideoOffer = null;
            pendingGlobalVideoCallerId = null;
            pendingGlobalVideoCallerName = null;
            pendingGlobalVideoCallData = null;
        };
    }
</script>
@endif
