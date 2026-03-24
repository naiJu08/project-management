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
        .debug-visible #debugPanel { display: block; }
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
        #audioMessage {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #ff9800;
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10001;
            display: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        #forceAudioBtn {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ff5722;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            z-index: 10002;
            display: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border: none;
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
            <button id="startBtn" onclick="startCall()" class="bg-green-500 px-6 py-3 rounded-full text-lg hover:bg-green-600 transition">
                <span>📞</span> Start Call
            </button>
            <button id="acceptBtn" onclick="acceptCall()" class="bg-green-600 px-6 py-3 rounded-full text-lg hover:bg-green-700 transition animate-pulse">
                <span>✅</span> Accept Call
            </button>
            <button id="endBtn" onclick="endCall()" class="bg-red-500 px-6 py-3 rounded-full text-lg hover:bg-red-600 transition">
                ❌ End
            </button>
            <button onclick="toggleDebug()" class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition">🐛 Debug</button>
        </div>
        <div id="debugPanel"></div>
    </div>

    <div id="remotePanel" class="audioPanel remotePanel" style="display: none;">
        <span id="remoteMuteIcon" class="muteIcon">🔊</span>
        <div class="volumeMeter"><div id="remoteVolumeLevel" class="volumeLevel"></div></div>
    </div>

    <div id="localPanel" class="audioPanel localPanel" style="display: none;">
        <span id="localMicIcon" class="muteIcon">🎤</span>
        <div class="volumeMeter"><div id="localVolumeLevel" class="volumeLevel"></div></div>
    </div>

    <div class="actionButtons">
        <button id="testMicBtn" style="display: none;" onclick="testMicrophone()">🎤 Test Mic</button>
        <button id="playRemoteBtn" style="display: none;" onclick="forcePlayRemote()">🔊 Play Remote</button>
    </div>

    <button id="forceAudioBtn">🔊 CLICK HERE TO ENABLE AUDIO</button>
    <audio id="remoteAudio" controls autoplay style="display: none;"></audio>
    <div id="audioMessage">🔊 Click anywhere to enable audio</div>

    <script>
        (function() {
            "use strict";

            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = "{{ $user->name }}";

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

            // WORKING ICE SERVERS WITH RELIABLE TURN
            const iceServers = [
                { urls: "stun:stun.l.google.com:19302" },
                { urls: "stun:stun1.l.google.com:19302" },
                { urls: "stun:stun2.l.google.com:19302" },
                { urls: "stun:stun3.l.google.com:19302" },
                { urls: "stun:stun4.l.google.com:19302" },
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
                    urls: "turn:turn.anyfirewall.com:3478?transport=udp",
                    username: "anyfirewall",
                    credential: "anyfirewall"
                }
            ];

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
            let remoteVolumeCtx = null;
            let remoteAnalyser = null;
            let remoteAnimation = null;
            let playbackAudioContext = null;
            let playbackGain = null;
            let currentRemoteStream = null;

            function cleanSDP(sdp) {
                if (!sdp || typeof sdp !== 'string') return sdp;
                let cleaned = sdp.replace(/\\r\\n/g, '\r\n').replace(/\\n/g, '\n').replace(/\\r/g, '\r').replace(/\\\\/g, '\\');
                cleaned = cleaned.replace(/\r?\n/g, '\r\n');
                let lines = cleaned.split(/\r?\n/).filter(line => line.trim().length > 0);
                return lines.join('\r\n') + '\r\n';
            }

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
                if (remoteVolumeCtx) return;
                try {
                    remoteVolumeCtx = new (window.AudioContext || window.webkitAudioContext)();
                    remoteAnalyser = remoteVolumeCtx.createAnalyser();
                    remoteAnalyser.fftSize = 256;
                    const source = remoteVolumeCtx.createMediaStreamSource(stream);
                    source.connect(remoteAnalyser);
                    if (remoteVolumeCtx.state === 'suspended') {
                        remoteVolumeCtx.resume().catch(e => debug("Failed to resume remote volume meter:", e));
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
                if (remoteVolumeCtx) remoteVolumeCtx.close();
                localAudioContext = null;
                remoteVolumeCtx = null;
                localAnalyser = null;
                remoteAnalyser = null;
                document.getElementById('localPanel').style.display = 'none';
                document.getElementById('remotePanel').style.display = 'none';
            }

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
                    debug("Local microphone test started");
                    updateStatus("🔊 Listening to your microphone");
                    setTimeout(() => {
                        testCtx.close();
                        updateStatus(callActive ? "Call connected!" : "Ready");
                    }, 3000);
                }).catch(e => debug("Test mic failed:", e));
            };

            function ensureRemotePlayback() {
                if (!currentRemoteStream) {
                    debug("No remote stream available yet.");
                    return false;
                }
                if (!playbackAudioContext) {
                    debug("Creating playback AudioContext");
                    playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (playbackGain && playbackGain.context === playbackAudioContext) {
                    if (playbackAudioContext.state !== 'running') {
                        playbackAudioContext.resume().then(() => debug("Resumed existing AudioContext")).catch(e => debug("Resume failed:", e));
                    }
                    return true;
                }
                try {
                    if (playbackGain) playbackGain.disconnect();
                    const source = playbackAudioContext.createMediaStreamSource(currentRemoteStream);
                    playbackGain = playbackAudioContext.createGain();
                    playbackGain.gain.value = 1;
                    source.connect(playbackGain);
                    playbackGain.connect(playbackAudioContext.destination);
                    debug("Connected remote stream to playback AudioContext");
                    if (playbackAudioContext.state !== 'running') {
                        playbackAudioContext.resume().then(() => {
                            debug("Playback AudioContext resumed");
                            updateStatus("✅ Remote audio playing");
                        }).catch(e => {
                            debug("Failed to resume AudioContext:", e);
                            updateStatus("🔊 Click force audio button");
                        });
                    }
                    return true;
                } catch (e) {
                    debug("❌ Failed to connect remote stream:", e);
                    return false;
                }
            }

            function attemptPlayRemoteAudio() {
                if (!currentRemoteStream) return false;
                if (ensureRemotePlayback()) {
                    debug("✅ Audio playback started");
                    updateStatus("✅ Audio is playing");
                    return true;
                }
                if (remoteAudioElement && remoteAudioElement.srcObject) {
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.play()
                        .then(() => {
                            debug("✅ Audio element playback started");
                            updateStatus("✅ Audio is playing");
                            return true;
                        })
                        .catch(e => {
                            debug("❌ Audio element play failed:", e);
                            return false;
                        });
                }
                return false;
            }

            window.forcePlayRemote = function() {
                if (attemptPlayRemoteAudio()) {
                    updateStatus("🔊 Force‑playing remote audio");
                    const msgDiv = document.getElementById('audioMessage');
                    const forceBtn = document.getElementById('forceAudioBtn');
                    if (msgDiv) msgDiv.style.display = 'none';
                    if (forceBtn) forceBtn.style.display = 'none';
                } else {
                    alert("Could not play remote audio. Please check your browser permissions.");
                }
            };

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

            function createPeer() {
                debug("Creating peer connection with ICE servers:", iceServers.length);
                updateStatus("Setting up connection...");
                pendingCandidates = [];
                isRemoteSet = false;
                peerConnection = new RTCPeerConnection({ 
                    iceServers: iceServers,
                    iceCandidatePoolSize: 10
                });

                peerConnection.onconnectionstatechange = () => {
                    debug("Connection state:", peerConnection.connectionState);
                    updateStatus(`Connection: ${peerConnection.connectionState}`);
                    if (peerConnection.connectionState === 'connected') {
                        updateStatus("✅ Call connected!");
                    } else if (peerConnection.connectionState === 'failed') {
                        updateStatus("❌ Connection failed");
                    }
                };
                
                peerConnection.oniceconnectionstatechange = () => {
                    debug("ICE state:", peerConnection.iceConnectionState);
                };
                
                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        const candidateStr = event.candidate.candidate;
                        let type = "unknown";
                        if (candidateStr.includes("typ host")) type = "host";
                        else if (candidateStr.includes("typ srflx")) type = "srflx (STUN)";
                        else if (candidateStr.includes("typ relay")) type = "✨ RELAY (TURN) ✨";
                        debug(`ICE candidate [${type}]:`, event.candidate);
                        
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
                    } else {
                        debug("ICE candidate gathering completed.");
                    }
                };
                
                peerConnection.ontrack = (event) => {
                    debug("🎵 Remote audio track received");
                    updateStatus("Audio connected - Call active");

                    currentRemoteStream = event.streams[0];
                    startRemoteVolumeMeter(event.streams[0]);
                    document.getElementById('playRemoteBtn').style.display = 'block';

                    remoteAudioElement = document.getElementById("remoteAudio");
                    remoteAudioElement.style.display = "block";
                    remoteAudioElement.autoplay = true;
                    remoteAudioElement.playsInline = true;
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.srcObject = event.streams[0];

                    const played = attemptPlayRemoteAudio();
                    if (!played) {
                        const msgDiv = document.getElementById('audioMessage');
                        const forceBtn = document.getElementById('forceAudioBtn');
                        if (msgDiv) msgDiv.style.display = 'block';
                        if (forceBtn) forceBtn.style.display = 'block';
                        updateStatus("🔊 Click the orange button to enable audio");
                    } else {
                        const msgDiv = document.getElementById('audioMessage');
                        const forceBtn = document.getElementById('forceAudioBtn');
                        if (msgDiv) msgDiv.style.display = 'none';
                        if (forceBtn) forceBtn.style.display = 'none';
                    }

                    const remotePanel = document.getElementById('remotePanel');
                    const muteIcon = document.getElementById('remoteMuteIcon');
                    remotePanel.onclick = () => {
                        if (playbackGain) {
                            playbackGain.gain.value = playbackGain.gain.value === 1 ? 0 : 1;
                            muteIcon.textContent = playbackGain.gain.value === 0 ? '🔇' : '🔊';
                        } else if (remoteAudioElement) {
                            remoteAudioElement.muted = !remoteAudioElement.muted;
                            muteIcon.textContent = remoteAudioElement.muted ? '🔇' : '🔊';
                        }
                    };
                };
            }

            // Force audio button handler
            document.getElementById('forceAudioBtn').onclick = () => {
                forcePlayRemote();
            };

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

                    if (!playbackAudioContext) {
                        playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                        await playbackAudioContext.resume();
                        debug("✅ Playback AudioContext resumed");
                    }
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
                        updateStatus("❌ No answer");
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
                    debug("========== ACCEPT CALL CLICKED ==========");
                    if (!incomingOffer || !incomingCallerId) {
                        debug("❌ No incoming call to accept");
                        alert("No incoming call to accept");
                        return;
                    }

                    if (incomingOffer.sdp) {
                        incomingOffer.sdp = cleanSDP(incomingOffer.sdp);
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
                        debug("✅ Microphone access granted");
                        startLocalVolumeMeter(localStream);
                        document.getElementById('testMicBtn').style.display = 'block';
                    } catch (micErr) {
                        debug("❌ Microphone error:", micErr);
                        alert("Microphone access is required for calls");
                        updateStatus("❌ Microphone access denied");
                        callActive = false;
                        showAcceptMode();
                        return;
                    }

                    if (!playbackAudioContext) {
                        playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                        await playbackAudioContext.resume();
                        debug("✅ Playback AudioContext resumed");
                    }

                    createPeer();
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                    debug("Setting remote description...");
                    try {
                        const offerDesc = new RTCSessionDescription({
                            type: incomingOffer.type,
                            sdp: incomingOffer.sdp
                        });
                        await peerConnection.setRemoteDescription(offerDesc);
                        debug("✅ Remote description set successfully");
                    } catch (err) {
                        debug("❌ setRemoteDescription failed:", err.message);
                        throw err;
                    }

                    debug("Creating answer...");
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    debug("✅ Local description set");

                    isRemoteSet = true;

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
                        throw new Error(`Failed to send answer: ${response.status}`);
                    }
                    debug("✅ Answer sent successfully");
                    updateStatus("Call connected!");
                } catch (err) {
                    debug("❌ ERROR in acceptCall:", err);
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
                if (!peerConnection) return;
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
                if (playbackGain) {
                    playbackGain.disconnect();
                    playbackGain = null;
                }
                if (playbackAudioContext) {
                    playbackAudioContext.close();
                    playbackAudioContext = null;
                }
                stopVolumeMeters();
                pendingCandidates = [];
                isRemoteSet = false;
                callActive = false;
                currentRemoteStream = null;
                document.getElementById('testMicBtn').style.display = 'none';
                document.getElementById('playRemoteBtn').style.display = 'none';
                const msgDiv = document.getElementById('audioMessage');
                const forceBtn = document.getElementById('forceAudioBtn');
                if (msgDiv) msgDiv.style.display = 'none';
                if (forceBtn) forceBtn.style.display = 'none';
                if (incomingCallerId) {
                    document.getElementById("callTitle").textContent = "Call ended";
                    updateStatus("Call ended");
                } else {
                    showStartMode();
                }
                incomingOffer = null;
                incomingCallerId = null;
            };

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
                const channelName = 'voice-call.' + userId;
                channel = pusher.subscribe(channelName);
                channel.bind('subscription_succeeded', () => {
                    debug("Subscribed to channel:", channelName);
                });
                channel.bind('CallOffer', (data) => {
                    debug("📞 Call offer received");
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

            document.addEventListener("DOMContentLoaded", function() {
                debug("Voice call page loaded");
                debug("User ID:", userId, "Other User ID:", otherUserId);
                debug("URL params:", window.location.search);
                initPusher();
            });
        })();
    </script>
</body>
</html>