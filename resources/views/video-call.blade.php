<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Video Call</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 35%),
                linear-gradient(180deg, #111b21 0%, #0b141a 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .call-container {
            width: 100%;
            max-width: 430px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 28px 18px 140px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(180deg, rgba(17, 27, 33, 0.92) 0%, rgba(11, 20, 26, 0.98) 100%);
        }

        .call-container::before,
        .call-container::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .call-container::before {
            width: 180px;
            height: 180px;
            background: rgba(59, 130, 246, 0.14);
            top: -40px;
            right: -50px;
            filter: blur(10px);
        }

        .call-container::after {
            width: 220px;
            height: 220px;
            background: rgba(18, 140, 126, 0.1);
            bottom: 120px;
            left: -80px;
            filter: blur(12px);
        }

        .avatar-circle {
            width: 132px;
            height: 132px;
            border-radius: 999px;
            margin-top: 84px;
            margin-bottom: 22px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.92), rgba(29, 78, 216, 0.96));
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ecfdf5;
            font-size: 46px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35), inset 0 0 0 6px #16232c;
            position: relative;
        }

        .avatar-circle::after {
            content: "Secure video";
            position: absolute;
            top: 24px;
            right: 18px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            font-size: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }

        #callTitle {
            margin-top: 0;
            font-size: 30px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #f8fafc;
        }

        #callStatus {
            margin-top: 12px;
            color: #94a3b8;
            font-size: 15px;
            min-height: 24px;
        }

        .callTimer {
            margin: 8px 0 0;
            min-height: 22px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: #93c5fd;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .callTimer.visible {
            opacity: 1;
            transform: translateY(0);
        }

        #callStatus::after {
            content: "";
            display: flex;
            width: 84px;
            max-width: 84px;
            height: 52px;
            margin: 34px auto 0;
            background:
                linear-gradient(180deg, transparent 32px, rgba(59, 130, 246, 0.88) 32px, rgba(59, 130, 246, 0.88) 52px) 0 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 14px, rgba(59, 130, 246, 0.88) 14px, rgba(59, 130, 246, 0.88) 52px) 19px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 26px, rgba(59, 130, 246, 0.88) 26px, rgba(59, 130, 246, 0.88) 52px) 38px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 8px, rgba(59, 130, 246, 0.88) 8px, rgba(59, 130, 246, 0.88) 52px) 57px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 30px, rgba(59, 130, 246, 0.88) 30px, rgba(59, 130, 246, 0.88) 52px) 76px 0/8px 52px no-repeat;
            opacity: 0.9;
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.28));
            transform-origin: center bottom;
            animation: blueWaveBars 1.35s ease-in-out infinite;
        }

        @keyframes blueWaveBars {
            0% {
                background-size: 8px 52px, 8px 52px, 8px 52px, 8px 52px, 8px 52px;
                transform: translateY(0) scaleY(0.88);
                opacity: 0.72;
            }

            20% {
                background-size: 8px 38px, 8px 52px, 8px 30px, 8px 52px, 8px 40px;
                transform: translateY(-1px) scaleY(1.02);
                opacity: 0.92;
            }

            40% {
                background-size: 8px 26px, 8px 44px, 8px 52px, 8px 34px, 8px 24px;
                transform: translateY(0) scaleY(0.96);
                opacity: 1;
            }

            60% {
                background-size: 8px 42px, 8px 24px, 8px 36px, 8px 52px, 8px 48px;
                transform: translateY(-2px) scaleY(1.06);
                opacity: 0.94;
            }

            80% {
                background-size: 8px 30px, 8px 50px, 8px 22px, 8px 40px, 8px 34px;
                transform: translateY(-1px) scaleY(0.98);
                opacity: 0.88;
            }

            100% {
                background-size: 8px 52px, 8px 52px, 8px 52px, 8px 52px, 8px 52px;
                transform: translateY(0) scaleY(0.88);
                opacity: 0.72;
            }
        }

        .controls {
            position: fixed;
            left: 50%;
            bottom: 26px;
            transform: translateX(-50%);
            z-index: 10002;
            width: min(390px, calc(100% - 28px));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border-radius: 28px;
            background: rgba(17, 27, 33, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(14px);
        }

        .control-btn {
            min-width: 86px;
            min-height: 58px;
            border: 0;
            border-radius: 22px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, opacity 0.2s ease, background 0.2s ease;
        }

        .control-btn:hover {
            transform: translateY(-1px);
        }

        .mute-btn {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
        }

        .mute-btn.muted {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        }

        .end-btn {
            background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
        }

        .video-preview {
            position: fixed;
            bottom: 120px;
            right: 20px;
            width: 120px;
            height: 90px;
            border-radius: 12px;
            overflow: hidden;
            background: #1a1a1a;
            border: 2px solid rgba(59, 130, 246, 0.5);
            z-index: 10001;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .video-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remote-video-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #1a1a1a;
            z-index: 0;
        }

        .connecting-overlay {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 18px;
            text-align: center;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px 30px;
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }

        .video-active .call-container {
            opacity: 0.3;
            pointer-events: none;
        }

        .video-active .remote-video-fullscreen {
            opacity: 1;
        }

        .remote-video-fullscreen {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        @media (max-width: 520px) {
            .call-container {
                padding: 22px 14px 138px;
            }

            #callTitle {
                font-size: 26px;
            }

            .controls {
                gap: 10px;
                padding: 14px;
            }

            .control-btn {
                min-width: 72px;
                min-height: 54px;
                font-size: 13px;
                padding: 0 14px;
            }

            .video-preview {
                width: 100px;
                height: 75px;
                bottom: 110px;
                right: 14px;
            }
        }
    </style>
</head>
<body>
    <video id="remoteVideo" class="remote-video-fullscreen" autoplay playsinline></video>
    
    <div class="call-container">
        <div class="avatar-circle" id="avatarCircle">
            <span id="avatarInitial">U</span>
        </div>
        <h2 id="callTitle">Video Call</h2>
        <p id="callStatus">Connecting...</p>
        <p id="callTimer" class="callTimer">00:00</p>
        
        <div class="connecting-overlay" id="connectingMsg">
            <div>📹 Connecting to video call...</div>
            <div style="font-size: 14px; margin-top: 10px;">Please allow camera/microphone access</div>
        </div>
    </div>

    <div class="video-preview">
        <video id="localVideo" autoplay muted playsinline></video>
    </div>

    <div class="controls">
        <button id="muteVideoBtn" class="control-btn mute-btn" title="Toggle Video">
            <span id="videoBtnIcon">📹</span>
        </button>
        <button id="muteAudioBtn" class="control-btn mute-btn" title="Toggle Audio">
            <span id="audioBtnIcon">🎤</span>
        </button>
        <button id="endCallBtn" class="control-btn end-btn" title="End Call">
              End
        </button>
    </div>

    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
        // Get call data from sessionStorage or URL params
        let callData = null;
        const urlParams = new URLSearchParams(window.location.search);
        
        try {
            const storedData = sessionStorage.getItem('pendingVideoCall');
            if (storedData) {
                callData = JSON.parse(storedData);
                sessionStorage.removeItem('pendingVideoCall');
            }
        } catch (e) {
            console.error("Failed to parse stored call data:", e);
        }
        
        // If no stored data, try to get from URL
        if (!callData) {
            const callerId = urlParams.get('callerId') || window.location.pathname.split('/').pop();
            callData = {
                callerUserId: callerId,
                room: `room-${Math.min({{ auth()->id() }}, parseInt(callerId))}-${Math.max({{ auth()->id() }}, parseInt(callerId))}`,
                callerName: `User ${callerId}`
            };
        }
        
        console.log("📹 Video call popup opened with data:", callData);
        
        // Update UI with WhatsApp style
        const callerName = callData.callerName || 'Unknown User';
        document.getElementById('callTitle').textContent = callerName;
        document.getElementById('avatarInitial').textContent = callerName.charAt(0).toUpperCase();
        
        // Socket connection
        const socket = io("https://pm.inovace.in");
        const myUserId = {{ auth()->id() }};
        
        socket.on("connect", () => {
            console.log("✅ Video call socket connected");
            socket.emit("join-user", myUserId);
            
            if (callData.room) {
                socket.emit("join-room", callData.room);
            }
        });
        
        // WebRTC setup
        let localStream = null;
        let peerConnection = null;
        let callStartTime = null;
        let callTimer = null;
        let pendingCandidates = [];
        let remoteDescriptionSet = false;
        
        const iceServers = [
            // Google STUN servers (primary)
            { urls: "stun:stun.l.google.com:19302" },
            { urls: "stun:stun1.l.google.com:19302" },
            { urls: "stun:stun2.l.google.com:19302" },
            { urls: "stun:stun3.l.google.com:19302" },
            { urls: "stun:stun4.l.google.com:19302" },
            
            // Public STUN servers (backup)
            { urls: "stun:stun.stunprotocol.org:3478" },
            { urls: "stun:stun.ekiga.net:3478" },
            { urls: "stun:stun.ideasip.com:3478" },
            { urls: "stun:stun.rixtelecom.se:3478" },
            { urls: "stun:stun.schlund.de:3478" },
            { urls: "stun:stun.internetcalls.com:3478" },
            
            // TURN servers (for NAT traversal)
            {
                urls: [
                    "turn:pm.inovace.in:3478?transport=udp",
                    "turn:pm.inovace.in:3478?transport=tcp",
                    "turns:pm.inovace.in:5349?transport=tcp"
                ],
                username: "webrtcuser",
                credential: "strongpassword123"
            },
            // Backup TURN servers (public)
            {
                urls: "turn:openrelay.metered.ca:80",
                username: "openrelayproject",
                credential: "openrelayproject"
            },
            {
                urls: [
                    "turn:openrelay.metered.ca:443",
                    "turns:openrelay.metered.ca:443?transport=tcp"
                ],
                username: "openrelayproject",
                credential: "openrelayproject"
            }
        ];
        
        const config = {
            iceServers: iceServers,
            iceCandidatePoolSize: 20,
            iceTransportPolicy: 'all',
            bundlePolicy: 'max-bundle',
            rtcpMuxPolicy: 'require'
        };

        function waitForIceGatheringComplete(pc) {
            if (!pc || pc.iceGatheringState === "complete") {
                return Promise.resolve();
            }

            return new Promise(resolve => {
                const timeout = setTimeout(done, 10000);

                function done() {
                    clearTimeout(timeout);
                    pc.removeEventListener("icegatheringstatechange", onStateChange);
                    resolve();
                }

                function onStateChange() {
                    if (pc.iceGatheringState === "complete") {
                        done();
                    }
                }

                pc.addEventListener("icegatheringstatechange", onStateChange);
            });
        }
        
        // Test network connectivity
        async function testNetworkConnectivity() {
            try {
                // Test connectivity to multiple STUN servers
                const stunServers = [
                    'stun:stun.l.google.com:19302',
                    'stun:stun.stunprotocol.org:3478',
                    'stun:stun.ekiga.net:3478'
                ];
                
                let connectivityScore = 0;
                
                for (const stunServer of stunServers) {
                    try {
                        const pc = new RTCPeerConnection({
                            iceServers: [{ urls: stunServer }]
                        });
                        
                        const promise = new Promise((resolve, reject) => {
                            const timeout = setTimeout(() => {
                                pc.close();
                                reject(new Error('STUN timeout'));
                            }, 3000);
                            
                            pc.onicecandidate = (e) => {
                                if (e.candidate) {
                                    clearTimeout(timeout);
                                    pc.close();
                                    resolve(true);
                                }
                            };
                            
                            pc.createDataChannel('test');
                            pc.createOffer().then(offer => {
                                return pc.setLocalDescription(offer);
                            }).catch(reject);
                        });
                        
                        await promise;
                        connectivityScore++;
                    } catch (error) {
                        console.log(`STUN server ${stunServer} failed:`, error.message);
                    }
                }
                
                console.log(`Network connectivity score: ${connectivityScore}/${stunServers.length}`);
                return connectivityScore >= 1; // At least one STUN server should work
                
            } catch (error) {
                console.error("Network connectivity test failed:", error);
                return true; // Assume network is OK if test fails
            }
        }
        
        // Initialize call
        async function initializeCall() {
            try {
                // Test network connectivity first
                document.getElementById('callStatus').textContent = 'Testing Network...';
                const networkOk = await testNetworkConnectivity();
                
                if (!networkOk) {
                    document.getElementById('callStatus').textContent = 'Poor Network Connection';
                    document.getElementById('connectingMsg').innerHTML = '<div>🌐 Poor network detected</div><div style="font-size: 14px; margin-top: 10px;">Attempting connection anyway...</div>';
                } else {
                    document.getElementById('callStatus').textContent = 'Network OK - Connecting...';
                }
                
                // Get media
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280, max: 1920 },
                        height: { ideal: 720, max: 1080 },
                        facingMode: "user"
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                });
                
                // Set local video
                const localVideo = document.getElementById('localVideo');
                localVideo.srcObject = localStream;
                
                // Create peer connection
                peerConnection = new RTCPeerConnection(config);
                
                // Add local tracks
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });
                
                // Setup peer connection listeners
                setupPeerConnectionListeners();
                
                // Handle incoming offer or create one
                if (callData.offer) {
                    // Receiver mode - handle incoming offer
                    await handleIncomingOffer(callData.offer);
                } else {
                    // Caller mode - create offer
                    await createAndSendOffer();
                }
                
            } catch (error) {
                console.error("❌ Failed to initialize call:", error);
                document.getElementById('callStatus').textContent = 'Failed: ' + error.message;
                document.getElementById('connectingMsg').style.display = 'none';
            }
        }
        
        async function restartConnection() {
            try {
                console.log("🔄 Restarting WebRTC connection...");
                
                // Close existing peer connection
                if (peerConnection) {
                    peerConnection.close();
                }
                
                // Create new peer connection
                peerConnection = new RTCPeerConnection(config);
                
                // Re-add local tracks
                if (localStream) {
                    localStream.getTracks().forEach(track => {
                        peerConnection.addTrack(track, localStream);
                    });
                }
                
                // Re-setup event listeners
                setupPeerConnectionListeners();
                
                // Restart the signaling process
                if (callData.offer) {
                    await handleIncomingOffer(callData.offer);
                } else {
                    await createAndSendOffer();
                }
                
            } catch (error) {
                console.error("❌ Failed to restart connection:", error);
                document.getElementById('callStatus').textContent = 'Connection Failed';
            }
        }
        
        function playRemoteVideo() {
            const remoteVideo = document.getElementById('remoteVideo');
            if (!remoteVideo || !remoteVideo.srcObject) return;
            
            // Already playing
            if (!remoteVideo.paused && remoteVideo.readyState >= 2) return;
            
            const playPromise = remoteVideo.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log("✅ Remote video playing successfully");
                    document.getElementById('connectingMsg').style.display = 'none';
                    document.getElementById('callStatus').textContent = 'Connected';
                    document.body.classList.add('video-active');
                    if (!callStartTime) {
                        callStartTime = Date.now();
                        startCallTimer();
                    }
                }).catch(error => {
                    console.warn("⚠️ play() failed, retrying in 500ms:", error.message);
                    if (error.name === 'NotAllowedError') {
                        remoteVideo.muted = true;
                    }
                    setTimeout(playRemoteVideo, 500);
                });
            }
        }
        
        function setupPeerConnectionListeners() {
            peerConnection.ontrack = event => {
                console.log("🎥 Remote stream received");
                const tracks = event.streams[0] ? event.streams[0].getTracks() : [event.track];
                console.log("🎥 Stream tracks:", tracks);
                const remoteVideo = document.getElementById('remoteVideo');
                
                if (!remoteVideo) {
                    console.error("❌ Remote video element not found");
                    return;
                }
                
                // Always update srcObject with latest stream
                if (event.streams && event.streams[0]) {
                    remoteVideo.srcObject = event.streams[0];
                } else {
                    // Fallback: build stream from track directly
                    if (!remoteVideo.srcObject) {
                        remoteVideo.srcObject = new MediaStream();
                    }
                    remoteVideo.srcObject.addTrack(event.track);
                }
                
                remoteVideo.muted = false;
                remoteVideo.autoplay = true;
                remoteVideo.playsInline = true;
                
                // Attempt to play after a short delay
                if (remoteVideo.playTimeout) clearTimeout(remoteVideo.playTimeout);
                remoteVideo.playTimeout = setTimeout(playRemoteVideo, 200);
            };
            
            peerConnection.onicecandidate = event => {
                if (event.candidate) {
                    socket.emit("ice-candidate", {
                        room: callData.room,
                        candidate: event.candidate
                    });
                }
            };
            
            peerConnection.onconnectionstatechange = () => {
                const state = peerConnection.connectionState;
                console.log("WebRTC connection state:", state);
                
                if (state === 'connected') {
                    document.getElementById('callStatus').textContent = 'Connected';
                    document.getElementById('connectingMsg').style.display = 'none';
                } else if (state === 'failed' || state === 'disconnected') {
                    document.getElementById('callStatus').textContent = 'Connection Lost - Retrying...';
                    // Attempt to restart connection after delay
                    setTimeout(() => {
                        if (peerConnection.connectionState === 'failed' || peerConnection.connectionState === 'disconnected') {
                            console.log("🔄 Attempting to restart connection...");
                            restartConnection();
                        }
                    }, 3000);
                } else if (state === 'connecting') {
                    document.getElementById('callStatus').textContent = 'Connecting...';
                }
            };
            
            peerConnection.oniceconnectionstatechange = () => {
                const iceState = peerConnection.iceConnectionState;
                console.log("ICE connection state:", iceState);
                
                if (iceState === 'failed') {
                    document.getElementById('callStatus').textContent = 'ICE Connection Failed - Check Network';
                } else if (iceState === 'disconnected') {
                    document.getElementById('callStatus').textContent = 'Reconnecting...';
                } else if (iceState === 'connected' || iceState === 'completed') {
                    document.getElementById('callStatus').textContent = 'Connected';
                    // Retry playing remote video in case ontrack fired before connection was ready
                    setTimeout(playRemoteVideo, 300);
                }
            };
            
            peerConnection.onicegatheringstatechange = () => {
                const gatheringState = peerConnection.iceGatheringState;
                console.log("ICE gathering state:", gatheringState);
                
                if (gatheringState === 'complete') {
                    console.log("✅ ICE gathering complete");
                }
            };
        }
        
        async function handleIncomingOffer(offer) {
            try {
                await peerConnection.setRemoteDescription(offer);
                remoteDescriptionSet = true;
                
                // Flush any queued ICE candidates
                for (const candidate of pendingCandidates) {
                    try {
                        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                    } catch (e) {
                        console.warn("Failed to add queued candidate:", e);
                    }
                }
                pendingCandidates = [];
                
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                await waitForIceGatheringComplete(peerConnection);
                
                socket.emit("answer", {
                    room: callData.room,
                    answer: peerConnection.localDescription
                });
                
                console.log("📞 Sent answer to caller");
            } catch (error) {
                console.error("❌ Failed to handle offer:", error);
            }
        }
        
        async function createAndSendOffer() {
            try {
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                await waitForIceGatheringComplete(peerConnection);
                
                socket.emit("offer", {
                    room: callData.room,
                    targetUserId: callData.callerUserId,
                    callerUserId: myUserId,
                    offer: peerConnection.localDescription
                });
                
                console.log("📞 Sent offer to receiver");
            } catch (error) {
                console.error("❌ Failed to create offer:", error);
            }
        }
        
        // Socket listeners
        socket.on("answer", async (data) => {
            console.log("✅ Answer received");
            try {
                await peerConnection.setRemoteDescription(data.answer);
                remoteDescriptionSet = true;
                console.log("✅ Remote description set, flushing", pendingCandidates.length, "pending candidates");
                
                // Flush queued ICE candidates
                for (const candidate of pendingCandidates) {
                    try {
                        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                    } catch (e) {
                        console.warn("Failed to add queued candidate:", e);
                    }
                }
                pendingCandidates = [];
            } catch (error) {
                console.error("❌ Failed to set remote description:", error);
            }
        });
        
        socket.on("ice-candidate", async (data) => {
            try {
                if (peerConnection && remoteDescriptionSet) {
                    await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                } else {
                    console.log("⏳ Queuing ICE candidate (remote desc not set yet)");
                    pendingCandidates.push(data.candidate);
                }
            } catch (error) {
                console.error("❌ Failed to add ICE candidate:", error);
            }
        });
        
        // UI Controls
        function startCallTimer() {
            callTimer = setInterval(() => {
                const elapsed = Math.floor((Date.now() - callStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                document.getElementById('callTimer').textContent = `${minutes}:${seconds}`;
                document.getElementById('callTimer').classList.add('visible');
            }, 1000);
        }
        
        function endCall() {
            console.log("📞 Ending call");
            
            if (callTimer) {
                clearInterval(callTimer);
            }
            
            if (peerConnection) {
                peerConnection.close();
            }
            
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            
            // Close window
            window.close();
        }
        
        function toggleVideo() {
            const videoTrack = localStream.getVideoTracks()[0];
            const btn = document.getElementById('muteVideoBtn');
            const icon = document.getElementById('videoBtnIcon');
            
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                btn.classList.toggle('muted');
                icon.textContent = videoTrack.enabled ? '📹' : '📹';
            }
        }
        
        function toggleAudio() {
            const audioTrack = localStream.getAudioTracks()[0];
            const btn = document.getElementById('muteAudioBtn');
            const icon = document.getElementById('audioBtnIcon');
            
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                btn.classList.toggle('muted');
                icon.textContent = audioTrack.enabled ? '🎤' : '🔇';
            }
        }
        
        // Event listeners
        document.getElementById('endCallBtn').addEventListener('click', endCall);
        document.getElementById('muteVideoBtn').addEventListener('click', toggleVideo);
        document.getElementById('muteAudioBtn').addEventListener('click', toggleAudio);
        
        // Handle window close
        window.addEventListener('beforeunload', () => {
            endCall();
        });
        
        // Initialize call when page loads
        initializeCall();
    </script>
</body>
</html>
