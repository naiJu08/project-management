// Local audio fix for video call
// Replace the local video setup in both startVideoCall and handleChatVideoOffer functions

// ✅ FIX: Proper local video and audio setup without AbortError
const localVideo = document.getElementById("localVideo");
if (localVideo) {
    // Clear any existing timeout
    if (localVideo.playTimeout) {
        clearTimeout(localVideo.playTimeout);
    }
    
    // Set stream immediately
    localVideo.srcObject = localStream;
    localVideo.muted = true; // Mute local to avoid echo
    
    // ✅ FIX: Explicitly enable local audio tracks for sending
    localStream.getAudioTracks().forEach(track => {
        track.enabled = true;
        console.log("🎵 Local audio track enabled for sending:", track);
    });
    
    // ✅ FIX: Explicitly enable local video tracks
    localStream.getVideoTracks().forEach(track => {
        track.enabled = true;
        console.log("🎥 Local video track enabled:", track);
    });
    
    // Single play attempt with error handling
    localVideo.playTimeout = setTimeout(() => {
        const playPromise = localVideo.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                console.log("✅ Local video playing successfully");
                console.log("🎵 Local audio tracks ready:", localStream.getAudioTracks().length);
            }).catch(error => {
                if (error.name === 'AbortError') {
                    console.warn("⚠️ Local video play was aborted, this is usually harmless");
                } else {
                    console.error("❌ Local video play error:", error);
                }
            });
        }
    }, 100);
}
