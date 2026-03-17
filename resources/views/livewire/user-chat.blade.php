@php use Illuminate\Support\Str; @endphp

<div class="flex flex-col overflow-hidden" style="height:800px;">

<h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
User Chat
</h1>

<div class="flex flex-1 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 h-full">

<!-- USERS LIST -->
<div wire:poll.5s class="w-72 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">

<!-- ⭐ NEW : USER SEARCH -->
<div class="p-3 border-b border-gray-200 dark:border-gray-700">
<input
type="text"
wire:model.debounce.300ms="search"
placeholder="Search user..."
class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm"
/>
</div>

@foreach($this->users as $user)

<div wire:click="selectUser({{ $user->id }})"
class="flex items-center gap-3 p-4 cursor-pointer
hover:bg-gray-100 dark:hover:bg-gray-800
@if($selectedUser == $user->id) bg-gray-100 dark:bg-gray-800 @endif">

<div class="relative">
<div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold">
{{ strtoupper(substr($user->name, 0, 1)) }}
</div>

<!-- ⭐ NEW : ONLINE STATUS -->
@php
$isOnline = $user->last_seen && $user->last_seen->gt(now()->subMinutes(2));
@endphp

<span class="absolute bottom-0 right-0 w-3 h-3 
{{ $isOnline ? 'bg-green-500' : 'bg-gray-400' }} 
border-2 border-white rounded-full"></span>
</div>

<div class="flex-1">
<div class="font-semibold text-sm text-gray-800 dark:text-gray-200">
{{ $user->name }}
</div>

<div class="text-xs text-gray-500 dark:text-gray-400 truncate">
@php
$lastMsg = \App\Models\DirectMessage::where(function($q) use ($user){
        $q->where('sender_id', auth()->id())
          ->where('receiver_id', $user->id);
    })->orWhere(function($q) use ($user){
        $q->where('sender_id', $user->id)
          ->where('receiver_id', auth()->id());
    })
    ->latest()
    ->first();
@endphp

@if($lastMsg)

    @if($lastMsg->file)

        @if(Str::contains($lastMsg->file,['jpg','jpeg','png','gif','webp']))
            📷 Photo
        @else
            📎 File
        @endif

    @else
        {{ Str::limit($lastMsg->message,35) }}
    @endif

@else
    Start Chatting
@endif
</div>

</div>

@php
$unreadCount = \App\Models\DirectMessage::where('sender_id', $user->id)
    ->where('receiver_id', auth()->id())
    ->whereNull('read_at')
    ->count();
@endphp

@if($unreadCount > 0)
<span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full min-w-[20px] text-center">
{{ $unreadCount }}
</span>
@endif

</div>

@endforeach

</div>



<!-- CHAT AREA -->
<div class="flex flex-col flex-1 min-h-0 h-full">

@if($selectedUser)

<!-- HEADER -->
<div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">

<div class="flex items-center gap-3">

<div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold">
{{ strtoupper(substr($this->selectedUserModel->name, 0, 1)) }}
</div>

<div>

<div class="font-semibold text-gray-800 dark:text-gray-200">
{{ $this->selectedUserModel->name }}
</div>

<!-- ⭐ NEW : ONLINE TEXT 
<div class="text-xs text-green-500">
Online
</div>-->

</div>

</div>

<!-- ⭐ NEW : HEADER ACTIONS -->
<div class="flex gap-3 text-gray-400">

<button onclick="startCall({{ $selectedUserModel->id }})" class="hover:text-primary-500">
📞
</button>

<button class="hover:text-primary-500">
🎥
</button>

<button class="hover:text-primary-500">
⋮
</button>

</div>

</div>

@endif



<!-- MESSAGES -->
<div id="chatMessages"
     wire:poll.10s.keep-alive
     wire:key="chatMessages"
     class="flex-1 overflow-y-auto px-6 pt-6 space-y-4 min-h-0">

@forelse($chatMessages as $index => $msg)

@php
$showAvatar = $index === 0 || $chatMessages[$index-1]->sender_id !== $msg->sender_id;
@endphp

@if($msg->sender_id == auth()->id())

<!-- MY MESSAGE -->
<div class="flex justify-end">
<div class="max-w-md">

<div class="relative bg-primary-600 chat-bubble-right text-white px-4 py-2 rounded-xl shadow-sm group break-words inline-block">
   <!-- HOVER TOOLBAR -->
<div class="absolute -right-10 top-3
opacity-0 group-hover:opacity-100
transition duration-200">
<div class="flex items-center gap-1 bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-600
rounded-full shadow px-2 py-[3px] text-[11px]">

<button
class="text-gray-500 hover:text-blue-500"
wire:click="editMessage({{ $msg->id }})">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-4 h-4"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M11 5h2M4 20h4l10-10a2 2 0 10-4-4L4 16v4z"/>

</svg>

</button>


<button
class="text-gray-500 hover:text-red-500"
wire:click="deleteMessage({{ $msg->id }})"
onclick="return confirm('Delete this message?')">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-4 h-4"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3"/>

</svg>

</button>

</div>

</div>

    {{-- FILE BOARD --}}
    @if($msg->file)
        <div class="mb-2 max-w-[140px] rounded-lg overflow-hidden border border-white/20 cursor-pointer transform hover:scale-105 transition duration-200">
            
                @if(Str::contains($msg->file,['jpg','jpeg','png','gif','webp']))
                <img src="{{ asset('storage/'.$msg->file) }}" 
                    class="w-full h-auto object-cover">
            @else
                <a href="{{ asset('storage/'.$msg->file) }}"
                   target="_blank"
                   class="text-xs underline text-blue-200">
                   📎 {{ basename($msg->file) }}
                </a>
            @endif

        </div>
    @endif

    {{-- TEXT BOARD --}}
@if($msg->message)

@if($editingMessageId === $msg->id)

<div class="flex items-center gap-2">

<input type="text"
wire:model.defer="editingText"
class="px-2 py-1 rounded border border-gray-400 w-full text-sm
text-gray-900
focus:outline-none focus:ring-2 focus:ring-primary-400
dark:bg-gray-800 dark:text-white dark:border-gray-600">

<button wire:click="updateMessage"
class="text-xs bg-green-500 text-white px-2 py-1 rounded">
Save
</button>

</div>

@else

<div class="!text-base break-words">
{{ $msg->message }}
</div>

@endif

@endif

</div>

<div class="text-xs text-gray-400 mt-1 text-right flex items-center justify-end gap-1">

<span>
{{ $msg->created_at->timezone(config('app.timezone'))->format('h:i A') }}
</span>

@if($msg->read_at)
<span class="text-blue-500">✔✔</span>
@else
<span class="text-gray-400">✔</span>
@endif

</div>

</div>
</div>

@else

<!-- OTHER USER MESSAGE -->
<div class="flex items-center gap-3 justify-start">

<!-- AVATAR -->
<div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
{{ strtoupper(substr($this->selectedUserModel->name, 0, 1)) }}
</div>

<div class="max-w-md relative ">

<div class="bg-gray-200 chat-bubble-left dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 rounded-xl shadow-sm space-y-1">

@if($msg->file)

@if(Str::contains($msg->file,['jpg','jpeg','png','gif','webp']))
<img src="{{ asset('storage/'.$msg->file) }}"
     class="rounded-lg max-w-[250px] cursor-pointer transform hover:scale-100 transition duration-200">
@else
<a href="{{ asset('storage/'.$msg->file) }}"
   target="_blank"
   class="text-blue-500 underline text-xs">
   📎 {{ basename($msg->file) }}
</a>
@endif

@endif

@if($msg->message)
<div class="!text-base break-words">
{{ $msg->message }}
</div>
@endif

</div>

<div class="text-xs text-gray-400 mt-1">
{{ $msg->created_at->timezone(config('app.timezone'))->format('h:i A') }}
</div>

</div>
</div>

@endif

@empty

<div class="flex items-center justify-center h-full">
<div class="text-center text-gray-400">

<svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
</svg>

<p class="mt-2 text-sm">No messages yet</p>

</div>
</div>

@endforelse
</div>

@php
$isOnline = $this->selectedUserModel 
    && $this->selectedUserModel->last_seen 
    && $this->selectedUserModel->last_seen->gt(now()->subMinutes(2));
@endphp

@if($message && $isOnline)
<div class="flex items-center gap-2 px-6 pb-2 text-sm text-gray-500 animate-pulse">

<div class="flex gap-1">
<span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
<span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-100"></span>
<span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-200"></span>
</div>

<span>User is typing...</span>

</div>
@endif

@if($selectedUser)

<!-- MESSAGE INPUT -->
<div class="border-t border-gray-200 dark:border-gray-700 px-6 pt-6 pb-4 flex-shrink-0 sticky bottom-0 bg-white dark:bg-gray-900">
@if ($files)

@foreach($files as $index => $file)

<div class="mb-3 flex items-center gap-3 p-2 bg-gray-100 dark:bg-gray-800 rounded-lg w-fit">

@if(str_contains($file->getMimeType(), 'image'))
<img src="{{ $file->temporaryUrl() }}" class="w-16 h-16 rounded object-cover cursor-pointer">
@else
<div class="w-16 h-16 flex items-center justify-center bg-gray-300 rounded">
📄
</div>
@endif

<div class="text-sm">
{{ $file->getClientOriginalName() }}
</div>

<button wire:click="$set('files', [])"
class="text-red-500 hover:text-red-700 text-lg">
✕
</button>

</div>

@endforeach

@endif


<form wire:submit.prevent="sendMessage" class="flex items-center gap-3 w-full">

<!-- FILE BUTTON -->
<label class="cursor-pointer px-3 py-2 bg-gray-200 rounded-lg flex-shrink-0">
📎
<input type="file" wire:model="files" multiple class="hidden">
</label>

<!-- MESSAGE INPUT -->
<textarea
id="chatInput"
wire:model.defer="message"
rows="1"
class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white
focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 resize-none"
placeholder="Type your message..."
@keydown="
if(event.key === 'Enter' && !event.shiftKey){
    event.preventDefault();

    let text = $event.target.value.trim();

    if(text.length === 0){
        return;
    }

    $wire.sendMessage();

    $event.target.value = '';
}
"
></textarea>

<!-- SEND BUTTON -->
<button type="submit"
class="px-5 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700
focus:outline-none focus:ring-2 focus:ring-primary-500 flex-shrink-0">

<svg class="w-5 h-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
</svg>

</button>

</form>

<p class="text-xs text-gray-400 mt-2">
Press Enter to send, Shift+Enter for new line
</p>

</div>

@endif

</div>

</div>

</div>

<script>

document.addEventListener("click", function(e){

    // OPEN IMAGE PREVIEW
    if(e.target.tagName === "IMG" && e.target.closest("#chatMessages")){
        const modal = document.getElementById("imagePreviewModal");
        const preview = document.getElementById("previewImage");

        preview.src = e.target.src;
        modal.style.display = "flex";
    }

    // CLOSE BUTTON CLICK
    if(e.target.id === "closePreview"){
        document.getElementById("imagePreviewModal").style.display = "none";
    }

    // CLICK OUTSIDE IMAGE CLOSE
    if(e.target.id === "imagePreviewModal"){
        document.getElementById("imagePreviewModal").style.display = "none";
    }

});

</script>

<div id="imagePreviewModal"
style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9);
align-items:center; justify-content:center; z-index:9999;">

<button id="closePreview"
style="position:absolute; top:20px; right:30px; font-size:28px; color:white; background:none; border:none; cursor:pointer;">
✕
</button>

<img id="previewImage"
style="max-width:90%; max-height:90%; border-radius:10px;">

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const chat = document.getElementById("chatMessages");

    if (!chat) return;

    function scrollToBottom(){
        chat.scrollTop = chat.scrollHeight;
    }

    // Scroll on initial load
    setTimeout(scrollToBottom, 300);

    // Watch for new messages added by Livewire
    const observer = new MutationObserver(function () {
        scrollToBottom();
    });

    observer.observe(chat, {
        childList: true,
        subtree: true
    });

});

</script>

<script>
function toggleMenu(id){

    document.querySelectorAll('[id^="menu-"]').forEach(menu=>{
        if(menu.id !== id){
            menu.classList.add('hidden');
        }
    });

    const menu = document.getElementById(id);
    menu.classList.toggle('hidden');
}

document.addEventListener("click", function(e){

    if(!e.target.closest('[onclick^="toggleMenu"]')){
        document.querySelectorAll('[id^="menu-"]').forEach(menu=>{
            menu.classList.add('hidden');
        });
    }

});
</script>

<script>

document.addEventListener('livewire:load', function () {

let localStream;
let peerConnection;
let currentUserId = null;


// START CALL
window.startCall = async function(userId){

    currentUserId = userId;

    window.open(
        "/voice-call/" + userId,
        "VoiceCallWindow",
        "width=420,height=650"
    );

    try{

        localStream = await navigator.mediaDevices.getUserMedia({ audio:true });

        peerConnection = new RTCPeerConnection({
            iceServers: [
                { urls: "stun:stun.l.google.com:19302" }
            ]
        });

        peerConnection.onicecandidate = (event)=>{

            if(event.candidate){
                Livewire.emit('sendIceCandidate', event.candidate, currentUserId);
            }

        };

        localStream.getTracks().forEach(track=>{
            peerConnection.addTrack(track, localStream);
        });

        setupAudio();

        const offer = await peerConnection.createOffer();

        await peerConnection.setLocalDescription(offer);

        Livewire.emit(
            'sendCallOffer',
            offer,
            userId,
            {{ auth()->id() }},
            "{{ auth()->user()->name }}"
        );

    }catch(err){

        alert("Microphone permission required");

    }

}


// ACCEPT CALL
async function acceptCall(offer){

    peerConnection = new RTCPeerConnection({
        iceServers: [
            { urls: "stun:stun.l.google.com:19302" }
        ]
    });

    peerConnection.onicecandidate = (event)=>{

        if(event.candidate){
            Livewire.emit('sendIceCandidate', event.candidate, currentUserId);
        }

    };

    localStream = await navigator.mediaDevices.getUserMedia({ audio:true });

    localStream.getTracks().forEach(track=>{
        peerConnection.addTrack(track, localStream);
    });

    setupAudio();

    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

    const answer = await peerConnection.createAnswer();

    await peerConnection.setLocalDescription(answer);

    Livewire.emit('sendCallAnswer', answer);

}


// END CALL
window.endCall = function(){

    if(peerConnection){
        peerConnection.close();
    }

    if(localStream){
        localStream.getTracks().forEach(track=>track.stop());
    }

    alert("Call ended");

}


// RECEIVE AUDIO
function setupAudio(){

    if(!peerConnection) return;

    peerConnection.ontrack = function(event){

        let audio = document.createElement("audio");

        audio.srcObject = event.streams[0];

        audio.autoplay = true;

        document.body.appendChild(audio);

    }

}


// RECEIVE OFFER (Incoming Call)
Livewire.on('receiveCallOffer', async (offer, callerId, callerName)=>{

    console.log("Incoming call from:", callerName);

    currentUserId = callerId;

    const accept = confirm("Incoming voice call from " + callerName);

    if(!accept) return;

    window.open(
        "/voice-call/" + callerId,
        "VoiceCallWindow",
        "width=420,height=650"
    );

    setTimeout(async ()=>{
        await acceptCall(offer);
    },500);

});


// RECEIVE ANSWER
Livewire.on('receiveCallAnswer', async (answer)=>{

    if(!peerConnection) return;

    await peerConnection.setRemoteDescription(
        new RTCSessionDescription(answer)
    );

});


// RECEIVE ICE
Livewire.on('receiveIceCandidate', async (candidate)=>{

    if(peerConnection){
        await peerConnection.addIceCandidate(
            new RTCIceCandidate(candidate)
        );
    }

});

});
</script>

<style>
.chat-bubble-right{
    position:relative;
}

.chat-bubble-right:after{
    display:none;
}

.chat-bubble-left{
    position:relative;
}

.chat-bubble-left:after{
    content:"";
    position:absolute;
    left:-6px;
    top:10px;
    border-width:6px;
    border-style:solid;
    border-color:transparent #e5e7eb transparent transparent;
}
</style>