<!DOCTYPE html>
<html>
<head>
    <title>Video Call</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            font-family: system-ui;
            overflow: hidden;
        }
        
        .video-container {
            position: relative;
            width: 100vw;
            height: 100vh;
        }
        
        #remoteVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #1a1a1a;
        }
        
        #localVideo {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 200px;
            height: 150px;
            border-radius: 10px;
            border: 2px solid #3b82f6;
            object-fit: cover;
            background: #2a2a2a;
        }
        
        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: rgba(0,0,0,0.7);
            padding: 10px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }
        
        .control-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            transform: scale(1.1);
        }
        
        .mute-btn {
            background: #374151;
            color: white;
        }
        
        .mute-btn.muted {
            background: #ef4444;
        }
        
        .end-btn {
            background: #dc2626;
            color: white;
        }
        
        .status {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
        }
        
        .caller-info {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            text-align: right;
        }
        
        .connecting {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" autoplay muted playsinline></video>
        
        <div class="status" id="callStatus">Connecting...</div>
        <div class="caller-info" id="callerInfo">
            <div id="callerName">User</div>
            <div id="callTimer">00:00</div>
        </div>
        
        <div class="connecting" id="connectingMsg">
            <div>📹 Connecting to video call...</div>
            <div style="font-size: 14px; margin-top: 10px;">Please allow camera/microphone access</div>
        </div>
        
        <div class="controls">
            <button id="muteVideoBtn" class="control-btn mute-btn" title="Toggle Video">📹</button>
            <button id="muteAudioBtn" class="control-btn mute-btn" title="Toggle Audio">🎤</button>
            <button id="endCallBtn" class="control-btn end-btn" title="End Call">📞</button>
        </div>
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
        
        // Update UI
        document.getElementById('callerName').textContent = callData.callerName || 'Unknown User';
        
        // Socket connection
        const socket = io("https://pm.inovace.in");
        const myUserId = {{ auth()->id() }};
        const remoteUserId = Number(callData.targetUserId) === Number(myUserId)
            ? Number(callData.callerUserId)
            : Number(callData.targetUserId || callData.callerUserId);
        
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
                        senderUserId: myUserId,
                        targetUserId: remoteUserId,
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
                    senderUserId: myUserId,
                    targetUserId: remoteUserId,
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
                    senderUserId: myUserId,
                    offer: peerConnection.localDescription
                });
                
                console.log("📞 Sent offer to receiver");
            } catch (error) {
                console.error("❌ Failed to create offer:", error);
            }
        }
        
        // Socket listeners
        socket.on("answer", async (data) => {
            if (Number(data.senderUserId) === Number(myUserId)) {
                return;
            }

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
            if (Number(data.senderUserId) === Number(myUserId)) {
                return;
            }

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
            
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                btn.classList.toggle('muted');
                btn.textContent = videoTrack.enabled ? '📹' : '📹';
            }
        }
        
        function toggleAudio() {
            const audioTrack = localStream.getAudioTracks()[0];
            const btn = document.getElementById('muteAudioBtn');
            
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                btn.classList.toggle('muted');
                btn.textContent = audioTrack.enabled ? '🎤' : '🔇';
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
