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
        #debugPanel {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
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
        /* Audio control panels */
        .audioPanel {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
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
        }
        .actionButtons button {
            background: rgba(0,0,0,0.7);
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
            width: 200px;
            height: 40px;
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
            <button onclick="toggleDebug()" class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition">🐛 Debug</button>
        </div>
        <div id="debugPanel"></div>
    </div>

    <!-- Remote audio control (bottom‑right) -->
    <div id="remotePanel" class="audioPanel remotePanel" style="display: none;">
        <span id="remoteMuteIcon" class="muteIcon">🔊</span>
        <div class="volumeMeter"><div id="remoteVolumeLevel" class="volumeLevel"></div></div>
    </div>

    <!-- Local audio control (bottom‑left) – only visible after mic is granted -->
    <div id="localPanel" class="audioPanel localPanel" style="display: none;">
        <span id="localMicIcon" class="muteIcon">🎤</span>
        <div class="volumeMeter"><div id="localVolumeLevel" class="volumeLevel"></div></div>
    </div>

    <div class="actionButtons">
        <button id="testMicBtn" style="display: none;" onclick="testMicrophone()">🎤 Test Mic</button>
        <button id="playRemoteBtn" style="display: none;" onclick="playRemoteAudio()">🔊 Play Remote</button>
    </div>

    <audio id="remoteAudio" controls autoplay style="display: none;"></audio>

    <script>
        (function() {
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
            window.toggleDebug = function() {
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

            // ==================== SDP CLEANER ====================
            function cleanSDP(sdp) {
                if (!sdp || typeof sdp !== 'string') return sdp;

                let cleaned = sdp;
                let prev;
                do {
                    prev = cleaned;
                    cleaned = cleaned.replace(/\\r\\n/g, '\r\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\r/g, '\r')
                        .replace(/\\\\/g, '\\');
                } while (prev !== cleaned);

                if (!cleaned.includes('\n') && !cleaned.includes('\r')) {
                    cleaned = cleaned.replace(/([a-z]=)/g, '\r\n$1');
                    cleaned = cleaned.replace(/^\r\n/, '');
                }

                cleaned = cleaned.replace(/\r?\n/g, '\r\n');
                let lines = cleaned.split(/\r?\n/).filter(line => line.trim().length > 0);
                lines = lines.map(line => repairSDPLine(line.trim()));
                return lines.join('\r\n') + '\r\n';
            }

            function repairSDPLine(line) {
                if (line.startsWith('a=src:')) line = 'a=ssrc:' + line.substring(6);
                if (line.startsWith('a=ssrc') && !line.startsWith('a=ssrc:')) {
                    line = line.replace(/^a=ssrc/, 'a=ssrc:');
                }
                if (line.startsWith('a=ssrc:')) {
                    let match = line.match(/^a=ssrc:(\d+)\s*(.*)$/);
                    if (match) {
                        let ssrc = match[1];
                        let rest = match[2].trim();
                        let msidMatch = rest.match(/msid:([a-f0-9-]+)(?:\s+)?([a-f0-9-]+)?/i);
                        if (msidMatch) {
                            let msid1 = msidMatch[1];
                            let msid2 = msidMatch[2];
                            if (!msid2) {
                                let remainder = rest.replace(/msid:[a-f0-9-]+/i, '');
                                let secondUuid = remainder.match(/([a-f0-9-]{36})/);
                                if (secondUuid) msid2 = secondUuid[1];
                                else msid2 = msid1;
                            }
                            return `a=ssrc:${ssrc} msid:${msid1} ${msid2}`;
                        }
                        return line;
                    }
                }
                return line;
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
            let remoteAudioElement = null;
            let localAudioContext = null;
            let localAnalyser = null;
            let localAnimation = null;
            let remoteAudioContext = null;
            let remoteAnalyser = null;
            let remoteAnimation = null;

            // ==================== VOLUME METERS ====================
            function startLocalVolumeMeter(stream) {
                if (localAudioContext) return;
                try {
                    localAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    localAnalyser = localAudioContext.createAnalyser();
                    localAnalyser.fftSize = 256;
                    const source = localAudioContext.createMediaStreamSource(stream);
                    source.connect(localAnalyser);
                    if (localAudioContext.state === 'suspended') {
                        localAudioContext.resume().catch(e => debug("Failed to resume local AudioContext:", e));
                    }
                    const meter = document.getElementById('localVolumeLevel');
                    const panel = document.getElementById('localPanel');
                    panel.style.display = 'flex';
                    function update() {
                        if (!localAnalyser) return;
                        const data = new Uint8Array(localAnalyser.frequencyBinCount);
                        localAnalyser.getByteFrequencyData(data);
                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        let avg = sum / data.length;
                        let percent = (avg / 255) * 100;
                        meter.style.width = percent + '%';
                        localAnimation = requestAnimationFrame(update);
                    }
                    update();
                    debug("Local volume meter started");
                } catch (e) {
                    debug("❌ Failed to start local volume meter:", e);
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
                    if (remoteAudioContext.state === 'suspended') {
                        remoteAudioContext.resume().catch(e => debug("Failed to resume remote AudioContext:", e));
                    }
                    const meter = document.getElementById('remoteVolumeLevel');
                    const panel = document.getElementById('remotePanel');
                    panel.style.display = 'flex';
                    function update() {
                        if (!remoteAnalyser) return;
                        const data = new Uint8Array(remoteAnalyser.frequencyBinCount);
                        remoteAnalyser.getByteFrequencyData(data);
                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        let avg = sum / data.length;
                        let percent = (avg / 255) * 100;
                        meter.style.width = percent + '%';
                        remoteAnimation = requestAnimationFrame(update);
                    }
                    update();
                    debug("Remote volume meter started");
                } catch (e) {
                    debug("❌ Failed to start remote volume meter:", e);
                }
            }

            function stopVolumeMeters() {
                if (localAnimation) cancelAnimationFrame(localAnimation);
                if (remoteAnimation) cancelAnimationFrame(remoteAnimation);
                if (localAudioContext) localAudioContext.close();
                if (remoteAudioContext) remoteAudioContext.close();
                localAudioContext = null;
                remoteAudioContext = null;
                localAnalyser = null;
                remoteAnalyser = null;
                document.getElementById('localPanel').style.display = 'none';
                document.getElementById('remotePanel').style.display = 'none';
            }

            // ==================== TEST MICROPHONE ====================
            window.testMicrophone = function() {
                if (!localStream) {
                    alert("No microphone stream available. Please start/accept a call first.");
                    return;
                }
                const testCtx = new (window.AudioContext || window.webkitAudioContext)();
                const source = testCtx.createMediaStreamSource(localStream);
                const gain = testCtx.createGain();
                source.connect(gain);
                gain.connect(testCtx.destination);
                gain.gain.value = 1;
                testCtx.resume().then(() => {
                    debug("Local microphone test started – you should hear your own voice");
                    updateStatus("🔊 Listening to your microphone – you should hear yourself");
                    setTimeout(() => {
                        testCtx.close();
                        updateStatus(callActive ? "Call connected!" : "Ready");
                    }, 3000);
                }).catch(e => debug("Test mic failed:", e));
            };

            // ==================== PLAY REMOTE AUDIO ====================
            window.playRemoteAudio = function() {
                if (!remoteAudioElement) {
                    alert("No remote audio element found.");
                    return;
                }
                remoteAudioElement.muted = false;
                remoteAudioElement.volume = 1;
                remoteAudioElement.play().then(() => {
                    debug("✅ Remote audio forced to play");
                    updateStatus("🔊 Remote audio forced – should now play");
                }).catch(e => {
                    debug("❌ Force play failed:", e);
                    alert("Could not play remote audio: " + e.message);
                });
            };

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
            window.addEventListener('message', function(event) {
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
                    debug("🎵 Remote audio track received");
                    updateStatus("Audio connected - Call active");

                    remoteAudioElement = document.getElementById("remoteAudio");
                    remoteAudioElement.style.display = "block";
                    remoteAudioElement.controls = true;
                    remoteAudioElement.autoplay = true;
                    remoteAudioElement.playsInline = true;
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.srcObject = event.streams[0];

                    // Start remote volume meter
                    startRemoteVolumeMeter(event.streams[0]);

                    const handleCanPlay = () => {
                        remoteAudioElement.removeEventListener('canplay', handleCanPlay);
                        remoteAudioElement.removeEventListener('loadedmetadata', handleCanPlay);
                        remoteAudioElement.play().then(() => {
                            debug("✅ Remote audio play succeeded");
                            if (remoteAudioElement.muted || remoteAudioElement.volume === 0) {
                                remoteAudioElement.muted = false;
                                remoteAudioElement.volume = 1;
                            }
                        }).catch(e => {
                            debug("⚠️ Remote audio play failed:", e.message);
                            updateStatus("🔊 Click anywhere to enable audio");
                            const enableAudio = () => {
                                remoteAudioElement.play().then(() => {
                                    debug("✅ Audio started after user interaction");
                                    updateStatus("Call connected!");
                                    document.removeEventListener('click', enableAudio);
                                }).catch(e2 => debug("❌ Still cannot play:", e2));
                            };
                            document.addEventListener('click', enableAudio);
                        });
                    };

                    remoteAudioElement.addEventListener('loadedmetadata', handleCanPlay, { once: true });
                    remoteAudioElement.addEventListener('canplay', handleCanPlay, { once: true });

                    // Show the "Play Remote" button in case the above fails
                    document.getElementById('playRemoteBtn').style.display = 'block';

                    // Mute/unmute control
                    const remotePanel = document.getElementById('remotePanel');
                    const muteIcon = document.getElementById('remoteMuteIcon');
                    remotePanel.onclick = () => {
                        if (remoteAudioElement) {
                            remoteAudioElement.muted = !remoteAudioElement.muted;
                            muteIcon.textContent = remoteAudioElement.muted ? '🔇' : '🔊';
                            updateStatus(remoteAudioElement.muted ? "Remote audio is muted" : "Remote audio is playing");
                        }
                    };
                };
            }

            // ==================== CALL FUNCTIONS ====================
            window.startCall = async function() {
                debug("Starting call...");
                if (callActive) return;
                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Requesting microphone...');
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    debug("Microphone access granted");
                    startLocalVolumeMeter(localStream);
                    document.getElementById('testMicBtn').style.display = 'block';
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

            window.acceptCall = async function() {
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
                        startLocalVolumeMeter(localStream);
                        document.getElementById('testMicBtn').style.display = 'block';
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

                    debug("Setting remote description...");
                    let remoteSet = false;
                    let lastError = null;

                    try {
                        const offerDesc = new RTCSessionDescription({
                            type: incomingOffer.type,
                            sdp: incomingOffer.sdp
                        });
                        await peerConnection.setRemoteDescription(offerDesc);
                        debug("✅ Remote description set successfully");
                        remoteSet = true;
                    } catch (err) {
                        lastError = err;
                        debug("❌ setRemoteDescription failed:", err.message);
                        console.error("SDP error:", err);
                    }

                    if (!remoteSet) {
                        throw new Error(`Failed to set remote description. Last error: ${lastError?.message}`);
                    }

                    debug("Creating answer...");
                    const answer = await peerConnection.createAnswer();
                    debug("Answer created:", answer.type);
                    await peerConnection.setLocalDescription(answer);
                    debug("✅ Local description set");

                    isRemoteSet = true;

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

            window.endCall = function() {
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
                if (remoteAudioElement) {
                    remoteAudioElement.srcObject = null;
                    remoteAudioElement.style.display = "none";
                }
                stopVolumeMeters();
                pendingCandidates = [];
                isRemoteSet = false;
                callActive = false;
                document.getElementById('testMicBtn').style.display = 'none';
                document.getElementById('playRemoteBtn').style.display = 'none';
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
            document.addEventListener("DOMContentLoaded", function() {
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