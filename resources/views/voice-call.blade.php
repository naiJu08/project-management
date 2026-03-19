<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Call - {{ $user->name }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <style>
        #startBtn { display: block; }
        #acceptBtn { display: none; }
        #endBtn { display: block; }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">
    <div class="text-center w-full max-w-md px-4">
        <!-- User Avatar -->
        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <!-- Call Title -->
        <h2 id="callTitle" class="mt-4 text-xl font-semibold">
            Voice Call with {{ $user->name }}
        </h2>

        <!-- Status Message -->
        <p id="callStatus" class="text-gray-400 text-sm mt-1">
            Initializing...
        </p>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-center mt-8 flex-wrap">
            <button id="startBtn" onclick="startCall()" class="bg-green-500 px-6 py-3 rounded-full text-lg hover:bg-green-600 transition flex items-center gap-2">
                <span>📞</span> Start Call
            </button>
            <button id="acceptBtn" onclick="acceptCall()" class="bg-green-600 px-6 py-3 rounded-full text-lg hover:bg-green-700 transition flex items-center gap-2 animate-pulse">
                <span>✅</span> Accept Call
            </button>
            <button id="endBtn" onclick="endCall()" class="bg-red-500 px-6 py-3 rounded-full text-lg hover:bg-red-600 transition">
                ❌ End
            </button>
        </div>
    </div>

    <script>
        (function() {
            "use strict";

            // ==================== CONFIG ====================
            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = "{{ $user->name }}";
            
            // ==================== STATE ====================
            let localStream = null;
            let peerConnection = null;
            let incomingOffer = null;
            let incomingCallerId = null;
            let incomingCallerName = null;
            let pendingCandidates = [];
            let isRemoteSet = false;
            let callActive = false;
            let connectionTimeout = null;
            let pusher = null;
            let channel = null;

            // ==================== CHECK PENDING CALL ====================
            function checkPendingCall() {
                try {
                    const pendingCall = sessionStorage.getItem('pendingCall');
                    if (pendingCall) {
                        const callData = JSON.parse(pendingCall);
                        const age = Date.now() - callData.timestamp;
                        
                        console.log(`Found pending call, age: ${age}ms`);
                        
                        if (age < 15000) { // 15 seconds
                            console.log("Using pending call data");
                            incomingOffer = callData.offer;
                            incomingCallerId = callData.callerId;
                            incomingCallerName = callData.callerName;
                            
                            // Clear it
                            sessionStorage.removeItem('pendingCall');
                            
                            // Show accept button
                            showAcceptMode();
                            document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                            updateStatus("Incoming call - Click Accept");
                            
                            return true;
                        } else {
                            sessionStorage.removeItem('pendingCall');
                        }
                    }
                } catch (e) {
                    console.error("Error checking pending call:", e);
                }
                return false;
            }

            // ==================== LISTEN FOR POST MESSAGES ====================
            window.addEventListener('message', function(event) {
                console.log("📨 Received message:", event.data.type);
                
                if (event.data.type === 'incoming-offer') {
                    console.log("📞 Received offer via postMessage");
                    incomingOffer = event.data.offer;
                    incomingCallerId = event.data.callerId;
                    incomingCallerName = event.data.callerName;
                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - Click Accept");
                }
                
                if (event.data.type === 'ice-candidate') {
                    console.log("❄️ Received ICE candidate via postMessage");
                    if (!peerConnection) {
                        pendingCandidates.push(event.data.candidate);
                    } else if (!isRemoteSet) {
                        pendingCandidates.push(event.data.candidate);
                    } else {
                        peerConnection.addIceCandidate(new RTCIceCandidate(event.data.candidate))
                            .catch(err => console.error("Error adding ICE:", err));
                    }
                }
                
                if (event.data.type === 'call-answer') {
                    console.log("✅ Received answer via postMessage");
                    handleAnswer(event.data.answer);
                }
            });

            // ==================== UI FUNCTIONS ====================
            function updateStatus(message) {
                console.log("STATUS:", message);
                document.getElementById("callStatus").innerHTML = message;
            }

            function showStartMode() {
                document.getElementById("startBtn").style.display = "block";
                document.getElementById("acceptBtn").style.display = "none";
                document.getElementById("callTitle").textContent = `Call ${otherUserName}`;
            }

            function showAcceptMode() {
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "block";
            }

            function hideAllButtons() {
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "none";
            }

            // ==================== WEBRTC ====================
            function createPeer() {
                console.log("Creating peer connection");
                updateStatus("Setting up connection...");

                pendingCandidates = [];
                isRemoteSet = false;

                peerConnection = new RTCPeerConnection({
                    iceServers: [
                        { urls: "stun:stun.l.google.com:19302" },
                        {
                            urls: [
                                "turn:pm.inovace.in:3478?transport=udp",
                                "turn:pm.inovace.in:3478?transport=tcp"
                            ],
                            username: "webrtcuser",
                            credential: "strongpassword123"
                        }
                    ]
                });

                peerConnection.onconnectionstatechange = () => {
                    console.log("Connection state:", peerConnection.connectionState);
                    updateStatus(`Connection: ${peerConnection.connectionState}`);
                    if (peerConnection.connectionState === 'connected') {
                        updateStatus("✅ Call connected!");
                    }
                };

                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        console.log("Sending ICE candidate");
                        fetch('/send-ice', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                candidate: event.candidate,
                                senderId: userId,
                                receiverId: incomingCallerId ?? otherUserId
                            })
                        }).catch(err => console.error("Error sending ICE:", err));
                    }
                };

                peerConnection.ontrack = (event) => {
                    console.log("Remote audio received");
                    updateStatus("Audio connected - Call active");

                    let audio = document.getElementById("remoteAudio");
                    if (!audio) {
                        audio = document.createElement("audio");
                        audio.id = "remoteAudio";
                        audio.autoplay = true;
                        document.body.appendChild(audio);
                    }
                    audio.srcObject = event.streams[0];
                };
            }

            // ==================== CALL FUNCTIONS ====================
            window.startCall = async function() {
                console.log("Starting call...");
                
                if (callActive) return;
                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Requesting microphone...');

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    console.log("Microphone access granted");
                } catch (err) {
                    console.error("Microphone error:", err);
                    alert("Microphone access is required for calls");
                    updateStatus("❌ Microphone access denied");
                    callActive = false;
                    showStartMode();
                    return;
                }

                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                try {
                    const offer = await peerConnection.createOffer();
                    await peerConnection.setLocalDescription(offer);
                    
                    updateStatus("Sending call request...");
                    
                    const response = await fetch('/send-offer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            offer: { type: offer.type, sdp: offer.sdp },
                            receiverId: otherUserId
                        })
                    });

                    if (!response.ok) throw new Error('Failed to send offer');

                    console.log("Offer sent successfully");
                    updateStatus("Call initiated - waiting for answer...");

                    connectionTimeout = setTimeout(() => {
                        if (!callActive) return;
                        console.log("No answer received - timeout");
                        updateStatus("❌ No answer - user may be unavailable");
                        endCall();
                    }, 30000);

                } catch (err) {
                    console.error("Error:", err);
                    updateStatus("❌ Failed to start call");
                    endCall();
                }
            };

            window.acceptCall = async function() {
                console.log("Accepting call...");
                
                if (!incomingOffer || !incomingCallerId) {
                    alert("No incoming call to accept");
                    return;
                }

                if (callActive) return;

                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Accessing microphone...');

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    console.log("Microphone access granted");
                } catch (err) {
                    console.error("Microphone error:", err);
                    alert("Microphone access is required for calls");
                    updateStatus("❌ Microphone access denied");
                    callActive = false;
                    showAcceptMode();
                    return;
                }

                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                try {
                    console.log("Setting remote description...");
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription({
                            type: incomingOffer.type,
                            sdp: incomingOffer.sdp
                        })
                    );
                    console.log("Remote description set");

                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    console.log("Answer created");

                    isRemoteSet = true;
                    
                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        } catch (err) {
                            console.error("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];

                    updateStatus("Sending answer...");
                    
                    const response = await fetch('/send-answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            answer: { type: answer.type, sdp: answer.sdp },
                            receiverId: incomingCallerId
                        })
                    });

                    if (!response.ok) throw new Error('Failed to send answer');

                    console.log("Answer sent successfully");
                    updateStatus("Call connected!");

                } catch (err) {
                    console.error("Error accepting call:", err);
                    updateStatus("❌ Failed to connect call");
                    endCall();
                }
            };

            async function handleAnswer(answer) {
                console.log("Handling answer");
                
                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }

                if (!peerConnection) {
                    console.log("No peer connection");
                    return;
                }

                try {
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription({
                            type: answer.type,
                            sdp: answer.sdp
                        })
                    );
                    console.log("Remote description set from answer");
                    
                    isRemoteSet = true;
                    
                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        } catch (err) {
                            console.error("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];
                    
                    updateStatus("Call connected!");
                    
                } catch (err) {
                    console.error("Error setting answer:", err);
                    updateStatus("❌ Connection failed");
                }
            }

            window.endCall = function() {
                console.log("Ending call");
                
                if (connectionTimeout) clearTimeout(connectionTimeout);
                
                if (peerConnection) {
                    peerConnection.close();
                    peerConnection = null;
                }
                
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                    localStream = null;
                }
                
                const audio = document.getElementById("remoteAudio");
                if (audio) {
                    audio.srcObject = null;
                    audio.remove();
                }
                
                pendingCandidates = [];
                isRemoteSet = false;
                callActive = false;
                
                if (incomingCallerId) {
                    document.getElementById("callTitle").textContent = "Call ended";
                    updateStatus("Call ended - close window");
                } else {
                    showStartMode();
                }
                
                incomingOffer = null;
                incomingCallerId = null;
            };

            // ==================== PUSHER ====================
            function initPusher() {
                console.log("Initializing Pusher...");
                
                Pusher.logToConsole = true;
                
                pusher = new Pusher(PUSHER_APP_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });

                pusher.connection.bind('connected', () => {
                    console.log("Pusher connected");
                    
                    const isCaller = new URLSearchParams(window.location.search).has('mode=caller');
                    console.log("Mode:", isCaller ? "CALLER" : "RECEIVER");
                    
                    const hasPending = checkPendingCall();
                    
                    if (isCaller) {
                        showStartMode();
                        updateStatus("Ready to start call");
                    } else if (!hasPending) {
                        document.getElementById("callTitle").textContent = `Waiting for call from ${otherUserName}...`;
                        updateStatus("Ready to receive calls");
                    }
                });

                const channelName = 'voice-call.' + userId;
                channel = pusher.subscribe(channelName);

                channel.bind('CallOffer', (data) => {
                    console.log("📞 Call offer received in voice window");
                    // Only handle if this is receiver window (no mode=caller)
                    if (!new URLSearchParams(window.location.search).has('mode=caller')) {
                        incomingOffer = data.offer;
                        incomingCallerId = data.callerId;
                        incomingCallerName = data.callerName;
                        showAcceptMode();
                        document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                        updateStatus("Incoming call - Click Accept");
                    }
                });

                channel.bind('CallAnswer', async (data) => {
                    console.log("Call answer received");
                    if (new URLSearchParams(window.location.search).has('mode=caller')) {
                        await handleAnswer(data.answer);
                    }
                });

                channel.bind('IceCandidate', (data) => {
                    console.log("ICE candidate received");
                    if (!peerConnection) {
                        pendingCandidates.push(data.candidate);
                    } else if (!isRemoteSet) {
                        pendingCandidates.push(data.candidate);
                    } else {
                        peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate))
                            .catch(err => console.error("Error adding ICE:", err));
                    }
                });
            }

            // ==================== INIT ====================
            document.addEventListener("DOMContentLoaded", function() {
                console.log("Voice call page loaded");
                console.log("User ID:", userId, "Other User ID:", otherUserId);
                initPusher();
            });

        })();
    </script>
</body>
</html>