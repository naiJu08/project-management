// Audio fix for video call
// Add this to the ontrack handler in attachPeerConnectionListeners function

peerConnection.ontrack = event => {
    console.log("🎥 Remote stream received");
    console.log("🎥 Stream tracks:", event.streams[0].getTracks());
    console.log("🎥 Stream active:", event.streams[0].active);
    const remoteVideo = document.getElementById("remoteVideo");
    
    if (!remoteVideo) {
        console.error("❌ Remote video element not found");
        return;
    }
    
    // ✅ FIX: Proper stream handling without interruption
    // Clear any existing timeout to avoid conflicts
    if (remoteVideo.playTimeout) {
        clearTimeout(remoteVideo.playTimeout);
    }
    
    // Set the stream immediately without pause/play cycle that causes AbortError
    remoteVideo.srcObject = event.streams[0];
    remoteVideo.muted = false; // ✅ CRITICAL: Ensure audio is enabled
    
    // ✅ FIX: Explicitly enable audio tracks
    event.streams[0].getAudioTracks().forEach(track => {
        track.enabled = true;
        console.log("🎵 Remote audio track enabled:", track);
    });
    
    // ✅ FIX: Explicitly enable video tracks
    event.streams[0].getVideoTracks().forEach(track => {
        track.enabled = true;
        console.log("🎥 Remote video track enabled:", track);
    });
    
    // Use a single play attempt with proper error handling
    remoteVideo.playTimeout = setTimeout(() => {
        const playPromise = remoteVideo.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                console.log("✅ Remote video playing successfully");
                // ✅ FIX: Check if audio is actually playing
                const audioTracks = event.streams[0].getAudioTracks();
                console.log("🎵 Audio tracks active:", audioTracks.length, audioTracks.map(t => ({enabled: t.enabled, muted: t.muted})));
            }).catch(error => {
                if (error.name === 'AbortError') {
                    console.warn("⚠️ Video play was aborted, this is usually harmless");
                } else if (error.name === 'NotAllowedError') {
                    console.warn("⚠️ Autoplay prevented, user interaction required");
                } else {
                    console.error("❌ Remote video play error:", error);
                }
            });
        }
    }, 100);
};
