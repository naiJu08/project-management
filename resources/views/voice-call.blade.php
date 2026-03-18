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
console.log("✅ JS LOADED");

const userId = {{ auth()->id() }};
const otherUserId = {{ $user->id }};

let localStream;
let peerConnection;

// ✅ PUSHER INIT
const pusher = new Pusher("0c08d7f3f0fa0c883f22", {
    cluster: "ap2",
    forceTLS: true
});

const channel = pusher.subscribe('voice-call.' + userId);

// ✅ CREATE PEER
function createPeer() {

    console.log("🧠 Creating Peer");

    peerConnection = new RTCPeerConnection({
        iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
    });

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
                    receiverId: otherUserId
                })
            });
        }
    };

    peerConnection.ontrack = (event) => {

        console.log("🔊 AUDIO RECEIVED");

        const audio = document.createElement("audio");
        audio.srcObject = event.streams[0];
        audio.autoplay = true;

        document.body.appendChild(audio);
    };
}

// ✅ START CALL (CALLER)
async function startCall() {

    console.log("🚀 START BUTTON CLICKED");

    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (err) {
        console.error("MIC ERROR:", err);
        alert("Microphone permission blocked");
        return;
    }

    createPeer();

    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });

    const offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);

    console.log("📤 SENDING OFFER");

    await fetch('/send-offer', {
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

    console.log("✅ OFFER SENT");
}

// ✅ RECEIVE OFFER (RECEIVER)
channel.bind('.CallOffer', async (data) => {

    console.log("📞 OFFER RECEIVED");

    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (err) {
        alert("Mic blocked");
        return;
    }

    createPeer();

    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });

    await peerConnection.setRemoteDescription(
        new RTCSessionDescription(data.offer)
    );

    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);

    console.log("📤 SENDING ANSWER");

    fetch('/send-answer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            answer: answer,
            receiverId: data.callerId
        })
    });
});

// ✅ RECEIVE ANSWER
channel.bind('.CallAnswer', async (data) => {

    console.log("✅ ANSWER RECEIVED");

    await peerConnection.setRemoteDescription(
        new RTCSessionDescription(data.answer)
    );
});

// ✅ RECEIVE ICE
channel.bind('.IceCandidate', async (data) => {

    console.log("❄ ICE RECEIVED");

    if (peerConnection) {
        await peerConnection.addIceCandidate(
            new RTCIceCandidate(data.candidate)
        );
    }
});

// ✅ END CALL
function endCall(){
    window.close();
}
</script>

</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">

<div class="text-center">

    <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
        {{ strtoupper(substr($user->name,0,1)) }}
    </div>

    <h2 class="mt-4 text-xl font-semibold">
        Calling {{ $user->name }}
    </h2>

    <p class="text-gray-400 text-sm mt-1">
        Connecting...
    </p>

    <div class="flex gap-6 justify-center mt-8">

        <!-- ✅ START CALL -->
        <button onclick="startCall()" class="bg-green-500 px-6 py-3 rounded-full text-lg">
            📞 Start Call
        </button>

        <!-- END -->
        <button onclick="endCall()" class="bg-red-500 px-6 py-3 rounded-full text-lg">
            ❌
        </button>

    </div>

</div>

</body>
</html>