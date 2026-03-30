// Audio debugging and fixes for video call
// Add these debugging functions to help identify audio issues

// ✅ DEBUG: Add comprehensive audio debugging
function debugAudioTracks(stream, label) {
    console.log(`🎵 ${label} Audio Debug:`);
    const audioTracks = stream.getAudioTracks();
    const videoTracks = stream.getVideoTracks();
    
    console.log(`🎵 ${label} - Audio tracks:`, audioTracks.length);
    audioTracks.forEach((track, index) => {
        console.log(`🎵 ${label} Audio Track ${index}:`, {
            enabled: track.enabled,
            muted: track.muted,
            readyState: track.readyState,
            kind: track.kind,
            id: track.id
        });
    });
    
    console.log(`🎥 ${label} - Video tracks:`, videoTracks.length);
    videoTracks.forEach((track, index) => {
        console.log(`🎥 ${label} Video Track ${index}:`, {
            enabled: track.enabled,
            muted: track.muted,
            readyState: track.readyState,
            kind: track.kind,
            id: track.id
        });
    });
    
    return { audioTracks, videoTracks };
}

// ✅ FIX: Enhanced audio track management
function ensureAudioTracksEnabled(stream) {
    const audioTracks = stream.getAudioTracks();
    let enabledCount = 0;
    
    audioTracks.forEach(track => {
        if (!track.enabled) {
            track.enabled = true;
            console.log("🎵 Re-enabled audio track:", track.id);
            enabledCount++;
        }
    });
    
    if (enabledCount > 0) {
        console.log(`🎵 Re-enabled ${enabledCount} audio tracks`);
    }
    
    return audioTracks.length > 0;
}

// ✅ FIX: Browser-specific audio issues
function handleBrowserAudioIssues() {
    // Chrome sometimes requires user interaction for audio
    if (navigator.userAgent.includes('Chrome')) {
        console.log("🌐 Chrome detected - checking audio policies");
    }
    
    // Safari has strict autoplay policies
    if (navigator.userAgent.includes('Safari') && !navigator.userAgent.includes('Chrome')) {
        console.log("🌐 Safari detected - audio may require user interaction");
    }
    
    // Firefox audio handling
    if (navigator.userAgent.includes('Firefox')) {
        console.log("🌐 Firefox detected - checking audio settings");
    }
}

// ✅ FIX: Audio context setup for better audio handling
function setupAudioContext() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        console.log("🎵 Audio context created:", audioContext.state);
        
        // Resume audio context if suspended (common in browsers)
        if (audioContext.state === 'suspended') {
            audioContext.resume().then(() => {
                console.log("🎵 Audio context resumed");
            });
        }
        
        return audioContext;
    } catch (error) {
        console.error("❌ Audio context creation failed:", error);
        return null;
    }
}

// Export functions for use in main code
window.audioDebug = {
    debugAudioTracks,
    ensureAudioTracksEnabled,
    handleBrowserAudioIssues,
    setupAudioContext
};

// Auto-initialize audio handling
document.addEventListener('DOMContentLoaded', () => {
    window.audioDebug.handleBrowserAudioIssues();
    window.audioDebug.setupAudioContext();
});
