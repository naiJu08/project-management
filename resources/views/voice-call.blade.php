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
        #endBtn { display: inline-flex; }

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

        #debugPanel {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.85);
            color: #0f0;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            max-width: 430px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
            text-align: left;
            word-break: break-word;
        }

        .debug-visible #debugPanel {
            display: block;
        }

        .audioPanel {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.75);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .localPanel {
            right: auto;
            left: 10px;
            bottom: 50px;
        }

        .volumeMeter {
            width: 50px;
            height: 4px;
            background: #333;
            border-radius: 2px;
            overflow: hidden;
        }

        .volumeLevel {
            width: 0%;
            height: 100%;
            transition: width 0.1s;
        }

        .remotePanel .volumeLevel { background: #0f0; }
        .localPanel .volumeLevel { background: #ff9800; }

        .muteIcon { font-size: 16px; }

        .actionButtons {
            position: fixed;
            bottom: 10px;
            left: 10px;
            display: flex;
            gap: 8px;
            z-index: 10000;
            flex-wrap: wrap;
        }

        .actionButtons button {
            background: rgba(0,0,0,0.75);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            color: white;
        }

        #remoteAudio {
            position: fixed;
            bottom: 50px;
            right: 10px;
            width: 240px;
            height: 42px;
            z-index: 10000;
            background: #222;
            border-radius: 5px;
        }
    </style>
</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">
    <div class="text-center w-full max-w-md px-4">
        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <h2 id="callTitle" class="mt-4 text-xl font-semibold">
            Voice Call with {{ $user->name }}
        </h2>

        <p id="callStatus" class="text-gray-400 text-sm mt-1">
            Initializing...
        </p>

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

            <button onclick="toggleDebug()" class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition">
                🐛 Debug
            </button>
        </div>

        <div id="debugPanel"></div>
    </div>

    <div id="remotePanel" class="audioPanel remotePanel" style="display: none;">
        <span id="remoteMuteIcon" class="muteIcon">🔊</span>
        <div class="volumeMeter">
            <div id="remoteVolumeLevel" class="volumeLevel"></div>
        </div>
    </div>

    <div id="localPanel" class="audioPanel localPanel" style="display: none;">
        <span id="localMicIcon" class="muteIcon">🎤</span>
        <div class="volumeMeter">
            <div id="localVolumeLevel" class="volumeLevel"></div>
        </div>
    </div>

    <div class="actionButtons">
        <button id="testMicBtn" style="display: none;" onclick="testMicrophone()">🎤 Test Mic</button>
        <button id="playRemoteBtn" style="display: none;" onclick="playRemoteAudio()">🔊 Play Remote</button>
    </div>

    <audio id="remoteAudio" controls autoplay playsinline></audio>

    <script>
        (function () {
            "use strict";

            // ==================== CONFIG ====================
            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = @json($user->name);

            // ==================== DEBUG ====================
            const debugLogs = [];

            function debug(...args) {
                const message = args.map(arg => {
                    if (typeof arg === "object") {
                        try { return JSON.stringify(arg); } catch (e) { return String(arg); }
                    }
                    return String(arg);
                }).join(" ");

                console.log("[DEBUG]", ...args);
                debugLogs.unshift({
                    time: new Date().toLocaleTimeString(),
                    message
                });
                updateDebugPanel();
            }

            window.toggleDebug = function () {
                document.body.classList.toggle("debug-visible");
                updateDebugPanel();
            };

            function updateDebugPanel() {
                const panel = document.getElementById("debugPanel");
                panel.innerHTML = debugLogs.slice(0, 35).map(log => `<div>${log.time}: ${log.message}</div>`).join("");
            }

            function updateStatus(message) {
                debug("STATUS:", message);
                document.getElementById("callStatus").innerHTML = message;
            }

            // ==================== SDP CLEANER ====================
            function cleanSDP(sdp) {
                if (!sdp || typeof sdp !== "string") return sdp;

                let cleaned = sdp;
                let prev;

                do {
                    prev = cleaned;
                    cleaned = cleaned
                        .replace(/\\r\\n/g, '\r\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\r/g, '\r')
                        .replace(/\\\\/g, '\\');
                } while (prev !== cleaned);

                if (!cleaned.includes('\n') && !cleaned.includes('\r')) {
                    cleaned = cleaned.replace(/([a-z]=)/g, '\r\n$1').replace(/^\r\n/, '');
                }

                cleaned = cleaned.replace(/\r?\n/g, '\r\n');
                const lines = cleaned
                    .split(/\r?\n/)
                    .map(line => line.trim())
                    .filter(line => line.length > 0);

                return lines.join('\r\n') + '\r\n';
            }

            // ==================== STATE ====================
            let localStream = null;
            let remoteStream = null;
            let peerConnection = null;
            let incomingOffer = null;
            let incomingCallerId = null;
            let incomingCallerName = null;
            let pendingCandidates = [];
            let isRemoteDescriptionSet = false;
            let callActive = false;
            let connectionTimeout = null;
            let pusher = null;
            let channel = null;
            let remoteAudioElement = null;

            let localAudioContext = null;
            let localAnalyser = null;
            let localAnimation = null;

            let remoteAudioContext = null;
            let remoteAnalyser = null;
            let remoteAnimation = null;

            // ==================== UI ====================
            function showStartMode() {
                document.getElementById("startBtn").style.display = "inline-flex";
                document.getElementById("acceptBtn").style.display = "none";
                document.getElementById("callTitle").textContent = `Voice Call with ${otherUserName}`;
            }

            function showAcceptMode() {
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "inline-flex";
            }

            function hideAllButtons() {
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "none";
            }

            // ==================== VOLUME METER ====================
            function startLocalVolumeMeter(stream) {
                if (localAudioContext) return;

                try {
                    localAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    localAnalyser = localAudioContext.createAnalyser();
                    localAnalyser.fftSize = 256;

                    const source = localAudioContext.createMediaStreamSource(stream);
                    source.connect(localAnalyser);

                    const meter = document.getElementById("localVolumeLevel");
                    document.getElementById("localPanel").style.display = "flex";

                    function update() {
                        if (!localAnalyser) return;
                        const data = new Uint8Array(localAnalyser.frequencyBinCount);
                        localAnalyser.getByteFrequencyData(data);

                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        const avg = sum / data.length;
                        meter.style.width = `${(avg / 255) * 100}%`;

                        localAnimation = requestAnimationFrame(update);
                    }

                    update();
                } catch (e) {
                    debug("Local meter error:", e);
                }
            }

            function startRemoteVolumeMeter(stream) {
                if (remoteAudioContext) return;

                try {
                    remoteAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    remoteAnalyser = remoteAudioContext.createAnalyser();
                    remoteAnalyser.fftSize = 256;

                    const source = remoteAudioContext.createMediaStreamSource(stream);
                    source.connect(remoteAnalyser);

                    const meter = document.getElementById("remoteVolumeLevel");
                    document.getElementById("remotePanel").style.display = "flex";

                    function update() {
                        if (!remoteAnalyser) return;
                        const data = new Uint8Array(remoteAnalyser.frequencyBinCount);
                        remoteAnalyser.getByteFrequencyData(data);

                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        const avg = sum / data.length;
                        meter.style.width = `${(avg / 255) * 100}%`;

                        remoteAnimation = requestAnimationFrame(update);
                    }

                    update();
                } catch (e) {
                    debug("Remote meter error:", e);
                }
            }

            function stopVolumeMeters() {
                if (localAnimation) cancelAnimationFrame(localAnimation);
                if (remoteAnimation) cancelAnimationFrame(remoteAnimation);

                if (localAudioContext) localAudioContext.close().catch(() => {});
                if (remoteAudioContext) remoteAudioContext.close().catch(() => {});

                localAudioContext = null;
                localAnalyser = null;
                localAnimation = null;

                remoteAudioContext = null;
                remoteAnalyser = null;
                remoteAnimation = null;

                document.getElementById("localPanel").style.display = "none";
                document.getElementById("remotePanel").style.display = "none";
                document.getElementById("localVolumeLevel").style.width = "0%";
                document.getElementById("remoteVolumeLevel").style.width = "0%";
            }

            // ==================== AUDIO HELPERS ====================
            window.testMicrophone = async function () {
                if (!localStream) {
                    alert("Microphone stream not available.");
                    return;
                }

                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const source = ctx.createMediaStreamSource(localStream);
                    const gain = ctx.createGain();
                    gain.gain.value = 1;

                    source.connect(gain);
                    gain.connect(ctx.destination);

                    await ctx.resume();
                    updateStatus("🔊 You should hear your own microphone for 3 seconds");

                    setTimeout(() => {
                        ctx.close().catch(() => {});
                        updateStatus(callActive ? "Call active" : "Ready");
                    }, 3000);
                } catch (e) {
                    debug("Mic test failed:", e);
                }
            };

            window.playRemoteAudio = async function () {
                if (!remoteAudioElement || !remoteAudioElement.srcObject) {
                    alert("No remote audio found.");
                    return;
                }

                try {
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    await remoteAudioElement.play();
                    document.getElementById("playRemoteBtn").style.display = "none";
                    updateStatus("🔊 Remote audio playing");
                } catch (e) {
                    debug("Manual remote play failed:", e);
                    alert("Remote audio play failed: " + e.message);
                }
            };

            async function ensureMicrophone() {
                if (localStream) return localStream;

                localStream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                });

                localStream.getAudioTracks().forEach(track => {
                    track.enabled = true;
                    debug("Local track:", {
                        label: track.label,
                        enabled: track.enabled,
                        muted: track.muted,
                        readyState: track.readyState
                    });
                });

                startLocalVolumeMeter(localStream);
                document.getElementById("testMicBtn").style.display = "inline-block";

                return localStream;
            }

            // ==================== PEER ====================
            function createPeer() {
                debug("Creating peer connection");

                if (peerConnection) {
                    try { peerConnection.close(); } catch (e) {}
                    peerConnection = null;
                }

                isRemoteDescriptionSet = false;

                if (!remoteStream) {
                    remoteStream = new MediaStream();
                } else {
                    remoteStream.getTracks().forEach(track => remoteStream.removeTrack(track));
                }

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
                    ],
                    iceCandidatePoolSize: 10
                });

                peerConnection.onicecandidate = (event) => {
                    if (!event.candidate) {
                        debug("ICE gathering completed");
                        return;
                    }

                    const targetId = incomingCallerId || otherUserId;
                    debug("Sending ICE candidate to:", targetId);

                    fetch("/send-ice", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            candidate: event.candidate,
                            senderId: userId,
                            receiverId: targetId
                        })
                    }).catch(err => debug("Send ICE error:", err));
                };

                peerConnection.ontrack = async (event) => {
                    debug("Remote track received:", event.track.kind);

                    if (event.track.kind !== "audio") return;

                    event.streams[0].getTracks().forEach(track => {
                        const exists = remoteStream.getTracks().some(t => t.id === track.id);
                        if (!exists) remoteStream.addTrack(track);
                    });

                    remoteAudioElement = document.getElementById("remoteAudio");
                    remoteAudioElement.srcObject = remoteStream;
                    remoteAudioElement.autoplay = true;
                    remoteAudioElement.playsInline = true;
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.style.display = "block";

                    event.track.onmute = () => debug("Remote audio track muted");
                    event.track.onunmute = () => debug("Remote audio track unmuted");
                    event.track.onended = () => debug("Remote audio track ended");

                    startRemoteVolumeMeter(remoteStream);

                    try {
                        await remoteAudioElement.play();
                        debug("Remote audio playing successfully");
                        updateStatus("✅ Call connected and audio playing");
                        document.getElementById("playRemoteBtn").style.display = "none";
                    } catch (err) {
                        debug("Autoplay blocked:", err);
                        updateStatus("⚠️ Audio received. Click Play Remote");
                        document.getElementById("playRemoteBtn").style.display = "inline-block";
                    }

                    const remotePanel = document.getElementById("remotePanel");
                    const muteIcon = document.getElementById("remoteMuteIcon");
                    remotePanel.style.display = "flex";

                    remotePanel.onclick = async () => {
                        remoteAudioElement.muted = !remoteAudioElement.muted;
                        muteIcon.textContent = remoteAudioElement.muted ? "🔇" : "🔊";

                        if (!remoteAudioElement.muted) {
                            try { await remoteAudioElement.play(); } catch (e) {}
                        }

                        updateStatus(remoteAudioElement.muted ? "Remote audio muted" : "Remote audio unmuted");
                    };
                };

                peerConnection.onconnectionstatechange = () => {
                    debug("connectionState:", peerConnection.connectionState);
                    updateStatus(`Connection: ${peerConnection.connectionState}`);

                    if (peerConnection.connectionState === "connected") {
                        updateStatus("✅ Call connected");
                    }

                    if (
                        peerConnection.connectionState === "failed" ||
                        peerConnection.connectionState === "disconnected" ||
                        peerConnection.connectionState === "closed"
                    ) {
                        debug("Connection ended with state:", peerConnection.connectionState);
                    }
                };

                peerConnection.oniceconnectionstatechange = () => {
                    debug("iceConnectionState:", peerConnection.iceConnectionState);

                    if (peerConnection.iceConnectionState === "failed") {
                        updateStatus("❌ ICE connection failed");
                    }
                };

                peerConnection.onsignalingstatechange = () => {
                    debug("signalingState:", peerConnection.signalingState);
                };

                peerConnection.onicegatheringstatechange = () => {
                    debug("iceGatheringState:", peerConnection.iceGatheringState);
                };
            }

            async function addLocalTracks() {
                if (!peerConnection || !localStream) return;

                const senders = peerConnection.getSenders();
                const existingTrackIds = senders
                    .filter(sender => sender.track)
                    .map(sender => sender.track.id);

                localStream.getTracks().forEach(track => {
                    if (!existingTrackIds.includes(track.id)) {
                        peerConnection.addTrack(track, localStream);
                        debug("Added local track to peer:", track.kind, track.id);
                    }
                });
            }

            async function flushPendingCandidates() {
                if (!peerConnection || !isRemoteDescriptionSet || pendingCandidates.length === 0) return;

                debug("Flushing pending ICE candidates:", pendingCandidates.length);

                const candidates = [...pendingCandidates];
                pendingCandidates = [];

                for (const candidate of candidates) {
                    try {
                        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        debug("Buffered ICE added successfully");
                    } catch (err) {
                        debug("Buffered ICE add failed:", err);
                    }
                }
            }

            async function handleAnswer(answer) {
                debug("Handling answer");

                if (!peerConnection) {
                    debug("No peer connection for answer");
                    return;
                }

                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }

                try {
                    if (answer?.sdp) answer.sdp = cleanSDP(answer.sdp);

                    await peerConnection.setRemoteDescription(new RTCSessionDescription({
                        type: answer.type,
                        sdp: answer.sdp
                    }));

                    isRemoteDescriptionSet = true;
                    debug("Remote answer set successfully");

                    await flushPendingCandidates();
                    updateStatus("✅ Answer received, connecting...");
                } catch (err) {
                    debug("Handle answer error:", err);
                    updateStatus("❌ Failed to apply answer");
                }
            }

            // ==================== CALL ====================
            window.startCall = async function () {
                if (callActive) {
                    debug("Call already active, ignoring start");
                    return;
                }

                try {
                    callActive = true;
                    hideAllButtons();
                    updateStatus('<span class="spinner"></span> Requesting microphone...');

                    await ensureMicrophone();
                    createPeer();
                    await addLocalTracks();

                    const offer = await peerConnection.createOffer({
                        offerToReceiveAudio: true
                    });

                    await peerConnection.setLocalDescription(offer);

                    updateStatus("Sending call request...");

                    const response = await fetch("/send-offer", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
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
                        throw new Error("Failed to send offer");
                    }

                    updateStatus("📞 Calling... waiting for answer");

                    connectionTimeout = setTimeout(() => {
                        if (!callActive) return;
                        debug("Call timeout reached");
                        updateStatus("❌ No answer received");
                        endCall();
                    }, 30000);

                } catch (err) {
                    debug("Start call error:", err);
                    updateStatus("❌ Failed to start call: " + err.message);
                    endCall();
                }
            };

            window.acceptCall = async function () {
                if (!incomingOffer || !incomingCallerId) {
                    alert("No incoming call to accept");
                    return;
                }

                if (callActive) {
                    debug("Call already active, ignoring accept");
                    return;
                }

                try {
                    callActive = true;
                    hideAllButtons();
                    updateStatus('<span class="spinner"></span> Accessing microphone...');

                    await ensureMicrophone();
                    createPeer();
                    await addLocalTracks();

                    const cleanedOffer = {
                        type: incomingOffer.type,
                        sdp: cleanSDP(incomingOffer.sdp)
                    };

                    debug("Setting remote offer description");
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(cleanedOffer));
                    isRemoteDescriptionSet = true;
                    debug("Remote offer set successfully");

                    await flushPendingCandidates();

                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);

                    updateStatus("Sending answer...");

                    const response = await fetch("/send-answer", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
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
                        const text = await response.text();
                        throw new Error(`Failed to send answer: ${response.status} ${text}`);
                    }

                    updateStatus("✅ Answer sent. Connecting...");
                } catch (err) {
                    debug("Accept call error:", err);
                    alert("Accept call failed: " + err.message);
                    updateStatus("❌ " + err.message);
                    endCall();
                }
            };

            window.endCall = function () {
                debug("Ending call");

                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }

                if (peerConnection) {
                    try {
                        peerConnection.getSenders().forEach(sender => {
                            if (sender.track) {
                                try { sender.track.stop(); } catch (e) {}
                            }
                        });
                        peerConnection.close();
                    } catch (e) {
                        debug("Peer close error:", e);
                    }
                    peerConnection = null;
                }

                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                    localStream = null;
                }

                if (remoteStream) {
                    remoteStream.getTracks().forEach(track => track.stop());
                    remoteStream = null;
                }

                remoteAudioElement = document.getElementById("remoteAudio");
                if (remoteAudioElement) {
                    try { remoteAudioElement.pause(); } catch (e) {}
                    remoteAudioElement.srcObject = null;
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                }

                stopVolumeMeters();

                pendingCandidates = [];
                isRemoteDescriptionSet = false;
                callActive = false;

                incomingOffer = null;
                incomingCallerId = null;
                incomingCallerName = null;

                document.getElementById("testMicBtn").style.display = "none";
                document.getElementById("playRemoteBtn").style.display = "none";

                showStartMode();
                updateStatus("Call ended");
            };

            // ==================== PENDING CALL ====================
            function checkPendingCall() {
                try {
                    const pendingCall = sessionStorage.getItem("pendingCall");
                    if (!pendingCall) return false;

                    const callData = JSON.parse(pendingCall);
                    const age = Date.now() - callData.timestamp;

                    if (age > 15000) {
                        sessionStorage.removeItem("pendingCall");
                        return false;
                    }

                    if (callData.offer?.sdp) {
                        callData.offer.sdp = cleanSDP(callData.offer.sdp);
                    }

                    incomingOffer = callData.offer;
                    incomingCallerId = callData.callerId;
                    incomingCallerName = callData.callerName;

                    sessionStorage.removeItem("pendingCall");

                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - click Accept");
                    return true;
                } catch (e) {
                    debug("checkPendingCall error:", e);
                    return false;
                }
            }

            // ==================== POST MESSAGE ====================
            window.addEventListener("message", async function (event) {
                debug("PostMessage received:", event.data?.type);

                if (event.data.type === "incoming-offer") {
                    incomingOffer = event.data.offer;
                    incomingCallerId = event.data.callerId;
                    incomingCallerName = event.data.callerName;

                    if (incomingOffer?.sdp) {
                        incomingOffer.sdp = cleanSDP(incomingOffer.sdp);
                    }

                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - click Accept");
                }

                if (event.data.type === "ice-candidate") {
                    const candidate = event.data.candidate;
                    debug("ICE candidate received via postMessage");

                    if (!peerConnection || !isRemoteDescriptionSet) {
                        pendingCandidates.push(candidate);
                        debug("ICE buffered");
                    } else {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                            debug("ICE added immediately");
                        } catch (err) {
                            debug("ICE add failed:", err);
                        }
                    }
                }

                if (event.data.type === "call-answer") {
                    debug("Answer received via postMessage");
                    await handleAnswer(event.data.answer);
                }
            });

            // ==================== PUSHER ====================
            function initPusher() {
                debug("Initializing Pusher");
                Pusher.logToConsole = true;

                pusher = new Pusher(PUSHER_APP_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });

                pusher.connection.bind("connected", () => {
                    debug("Pusher connected");

                    const isCaller = new URLSearchParams(window.location.search).has("mode=caller");
                    const hasPending = checkPendingCall();

                    if (isCaller) {
                        showStartMode();
                        updateStatus("Ready to start call");
                    } else if (!hasPending) {
                        document.getElementById("callTitle").textContent = `Waiting for call from ${otherUserName}...`;
                        updateStatus("Ready to receive calls");
                    }
                });

                pusher.connection.bind("error", (error) => {
                    debug("Pusher connection error:", error);
                });

                const channelName = "voice-call." + userId;
                channel = pusher.subscribe(channelName);

                channel.bind("subscription_succeeded", () => {
                    debug("Subscribed to channel:", channelName);
                });

                channel.bind("CallOffer", (data) => {
                    debug("CallOffer received", data);

                    if (!new URLSearchParams(window.location.search).has("mode=caller")) {
                        incomingOffer = data.offer;
                        incomingCallerId = data.callerId;
                        incomingCallerName = data.callerName;

                        if (incomingOffer?.sdp) {
                            incomingOffer.sdp = cleanSDP(incomingOffer.sdp);
                        }

                        showAcceptMode();
                        document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                        updateStatus("Incoming call - click Accept");
                    }
                });

                channel.bind("CallAnswer", async (data) => {
                    debug("CallAnswer received");
                    if (data.answer?.sdp) {
                        data.answer.sdp = cleanSDP(data.answer.sdp);
                    }
                    await handleAnswer(data.answer);
                });

                channel.bind("IceCandidate", async (data) => {
                    debug("IceCandidate received");

                    if (!peerConnection || !isRemoteDescriptionSet) {
                        pendingCandidates.push(data.candidate);
                        debug("ICE candidate buffered");
                    } else {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                            debug("ICE candidate added immediately");
                        } catch (err) {
                            debug("Error adding ICE candidate:", err);
                        }
                    }
                });
            }

            // ==================== INIT ====================
            document.addEventListener("DOMContentLoaded", function () {
                debug("Voice call page loaded");
                debug("User:", userId, "Other:", otherUserId);
                debug("URL:", window.location.href);
                debug("CSRF token present:", !!document.querySelector('meta[name="csrf-token"]')?.content);

                remoteAudioElement = document.getElementById("remoteAudio");
                remoteAudioElement.muted = false;
                remoteAudioElement.volume = 1;

                initPusher();
            });
        })();
    </script>
</body>
</html>