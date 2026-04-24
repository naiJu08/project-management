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
                    "turn:pm.inovace.in:3478?transport=tcp"
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
                urls: "turn:openrelay.metered.ca:443",
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
                console.log("🚀 Initializing video call...");
                console.log("📊 Call data:", {
                    hasOffer: !!callData?.offer,
                    room: callData?.room,
                    callerUserId: callData?.callerUserId,
                    myUserId: myUserId,
                    mode: callData?.offer ? 'receiver' : 'caller'
                });

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
                console.log("📹 Getting user media...");
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
                console.log("✅ Local stream obtained:", localStream.id);
                console.log("   - Video tracks:", localStream.getVideoTracks().length);
                console.log("   - Audio tracks:", localStream.getAudioTracks().length);

                // Set local video
                const localVideo = document.getElementById('localVideo');
                localVideo.srcObject = localStream;
                console.log("✅ Local video set");

                // Create peer connection
                console.log("🔌 Creating peer connection...");
                peerConnection = new RTCPeerConnection(config);
                console.log("✅ Peer connection created");

                // Add local tracks
                console.log("🎵 Adding local tracks to peer connection...");
                localStream.getTracks().forEach(track => {
                    const sender = peerConnection.addTrack(track, localStream);
                    console.log(`   - Added ${track.kind} track, sender created:`, !!sender);
                });

                // Setup peer connection listeners
                console.log("👂 Setting up peer connection listeners...");
                setupPeerConnectionListeners();

                // Process any pending answer that arrived early
                if (pendingAnswer) {
                    console.log("🔄 Processing early pending answer");
                    try {
                        await peerConnection.setRemoteDescription(pendingAnswer);
                        remoteDescriptionSet = true;
                        pendingAnswer = null;
                        console.log("✅ Early answer applied");
                    } catch (e) {
                        console.error("❌ Failed to apply early answer:", e);
                    }
                }

                // Handle incoming offer or create one
                if (callData.offer) {
                    // Receiver mode - handle incoming offer
                    console.log("📥 Receiver mode: handling incoming offer");
                    await handleIncomingOffer(callData.offer);
                } else {
                    // Caller mode - create offer
                    console.log("📤 Caller mode: creating offer");
                    await createAndSendOffer();
                }

                console.log("✅ Call initialization complete");

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
            if (!remoteVideo || !remoteVideo.srcObject) {
                console.log("⏳ Remote video not ready yet (no srcObject)");
                return;
            }

            // Check if already playing properly
            if (!remoteVideo.paused && remoteVideo.readyState >= 2) {
                console.log("✅ Remote video already playing");
                document.getElementById('connectingMsg').style.display = 'none';
                document.getElementById('callStatus').textContent = 'Connected';
                if (!callStartTime) {
                    callStartTime = Date.now();
                    startCallTimer();
                }
                return;
            }

            console.log("🎬 Attempting to play remote video...");
            console.log("   - Video readyState:", remoteVideo.readyState);
            console.log("   - Stream active:", remoteVideo.srcObject.active);
            console.log("   - Stream tracks:", remoteVideo.srcObject.getTracks().map(t => `${t.kind}:${t.readyState}`).join(', '));

            // If readyState is 0 or 1, wait for more data before playing
            if (remoteVideo.readyState < 2) {
                console.log("⏳ Video not ready yet (readyState < 2), waiting for canplay event...");

                // Set up one-time event listener for canplay
                const onCanPlay = () => {
                    console.log("🎯 canplay event fired, readyState:", remoteVideo.readyState);
                    remoteVideo.removeEventListener('canplay', onCanPlay);
                    remoteVideo.removeEventListener('loadedmetadata', onCanPlay);
                    playRemoteVideo();
                };

                remoteVideo.addEventListener('canplay', onCanPlay, { once: true });
                remoteVideo.addEventListener('loadedmetadata', onCanPlay, { once: true });

                // Fallback: try anyway after a delay
                setTimeout(() => {
                    remoteVideo.removeEventListener('canplay', onCanPlay);
                    remoteVideo.removeEventListener('loadedmetadata', onCanPlay);
                    if (remoteVideo.readyState >= 2) {
                        console.log("⏰ Fallback: video ready after delay");
                        playRemoteVideo();
                    }
                }, 2000);

                return;
            }

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
                    console.warn("⚠️ play() failed:", error.name, error.message);

                    // Handle specific errors
                    if (error.name === 'NotAllowedError') {
                        console.log("📱 Autoplay blocked - will retry on user interaction");
                        // Show a message to click to play
                        document.getElementById('connectingMsg').innerHTML = `
                            <div>📹 Click anywhere to start video</div>
                            <div style="font-size: 14px; margin-top: 10px;">Browser blocked autoplay</div>
                        `;
                        document.getElementById('connectingMsg').style.cursor = 'pointer';
                        document.getElementById('connectingMsg').onclick = () => {
                            playRemoteVideo();
                            document.getElementById('connectingMsg').onclick = null;
                            document.getElementById('connectingMsg').style.cursor = 'default';
                        };
                    } else if (error.name === 'AbortError') {
                        console.log("🔄 Play aborted, will retry");
                        setTimeout(playRemoteVideo, 300);
                    } else {
                        console.error("❌ Video play error:", error);
                        setTimeout(playRemoteVideo, 500);
                    }
                });
            }
        }
        
        function setupPeerConnectionListeners() {
            peerConnection.ontrack = event => {
                console.log("🎥 Remote stream received");
                const tracks = event.streams[0] ? event.streams[0].getTracks() : [event.track];
                console.log("🎥 Stream tracks:", tracks.map(t => `${t.kind}:${t.readyState}`).join(', '));
                console.log("🎥 Stream active:", event.streams[0]?.active);
                console.log("🎥 Track receivers:", peerConnection.getReceivers().length);

                const remoteVideo = document.getElementById('remoteVideo');

                if (!remoteVideo) {
                    console.error("❌ Remote video element not found");
                    return;
                }

                // Always update srcObject with latest stream
                if (event.streams && event.streams[0]) {
                    console.log("🎥 Setting srcObject from event.streams[0]");
                    remoteVideo.srcObject = event.streams[0];
                } else {
                    // Fallback: build stream from track directly
                    console.log("🎥 Building stream from individual track");
                    if (!remoteVideo.srcObject) {
                        remoteVideo.srcObject = new MediaStream();
                    }
                    remoteVideo.srcObject.addTrack(event.track);
                }

                remoteVideo.muted = false;

                // Call playRemoteVideo which will wait for canplay event
                console.log("🎥 Triggering video playback (will wait for ready state)");
                playRemoteVideo();
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
                    document.getElementById('callStatus').textContent = 'ICE Connected - Waiting for video...';
                    // Retry playing remote video multiple times after ICE connects
                    // Media may arrive with some delay
                    [300, 1000, 2000, 4000].forEach(delay => {
                        setTimeout(() => {
                            const remoteVideo = document.getElementById('remoteVideo');
                            if (remoteVideo && remoteVideo.paused) {
                                console.log(`🔄 ICE connected, retrying video play (${delay}ms)`);
                                playRemoteVideo();
                            }
                        }, delay);
                    });
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

                socket.emit("answer", {
                    room: callData.room,
                    answer: answer
                });

                console.log("📞 Sent answer to caller");
            } catch (error) {
                console.error("❌ Failed to handle offer:", error);
            }
        }
        
        async function createAndSendOffer() {
            try {
                console.log("📝 Creating offer...");
                const offer = await peerConnection.createOffer();
                console.log("✅ Offer created");

                await peerConnection.setLocalDescription(offer);
                console.log("✅ Local description set (offer)");

                socket.emit("offer", {
                    room: callData.room,
                    targetUserId: callData.callerUserId,
                    callerUserId: myUserId,
                    offer: offer
                });

                console.log("📞 Sent offer to receiver, room:", callData.room);
            } catch (error) {
                console.error("❌ Failed to create offer:", error);
            }
        }
        
        // Socket listeners
        let pendingAnswer = null;

        socket.on("answer", async (data) => {
            console.log("✅ Answer received");

            // If peerConnection not ready, queue the answer
            if (!peerConnection) {
                console.log("⏳ PeerConnection not ready, queueing answer");
                pendingAnswer = data.answer;
                return;
            }

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
                const candidateType = data.candidate.candidate?.includes('typ relay') ? 'TURN' :
                                     data.candidate.candidate?.includes('typ srflx') ? 'STUN' : 'host';
                console.log(`📡 ICE candidate received [${candidateType}]`);

                if (!peerConnection) {
                    console.log("⏳ PeerConnection not ready, queuing ICE candidate");
                    pendingCandidates.push(data.candidate);
                    return;
                }

                if (!remoteDescriptionSet) {
                    console.log("⏳ Remote description not set, queuing ICE candidate");
                    pendingCandidates.push(data.candidate);
                    return;
                }

                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                console.log(`✅ ICE candidate added [${candidateType}]`);
            } catch (error) {
                console.error("❌ Failed to add ICE candidate:", error);
                // Still queue it in case it can be added later
                pendingCandidates.push(data.candidate);
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
