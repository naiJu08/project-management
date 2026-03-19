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
        /* Base button visibility */
        #startBtn { display: block; }
        #acceptBtn { display: none; }
        #endBtn { display: block; }
        #debugPanel { display: none; }
        
        .debug-visible #debugPanel {
            display: block;
            position: fixed;
            bottom: 10px;
            left: 10px;
            right: 10px;
            background: rgba(0,0,0,0.9);
            color: #0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        /* Loading spinner */
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
            <!-- START CALL -->
            <button id="startBtn" onclick="window.startCall()" class="bg-green-500 px-6 py-3 rounded-full text-lg hover:bg-green-600 transition flex items-center gap-2">
                <span>📞</span> Start Call
            </button>

            <!-- ACCEPT CALL -->
            <button id="acceptBtn" onclick="window.acceptCall()" class="bg-green-600 px-6 py-3 rounded-full text-lg hover:bg-green-700 transition flex items-center gap-2 animate-pulse">
                <span>✅</span> Accept Call
            </button>

            <!-- END CALL -->
            <button id="endBtn" onclick="window.endCall()" class="bg-red-500 px-6 py-3 rounded-full text-lg hover:bg-red-600 transition">
                ❌ End
            </button>

            <!-- DEBUG TOGGLE -->
            <button onclick="toggleDebug()" class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition" title="Toggle Debug">
                🐛
            </button>

            <!-- TEST BUTTON -->
            <button onclick="window.testBroadcast()" class="bg-blue-500 px-4 py-3 rounded-full text-lg hover:bg-blue-600 transition" title="Test Pusher">
                🧪
            </button>
        </div>

        <!-- Debug Panel -->
        <div id="debugPanel" class="mt-4 text-left bg-gray-800 p-4 rounded-lg hidden">
            <div class="font-bold mb-2 flex justify-between">
                <span>🔍 Debug Info</span>
                <button onclick="clearDebug()" class="text-xs text-red-400">Clear</button>
            </div>
            <div id="debugLog" class="text-xs space-y-1 max-h-40 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        (function() {
            "use strict";

            // ==================== CONFIGURATION ====================
            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            
            // ==================== GLOBAL VARIABLES ====================
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = "{{ $user->name }}";
            
            // Debug logging
            const debugLogs = [];
            function debug(...args) {
                const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ');
                console.log("[DEBUG]", ...args);
                debugLogs.unshift({ timestamp: new Date().toLocaleTimeString(), message });
                
                const debugDiv = document.getElementById('debugLog');
                if (debugDiv) {
                    debugDiv.innerHTML = debugLogs.slice(0, 15).map(log => 
                        `<div class="border-b border-gray-700 pb-1">${log.timestamp}: ${log.message}</div>`
                    ).join('');
                }
            }
            
            window.toggleDebug = function() {
                document.body.classList.toggle('debug-visible');
                document.getElementById('debugPanel').classList.toggle('hidden');
            };
            
            window.clearDebug = function() {
                debugLogs.length = 0;
                document.getElementById('debugLog').innerHTML = '';
            };
            
            let localStream = null;
            let peerConnection = null;
            let incomingOffer = null;
            let incomingCallerId = null;
            let incomingCallerName = null;
            let pendingCandidates = [];
            let isRemoteSet = false;
            let callActive = false;
            let connectionTimeout = null;
            
            // Pusher instances
            let pusher = null;
            let channel = null;

            // ==================== CHECK SESSION STORAGE FOR PENDING CALL ====================
            function checkPendingCall() {
                try {
                    const pendingCall = sessionStorage.getItem('pendingCall');
                    if (pendingCall) {
                        const callData = JSON.parse(pendingCall);
                        const age = Date.now() - callData.timestamp;
                        
                        debug(`Found pending call in sessionStorage, age: ${age}ms`);
                        
                        // Only use if less than 15 seconds old
                        if (age < 15000) {
                            debug("Using pending call data");
                            incomingOffer = callData.offer;
                            incomingCallerId = callData.callerId;
                            incomingCallerName = callData.callerName;
                            
                            // Clear it immediately
                            sessionStorage.removeItem('pendingCall');
                            
                            // Show accept button
                            showAcceptMode();
                            document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                            updateStatus("Incoming call - Click Accept");
                            
                            return true;
                        } else {
                            debug("Pending call expired, removing");
                            sessionStorage.removeItem('pendingCall');
                        }
                    }
                } catch (e) {
                    debug("Error checking pending call:", e);
                }
                return false;
            }

            // ==================== UTILITY FUNCTIONS ====================
            function updateStatus(message) {
                debug("STATUS:", message);
                document.getElementById("callStatus").innerHTML = message;
            }

            function showStartMode() {
                debug("UI: Showing start mode");
                document.getElementById("startBtn").style.display = "block";
                document.getElementById("acceptBtn").style.display = "none";
                document.getElementById("callTitle").textContent = `Call ${otherUserName}`;
            }

            function showAcceptMode() {
                debug("UI: Showing accept mode");
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "block";
            }

            function hideAllButtons() {
                debug("UI: Hiding all buttons");
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "none";
            }

            function cleanupConnection() {
                debug("Cleaning up connection");
                
                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }
                
                if (peerConnection) {
                    peerConnection.close();
                    peerConnection = null;
                }
                
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                    localStream = null;
                }
                
                const remoteAudio = document.getElementById("remoteAudio");
                if (remoteAudio) {
                    remoteAudio.srcObject = null;
                    remoteAudio.remove();
                }
                
                pendingCandidates = [];
                isRemoteSet = false;
                callActive = false;
            }

            // ==================== WEBRTC PEER CONNECTION ====================
            function createPeer() {
                debug("Creating peer connection");
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
                    debug("Connection state:", peerConnection.connectionState);
                    updateStatus(`Connection: ${peerConnection.connectionState}`);
                    
                    if (peerConnection.connectionState === 'connected') {
                        updateStatus("✅ Call connected!");
                    } else if (peerConnection.connectionState === 'disconnected' || 
                               peerConnection.connectionState === 'failed') {
                        updateStatus("❌ Connection lost");
                    }
                };

                peerConnection.oniceconnectionstatechange = () => {
                    debug("ICE state:", peerConnection.iceConnectionState);
                };

                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        debug("Sending ICE candidate");
                        
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
                        }).catch(err => {
                            debug("Error sending ICE:", err);
                        });
                    }
                };

                peerConnection.ontrack = (event) => {
                    debug("Remote audio received");
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
                debug("Starting call...");
                
                if (callActive) {
                    debug("Call already active");
                    return;
                }
                
                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Requesting microphone...');

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    debug("Microphone access granted");
                    updateStatus("Creating connection...");
                } catch (err) {
                    debug("Microphone error:", err);
                    alert("Microphone access is required for calls");
                    updateStatus("❌ Microphone access denied");
                    callActive = false;
                    showStartMode();
                    return;
                }

                createPeer();
                
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

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
                            offer: {
                                type: offer.type,
                                sdp: offer.sdp
                            },
                            receiverId: otherUserId
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to send offer');
                    }

                    debug("Offer sent successfully");
                    updateStatus("Call initiated - waiting for answer...");

                    connectionTimeout = setTimeout(() => {
                        if (!callActive) return;
                        debug("No answer received - timeout");
                        updateStatus("❌ No answer - user may be unavailable");
                        endCall();
                    }, 30000);

                } catch (err) {
                    debug("Error creating/sending offer:", err);
                    updateStatus("❌ Failed to start call");
                    endCall();
                }
            };

            window.acceptCall = async function() {
                debug("Accepting call...");
                
                if (!incomingOffer) {
                    debug("No offer to accept");
                    updateStatus("❌ No incoming call");
                    return;
                }

                if (!incomingCallerId) {
                    debug("No caller ID");
                    updateStatus("❌ Caller information missing");
                    return;
                }

                if (callActive) {
                    debug("Call already active");
                    return;
                }

                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Accessing microphone...');

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    debug("Microphone access granted");
                    updateStatus("Connecting...");
                } catch (err) {
                    debug("Microphone error:", err);
                    alert("Microphone access is required for calls");
                    updateStatus("❌ Microphone access denied");
                    callActive = false;
                    showAcceptMode();
                    return;
                }

                createPeer();
                
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                try {
                    debug("Setting remote description...");
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription({
                            type: incomingOffer.type,
                            sdp: incomingOffer.sdp
                        })
                    );
                    debug("Remote description set");

                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    debug("Answer created and set");

                    isRemoteSet = true;
                    
                    pendingCandidates.forEach(candidate => {
                        peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                            .catch(err => debug("Error adding ICE:", err));
                    });
                    pendingCandidates = [];

                    updateStatus("Sending answer...");
                    
                    const response = await fetch('/send-answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            answer: {
                                type: answer.type,
                                sdp: answer.sdp
                            },
                            receiverId: incomingCallerId
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to send answer');
                    }

                    debug("Answer sent successfully");
                    updateStatus("Call connected!");

                } catch (err) {
                    debug("Error accepting call:", err);
                    updateStatus("❌ Failed to connect call");
                    endCall();
                }
            };

            window.endCall = function() {
                debug("Ending call");
                
                cleanupConnection();
                
                // Check if this was a receiver window
                if (incomingCallerId) {
                    document.getElementById("callTitle").textContent = `Call ended`;
                    updateStatus("Call ended - close window");
                } else {
                    showStartMode();
                }
                
                incomingOffer = null;
                incomingCallerId = null;
                incomingCallerName = null;
            };

            // ==================== PUSHER SETUP ====================
            function initPusher() {
                debug("Initializing Pusher...");
                
                Pusher.logToConsole = true;
                
                pusher = new Pusher(PUSHER_APP_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });

                pusher.connection.bind('connected', () => {
                    debug("Pusher connected");
                    debug("Socket ID:", pusher.connection.socket_id);
                    
                    const isCaller = new URLSearchParams(window.location.search).has('mode=caller');
                    debug("Page mode:", isCaller ? "CALLER" : "RECEIVER");
                    
                    // Check for pending call from sessionStorage
                    const hasPending = checkPendingCall();
                    
                    if (isCaller) {
                        showStartMode();
                        updateStatus("Ready to start call");
                    } else if (!hasPending) {
                        // Only show waiting if no pending call
                        document.getElementById("callTitle").textContent = `Waiting for call from ${otherUserName}...`;
                        updateStatus("Ready to receive calls");
                        document.getElementById("acceptBtn").style.display = "none";
                    }
                });

                pusher.connection.bind('error', (error) => {
                    debug("Pusher error:", error);
                    updateStatus("Connection error");
                });

                const channelName = 'voice-call.' + userId;
                debug("Subscribing to channel:", channelName);
                
                channel = pusher.subscribe(channelName);

                channel.bind('subscription_succeeded', () => {
                    debug("Subscribed to channel:", channelName);
                });

                channel.bind('subscription_error', (error) => {
                    debug("Subscription error:", error);
                    updateStatus("Failed to connect to call service");
                });

                // Test broadcast handler
                channel.bind('TestBroadcast', (data) => {
                    debug("✅ Test broadcast received:", data);
                    updateStatus("✅ Test broadcast working!");
                    alert("Test broadcast received! Pusher is working!");
                });

                // Incoming call offer
                channel.bind('CallOffer', (data) => {
                    debug("📞 INCOMING CALL OFFER RECEIVED!", data);
                    
                    incomingOffer = data.offer;
                    incomingCallerId = data.callerId;
                    incomingCallerName = data.callerName || 'User ' + data.callerId;
                    
                    debug("Offer stored:", {
                        callerId: incomingCallerId,
                        callerName: incomingCallerName
                    });
                    
                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - Click Accept");
                });

                // Call answer received
                channel.bind('CallAnswer', async (data) => {
                    debug("Call answer received:", data);
                    
                    if (connectionTimeout) {
                        clearTimeout(connectionTimeout);
                        connectionTimeout = null;
                    }

                    if (!peerConnection) {
                        debug("No peer connection for answer");
                        return;
                    }

                    try {
                        await peerConnection.setRemoteDescription(
                            new RTCSessionDescription({
                                type: data.answer.type,
                                sdp: data.answer.sdp
                            })
                        );
                        debug("Remote description set from answer");
                        
                        isRemoteSet = true;
                        
                        pendingCandidates.forEach(candidate => {
                            peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                                .catch(err => debug("Error adding ICE:", err));
                        });
                        pendingCandidates = [];
                        
                        updateStatus("Call connected!");
                        
                    } catch (err) {
                        debug("Error setting answer:", err);
                        updateStatus("❌ Connection failed");
                    }
                });

                // ICE candidate received
                channel.bind('IceCandidate', (data) => {
                    debug("ICE candidate received from:", data.senderId);
                    
                    if (!peerConnection) {
                        debug("Buffering ICE candidate");
                        pendingCandidates.push(data.candidate);
                        return;
                    }

                    if (!isRemoteSet) {
                        debug("Buffering ICE candidate (remote not set)");
                        pendingCandidates.push(data.candidate);
                    } else {
                        peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate))
                            .then(() => debug("ICE candidate added"))
                            .catch(err => debug("Error adding ICE:", err));
                    }
                });
            }

            // ==================== TEST FUNCTION ====================
            window.testBroadcast = async function() {
                debug("Testing broadcast...");
                updateStatus("Sending test broadcast...");
                
                try {
                    const response = await fetch('/api/test-broadcast', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Test failed');
                    }
                    
                    const data = await response.json();
                    debug("Test broadcast sent:", data);
                    updateStatus("Test broadcast sent - check receiver console");
                    
                } catch (err) {
                    debug("Test broadcast error:", err);
                    updateStatus("❌ Test failed");
                }
            };

            // ==================== INITIALIZATION ====================
            document.addEventListener("DOMContentLoaded", function() {
                debug("Voice call page loaded");
                debug("Current User ID:", userId);
                debug("Other User ID:", otherUserId);
                debug("URL Parameters:", window.location.search);
                
                // Check for required elements
                const requiredElements = ['startBtn', 'acceptBtn', 'endBtn', 'callTitle', 'callStatus'];
                requiredElements.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) {
                        debug(`Missing element: #${id}`);
                    }
                });
                
                // Initialize Pusher
                initPusher();
            });

        })();
    </script>
</body>
</html>