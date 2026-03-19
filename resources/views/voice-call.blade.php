<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Call</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <style>
        /* Start button defaults to visible */
        #startBtn {
            display: block;
        }

        /* Accept button defaults to hidden */
        #acceptBtn {
            display: none;
        }

        /* When in hidden state */
        #startBtn.hidden-btn,
        #acceptBtn.hidden-btn {
            display: none !important;
            visibility: hidden !important;
        }

        /* When in show state */
        #startBtn.show-btn,
        #acceptBtn.show-btn {
            display: block !important;
            visibility: visible !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("✅ JS LOADED");

            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};

            // ✅ DIAGNOSTIC: Show user IDs
            console.log("👤 DIAGNOSTIC: Current user (userId):", userId);
            console.log("👤 DIAGNOSTIC: Other user (otherUserId):", otherUserId);
            console.log("📍 DIAGNOSTIC: Voice-call page URL:", window.location.href);
            console.log("📍 DIAGNOSTIC: Will subscribe to channel: voice-call." + userId);

            // ✅ CHECK BUTTONS EXIST
            console.log("🔍 Checking buttons...");
            console.log("✅ startBtn exists:", document.getElementById("startBtn") !== null);
            console.log("✅ acceptBtn exists:", document.getElementById("acceptBtn") !== null);

            let localStream;
            let peerConnection;
            let incomingOffer = null;
            let incomingCallerId = null;

            let pendingCandidates = [];
            let isRemoteSet = false;
            let callActive = false;
            let connectionTimeout = null;

            // ✅ UPDATE STATUS
            function updateStatus(message) {
                console.log("📊 STATUS: " + message);
                document.getElementById("callStatus").textContent = message;
            }

            // ✅ BUTTON VISIBILITY HELPERS
            function showStartMode() {
                console.log("🔘 Switching to START mode");
                const startBtn = document.getElementById("startBtn");
                const acceptBtn = document.getElementById("acceptBtn");

                if (startBtn) {
                    startBtn.style.display = "block";
                    startBtn.style.visibility = "visible";
                    console.log("✅ Start button: visible");
                }
                if (acceptBtn) {
                    acceptBtn.style.display = "none";
                    acceptBtn.style.visibility = "hidden";
                    console.log("✅ Accept button: hidden");
                }
            }

            function showAcceptMode() {
                console.log("🔘 Switching to ACCEPT mode");
                const startBtn = document.getElementById("startBtn");
                const acceptBtn = document.getElementById("acceptBtn");
                const title = document.getElementById("callTitle");

                if (startBtn) {
                    startBtn.style.display = "none";
                    startBtn.style.visibility = "hidden";
                    console.log("✅ Start button: hidden");
                }
                if (acceptBtn) {
                    acceptBtn.style.display = "block";
                    acceptBtn.style.visibility = "visible";
                    console.log("✅ Accept button: visible");
                }

                if (title) {
                    title.textContent = "Incoming Call";
                    console.log("✅ Title updated to: Incoming Call");
                }
            }

            function hideAllButtons() {
                console.log("🔘 Hiding all buttons (call active)");
                const startBtn = document.getElementById("startBtn");
                const acceptBtn = document.getElementById("acceptBtn");

                if (startBtn) startBtn.style.display = "none";
                if (acceptBtn) acceptBtn.style.display = "none";
            }

            // ✅ INITIALIZE PAGE MODE
            function initializePage() {
                console.log("🎯 Initializing page...");
                const isReceiver = new URLSearchParams(window.location.search).get('mode') !== 'caller';
                
                if (isReceiver) {
                    console.log("📱 Page mode: RECEIVER - waiting for incoming call");
                    console.log("📱 DIAGNOSTIC: This is RECEIVER mode - expecting voice-call from user", otherUserId);
                    console.log("📱 DIAGNOSTIC: Will receive offer on channel 'voice-call." + userId + "'");
                    showAcceptMode();
                    updateStatus("Ready to receive call...");
                } else {
                    console.log("📱 Page mode: CALLER - ready to initiate call");
                    console.log("📱 DIAGNOSTIC: This is CALLER mode - will call user", otherUserId);
                    showStartMode();
                    updateStatus("Ready - Click Start to call");
                }
            }

            // ✅ PUSHER INIT
            const pusher = new Pusher("0c08d7f3f0fa0c883f22", {
                cluster: "ap2",
                forceTLS: true,
                activityTimeout: 30000,  // 30s timeout
                pongTimeout: 10000        // 10s wait for pong
            });

            // **DEBUG: Track Pusher lifecycle**
            window.Pusher.logToConsole = true;  // Enable Pusher debug logs

        pusher.connection.bind('connected', () => {
            console.log("✅ PUSHER CONNECTED");
            console.log("🔌 DIAGNOSTIC: Pusher WebSocket connected at", new Date().toLocaleTimeString());
            console.log("🔌 DIAGNOSTIC: Socket ID:", pusher.connection.socket_id);
            initializePage();
        });

        pusher.connection.bind('error', (error) => {
            console.error("❌ PUSHER ERROR:", error);
            console.error("❌ DIAGNOSTIC: Pusher connection failed:", error.message);
            console.error("❌ DIAGNOSTIC: Error type:", error.type);
            updateStatus("Connection Error: " + error.type);
        });

        pusher.connection.bind('state_change', (states) => {
            console.log("🔄 PUSHER STATE CHANGE:", states);
            console.log("  Previous:", states.previous, "→ Current:", states.current);
        });

        const channel = pusher.subscribe('voice-call.' + userId);

        console.log("🔄 DIAGNOSTIC: Subscribing to 'voice-call." + userId + "'...");
        console.log("🔄 DIAGNOSTIC: Expected channel name: voice-call." + userId);

        channel.bind('subscription_succeeded', () => {
            console.log("✅ CHANNEL SUBSCRIBED: voice-call." + userId);
            console.log("🎧 Listening for call offers on channel: voice-call." + userId);
            console.log("📢 DIAGNOSTIC: Ready to receive events on this channel");
            console.log("📢 DIAGNOSTIC: Channel object:", channel);
            console.log("📢 DIAGNOSTIC: Channel auth_status:", channel.auth_status);
            updateStatus("Ready - Waiting for incoming calls...");
        });

        channel.bind('error', (error) => {
            console.error("❌ CHANNEL ERROR:", error);
            console.error("❌ Channel subscription error:", error.message);
        });
        
        channel.bind('pusher:subscription_error', (data) => {
            console.error("❌ PUSHER SUBSCRIPTION ERROR:", data);
            console.error("❌ Status code:", data.status);
        });

        // ✅ TEST MESSAGE HANDLER
        channel.bind('TestBroadcast', (data) => {
            console.log("✅ TEST MESSAGE RECEIVED!");
            console.log("✅ Test message:", data.message);
            console.log("✅ BROADCAST SYSTEM IS WORKING!");
            updateStatus("✅ Test broadcast received! System working!");
        });

        // ✅ CREATE PEER
        function createPeer() {

            console.log("🧠 Creating Peer");
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

            // ✅ CONNECTION STATE
            peerConnection.onconnectionstatechange = () => {
                console.log("🔗 Connection State: " + peerConnection.connectionState);
                updateStatus("Connection: " + peerConnection.connectionState);
            };

            peerConnection.oniceconnectionstatechange = () => {
                console.log("❄ ICE Connection State: " + peerConnection.iceConnectionState);
                updateStatus("ICE: " + peerConnection.iceConnectionState);
            };

            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {

                    console.log("📡 Sending ICE");

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
                        console.error("❌ Error sending ICE candidate:", err);
                    });
                }
            };

            peerConnection.ontrack = (event) => {

                console.log("🔊 AUDIO RECEIVED");
                updateStatus("Audio received - Call active");

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

        // ✅ START CALL (CALLER)
        async function startCall() {

            if (callActive) return;
            callActive = true;

            hideAllButtons();

            updateStatus("Requesting microphone access...");

            console.log("🚀 START BUTTON CLICKED");

            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                updateStatus("Microphone accessed, creating offer...");
            } catch (err) {
                alert("Microphone permission blocked");
                updateStatus("❌ Microphone access denied");
                callActive = false;
                showStartMode();
                return;
            }

            createPeer();

            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });

            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);

            updateStatus("Notifying recipient and waiting for connection...");
            console.log("📢 Offer created, waiting 2s for receiver to connect to Pusher...");

            // Wait for receiver to open and subscribe before sending offer
            await new Promise(resolve => setTimeout(resolve, 2000));

            updateStatus("Sending offer to recipient...");

            try {
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
                    throw new Error('Failed to send offer: ' + response.statusText);
                }
                console.log("✅ Offer sent successfully");
                updateStatus("Offer sent, waiting for answer...");

                // Set timeout for answer
                connectionTimeout = setTimeout(() => {
                    console.error("❌ No answer received within 30 seconds");
                    updateStatus("❌ No response - call may have been declined or is unreachable");
                }, 30000);

            } catch (err) {
                console.error('❌ Error sending offer:', err);
                updateStatus("❌ Failed to send offer: " + err.message);
                callActive = false;
                showStartMode();
                throw err;
            }
        }

        async function acceptCall() {
            try {
            console.log("✅ [BUTTON CLICKED] ACCEPT CALL BUTTON CLICKED!");
            console.log("✅ [ENTRY] Entering acceptCall() function");
            console.log("✅ [STATE] callActive:", callActive);
            console.log("✅ [STATE] incomingOffer:", incomingOffer ? "SET ✅" : "NULL ❌");
            console.log("✅ [STATE] incomingCallerId:", incomingCallerId ? "SET ✅" : "NULL ❌");

            if (callActive) {
                console.error("❌ [GUARD] Call already active, returning");
                return;
            }
            
            // ✅ CHECK IF OFFER EXISTS
            if (!incomingOffer) {
                console.error("❌ [GUARD] Cannot accept call - no offer received yet");
                updateStatus("❌ Waiting for call offer... please wait");
                return;
            }

            // ✅ CHECK IF CALLER ID IS SET
            if (!incomingCallerId) {
                console.error("❌ [GUARD] Cannot accept call - caller ID not set");
                updateStatus("❌ Caller information missing");
                return;
            }

            if (!incomingOffer.type || !incomingOffer.sdp) {
                console.error("❌ [GUARD] Offer incomplete:", incomingOffer);
                updateStatus("❌ Offer data is incomplete");
                return;
            }

            console.log("✅ [PASSED] All guards passed, proceeding with accept...");

            callActive = true;

            hideAllButtons();

            updateStatus("Requesting microphone access...");

            console.log("✅ [MIC REQUEST] Requesting microphone permission...");

            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log("✅ [MIC SUCCESS] Microphone accessed successfully");
                console.log("✅ [MIC TRACKS] Audio tracks:", localStream.getAudioTracks().length);
                updateStatus("Microphone accessed, creating answer...");
            } catch (err) {
                console.error("❌ [MIC ERROR] Microphone error:", err);
                alert("Mic blocked");
                updateStatus("❌ Microphone access denied");
                callActive = false;
                showAcceptMode();
                return;
            }

            console.log("✅ [PEER CREATE] Creating peer connection...");
            createPeer();
            console.log("✅ [PEER CREATED] Peer connection created successfully");

            console.log("✅ [ADD TRACK] Adding local audio track to peer...");
            localStream.getTracks().forEach(track => {
                console.log("  - Adding track:", track.kind, track.id);
                peerConnection.addTrack(track, localStream);
            });
            console.log("✅ [TRACK ADDED] Tracks added successfully");

            try {
                console.log("✅ [SET REMOTE DESC] Setting remote description from offer...");
                console.log("🔄 About to set remote description with:", {
                    type: incomingOffer.type,
                    sdp: incomingOffer.sdp ? incomingOffer.sdp.substring(0, 100) : 'NO SDP'
                });
                
                await peerConnection.setRemoteDescription(
                    new RTCSessionDescription({
                        type: incomingOffer.type,
                        sdp: incomingOffer.sdp
                    })
                );
                console.log("✅ [REMOTE SET] Remote description set successfully");
                updateStatus("Processing offer, creating answer...");
            } catch (err) {
                console.error("❌ [REMOTE ERROR] Error setting remote description:", err);
                console.error("❌ [DEBUG] incomingOffer structure:", {
                    type: incomingOffer?.type,
                    sdp: incomingOffer?.sdp ? incomingOffer.sdp.substring(0, 100) : null,
                    fullObj: incomingOffer
                });
                updateStatus("❌ Error processing offer: " + err.message);
                callActive = false;
                showAcceptMode();
                return;
            }

            console.log("✅ [CREATE ANSWER] Creating answer from offer...");
            const answer = await peerConnection.createAnswer();
            console.log("✅ [ANSWER CREATED] Answer created:", {
                type: answer.type,
                sdp: answer.sdp ? answer.sdp.substring(0, 100) : null
            });

            console.log("✅ [SET LOCAL DESC] Setting local description with answer...");
            await peerConnection.setLocalDescription(answer);
            console.log("✅ [LOCAL SET] Local description set successfully");

            isRemoteSet = true;

            console.log("✅ [ADD ICE] Adding pending ICE candidates...");
            pendingCandidates.forEach(c => {
                peerConnection.addIceCandidate(new RTCIceCandidate(c)).catch(err => {
                    console.error("❌ Error adding ICE candidate:", err);
                });
            });
            console.log("✅ [ICE ADDED] Pending ICE processed, count:", pendingCandidates.length);

            pendingCandidates = [];

            updateStatus("Sending answer...");

            console.log("✅ [SEND ANSWER] About to send answer to backend...");
            console.log("✅ [SEND ANSWER] Answer details:", {
                type: answer.type,
                sdp: answer.sdp ? answer.sdp.substring(0, 100) + '...' : 'MISSING',
                receiverId: incomingCallerId
            });

            try {
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
                
                console.log("📤 [RESPONSE] Send answer response status:", response.status);
                console.log("📤 [RESPONSE] Sending answer to caller:", incomingCallerId);
                
                if (!response.ok) {
                    throw new Error('Failed to send answer: ' + response.statusText);
                }
                console.log("✅ [SUCCESS] Answer sent successfully");
                console.log("✅ [SUCCESS] Answer sent to receiverId:", incomingCallerId);
                updateStatus("Answer sent, establishing connection...");
            } catch (err) {
                console.error('❌ [SEND ERROR] Error sending answer:', err);
                console.error('❌ [DEBUG] Failed with receiverId:', incomingCallerId);
                updateStatus("❌ Failed to send answer: " + err.message);
                callActive = false;
                showAcceptMode();
                throw err;
            }
            } catch (outerErr) {
                console.error('❌ [CRITICAL ERROR] Unexpected error in acceptCall:', outerErr);
                console.error('❌ Stack:', outerErr.stack);
                alert("ERROR: " + outerErr.message);
            }
        }
        // ✅ RECEIVE OFFER (RECEIVER)
        // channel.bind('.CallOffer', async (data) => {

        //     console.log("📞 OFFER RECEIVED");

        //     try {
        //         localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        //     } catch (err) {
        //         alert("Mic blocked");
        //         return;
        //     }

        //     createPeer();

        //     localStream.getTracks().forEach(track => {
        //         peerConnection.addTrack(track, localStream);
        //     });

        //     await peerConnection.setRemoteDescription(
        //         new RTCSessionDescription(data.offer)
        //     );

        //     const answer = await peerConnection.createAnswer();
        //     await peerConnection.setLocalDescription(answer);

        //     console.log("📤 SENDING ANSWER");

        //     fetch('/send-answer', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //         },
        //         body: JSON.stringify({
        //             answer: answer,
        //             receiverId: data.callerId
        //         })
        //     });
        // });

        // let incomingOffer = null;
        // let incomingCallerId = null;

        console.log("🔗 [CRITICAL] About to bind 'CallOffer' event listener...");
        console.log("🔗 [CRITICAL] Current channel state:", channel.state);
        console.log("🔗 [CRITICAL] Current channel name:", channel.name);
        console.log("🔗 [CRITICAL] userId:", userId);
        console.log("🔗 [CRITICAL] otherUserId:", otherUserId);

        channel.bind('CallOffer', (data) => {

            console.log("📞 ============ OFFER RECEIVED ============");
            console.log("📞 Event fired at:", new Date().toLocaleTimeString());
            console.log("📞 Full data object keys:", Object.keys(data));
            console.log("📞 Full data:", JSON.stringify(data, null, 2));
            console.log("📞 DIAGNOSTIC: This event is firing on receiver's voice-call page!");
            
            // Handle nested offer structure (in case it comes wrapped)
            let offer = data.offer;
            if (offer && typeof offer === 'string') {
                try {
                    offer = JSON.parse(offer);
                    console.log("📞 Parsed offer from string");
                } catch (e) {
                    console.error("❌ Failed to parse offer string:", e);
                    updateStatus("❌ Invalid offer format received");
                    return;
                }
            }
            
            console.log("📞 Caller ID:", data.callerId, "| My ID:", userId);
            
            // Validate offer structure
            if (!offer) {
                console.error("❌ No offer found in data");
                updateStatus("❌ No offer received from caller");
                return;
            }

            console.log("📞 Offer structure:", {
                type: offer.type,
                sdp: offer.sdp ? offer.sdp.substring(0, 100) + '...' : 'MISSING SDP',
                hasType: !!offer.type,
                hasSdp: !!offer.sdp
            });

            // Store the offer
            incomingOffer = offer;
            incomingCallerId = data.callerId;

            console.log("📞 Stored offer and caller ID - ready to accept");
            console.log("📞 incomingOffer is now:", incomingOffer ? "SET ✅" : "NULL ❌");
            console.log("📞 incomingCallerId is now:", incomingCallerId ? "SET ✅" : "NULL ❌");

            // Update status
            const statusMsg = "Incoming call from User " + data.callerId;
            updateStatus(statusMsg);

            // Switch to accept mode - this handles title and button updates
            console.log("📞 Switching to accept mode...");
            showAcceptMode();
            console.log("📞 ✅ ACCEPT MODE - Ready to accept call");
        });

        console.log("🔗 [CRITICAL] ✅ 'CallOffer' event binding SUCCESSFUL");
        console.log("🔗 [CRITICAL] All channel callbacks:", channel.callbacks);

        // Timeout to show if offer never arrives
        setTimeout(() => {
            if (!incomingOffer) {
                console.error("⏱️ ❌ TIMEOUT: Offer not received after 5 seconds");
                console.error("⏱️ DIAGNOSTICS:");
                console.error("  - Channel subscribed?", channel.state === 'subscribed');
                console.error("  - Channel name:", channel.name);
                console.error("  - CallOffer bound?", channel.callbacks && channel.callbacks['CallOffer'] && channel.callbacks['CallOffer'].length > 0);
                console.error("  - Total callbacks bound:", channel.callbacks ? Object.keys(channel.callbacks).length : 0);
                console.error("  - incomingOffer value:", incomingOffer);
                console.error("⏱️ Possible causes:");
                console.error("  1. Event not being broadcast from backend");
                console.error("  2. Event being broadcast to wrong channel");
                console.error("  3. Frontend not listening on correct channel");
                console.error("  4. Event name mismatch (CallOffer vs calloffer?)");
            } else {
                console.log("✅ Offer received in time!");
            }
        }, 5000);

        // ✅ RECEIVE ANSWER
        channel.bind('CallAnswer', async (data) => {

            console.log("✅ ANSWER RECEIVED");
            console.log("✅ Answer structure:", {
                type: data.answer?.type,
                sdp: data.answer?.sdp ? data.answer.sdp.substring(0, 50) + '...' : null
            });
            updateStatus("Answer received, connecting...");

            if (connectionTimeout) {
                clearTimeout(connectionTimeout);
            }

            if (!peerConnection) {
                console.log("⚠ Peer not ready");
                updateStatus("❌ Peer connection not ready");
                return;
            }

            try {
                await peerConnection.setRemoteDescription(
                    new RTCSessionDescription({
                        type: data.answer.type,
                        sdp: data.answer.sdp
                    })
                );
                console.log("✅ Remote description set");
            } catch (err) {
                console.error("❌ Error setting answer description:", err);
                console.error("❌ Answer structure was:", {
                    type: data.answer?.type,
                    sdp: data.answer?.sdp ? data.answer.sdp.substring(0, 100) : null
                });
                updateStatus("❌ Error setting answer: " + err.message);
                return;
            }

            isRemoteSet = true;

            pendingCandidates.forEach(c => {
                peerConnection.addIceCandidate(new RTCIceCandidate(c)).catch(err => {
                    console.error("❌ Error adding ICE candidate:", err);
                });
            });

            pendingCandidates = [];
        });

        // ✅ RECEIVE ICE
        channel.bind('IceCandidate', async (data) => {

            console.log("❄ ICE RECEIVED from:", data.senderId);

            if (!peerConnection) {
                console.log("⚠ Peer not created yet, buffering candidate");
                pendingCandidates.push(data.candidate);
                return;
            }

            if (!isRemoteSet) {
                console.log("⚠ Remote not set yet, buffering candidate");
                pendingCandidates.push(data.candidate);
            } else {
                try {
                    await peerConnection.addIceCandidate(
                        new RTCIceCandidate(data.candidate)
                    );
                    console.log("✅ ICE candidate added");
                } catch (err) {
                    console.error("❌ Error adding ICE candidate:", err);
                }
            }
        });

        // ✅ END CALL
        function endCall() {

            console.log("❌ CALL ENDED");

            if (connectionTimeout) {
                clearTimeout(connectionTimeout);
            }

            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            const audio = document.getElementById("remoteAudio");
            if (audio) {
                audio.srcObject = null;
                audio.remove();
            }

            callActive = false;
            incomingOffer = null;
            incomingCallerId = null;

            updateStatus("Call ended");

            setTimeout(() => {
                window.close();
            }, 1000);
        }

        window.startCall = startCall;
        window.acceptCall = acceptCall;
        window.endCall = endCall;

        // ✅ CRITICAL FIX: Bind button click handlers AFTER function definition
        console.log("🔗 Binding button click handlers...");
        
        const startBtn = document.getElementById("startBtn");
        const acceptBtn = document.getElementById("acceptBtn");
        const endBtn = document.getElementById("endBtn");
        
        if (startBtn) {
            startBtn.addEventListener("click", function(e) {
                console.log("🖱️ START BUTTON CLICKED - Calling startCall()");
                startCall().catch(err => console.error("❌ startCall error:", err));
            });
            console.log("✅ Start button click handler bound");
        } else {
            console.warn("⚠️ Start button element not found");
        }
        
        if (acceptBtn) {
            acceptBtn.addEventListener("click", function(e) {
                console.log("🖱️ ACCEPT BUTTON CLICKED - Calling acceptCall()");
                acceptCall().catch(err => console.error("❌ acceptCall error:", err));
            });
            console.log("✅ Accept button click handler bound");
        } else {
            console.warn("⚠️ Accept button element not found");
        }
        
        if (endBtn) {
            endBtn.addEventListener("click", function(e) {
                console.log("🖱️ END BUTTON CLICKED - Calling endCall()");
                endCall();
            });
            console.log("✅ End button click handler bound");
        } else {
            console.warn("⚠️ End button element not found");
        }

        // 🧪 SIMPLE TEST: Verify accept button is callable
        window.testAcceptClick = function() {
            console.log("🧪 TEST: Accept button click test fired!");
            return true;
        };

        // ✅ TEST BROADCAST FUNCTION
        window.testBroadcast = async function() {
            console.log("🧪 Testing broadcast system...");
            updateStatus("🧪 Sending test message...");
            
            try {
                const response = await fetch('/api/test-broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Test broadcast failed: ' + response.statusText);
                }
                
                const data = await response.json();
                console.log("🧪 Test broadcast sent:", data);
                updateStatus("🧪 Test message sent - check console for response");
            } catch (err) {
                console.error("❌ Test broadcast error:", err);
                updateStatus("❌ Test broadcast failed: " + err.message);
            }
        };
        });
    </script>

</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">

    <div class="text-center">

        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <h2 id="callTitle" class="mt-4 text-xl font-semibold">
            Calling {{ $user->name }}
        </h2>

        <p id="callStatus" class="text-gray-400 text-sm mt-1">
            Waiting to start...
        </p>

        <div class="flex gap-6 justify-center mt-8">

            <!-- ✅ START CALL -->
            <button id="startBtn" class="bg-green-500 px-6 py-3 rounded-full text-lg">
                📞 Start Call
            </button>

            <!-- END -->
            <button id="endBtn" class="bg-red-500 px-6 py-3 rounded-full text-lg">
                ❌
            </button>

            <!-- ✅ ACCEPT CALL -->
            <button id="acceptBtn" class="bg-green-600 px-6 py-3 rounded-full text-lg">
                ✅ Accept Call
            </button>

            <!-- 🧪 TEST BROADCAST -->
            <button onclick="testBroadcast()" class="bg-blue-500 px-6 py-3 rounded-full text-lg" title="Test if Pusher broadcasting is working">
                🧪 Test
            </button>

        </div>

    </div>

</body>

</html>