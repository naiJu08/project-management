<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Voice Call</title>

<script src="https://cdn.tailwindcss.com"></script>


<!-- Pusher -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<!-- Echo -->
<script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>

<script>
window.Pusher = Pusher;

const EchoInstance = new Echo.default({
    broadcaster: 'pusher',
    key: 'local',
    cluster: 'mt1',   
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true
});

window.Echo = EchoInstance;
</script>

</head>

<body class="bg-gray-900 text-white flex items-center justify-center h-screen">

<div class="text-center">

<!-- USER AVATAR -->
<div class="w-24 h-24 rounded-full bg-primary-600 flex items-center justify-center text-3xl font-bold mx-auto">
{{ strtoupper(substr($user->name,0,1)) }}
</div>

<h2 class="mt-4 text-xl font-semibold">
Calling {{ $user->name }}
</h2>

<p class="text-gray-400 text-sm mt-1">
Connecting...
</p>

<!-- CALL BUTTONS -->
<div class="flex gap-6 justify-center mt-8">

<button onclick="startVoice()"

class="bg-green-500 hover:bg-green-600 px-6 py-3 rounded-full text-lg">
🎤
</button>

<button onclick="endCall()"
class="bg-red-500 hover:bg-red-600 px-6 py-3 rounded-full text-lg">
❌
</button>

</div>

</div>

<script>

let localStream;
let peerConnection;

// START CALL
async function startVoice(){

    try{

        localStream = await navigator.mediaDevices.getUserMedia({ audio:true });

        peerConnection = new RTCPeerConnection({
            iceServers: [
                { urls: "stun:stun.l.google.com:19302" }
            ]
        });

        peerConnection.onicecandidate = event => {
            if(event.candidate){
                console.log("ICE candidate:", event.candidate);
            }
        };

        peerConnection.ontrack = function(event){

            let audio = document.createElement("audio");
            audio.srcObject = event.streams[0];
            audio.autoplay = true;

            document.body.appendChild(audio);

        };

        localStream.getTracks().forEach(track=>{
            peerConnection.addTrack(track, localStream);
        });

        const offer = await peerConnection.createOffer();

        await peerConnection.setLocalDescription(offer);


        // SUBSCRIBE TO CHANNEL
        window.Echo.channel('voice-call')

        .subscribed(() => {
            console.log("Connected to voice-call channel");
        })

        // RECEIVE OFFER
        .listen('.CallOffer', async (data) => {

            console.log("Offer received", data);

            await peerConnection.setRemoteDescription(data.offer);

            const answer = await peerConnection.createAnswer();

            await peerConnection.setLocalDescription(answer);

            fetch('/send-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    answer: answer
                })
            });

        })

        // RECEIVE ANSWER
        .listen('.CallAnswer', async (data) => {

            console.log("Answer received", data);

            await peerConnection.setRemoteDescription(data.answer);

        });


        // SEND OFFER
        fetch('/send-offer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                offer: offer
            })
        });

        console.log("Offer created", offer);

        alert("Microphone connected 🎤");

    }catch(err){

        alert("Microphone permission required");

    }

}


// END CALL
function endCall(){

    if(peerConnection){
        peerConnection.close();
    }

    if(localStream){
        localStream.getTracks().forEach(track=>track.stop());
    }

    window.close();

}

</script>

</body>
</html>