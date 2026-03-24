<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Call - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-2xl mb-4">Call with {{ $user->name }}</h1>
            <div id="status" class="mb-4 text-yellow-400">Initializing...</div>
            
            <button id="startBtn" onclick="startCall()" class="bg-green-500 px-6 py-2 rounded mr-2">Start Call</button>
            <button id="acceptBtn" onclick="acceptCall()" class="bg-blue-500 px-6 py-2 rounded mr-2 hidden">Accept Call</button>
            <button id="endBtn" onclick="endCall()" class="bg-red-500 px-6 py-2 rounded">End Call</button>
            
            <div id="debug" class="mt-4 text-left text-xs text-gray-400 max-h-64 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        // Configuration
        const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
        const PUSHER_CLUSTER = "ap2";
        const userId = {{ auth()->id() }};
        const otherUserId = {{ $user->id }};
        
        // State
        let localStream = null;
        let peerConnection = null;
        let incomingOffer = null;
        let incomingCallerId = null;
        let pendingCandidates = [];
        let callActive = false;
        
        // Working ICE servers (Metered.ca is reliable)
        const iceServers = [
            { urls: "stun:stun.l.google.com:19302" },
            {
                urls: ["turn:openrelay.metered.ca:80", "turn:openrelay.metered.ca:443"],
                username: "openrelayproject",
                credential: "openrelayproject"
            }
        ];
        
        function log(msg) {
            console.log(msg);
            const debug = document.getElementById('debug');
            debug.innerHTML += `<div>${new Date().toLocaleTimeString()}: ${msg}</div>`;
            debug.scrollTop = debug.scrollHeight;
        }
        
        function updateStatus(msg) {
            document.getElementById('status').innerHTML = msg;
            log(msg);
        }
        
        async function startCall() {
            log("Starting call...");
            if (callActive) return;
            
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                log("✅ Microphone OK");
                
                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                
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
                
                if (response.ok) {
                    updateStatus("📞 Call initiated, waiting for answer...");
                    callActive = true;
                    document.getElementById('startBtn').style.display = 'none';
                }
            } catch (err) {
                log("❌ Error: " + err.message);
                alert("Microphone access required");
            }
        }
        
        async function acceptCall() {
            log("Accepting call...");
            if (!incomingOffer) {
                alert("No incoming call");
                return;
            }
            
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                log("✅ Microphone OK");
                
                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                
                await peerConnection.setRemoteDescription(new RTCSessionDescription(incomingOffer));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                
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
                
                if (response.ok) {
                    updateStatus("✅ Call connected!");
                    callActive = true;
                    document.getElementById('acceptBtn').style.display = 'none';
                }
            } catch (err) {
                log("❌ Error: " + err.message);
            }
        }
        
        function createPeer() {
            log("Creating peer connection...");
            peerConnection = new RTCPeerConnection({ iceServers });
            
            peerConnection.ontrack = (event) => {
                log("🎵 Received audio track!");
                const audio = new Audio();
                audio.srcObject = event.streams[0];
                audio.autoplay = true;
                audio.play().then(() => {
                    log("✅ Audio playing!");
                    updateStatus("🎵 Audio connected!");
                }).catch(e => {
                    log("❌ Autoplay blocked - click page to enable audio");
                    document.body.onclick = () => {
                        audio.play();
                        document.body.onclick = null;
                        log("✅ Audio started after click");
                    };
                });
            };
            
            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    log(`📡 ICE candidate: ${event.candidate.type}`);
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
                    });
                }
            };
            
            peerConnection.onconnectionstatechange = () => {
                log(`🔌 Connection state: ${peerConnection.connectionState}`);
                if (peerConnection.connectionState === 'connected') {
                    updateStatus("✅ Connected!");
                }
            };
            
            // Add any pending candidates
            pendingCandidates.forEach(candidate => {
                peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            });
            pendingCandidates = [];
        }
        
        function endCall() {
            log("Ending call");
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            if (peerConnection) peerConnection.close();
            localStream = null;
            peerConnection = null;
            callActive = false;
            document.getElementById('startBtn').style.display = 'block';
            document.getElementById('acceptBtn').style.display = 'none';
            updateStatus("Call ended");
        }
        
        // Initialize Pusher
        function initPusher() {
            log("Initializing Pusher...");
            const pusher = new Pusher(PUSHER_APP_KEY, { cluster: PUSHER_CLUSTER, forceTLS: true });
            const channel = pusher.subscribe('voice-call.' + userId);
            
            channel.bind('CallOffer', (data) => {
                log(`📞 Incoming call from ${data.callerName}`);
                incomingOffer = data.offer;
                incomingCallerId = data.callerId;
                document.getElementById('acceptBtn').style.display = 'block';
                document.getElementById('startBtn').style.display = 'none';
                updateStatus(`Incoming call from ${data.callerName}`);
            });
            
            channel.bind('CallAnswer', async (data) => {
                log("✅ Received answer");
                if (peerConnection) {
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
                }
            });
            
            channel.bind('IceCandidate', (data) => {
                log(`❄️ Received ICE candidate`);
                if (peerConnection) {
                    peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                } else {
                    pendingCandidates.push(data.candidate);
                }
            });
            
            log("✅ Ready!");
            updateStatus("Ready to call");
        }
        
        // Start
        document.addEventListener('DOMContentLoaded', initPusher);
    </script>
</body>
</html>