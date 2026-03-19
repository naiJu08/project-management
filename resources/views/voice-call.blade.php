<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Call</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("✅ JS LOADED");

            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};

            let localStream;
            let peerConnection;
            let incomingOffer = null;
            let incomingCallerId = null;

            let pendingCandidates = [];
            let isRemoteSet = false;
            let callActive = false;
            // ✅ PUSHER INIT
            const pusher = new Pusher("0c08d7f3f0fa0c883f22", {
                cluster: "ap2",
                forceTLS: true
            });

            const channel = pusher.subscribe('voice-call.' + userId);

            // ✅ CREATE PEER
            function createPeer() {

                console.log("🧠 Creating Peer");

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
                // iceServers: [
                //   { urls: "stun:stun.l.google.com:19302" },
                // {
                //     urls: "turn:pm.inovace.in:3478",
                //     username: "webrtcuser",
                //     credential: "strongpassword123"
                // }
                //    ]
                // });

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

                document.getElementById("startBtn").classList.add("hidden");
                document.getElementById("acceptBtn").classList.add("hidden");

                console.log("🚀 START BUTTON CLICKED");

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (err) {
                    alert("Microphone permission blocked");
                    callActive = false;
                    return;
                }

                createPeer();

                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);

                try {
                    const response = await fetch('/send-offer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            offer: offer,
                            receiverId: otherUserId
                        })
                    });
                    if (!response.ok) {
                        throw new Error('Failed to send offer: ' + response.statusText);
                    }
                } catch (err) {
                    console.error('❌ Error sending offer:', err);
                    callActive = false;
                    document.getElementById("startBtn").classList.remove("hidden");
                    throw err;
                }
            }

            async function acceptCall() {

                if (callActive) return;
                callActive = true;

                document.getElementById("startBtn").classList.add("hidden");
                document.getElementById("acceptBtn").classList.add("hidden");

                console.log("✅ ACCEPT CLICKED");

                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (err) {
                    alert("Mic blocked");
                    callActive = false;
                    return;
                }

                createPeer();

                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                try {
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription(incomingOffer)
                    );
                } catch (err) {
                    console.error("❌ Error setting remote description:", err);
                    callActive = false;
                    document.getElementById("acceptBtn").classList.remove("hidden");
                    return;
                }

                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);

                isRemoteSet = true;

                pendingCandidates.forEach(c => {
                    peerConnection.addIceCandidate(new RTCIceCandidate(c)).catch(err => {
                        console.error("❌ Error adding ICE candidate:", err);
                    });
                });

                pendingCandidates = [];

                try {
                    const response = await fetch('/send-answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            answer: answer,
                            receiverId: incomingCallerId
                        })
                    });
                    if (!response.ok) {
                        throw new Error('Failed to send answer: ' + response.statusText);
                    }
                } catch (err) {
                    console.error('❌ Error sending answer:', err);
                    callActive = false;
                    document.getElementById("acceptBtn").classList.remove("hidden");
                    throw err;
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

            channel.bind('.CallOffer', (data) => {

                console.log("📞 OFFER RECEIVED");

                incomingOffer = data.offer;
                incomingCallerId = data.callerId;

                document.getElementById("startBtn").classList.add("hidden");
                document.getElementById("acceptBtn").classList.remove("hidden");
            });

            // ✅ RECEIVE ANSWER
            channel.bind('.CallAnswer', async (data) => {

                console.log("✅ ANSWER RECEIVED");

                if (!peerConnection) {
                    console.log("⚠ Peer not ready");
                    return;
                }

                try {
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription(data.answer)
                    );
                } catch (err) {
                    console.error("❌ Error setting answer description:", err);
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
            channel.bind('.IceCandidate', async (data) => {

                console.log("❄ ICE RECEIVED");

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
                    } catch (err) {
                        console.error("❌ Error adding ICE candidate:", err);
                    }
                }
            });

            // ✅ END CALL
            function endCall() {

                console.log("❌ CALL ENDED");

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

                window.close();
            }

            window.startCall = startCall;
            window.acceptCall = acceptCall;
            window.endCall = endCall;
        });
    </script>

</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">

    <div class="text-center">

        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <h2 class="mt-4 text-xl font-semibold">
            Calling {{ $user->name }}
        </h2>

        <p class="text-gray-400 text-sm mt-1">
            Connecting...
        </p>

        <div class="flex gap-6 justify-center mt-8">

            <!-- ✅ START CALL -->
            <button id="startBtn" onclick="startCall()" class="bg-green-500 px-6 py-3 rounded-full text-lg">
                📞 Start Call
            </button>

            <!-- END -->
            <button onclick="endCall()" class="bg-red-500 px-6 py-3 rounded-full text-lg">
                ❌
            </button>

            <!-- ✅ ACCEPT CALL -->
            <button id="acceptBtn" onclick="acceptCall()" class="hidden bg-green-600 px-6 py-3 rounded-full text-lg">
                ✅ Accept Call
            </button>

        </div>

    </div>

</body>

</html>