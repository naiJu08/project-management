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
        #startBtn {
            display: block;
        }

        #acceptBtn {
            display: none;
        }

        #endBtn {
            display: block;
        }

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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #debugPanel {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: #0f0;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            max-width: 400px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
        }

        .debug-visible #debugPanel {
            display: block;
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
            <button id="startBtn" onclick="startCall()"
                class="bg-green-500 px-6 py-3 rounded-full text-lg hover:bg-green-600 transition flex items-center gap-2">
                <span>📞</span> Start Call
            </button>
            <button id="acceptBtn" onclick="acceptCall()"
                class="bg-green-600 px-6 py-3 rounded-full text-lg hover:bg-green-700 transition flex items-center gap-2 animate-pulse">
                <span>✅</span> Accept Call
            </button>
            <button id="endBtn" onclick="endCall()"
                class="bg-red-500 px-6 py-3 rounded-full text-lg hover:bg-red-600 transition">
                ❌ End
            </button>
            <button onclick="toggleDebug()"
                class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition">🐛 Debug</button>
        </div>

        <!-- Debug Panel -->
        <div id="debugPanel"></div>
    </div>

    <script>
        (function () {
            "use strict";

            // ==================== CONFIG ====================
            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = "{{ $user->name }}";

            // ==================== DEBUG LOGGING ====================
            const debugLogs = [];
            function debug(...args) {
                const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ');
                console.log("[DEBUG]", ...args);
                debugLogs.unshift({ time: new Date().toLocaleTimeString(), message });
                updateDebugPanel();
            }
            window.toggleDebug = function () {
                document.body.classList.toggle('debug-visible');
                updateDebugPanel();
            };
            function updateDebugPanel() {
                const panel = document.getElementById('debugPanel');
                if (panel) {
                    panel.innerHTML = debugLogs.slice(0, 20).map(log =>
                        `<div>${log.time}: ${log.message}</div>`
                    ).join('');
                }
            }

            // ==================== ROBUST SDP CLEANER + REPAIR ====================
            function cleanSDP(sdp) {
                if (!sdp || typeof sdp !== 'string') return sdp;

                // 1. Recursive decode of escaped characters
                let cleaned = sdp;
                let prev;
                do {
                    prev = cleaned;
                    cleaned = cleaned.replace(/\\r\\n/g, '\r\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\r/g, '\r')
                        .replace(/\\\\/g, '\\');
                } while (prev !== cleaned);

                // 2. Normalize line endings to CRLF
                cleaned = cleaned.replace(/\r?\n/g, '\r\n');

                // 3. Split into lines, trim each
                let lines = cleaned.split(/\r?\n/).map(line => line.trim()).filter(line => line.length > 0);

                // 4. Repair specific lines (especially a=ssrc)
                lines = lines.map(line => repairSDPLine(line));

                // 5. Rejoin with CRLF
                return lines.join('\r\n');
            }

            function repairSDPLine(line) {
                // Fix common a=ssrc line issues
                if (line.startsWith('a=ssrc:')) {
                    // Expected format: a=ssrc:<ssrc> msid:<msid> <appdata>
                    // Sometimes there are extra spaces or missing spaces
                    // We'll split by space and reassemble correctly
                    let parts = line.split(/\s+/);
                    if (parts.length >= 3) {
                        // parts[0] = "a=ssrc:<ssrc>"
                        // parts[1] = "msid:<msid>"
                        // parts[2...] = appdata (maybe more)
                        let ssrcPart = parts[0];
                        let msidPart = parts[1];
                        // Ensure msidPart starts with "msid:"
                        if (!msidPart.startsWith('msid:')) {
                            msidPart = 'msid:' + msidPart;
                        }
                        let appdata = parts.slice(2).join(' ');
                        // If appdata is empty, just use ssrc and msid
                        if (appdata) {
                            return `${ssrcPart} ${msidPart} ${appdata}`;
                        } else {
                            return `${ssrcPart} ${msidPart}`;
                        }
                    }
                }
                return line;
            }

            // Extreme fallback: rebuild SDP from scratch keeping only valid lines
            function forceRepairSDP(sdp) {
                let lines = sdp.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
                let validLines = [];
                for (let line of lines) {
                    // Accept lines that match known SDP patterns
                    if (/^[vosiucbkt]=\S/.test(line) ||          // session level lines
                        /^[ma]=\S/.test(line) ||                  // media lines
                        /^a=\S/.test(line)) {                     // attribute lines
                        validLines.push(repairSDPLine(line));
                    } else {
                        console.warn("Dropping invalid SDP line:", line);
                    }
                }
                return validLines.join('\r\n');
            }

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

                        debug(`Found pending call, age: ${age}ms`);

                        if (age < 15000) {
                            debug("Using pending call data");
                            if (callData.offer && callData.offer.sdp) {
                                callData.offer.sdp = cleanSDP(callData.offer.sdp);
                            }
                            incomingOffer = callData.offer;
                            incomingCallerId = callData.callerId;
                            incomingCallerName = callData.callerName;

                            sessionStorage.removeItem('pendingCall');

                            showAcceptMode();
                            document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                            updateStatus("Incoming call - Click Accept");

                            return true;
                        } else {
                            sessionStorage.removeItem('pendingCall');
                        }
                    }
                } catch (e) {
                    debug("Error checking pending call:", e);
                }
                return false;
            }

            // ==================== LISTEN FOR POST MESSAGES ====================
            window.addEventListener('message', function (event) {
                debug("📨 Received message:", event.data.type);

                if (event.data.type === 'incoming-offer') {
                    debug("📞 Received offer via postMessage");
                    if (event.data.offer && event.data.offer.sdp) {
                        event.data.offer.sdp = cleanSDP(event.data.offer.sdp);
                    }
                    incomingOffer = event.data.offer;
                    incomingCallerId = event.data.callerId;
                    incomingCallerName = event.data.callerName;
                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - Click Accept");
                }

                if (event.data.type === 'ice-candidate') {
                    debug("❄️ Received ICE candidate via postMessage");
                    if (!peerConnection) {
                        pendingCandidates.push(event.data.candidate);
                    } else if (!isRemoteSet) {
                        pendingCandidates.push(event.data.candidate);
                    } else {
                        peerConnection.addIceCandidate(new RTCIceCandidate(event.data.candidate))
                            .catch(err => debug("Error adding ICE:", err));
                    }
                }

                if (event.data.type === 'call-answer') {
                    debug("✅ Received answer via postMessage");
                    handleAnswer(event.data.answer);
                }
            });

            // ==================== UI FUNCTIONS ====================
            function updateStatus(message) {
                debug("STATUS:", message);
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
                    } else if (peerConnection.connectionState === 'failed') {
                        updateStatus("❌ Connection failed - check TURN server");
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
                        }).catch(err => debug("Error sending ICE:", err));
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
            window.startCall = async function () {
                debug("Starting call...");

                if (callActive) return;
                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Requesting microphone...');

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    debug("Microphone access granted");
                } catch (err) {
                    debug("Microphone error:", err);
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

                    debug("Offer sent successfully");
                    updateStatus("Call initiated - waiting for answer...");

                    connectionTimeout = setTimeout(() => {
                        if (!callActive) return;
                        debug("No answer received - timeout");
                        updateStatus("❌ No answer - user may be unavailable");
                        endCall();
                    }, 30000);

                } catch (err) {
                    debug("Error:", err);
                    updateStatus("❌ Failed to start call");
                    endCall();
                }
            };

            window.acceptCall = async function () {
                try {
                    console.log("========== ACCEPT CALL CLICKED ==========");
                    debug("========== ACCEPT CALL CLICKED ==========");
                    debug("incomingOffer:", incomingOffer ? "present" : "null");
                    debug("incomingCallerId:", incomingCallerId);

                    if (incomingOffer) {
                        debug("Offer type:", incomingOffer.type);
                        debug("Offer has sdp:", !!incomingOffer.sdp);
                        console.log("Full offer:", incomingOffer);
                        if (incomingOffer.sdp) {
                            console.log("Original SDP preview (first 500 chars):", incomingOffer.sdp.substring(0, 500));
                        }
                    }

                    if (!incomingOffer || !incomingCallerId) {
                        debug("❌ No incoming call to accept");
                        alert("No incoming call to accept");
                        return;
                    }

                    // ----- CLEAN SDP THOROUGHLY -----
                    if (incomingOffer.sdp) {
                        debug("Original SDP length:", incomingOffer.sdp.length);
                        incomingOffer.sdp = cleanSDP(incomingOffer.sdp);
                        debug("Cleaned SDP length:", incomingOffer.sdp.length);
                        console.log("Cleaned SDP preview (first 500 chars):", incomingOffer.sdp.substring(0, 500));
                        console.log("=== FULL CLEANED SDP ===");
                        console.log(incomingOffer.sdp);
                        console.log("========================");
                    }

                    if (callActive) {
                        debug("Call already active");
                        return;
                    }

                    callActive = true;
                    hideAllButtons();
                    updateStatus('<span class="spinner"></span> Accessing microphone...');

                    debug("Requesting microphone permission...");
                    try {
                        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        debug("✅ Microphone access granted");
                    } catch (micErr) {
                        debug("❌ Microphone error:", micErr);
                        alert("Microphone access is required for calls. Please check permissions.");
                        updateStatus("❌ Microphone access denied");
                        callActive = false;
                        showAcceptMode();
                        return;
                    }

                    createPeer();
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                    // ----- SET REMOTE DESCRIPTION WITH MULTIPLE FALLBACKS -----
                    debug("Setting remote description...");
                    let remoteSet = false;
                    let lastError = null;

                    // Try 1: Direct
                    try {
                        await peerConnection.setRemoteDescription(incomingOffer);
                        debug("✅ Remote description set (direct)");
                        remoteSet = true;
                    } catch (sdpErr) {
                        lastError = sdpErr;
                        debug("❌ Direct setRemoteDescription failed:", sdpErr.message);
                        console.error("Direct SDP error:", sdpErr);

                        // Try 2: Using RTCSessionDescription constructor
                        try {
                            debug("Attempting fallback with new RTCSessionDescription...");
                            const cleanOffer = new RTCSessionDescription({
                                type: incomingOffer.type,
                                sdp: incomingOffer.sdp
                            });
                            await peerConnection.setRemoteDescription(cleanOffer);
                            debug("✅ Remote description set via RTCSessionDescription");
                            remoteSet = true;
                        } catch (fallbackErr) {
                            debug("❌ RTCSessionDescription fallback failed:", fallbackErr.message);
                            lastError = fallbackErr;

                            // Try 3: Extreme repair – rebuild SDP from valid lines
                            try {
                                debug("Attempting extreme repair (drop invalid lines)...");
                                const repairedSDP = forceRepairSDP(incomingOffer.sdp);
                                console.log("Repaired SDP preview:", repairedSDP.substring(0, 500));
                                const extremeOffer = new RTCSessionDescription({
                                    type: incomingOffer.type,
                                    sdp: repairedSDP
                                });
                                await peerConnection.setRemoteDescription(extremeOffer);
                                debug("✅ Remote description set after extreme repair");
                                remoteSet = true;
                            } catch (finalErr) {
                                debug("❌ Extreme repair also failed:", finalErr);
                                lastError = finalErr;
                            }
                        }
                    }

                    if (!remoteSet) {
                        throw new Error(`All attempts to set remote description failed. Last error: ${lastError?.message}`);
                    }

                    // ----- CREATE ANSWER -----
                    debug("Creating answer...");
                    const answer = await peerConnection.createAnswer();
                    debug("Answer created:", answer.type);

                    await peerConnection.setLocalDescription(answer);
                    debug("✅ Local description set");

                    isRemoteSet = true;

                    // Add any pending ICE candidates
                    debug("Adding buffered ICE candidates:", pendingCandidates.length);
                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                            debug("Added buffered ICE candidate");
                        } catch (err) {
                            debug("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];

                    // Send answer to caller
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

                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Failed to send answer: ${response.status} ${text}`);
                    }

                    const result = await response.json();
                    debug("✅ Answer sent successfully:", result);
                    updateStatus("Call connected!");

                } catch (err) {
                    debug("❌ UNCAUGHT ERROR in acceptCall:", err);
                    console.error("FULL UNCAUGHT ERROR:", err);
                    alert("Error: " + err.message);
                    updateStatus("❌ Error: " + err.message);
                    endCall();
                }
            };

            async function handleAnswer(answer) {
                debug("Handling answer");

                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }

                if (!peerConnection) {
                    debug("No peer connection");
                    return;
                }

                if (answer.sdp) {
                    answer.sdp = cleanSDP(answer.sdp);
                }

                try {
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription({
                            type: answer.type,
                            sdp: answer.sdp
                        })
                    );
                    debug("✅ Remote description set from answer");

                    isRemoteSet = true;

                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        } catch (err) {
                            debug("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];

                    updateStatus("Call connected!");

                } catch (err) {
                    debug("❌ Error setting answer:", err);
                    updateStatus("❌ Connection failed");
                }
            }

            window.endCall = function () {
                debug("Ending call");

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

            // Add this function before cleanSDP
            function manualSDPRepair(sdp) {
                // Split into lines
                let lines = sdp.split(/\r?\n/);
                let repaired = [];

                for (let line of lines) {
                    line = line.trim();
                    if (!line) continue;

                    // Fix a=ssrc lines that might have been corrupted
                    if (line.startsWith('a=ssrc:')) {
                        // Remove any escaped characters
                        line = line.replace(/\\r/g, '').replace(/\\n/g, '');

                        // Ensure proper format: a=ssrc:12345 msid:uuid1 uuid2
                        let match = line.match(/a=ssrc:(\d+)(?:\s+msid:)?([a-f0-9-]+)(?:\s+)?([a-f0-9-]+)?/i);
                        if (match) {
                            let ssrc = match[1];
                            let msid1 = match[2];
                            let msid2 = match[3] || msid1; // If only one UUID, duplicate it
                            line = a = ssrc:${ ssrc } msid:${ msid1 } ${ msid2 };
                        }
                    }

                    repaired.push(line);
                }

                return repaired.join('\r\n');
            }

            // ==================== PUSHER ====================
            function initPusher() {
                debug("Initializing Pusher...");

                Pusher.logToConsole = true;

                pusher = new Pusher(PUSHER_APP_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });

                pusher.connection.bind('connected', () => {
                    debug("Pusher connected");

                    const isCaller = new URLSearchParams(window.location.search).has('mode=caller');
                    debug("Mode:", isCaller ? "CALLER" : "RECEIVER");

                    const hasPending = checkPendingCall();

                    if (isCaller) {
                        showStartMode();
                        updateStatus("Ready to start call");
                    } else if (!hasPending) {
                        document.getElementById("callTitle").textContent = `Waiting for call from ${otherUserName}...`;
                        updateStatus("Ready to receive calls");
                    }
                });

                pusher.connection.bind('error', (error) => {
                    debug("Pusher error:", error);
                });

                const channelName = 'voice-call.' + userId;
                channel = pusher.subscribe(channelName);

                channel.bind('subscription_succeeded', () => {
                    debug("Subscribed to channel:", channelName);
                });

                channel.bind('CallOffer', (data) => {
                    debug("📞 Call offer received in voice window");
                    if (!new URLSearchParams(window.location.search).has('mode=caller')) {
                        if (data.offer && data.offer.sdp) {
                            data.offer.sdp = cleanSDP(data.offer.sdp);
                        }
                        incomingOffer = data.offer;
                        incomingCallerId = data.callerId;
                        incomingCallerName = data.callerName;
                        showAcceptMode();
                        document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                        updateStatus("Incoming call - Click Accept");
                    }
                });

                channel.bind('CallAnswer', async (data) => {
                    debug("Call answer received");
                    if (new URLSearchParams(window.location.search).has('mode=caller')) {
                        if (data.answer && data.answer.sdp) {
                            data.answer.sdp = cleanSDP(data.answer.sdp);
                        }
                        await handleAnswer(data.answer);
                    }
                });

                channel.bind('IceCandidate', (data) => {
                    debug("ICE candidate received");
                    if (!peerConnection) {
                        pendingCandidates.push(data.candidate);
                    } else if (!isRemoteSet) {
                        pendingCandidates.push(data.candidate);
                    } else {
                        peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate))
                            .catch(err => debug("Error adding ICE:", err));
                    }
                });
            }

            // ==================== INIT ====================
            document.addEventListener("DOMContentLoaded", function () {
                debug("Voice call page loaded");
                debug("User ID:", userId, "Other User ID:", otherUserId);
                debug("URL params:", window.location.search);

                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                debug("CSRF token present:", !!token);

                initPusher();
            });

        })();
    </script>
</body>

</html>