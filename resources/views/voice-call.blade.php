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
        /* Audio control panel */
        #audioControl {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 10000;
            display: none;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        #volumeMeter {
            width: 50px;
            height: 4px;
            background: #333;
            border-radius: 2px;
            overflow: hidden;
        }
        #volumeLevel {
            width: 0%;
            height: 100%;
            background: #0f0;
            transition: width 0.1s;
        }
        #muteIcon {
            font-size: 16px;
        }
        #testSpeakerBtn {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 10000;
            cursor: pointer;
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

    <div id="audioControl">
        <span id="muteIcon">🔊</span>
        <div id="volumeMeter"><div id="volumeLevel"></div></div>
    </div>
    <button id="testSpeakerBtn" onclick="testSpeaker()">🔊 Test Speaker</button>

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

                // Recursively decode escaped characters
                let cleaned = sdp;
                let prev;
                do {
                    prev = cleaned;
                    cleaned = cleaned.replace(/\\r\\n/g, '\r\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\r/g, '\r')
                        .replace(/\\\\/g, '\\');
                } while (prev !== cleaned);

                // If no newlines, insert before each line that starts with a letter and '='
                if (!cleaned.includes('\n') && !cleaned.includes('\r')) {
                    cleaned = cleaned.replace(/([a-z]=)/g, '\r\n$1');
                    cleaned = cleaned.replace(/^\r\n/, '');
                }

                // Normalize line endings to CRLF
                cleaned = cleaned.replace(/\r?\n/g, '\r\n');

                // Split into lines, filter empty
                let lines = cleaned.split(/\r?\n/).filter(line => line.trim().length > 0);

                // Repair each line
                lines = lines.map(line => repairSDPLine(line.trim()));

                // Rejoin with CRLF and ensure a trailing CRLF
                return lines.join('\r\n') + '\r\n';
            }

            function repairSDPLine(line) {
                // Fix a=src: -> a=ssrc:
                if (line.startsWith('a=src:')) {
                    line = 'a=ssrc:' + line.substring(6);
                }

                // Ensure a=ssrc has colon after "ssrc"
                if (line.startsWith('a=ssrc') && !line.startsWith('a=ssrc:')) {
                    line = line.replace(/^a=ssrc/, 'a=ssrc:');
                }

                // Repair a=ssrc lines (msid format)
                if (line.startsWith('a=ssrc:')) {
                    let match = line.match(/^a=ssrc:(\d+)\s*(.*)$/);
                    if (match) {
                        let ssrc = match[1];
                        let rest = match[2].trim();

                        // Find msid part
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
            let audioContext = null;
            let audioAnalyser = null;
            let animationFrame = null;
            let remoteAudioElement = null;

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

            // ==================== VOLUME METER ====================
            function initVolumeMeter(stream) {
                if (audioContext) {
                    debug("Volume meter already initialized");
                    return;
                }
                debug("Initializing volume meter...");
                try {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    audioAnalyser = audioContext.createAnalyser();
                    audioAnalyser.fftSize = 256;
                    const source = audioContext.createMediaStreamSource(stream);
                    source.connect(audioAnalyser);
                    // Resume audio context if suspended (browser policy)
                    if (audioContext.state === 'suspended') {
                        audioContext.resume().then(() => debug("AudioContext resumed")).catch(e => debug("Failed to resume AudioContext:", e));
                    }
                    const meter = document.getElementById('volumeLevel');
                    const control = document.getElementById('audioControl');
                    control.style.display = 'flex';
                    function updateMeter() {
                        if (!audioAnalyser) return;
                        const dataArray = new Uint8Array(audioAnalyser.frequencyBinCount);
                        audioAnalyser.getByteFrequencyData(dataArray);
                        let sum = 0;
                        for (let i = 0; i < dataArray.length; i++) sum += dataArray[i];
                        let avg = sum / dataArray.length;
                        let percent = (avg / 255) * 100;
                        meter.style.width = percent + '%';
                        animationFrame = requestAnimationFrame(updateMeter);
                    }
                    updateMeter();
                    debug("Volume meter started");
                } catch (err) {
                    debug("❌ Failed to initialize volume meter:", err);
                }
            }

            function stopVolumeMeter() {
                if (animationFrame) cancelAnimationFrame(animationFrame);
                if (audioContext) audioContext.close();
                audioContext = null;
                audioAnalyser = null;
                document.getElementById('audioControl').style.display = 'none';
            }

            // ==================== SPEAKER TEST ====================
            window.testSpeaker = function() {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                oscillator.connect(gain);
                gain.connect(audioCtx.destination);
                oscillator.frequency.value = 440;
                gain.gain.value = 0.5;
                oscillator.start();
                gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.5);
                setTimeout(() => {
                    audioCtx.close();
                }, 500);
                updateStatus("🔊 Test tone played – you should hear a beep");
                setTimeout(() => {
                    if (callActive) updateStatus("Call connected!");
                    else updateStatus("Ready to receive calls");
                }, 1000);
            };

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

                    if (!remoteAudioElement) {
                        remoteAudioElement = document.createElement("audio");
                        remoteAudioElement.id = "remoteAudio";
                        remoteAudioElement.autoplay = true;
                        remoteAudioElement.playsInline = true;
                        remoteAudioElement.controls = false;
                        remoteAudioElement.style.display = "none";
                        document.body.appendChild(remoteAudioElement);
                        debug("Created hidden audio element");
                    }
                    remoteAudioElement.srcObject = event.streams[0];
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.muted = false;

                    // Start volume meter to visualize incoming audio
                    initVolumeMeter(event.streams[0]);

                    // Attempt to play
                    remoteAudioElement.play().then(() => {
                        debug("✅ Audio playback started successfully");
                        if (remoteAudioElement.paused) {
                            debug("⚠️ Audio element is paused despite play() success, retrying...");
                            remoteAudioElement.play().catch(e => debug("❌ Retry failed:", e));
                        }
                    }).catch(e => {
                        debug("⚠️ Audio playback failed (autoplay blocked):", e.message);
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

                    // Mute/unmute control
                    const control = document.getElementById('audioControl');
                    const muteIcon = document.getElementById('muteIcon');
                    control.onclick = () => {
                        if (!remoteAudioElement) return;
                        remoteAudioElement.muted = !remoteAudioElement.muted;
                        muteIcon.textContent = remoteAudioElement.muted ? '🔇' : '🔊';
                        updateStatus(remoteAudioElement.muted ? "Remote audio is muted" : "Remote audio is playing");
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

                    // Clean SDP
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

                    // Set remote description
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

                    // Create answer
                    debug("Creating answer...");
                    const answer = await peerConnection.createAnswer();
                    debug("Answer created:", answer.type);
                    await peerConnection.setLocalDescription(answer);
                    debug("✅ Local description set");

                    isRemoteSet = true;

                    // Add pending ICE candidates
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

                    // Send answer
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
                    remoteAudioElement.remove();
                    remoteAudioElement = null;
                }
                stopVolumeMeter();
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