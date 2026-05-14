<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Call - {{ $user->name }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <style>
        /* Your existing styles (unchanged) */
        #startBtn {
            display: block;
        }

        #acceptBtn {
            display: none;
        }

        #endBtn {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #debugPanel {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: #0f0;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            max-width: 400px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
        }

        .debug-visible #debugPanel {
            display: block;
        }

        .audioPanel {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .localPanel {
            right: auto;
            left: 10px;
            bottom: 50px;
        }

        .volumeMeter {
            width: 50px;
            height: 4px;
            background: #333;
            border-radius: 2px;
            overflow: hidden;
        }

        .volumeLevel {
            width: 0%;
            height: 100%;
            transition: width 0.1s;
        }

        .remotePanel .volumeLevel {
            background: #0f0;
        }

        .localPanel .volumeLevel {
            background: #ff9800;
        }

        .muteIcon {
            font-size: 16px;
        }

        .actionButtons {
            position: fixed;
            bottom: 10px;
            left: 10px;
            display: flex;
            gap: 8px;
            z-index: 10000;
        }

        .actionButtons button {
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            color: white;
        }

        #remoteAudio {
            position: fixed;
            bottom: 50px;
            right: 10px;
            width: 200px;
            height: 40px;
            z-index: 10000;
            background: #222;
            border-radius: 5px;
        }

        #audioMessage {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #ff9800;
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10001;
            display: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #reconnectBtn {
            display: none;
            background: #f97316;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 35%),
                linear-gradient(180deg, #111b21 0%, #0b141a 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body > div.text-center {
            width: 100%;
            max-width: 430px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 28px 18px 140px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background: linear-gradient(180deg, rgba(17, 27, 33, 0.92) 0%, rgba(11, 20, 26, 0.98) 100%);
        }

        body > div.text-center::before,
        body > div.text-center::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        body > div.text-center::before {
            width: 180px;
            height: 180px;
            background: rgba(59, 130, 246, 0.14);
            top: -40px;
            right: -50px;
            filter: blur(10px);
        }

        body > div.text-center::after {
            width: 220px;
            height: 220px;
            background: rgba(18, 140, 126, 0.1);
            bottom: 120px;
            left: -80px;
            filter: blur(12px);
        }

        body > div.text-center > div:first-child {
            width: 132px;
            height: 132px;
            border-radius: 999px;
            margin-top: 84px;
            margin-bottom: 22px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.92), rgba(29, 78, 216, 0.96));
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ecfdf5;
            font-size: 46px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35), inset 0 0 0 6px #16232c;
        }

        body > div.text-center > div:first-child::before {
            content: "";
            position: absolute;
            top: 26px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #d1f4cc;
        }

        body > div.text-center > div:first-child::after {
            content: "Secure audio";
            position: absolute;
            top: 24px;
            right: 18px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            font-size: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }

        #callTitle {
            margin-top: 0;
            font-size: 30px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #f8fafc;
        }

        #callStatus {
            margin-top: 12px;
            color: #94a3b8;
            font-size: 15px;
            min-height: 24px;
        }

        .callTimer {
            margin: 8px 0 0;
            min-height: 22px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: #93c5fd;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .callTimer.visible {
            opacity: 1;
            transform: translateY(0);
        }

        #callStatus::after {
            content: "";
            display: flex;
            width: 84px;
            max-width: 84px;
            height: 52px;
            margin: 34px auto 0;
            background:
                linear-gradient(180deg, transparent 32px, rgba(59, 130, 246, 0.88) 32px, rgba(59, 130, 246, 0.88) 52px) 0 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 14px, rgba(59, 130, 246, 0.88) 14px, rgba(59, 130, 246, 0.88) 52px) 19px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 26px, rgba(59, 130, 246, 0.88) 26px, rgba(59, 130, 246, 0.88) 52px) 38px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 8px, rgba(59, 130, 246, 0.88) 8px, rgba(59, 130, 246, 0.88) 52px) 57px 0/8px 52px no-repeat,
                linear-gradient(180deg, transparent 30px, rgba(59, 130, 246, 0.88) 30px, rgba(59, 130, 246, 0.88) 52px) 76px 0/8px 52px no-repeat;
            opacity: 0.9;
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.28));
            transform-origin: center bottom;
            animation: blueWaveBars 1.35s ease-in-out infinite;
        }

        @keyframes blueWaveBars {
            0% {
                background-size: 8px 52px, 8px 52px, 8px 52px, 8px 52px, 8px 52px;
                transform: translateY(0) scaleY(0.88);
                opacity: 0.72;
            }

            20% {
                background-size: 8px 38px, 8px 52px, 8px 30px, 8px 52px, 8px 40px;
                transform: translateY(-1px) scaleY(1.02);
                opacity: 0.92;
            }

            40% {
                background-size: 8px 26px, 8px 44px, 8px 52px, 8px 34px, 8px 24px;
                transform: translateY(0) scaleY(0.96);
                opacity: 1;
            }

            60% {
                background-size: 8px 42px, 8px 24px, 8px 36px, 8px 52px, 8px 48px;
                transform: translateY(-2px) scaleY(1.06);
                opacity: 0.94;
            }

            80% {
                background-size: 8px 30px, 8px 50px, 8px 22px, 8px 40px, 8px 34px;
                transform: translateY(-1px) scaleY(0.98);
                opacity: 0.88;
            }

            100% {
                background-size: 8px 52px, 8px 52px, 8px 52px, 8px 52px, 8px 52px;
                transform: translateY(0) scaleY(0.88);
                opacity: 0.72;
            }
        }

        body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap {
            position: fixed;
            left: 50%;
            bottom: 26px;
            transform: translateX(-50%);
            z-index: 10002;
            width: min(390px, calc(100% - 28px));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border-radius: 28px;
            background: rgba(17, 27, 33, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(14px);
        }

        body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap button {
            min-width: 86px;
            min-height: 58px;
            border: 0;
            border-radius: 22px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, opacity 0.2s ease, background 0.2s ease;
        }

        body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap button:hover {
            transform: translateY(-1px);
        }

        #startBtn { background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%) !important; }
        #acceptBtn { background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%) !important; animation-duration: 1.8s; }
        #muteBtn { background: linear-gradient(180deg, #334155 0%, #1e293b 100%) !important; }
        #muteBtn.muted { background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%) !important; }
        #endBtn { background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%) !important; }
        #reconnectBtn { background: linear-gradient(180deg, #fb923c 0%, #f97316 100%) !important; }
        body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap button:last-child { background: rgba(255, 255, 255, 0.08) !important; color: #e2e8f0; }

        #debugPanel {
            bottom: 108px;
            left: 12px;
            right: 12px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.82);
            color: #86efac;
            border-radius: 14px;
        }

        .audioPanel {
            display: none !important;
            bottom: 104px;
            background: rgba(0, 0, 0, 0.7);
        }

        .localPanel {
            left: 12px;
            bottom: 104px;
        }

        .remotePanel {
            right: 12px;
        }

        .remotePanel .volumeLevel { background: #22c55e; }
        .localPanel .volumeLevel { background: #f59e0b; }

        .actionButtons {
            display: none !important;
        }

        .actionButtons button {
            background: rgba(0, 0, 0, 0.42);
            padding: 8px 12px;
            border-radius: 999px;
            backdrop-filter: blur(10px);
        }

        body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap button:last-child {
            display: none !important;
        }

        #remoteAudio {
            display: none !important;
            bottom: 168px;
            right: 12px;
            width: 220px;
            height: 42px;
            background: rgba(17, 24, 39, 0.88);
            border-radius: 10px;
        }

        #audioMessage {
            top: 64px;
            right: 12px;
            background: #f59e0b;
            color: #111827;
            padding: 8px 12px;
        }

        @media (max-width: 520px) {
            body > div.text-center {
                padding: 22px 14px 138px;
            }

            #callTitle {
                font-size: 26px;
            }

            body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap {
                gap: 10px;
                padding: 14px;
            }

            body > div.text-center > div.flex.gap-4.justify-center.mt-8.flex-wrap button {
                min-width: 72px;
                min-height: 54px;
                font-size: 13px;
                padding: 0 14px;
            }
        }
    </style>
</head>

<body class="text-white">
    <div class="text-center w-full max-w-md px-4">
        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold mx-auto">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <h2 id="callTitle" class="mt-4 text-xl font-semibold">
            Voice Call with {{ $user->name }}
        </h2>
        <p id="callStatus" class="text-gray-400 text-sm mt-1">
            Initializing...
        </p>
        <p id="callTimer" class="callTimer">
            00:00
        </p>
        <div class="flex gap-4 justify-center mt-8 flex-wrap">
            <button id="muteBtn" onclick="toggleMute()" class="transition" style="display: none;">
                <span id="muteBtnText">Mute</span>
            </button>
            <button id="startBtn" onclick="startCall()"
                class="bg-green-500 px-6 py-3 rounded-full text-lg hover:bg-green-600 transition flex items-center gap-2">
                <span>📞</span> Start Call
            </button>
            <button id="acceptBtn" onclick="acceptCall()"
                class="bg-green-600 px-6 py-3 rounded-full text-lg hover:bg-green-700 transition flex items-center gap-2 animate-pulse">
                <span>✅</span> Accept Call
            </button>
            <button id="endBtn" onclick="endCall()"
                class="bg-red-500 px-6 py-3 rounded-full text-lg hover:bg-red-600 transition">
                End
            </button>
            <button id="reconnectBtn" onclick="restartIce()"
                class="bg-orange-500 px-6 py-3 rounded-full text-lg hover:bg-orange-600 transition">
                🔄 Reconnect
            </button>
            <button onclick="toggleDebug()"
                class="bg-gray-600 px-4 py-3 rounded-full text-lg hover:bg-gray-700 transition">🐛 Debug</button>
        </div>
        <div id="debugPanel"></div>
    </div>

    <!-- Remote audio control (bottom‑right) -->
    <div id="remotePanel" class="audioPanel remotePanel" style="display: none;">
        <span id="remoteMuteIcon" class="muteIcon">🔊</span>
        <div class="volumeMeter">
            <div id="remoteVolumeLevel" class="volumeLevel"></div>
        </div>
    </div>

    <!-- Local audio control (bottom‑left) – only visible after mic is granted -->
    <div id="localPanel" class="audioPanel localPanel" style="display: none;">
        <span id="localMicIcon" class="muteIcon">🎤</span>
        <div class="volumeMeter">
            <div id="localVolumeLevel" class="volumeLevel"></div>
        </div>
    </div>

    <div class="actionButtons">
        <button id="testMicBtn" style="display: none;" onclick="testMicrophone()">🎤 Test Mic</button>
        <button id="playRemoteBtn" style="display: none;" onclick="forcePlayRemote()">🔊 Play Remote</button>
        <button onclick="testTurnServers()" style="background:rgba(0,150,255,0.8);">🔬 Test TURN</button>
    </div>

    <audio id="remoteAudio" controls autoplay style="display: none;"></audio>
    <div id="audioMessage">🔊 Click anywhere to enable audio</div>

    <script>
        (function () {
            "use strict";

            // ==================== CONFIG ====================
            const PUSHER_APP_KEY = "0c08d7f3f0fa0c883f22";
            const PUSHER_CLUSTER = "ap2";
            const userId = {{ auth()->id() }};
            const otherUserId = {{ $user->id }};
            const otherUserName = "{{ $user->name }}";

            // ==================== DEBUG LOGGING ====================
            const debugLogs = [];
            function debug(...args) {
                const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ');
                console.log("[DEBUG]", ...args);
                debugLogs.unshift({ time: new Date().toLocaleTimeString(), message });
                updateDebugPanel();
            }
            window.toggleDebug = function () {
                document.body.classList.toggle('debug-visible');
                updateDebugPanel();
            };
            function updateDebugPanel() {
                const panel = document.getElementById('debugPanel');
                if (panel) {
                    panel.innerHTML = debugLogs.slice(0, 20).map(log =>
                        `<div>${log.time}: ${log.message}</div>`
                    ).join('');
                }
            }

            // ==================== ICE SERVERS ====================
            // Will be populated dynamically via /get-ice-servers
            // Comprehensive default ICE servers for cross-WiFi connectivity (same as video-call)
            let iceServers = [
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

                // TURN servers (for NAT traversal across different WiFi networks)
                {
                    urls: [
                        "turn:pm.inovace.in:3478?transport=udp",
                        "turn:pm.inovace.in:3478?transport=tcp",
                        "turns:pm.inovace.in:5349?transport=tcp"
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
                    urls: [
                        "turn:openrelay.metered.ca:443",
                        "turns:openrelay.metered.ca:443?transport=tcp"
                    ],
                    username: "openrelayproject",
                    credential: "openrelayproject"
                }
            ];
            const defaultIceServers = JSON.parse(JSON.stringify(iceServers));

            function normalizeIceServerUrls(server) {
                if (!server || !server.urls) {
                    return [];
                }

                return Array.isArray(server.urls) ? server.urls : [server.urls];
            }

            function mergeIceServers(primaryServers, extraServers) {
                const merged = [];
                const seen = new Set();

                [...primaryServers, ...extraServers].forEach(server => {
                    if (!server || !server.urls) {
                        return;
                    }

                    const urls = normalizeIceServerUrls(server);
                    const key = JSON.stringify({
                        urls,
                        username: server.username ?? null,
                        credential: server.credential ?? null
                    });

                    if (seen.has(key)) {
                        return;
                    }

                    seen.add(key);
                    merged.push(server);
                });

                return merged;
            }

            // ==================== SDP CLEANER (improved) ====================
            function cleanSDP(sdp) {
                if (!sdp || typeof sdp !== 'string') return sdp;

                // Normalize line breaks
                let cleaned = sdp.replace(/\\r\\n/g, '\r\n')
                    .replace(/\\n/g, '\n')
                    .replace(/\\r/g, '\r')
                    .replace(/\\\\/g, '\\');

                // Ensure each line ends with \r\n
                cleaned = cleaned.replace(/\r?\n/g, '\r\n');

                // Remove any empty lines
                let lines = cleaned.split(/\r?\n/).filter(line => line.trim().length > 0);
                // Repair common SDP issues (only if needed)
                lines = lines.map(line => {
                    if (line.startsWith('a=src:')) line = 'a=ssrc:' + line.substring(6);
                    if (line.startsWith('a=ssrc') && !line.startsWith('a=ssrc:')) {
                        line = line.replace(/^a=ssrc/, 'a=ssrc:');
                    }
                    if (line.startsWith('a=ssrc:')) {
                        let match = line.match(/^a=ssrc:(\d+)\s*(.*)$/);
                        if (match) {
                            let ssrc = match[1];
                            let rest = match[2].trim();
                            let msidMatch = rest.match(/msid:([a-f0-9-]+)(?:\s+)?([a-f0-9-]+)?/i);
                            if (msidMatch) {
                                let msid1 = msidMatch[1];
                                let msid2 = msidMatch[2];
                                if (!msid2) {
                                    let remainder = rest.replace(/msid:[a-f0-9-]+/i, '');
                                    let secondUuid = remainder.match(/([a-f0-9-]{36})/);
                                    if (secondUuid) msid2 = secondUuid[1];
                                    else msid2 = msid1;
                                }
                                return `a=ssrc:${ssrc} msid:${msid1} ${msid2}`;
                            }
                            return line;
                        }
                    }
                    return line;
                });
                return lines.join('\r\n') + '\r\n';
            }

            function waitForIceGatheringComplete(pc) {
                if (!pc || pc.iceGatheringState === "complete") {
                    return Promise.resolve();
                }

                return new Promise(resolve => {
                    const timeout = setTimeout(done, 10000);

                    function done() {
                        clearTimeout(timeout);
                        pc.removeEventListener("icegatheringstatechange", onStateChange);
                        resolve();
                    }

                    function onStateChange() {
                        if (pc.iceGatheringState === "complete") {
                            done();
                        }
                    }

                    pc.addEventListener("icegatheringstatechange", onStateChange);
                });
            }

            // ==================== STATE ====================
            let localStream = null;
            let peerConnection = null;
            let incomingOffer = null;
            let incomingCallerId = null;
            let incomingCallerName = null;
            let pendingCandidates = [];
            let isRemoteSet = false;
            let callActive = false;
            let connectionTimeout = null;
            let pusher = null;
            let channel = null;
            let remoteAudioElement = null;
            let localAudioContext = null;
            let localAnalyser = null;
            let localAnimation = null;
            let remoteVolumeCtx = null;
            let remoteAnalyser = null;
            let remoteAnimation = null;
            let playbackAudioContext = null;
            let playbackGain = null;
            let currentRemoteStream = null;
            let iceRestartPending = false;
            let handlingAnswer = false;
            let endingCall = false;
            let closeWindowTimer = null;
            let callTimerInterval = null;
            let callStartTime = null;
            let micMuted = false;
            let ringtoneAudioContext = null;
            let ringtoneGain = null;
            let ringtoneInterval = null;
            let ringtoneActive = false;
            let ringtonePendingUnlock = false;

            // ==================== VOLUME METERS (unchanged) ====================
            function stopIncomingRingtone() {
                ringtoneActive = false;
                ringtonePendingUnlock = false;

                if (ringtoneInterval) {
                    clearInterval(ringtoneInterval);
                    ringtoneInterval = null;
                }

                if (ringtoneGain) {
                    try {
                        ringtoneGain.gain.cancelScheduledValues(0);
                        ringtoneGain.gain.value = 0;
                    } catch (e) {
                        debug("Ringtone gain reset failed:", e);
                    }
                    ringtoneGain = null;
                }

                if (ringtoneAudioContext) {
                    ringtoneAudioContext.close().catch(() => {});
                    ringtoneAudioContext = null;
                }
            }

            function playRingtoneBurst() {
                if (!ringtoneAudioContext || !ringtoneGain) {
                    return;
                }

                const now = ringtoneAudioContext.currentTime;
                const oscA = ringtoneAudioContext.createOscillator();
                const oscB = ringtoneAudioContext.createOscillator();

                oscA.type = "triangle";
                oscB.type = "triangle";
                oscA.frequency.setValueAtTime(1020, now);
                oscB.frequency.setValueAtTime(780, now);

                oscA.connect(ringtoneGain);
                oscB.connect(ringtoneGain);

                ringtoneGain.gain.cancelScheduledValues(now);
                ringtoneGain.gain.setValueAtTime(0.0001, now);
                ringtoneGain.gain.exponentialRampToValueAtTime(0.22, now + 0.02);
                ringtoneGain.gain.exponentialRampToValueAtTime(0.0001, now + 0.5);

                oscA.start(now);
                oscB.start(now);
                oscA.stop(now + 0.52);
                oscB.stop(now + 0.52);
            }

            async function startIncomingRingtone() {
                ringtoneActive = true;

                if (ringtoneInterval || ringtoneAudioContext) {
                    return;
                }

                try {
                    ringtoneAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    ringtoneGain = ringtoneAudioContext.createGain();
                    ringtoneGain.gain.value = 0.0001;
                    ringtoneGain.connect(ringtoneAudioContext.destination);

                    if (ringtoneAudioContext.state !== "running") {
                        await ringtoneAudioContext.resume();
                    }

                    playRingtoneBurst();
                    ringtoneInterval = setInterval(playRingtoneBurst, 1000);
                    debug("Incoming ringtone started");
                } catch (err) {
                    debug("Incoming ringtone blocked or failed:", err);
                    ringtonePendingUnlock = true;
                    if (ringtoneAudioContext) {
                        ringtoneAudioContext.close().catch(() => {});
                        ringtoneAudioContext = null;
                    }
                    ringtoneGain = null;
                }
            }

            async function retryRingtoneAfterUnlock() {
                if (!ringtoneActive || !ringtonePendingUnlock || callActive) {
                    return;
                }
                ringtonePendingUnlock = false;
                await startIncomingRingtone();
            }

            function startLocalVolumeMeter(stream) {
                if (localAudioContext) return;
                try {
                    localAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    localAnalyser = localAudioContext.createAnalyser();
                    localAnalyser.fftSize = 256;
                    const source = localAudioContext.createMediaStreamSource(stream);
                    source.connect(localAnalyser);
                    if (localAudioContext.state === 'suspended') {
                        localAudioContext.resume().catch(e => debug("Failed to resume local AudioContext:", e));
                    }
                    const meter = document.getElementById('localVolumeLevel');
                    const panel = document.getElementById('localPanel');
                    panel.style.display = 'flex';
                    function update() {
                        if (!localAnalyser) return;
                        const data = new Uint8Array(localAnalyser.frequencyBinCount);
                        localAnalyser.getByteFrequencyData(data);
                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        let avg = sum / data.length;
                        let percent = (avg / 255) * 100;
                        meter.style.width = percent + '%';
                        localAnimation = requestAnimationFrame(update);
                    }
                    update();
                    debug("Local volume meter started");
                } catch (e) {
                    debug("❌ Failed to start local volume meter:", e);
                }
            }

            function startRemoteVolumeMeter(stream) {
                if (remoteVolumeCtx) return;
                try {
                    remoteVolumeCtx = new (window.AudioContext || window.webkitAudioContext)();
                    remoteAnalyser = remoteVolumeCtx.createAnalyser();
                    remoteAnalyser.fftSize = 256;
                    const source = remoteVolumeCtx.createMediaStreamSource(stream);
                    source.connect(remoteAnalyser);
                    if (remoteVolumeCtx.state === 'suspended') {
                        remoteVolumeCtx.resume().catch(e => debug("Failed to resume remote volume meter:", e));
                    }
                    const meter = document.getElementById('remoteVolumeLevel');
                    const panel = document.getElementById('remotePanel');
                    panel.style.display = 'flex';
                    function update() {
                        if (!remoteAnalyser) return;
                        const data = new Uint8Array(remoteAnalyser.frequencyBinCount);
                        remoteAnalyser.getByteFrequencyData(data);
                        let sum = 0;
                        for (let i = 0; i < data.length; i++) sum += data[i];
                        let avg = sum / data.length;
                        let percent = (avg / 255) * 100;
                        meter.style.width = percent + '%';
                        remoteAnimation = requestAnimationFrame(update);
                    }
                    update();
                    debug("Remote volume meter started");
                } catch (e) {
                    debug("❌ Failed to start remote volume meter:", e);
                }
            }

            function stopVolumeMeters() {
                if (localAnimation) cancelAnimationFrame(localAnimation);
                if (remoteAnimation) cancelAnimationFrame(remoteAnimation);
                if (localAudioContext) localAudioContext.close();
                if (remoteVolumeCtx) remoteVolumeCtx.close();
                localAudioContext = null;
                remoteVolumeCtx = null;
                localAnalyser = null;
                remoteAnalyser = null;
                document.getElementById('localPanel').style.display = 'none';
                document.getElementById('remotePanel').style.display = 'none';
            }

            // ==================== TEST MICROPHONE ====================
            window.testMicrophone = function () {
                if (!localStream) {
                    alert("No microphone stream available. Please start/accept a call first.");
                    return;
                }
                const testCtx = new (window.AudioContext || window.webkitAudioContext)();
                const source = testCtx.createMediaStreamSource(localStream);
                const gain = testCtx.createGain();
                source.connect(gain);
                gain.connect(testCtx.destination);
                gain.gain.value = 1;
                testCtx.resume().then(() => {
                    debug("Local microphone test started – you should hear your own voice");
                    updateStatus("🔊 Listening to your microphone – you should hear yourself");
                    setTimeout(() => {
                        testCtx.close();
                        updateStatus(callActive ? "Call connected!" : "Ready");
                    }, 3000);
                }).catch(e => debug("Test mic failed:", e));
            };

            // ==================== REMOTE PLAYBACK (unchanged) ====================
            function ensureRemotePlayback() {
                if (!currentRemoteStream) {
                    debug("No remote stream available yet.");
                    return false;
                }
                if (!playbackAudioContext) {
                    debug("Creating playback AudioContext on the fly");
                    playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (playbackGain && playbackGain.context === playbackAudioContext) {
                    if (playbackAudioContext.state !== 'running') {
                        playbackAudioContext.resume().then(() => debug("Resumed existing AudioContext")).catch(e => debug("Resume failed:", e));
                    }
                    return true;
                }
                try {
                    if (playbackGain) playbackGain.disconnect();
                    const source = playbackAudioContext.createMediaStreamSource(currentRemoteStream);
                    playbackGain = playbackAudioContext.createGain();
                    playbackGain.gain.value = 1;
                    source.connect(playbackGain);
                    playbackGain.connect(playbackAudioContext.destination);
                    debug("Connected remote stream to playback AudioContext");
                    if (playbackAudioContext.state !== 'running') {
                        playbackAudioContext.resume().then(() => {
                            debug("Playback AudioContext resumed");
                            updateStatus("✅ Remote audio playing (via AudioContext)");
                        }).catch(e => {
                            debug("Failed to resume AudioContext automatically:", e);
                            updateStatus("🔊 Click anywhere to enable audio");
                        });
                    }
                    return true;
                } catch (e) {
                    debug("❌ Failed to connect remote stream to AudioContext:", e);
                    return false;
                }
            }

            function attemptPlayRemoteAudio() {
                if (!currentRemoteStream) return false;
                if (ensureRemotePlayback()) {
                    debug("✅ AudioContext playback started.");
                    updateStatus("✅ Audio is playing");
                    return true;
                }
                if (remoteAudioElement && remoteAudioElement.srcObject) {
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.play()
                        .then(() => {
                            debug("✅ <audio> element playback started.");
                            updateStatus("✅ Audio is playing (fallback)");
                            return true;
                        })
                        .catch(e => {
                            debug("❌ <audio> element play() failed:", e);
                            return false;
                        });
                }
                return false;
            }

            window.forcePlayRemote = function () {
                if (attemptPlayRemoteAudio()) {
                    updateStatus("🔊 Force‑playing remote audio");
                    const msgDiv = document.getElementById('audioMessage');
                    if (msgDiv) msgDiv.style.display = 'none';
                } else {
                    alert("Could not play remote audio. Please check your browser permissions.");
                }
            };

            // ==================== CHECK PENDING CALL ====================
            function checkPendingCall() {
                try {
                    const pendingCall = sessionStorage.getItem('pendingCall');
                    if (pendingCall) {
                        const callData = JSON.parse(pendingCall);
                        const age = Date.now() - callData.timestamp;
                        debug(`Found pending call, age: ${age}ms`);
                        if (age < 15000) {
                            debug("Using pending call data");
                            if (callData.offer && callData.offer.sdp) {
                                callData.offer.sdp = cleanSDP(callData.offer.sdp);
                            }
                            incomingOffer = callData.offer;
                            incomingCallerId = callData.callerId;
                            incomingCallerName = callData.callerName;
                            sessionStorage.removeItem('pendingCall');
                            showAcceptMode();
                            document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                            updateStatus("Incoming call - Click Accept");
                            return true;
                        } else {
                            sessionStorage.removeItem('pendingCall');
                        }
                    }
                } catch (e) {
                    debug("Error checking pending call:", e);
                }
                return false;
            }

            // ==================== ICE CANDIDATE DEDUPLICATION ====================
            const seenCandidates = new Set();
            function addIceCandidate(candidate, source) {
                if (!candidate || !candidate.candidate) {
                    debug(`❄️ Received empty/null candidate from ${source}`);
                    return;
                }
                const key = candidate.candidate;
                if (seenCandidates.has(key)) {
                    debug(`❄️ Skipping duplicate ICE from ${source}`);
                    return;
                }
                seenCandidates.add(key);
                
                const candidateStr = candidate.candidate;
                let type = "unknown";
                if (candidateStr.includes("typ host")) type = "host";
                else if (candidateStr.includes("typ srflx")) type = "srflx (STUN)";
                else if (candidateStr.includes("typ relay")) type = "relay (TURN)";
                debug(`❄️ Received ICE [${type}] from ${source}:`, candidateStr.substring(0, 80) + "...");
                
                if (!peerConnection || !isRemoteSet) {
                    debug(`❄️ Buffering candidate (peerConnection=${!!peerConnection}, isRemoteSet=${isRemoteSet})`);
                    pendingCandidates.push(candidate);
                } else {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                        .then(() => debug(`✅ ICE candidate added successfully`))
                        .catch(err => debug("❌ Error adding ICE:", err));
                }
            }

            // ==================== LISTEN FOR POST MESSAGES ====================
            window.addEventListener('message', function (event) {
                debug("📨 Received message:", event.data.type);
                if (event.data.type === 'incoming-offer') {
                    debug("📞 Received offer via postMessage");
                    if (event.data.offer && event.data.offer.sdp) {
                        event.data.offer.sdp = cleanSDP(event.data.offer.sdp);
                    }
                    incomingOffer = event.data.offer;
                    incomingCallerId = event.data.callerId;
                    incomingCallerName = event.data.callerName;
                    showAcceptMode();
                    document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                    updateStatus("Incoming call - Click Accept");
                }
                if (event.data.type === 'ice-candidate') {
                    addIceCandidate(event.data.candidate, 'postMessage');
                }
                if (event.data.type === 'call-answer') {
                    debug("✅ Received answer via postMessage");
                    // Skip if already handled via Pusher
                    if (isRemoteSet) { debug("Answer already handled via Pusher, skipping postMessage duplicate"); return; }
                    handleAnswer(event.data.answer);
                }
                if (event.data.type === 'call-ended') {
                    debug("Call ended received via postMessage");
                    updateStatus("Other side ended the call");
                    window.endCall(false, true);
                }
            });

            // ==================== UI FUNCTIONS ====================
            function updateStatus(message) {
                debug("STATUS:", message);
                document.getElementById("callStatus").innerHTML = message;

                const normalizedMessage = String(message).toLowerCase();
                if (normalizedMessage.includes("call connected")) {
                    startCallTimer();
                } else if (
                    normalizedMessage.includes("failed") ||
                    normalizedMessage.includes("ended") ||
                    normalizedMessage.includes("lost") ||
                    normalizedMessage.includes("denied") ||
                    normalizedMessage.includes("error") ||
                    normalizedMessage.includes("waiting for answer") ||
                    normalizedMessage.includes("sending call request") ||
                    normalizedMessage.includes("sending answer") ||
                    normalizedMessage.includes("requesting microphone") ||
                    normalizedMessage.includes("accessing microphone") ||
                    normalizedMessage.includes("setting up connection") ||
                    normalizedMessage.includes("ready to receive")
                ) {
                    stopCallTimer();
                }
            }

            function formatCallDuration(totalSeconds) {
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            function updateCallTimer() {
                const timer = document.getElementById("callTimer");
                if (!timer || !callStartTime) {
                    return;
                }

                const elapsedSeconds = Math.max(0, Math.floor((Date.now() - callStartTime) / 1000));
                timer.textContent = formatCallDuration(elapsedSeconds);
            }

            function startCallTimer() {
                const timer = document.getElementById("callTimer");
                if (!timer) {
                    return;
                }

                if (!callStartTime) {
                    callStartTime = Date.now();
                }

                timer.classList.add("visible");
                updateCallTimer();

                if (callTimerInterval) {
                    return;
                }

                callTimerInterval = setInterval(updateCallTimer, 1000);
            }

            function stopCallTimer() {
                const timer = document.getElementById("callTimer");

                if (callTimerInterval) {
                    clearInterval(callTimerInterval);
                    callTimerInterval = null;
                }

                callStartTime = null;

                if (timer) {
                    timer.textContent = "00:00";
                    timer.classList.remove("visible");
                }
            }

            function syncMuteButton() {
                const muteBtn = document.getElementById("muteBtn");
                const muteBtnText = document.getElementById("muteBtnText");

                if (!muteBtn || !muteBtnText) {
                    return;
                }

                muteBtn.style.display = localStream ? "inline-flex" : "none";
                muteBtn.classList.toggle("muted", micMuted);
                muteBtnText.textContent = micMuted ? "Unmute" : "Mute";
            }

            window.toggleMute = function () {
                if (!localStream) {
                    return;
                }

                const audioTracks = localStream.getAudioTracks();
                if (!audioTracks.length) {
                    return;
                }

                micMuted = !micMuted;
                audioTracks.forEach(track => {
                    track.enabled = !micMuted;
                });

                syncMuteButton();
                updateStatus(micMuted ? "🎤 Microphone muted" : "🎤 Microphone active");
            };

            function showStartMode() {
                stopIncomingRingtone();
                document.getElementById("startBtn").style.display = "block";
                document.getElementById("acceptBtn").style.display = "none";
                document.getElementById("reconnectBtn").style.display = "none";
                document.getElementById("callTitle").textContent = `Call ${otherUserName}`;
            }

            function showAcceptMode() {
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "block";
                document.getElementById("reconnectBtn").style.display = "none";
                startIncomingRingtone();
            }

            function hideAllButtons() {
                stopIncomingRingtone();
                document.getElementById("startBtn").style.display = "none";
                document.getElementById("acceptBtn").style.display = "none";
                document.getElementById("reconnectBtn").style.display = "none";
            }

            async function notifyCallEnded() {
                const receiverId = incomingCallerId ?? otherUserId;

                if (!receiverId) {
                    return;
                }

                const payload = JSON.stringify({ receiverId });
                const url = '/end-call';

                try {
                    if (navigator.sendBeacon) {
                        const blob = new Blob([payload], { type: 'application/json' });
                        navigator.sendBeacon(url, blob);
                        return;
                    }
                } catch (err) {
                    debug("Call end beacon failed:", err);
                }

                try {
                    await fetch(url, {
                        method: 'POST',
                        keepalive: true,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: payload
                    });
                } catch (err) {
                    debug("Call end notify failed:", err);
                }
            }

            function closeCallWindow(delay = 250) {
                if (closeWindowTimer) {
                    clearTimeout(closeWindowTimer);
                }

                closeWindowTimer = setTimeout(() => {
                    try {
                        window.close();
                    } catch (err) {
                        debug("Window close failed:", err);
                    }
                }, delay);
            }

            // ==================== WEBRTC ====================
            function createPeer() {
                debug("Creating peer connection with ICE servers:", iceServers);
                updateStatus("Setting up connection...");
                pendingCandidates = [];
                isRemoteSet = false;
                seenCandidates.clear();

                peerConnection = new RTCPeerConnection({
                    iceServers: iceServers,
                    iceCandidatePoolSize: 20,
                    iceTransportPolicy: 'all',  // Allow STUN + TURN for better connectivity
                    bundlePolicy: 'max-bundle',
                    rtcpMuxPolicy: 'require',
                    sdpSemantics: 'unified-plan'
                });

                peerConnection.onconnectionstatechange = () => {
                    debug("Connection state:", peerConnection.connectionState);
                    updateStatus(`Connection: ${peerConnection.connectionState}`);
                    if (peerConnection.connectionState === 'connected') {
                        updateStatus("✅ Call connected!");
                        document.getElementById("reconnectBtn").style.display = "none";
                    } else if (peerConnection.connectionState === 'failed') {
                        updateStatus("❌ Connection failed - check network or TURN server");
                        document.getElementById("reconnectBtn").style.display = "inline-block";
                    }
                };
                peerConnection.oniceconnectionstatechange = () => {
                    debug("ICE state:", peerConnection.iceConnectionState);
                    updateStatus(`Connection: ${peerConnection.iceConnectionState}`);
                    
                    if (peerConnection.iceConnectionState === 'failed') {
                        debug("❌ ICE connection failed - attempting automatic restart...");
                        updateStatus("❌ Connection failed - retrying...");
                        document.getElementById("reconnectBtn").style.display = "inline-block";
                        
                        // Automatic retry with delay
                        setTimeout(() => {
                            if (!iceRestartPending) {
                                restartIce();
                            }
                        }, 2000);
                    } else if (peerConnection.iceConnectionState === 'disconnected') {
                        debug("⚠️ ICE connection disconnected - monitoring for reconnection...");
                        updateStatus("Connection lost - monitoring...");
                        
                        // Wait a bit to see if it reconnects automatically
                        setTimeout(() => {
                            if (peerConnection.iceConnectionState === 'disconnected') {
                                debug("Still disconnected, attempting ICE restart...");
                                restartIce();
                            }
                        }, 3000);
                    } else if (peerConnection.iceConnectionState === 'connected') {
                        debug("✅ ICE connection established successfully");
                        updateStatus("✅ Call connected!");
                        document.getElementById("reconnectBtn").style.display = "none";
                        
                        // Process pending track event if we have one
                        if (window.pendingTrackEvent) {
                            debug("🎵 Processing pending track event now that ICE is connected");
                            setupRemoteAudio(window.pendingTrackEvent);
                            window.pendingTrackEvent = null;
                        }
                    }
                };
                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        // Log candidate type (host/srflx/relay)
                        const candidateStr = event.candidate.candidate;
                        let type = "unknown";
                        if (candidateStr.includes("typ host")) type = "host";
                        else if (candidateStr.includes("typ srflx")) type = "srflx (STUN)";
                        else if (candidateStr.includes("typ relay")) type = "relay (TURN)";
                        debug(`❄️ ICE candidate [${type}]:`, candidateStr.substring(0, 100) + "...");

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
                        }).then(() => debug("✅ ICE candidate sent"))
                          .catch(err => debug("❌ Error sending ICE:", err));
                    } else {
                        debug("✅ ICE candidate gathering completed (null = done)");
                    }
                };
                peerConnection.ontrack = (event) => {
                    debug("🎵 Remote audio track received - ICE state:", peerConnection.iceConnectionState);
                    debug("🎵 Connection state:", peerConnection.connectionState);
                    
                    // Set up audio if ICE is connected, completed, or checking (checking means ICE negotiation is in progress)
                    if (peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed' || peerConnection.iceConnectionState === 'checking') {
                        setupRemoteAudio(event);
                    } else {
                        debug("⏳ Track received but ICE not ready yet - waiting...");
                        // Store the event for later when ICE connects
                        window.pendingTrackEvent = event;
                        
                        // Fallback: process after 5 seconds even if ICE doesn't connect
                        setTimeout(() => {
                            if (window.pendingTrackEvent === event) {
                                debug("⚠️ Timeout: processing audio even though ICE not fully connected");
                                setupRemoteAudio(event);
                                window.pendingTrackEvent = null;
                            }
                        }, 5000);
                    }
                };

                function setupRemoteAudio(event) {
                    debug("🔊 Setting up remote audio playback");
                    debug("🔊 Stream tracks:", event.streams[0].getTracks().map(t => `${t.kind} (${t.enabled ? 'enabled' : 'disabled'})`));
                    updateStatus("Audio connected - Call active");

                    currentRemoteStream = event.streams[0];
                    startRemoteVolumeMeter(event.streams[0]);
                    document.getElementById('playRemoteBtn').style.display = 'block';

                    remoteAudioElement = document.getElementById("remoteAudio");
                    remoteAudioElement.style.display = "block";
                    remoteAudioElement.controls = true;
                    remoteAudioElement.autoplay = true;
                    remoteAudioElement.playsInline = true;
                    remoteAudioElement.muted = false;
                    remoteAudioElement.volume = 1;
                    remoteAudioElement.srcObject = event.streams[0];
                    debug("🔊 Audio element configured, srcObject set");

                    const played = attemptPlayRemoteAudio();
                    debug("🔊 attemptPlayRemoteAudio returned:", played);
                    if (!played) {
                        const msgDiv = document.getElementById('audioMessage');
                        if (msgDiv) msgDiv.style.display = 'block';
                        const enableAudio = () => {
                            debug("User clicked – trying to enable audio...");
                            if (attemptPlayRemoteAudio()) {
                                if (msgDiv) msgDiv.style.display = 'none';
                                document.removeEventListener('click', enableAudio);
                                updateStatus("✅ Audio enabled after click");
                            } else {
                                debug("Still unable to play audio.");
                            }
                        };
                        document.addEventListener('click', enableAudio);
                        updateStatus("🔊 Click anywhere to enable audio");
                    } else {
                        const msgDiv = document.getElementById('audioMessage');
                        if (msgDiv) msgDiv.style.display = 'none';
                    }

                    // Mute/unmute control
                    const remotePanel = document.getElementById('remotePanel');
                    const muteIcon = document.getElementById('remoteMuteIcon');
                    remotePanel.onclick = () => {
                        if (playbackGain) {
                            playbackGain.gain.value = playbackGain.gain.value === 1 ? 0 : 1;
                            muteIcon.textContent = playbackGain.gain.value === 0 ? '🔇' : '🔊';
                            updateStatus(playbackGain.gain.value === 0 ? "Remote audio is muted" : "Remote audio is playing");
                        } else if (remoteAudioElement) {
                            remoteAudioElement.muted = !remoteAudioElement.muted;
                            muteIcon.textContent = remoteAudioElement.muted ? '🔇' : '🔊';
                            updateStatus(remoteAudioElement.muted ? "Remote audio is muted" : "Remote audio is playing");
                        }
                    };
                }
            }

            // ==================== TURN SERVER DIAGNOSTIC ====================
            window.testTurnServers = async function () {
                debug("🔬 Testing TURN servers...");
                document.body.classList.add('debug-visible');
                const turnServers = iceServers.filter(s => {
                    const urls = Array.isArray(s.urls) ? s.urls : [s.urls];
                    return urls.some(u => u.startsWith('turn:') || u.startsWith('turns:'));
                });
                if (turnServers.length === 0) {
                    debug("❌ No TURN servers in ICE config!");
                    return;
                }
                debug(`Testing ${turnServers.length} TURN server(s)...`);
                for (const server of turnServers) {
                    const urls = Array.isArray(server.urls) ? server.urls : [server.urls];
                    const label = urls[0];
                    try {
                        const pc = new RTCPeerConnection({
                            iceServers: [server],
                            iceTransportPolicy: 'relay'
                        });
                        const result = await new Promise((resolve) => {
                            const timer = setTimeout(() => { pc.close(); resolve('❌ timeout'); }, 8000);
                            pc.onicecandidate = (e) => {
                                if (e.candidate && e.candidate.candidate.includes('typ relay')) {
                                    clearTimeout(timer);
                                    pc.close();
                                    resolve('✅ relay candidate received');
                                } else if (!e.candidate) {
                                    clearTimeout(timer);
                                    pc.close();
                                    resolve('❌ no relay candidate');
                                }
                            };
                            pc.createDataChannel('test');
                            pc.createOffer().then(o => pc.setLocalDescription(o)).catch(() => { clearTimeout(timer); resolve('❌ offer failed'); });
                        });
                        debug(`TURN [${label}]: ${result}`);
                    } catch (e) {
                        debug(`TURN [${label}]: ❌ error - ${e.message}`);
                    }
                }
                debug("🔬 TURN test complete.");
            };

            // ==================== NETWORK CONNECTIVITY TEST ====================
            async function testNetworkConnectivity() {
                try {
                    debug("Testing network connectivity for cross-network calls...");
                    updateStatus("Testing network connectivity...");
                    
                    // Test connectivity to multiple STUN servers
                    const stunServers = [
                        'stun:stun.l.google.com:19302',
                        'stun:stun.stunprotocol.org:3478',
                        'stun:stun.ekiga.net:3478'
                    ];
                    
                    let connectivityScore = 0;
                    let workingServers = [];
                    
                    for (const stunServer of stunServers) {
                        try {
                            const pc = new RTCPeerConnection({
                                iceServers: [{ urls: stunServer }]
                            });
                            
                            const promise = new Promise((resolve, reject) => {
                                const timeout = setTimeout(() => {
                                    pc.close();
                                    reject(new Error('STUN timeout'));
                                }, 5000);
                                
                                pc.onicecandidate = (e) => {
                                    if (e.candidate && e.candidate.type === 'srflx') {
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
                            workingServers.push(stunServer);
                            debug(`STUN server ${stunServer} working`);
                        } catch (error) {
                            debug(`STUN server ${stunServer} failed:`, error.message);
                        }
                    }
                    
                    debug(`Network connectivity score: ${connectivityScore}/${stunServers.length}`);
                    debug(`Working STUN servers:`, workingServers);
                    
                    if (connectivityScore === 0) {
                        updateStatus("Poor network connectivity - using TURN servers");
                        return false;
                    } else if (connectivityScore < stunServers.length / 2) {
                        updateStatus("Limited network connectivity - may need TURN servers");
                        return true;
                    } else {
                        updateStatus("Good network connectivity");
                        return true;
                    }
                    
                } catch (error) {
                    debug("Network connectivity test failed:", error);
                    updateStatus("Network test failed - proceeding anyway");
                    return true;
                }
            }

            // ==================== IMPROVED ICE RESTART ====================
            async function restartIce() {
                if (!peerConnection || iceRestartPending) return;
                iceRestartPending = true;
                debug("Attempting ICE restart for cross-network connectivity...");
                updateStatus("Reconnecting...");
                
                try {
                    // Create new peer connection with fresh ICE candidates
                    const oldPeerConnection = peerConnection;
                    createPeer();
                    
                    // Re-add local stream if available
                    if (localStream) {
                        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                    }
                    
                    // Create new offer
                    const offer = await peerConnection.createOffer({
                        offerToReceiveAudio: true
                    });
                    await peerConnection.setLocalDescription(offer);
                    await waitForIceGatheringComplete(peerConnection);
                    
                    // Close old connection
                    oldPeerConnection.close();
                    
                    // Send the new offer to the other peer
                    const response = await fetch('/send-offer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            offer: {
                                type: peerConnection.localDescription.type,
                                sdp: peerConnection.localDescription.sdp
                            },
                            receiverId: incomingCallerId ?? otherUserId,
                            iceRestart: true
                        })
                    });
                    
                    if (!response.ok) throw new Error('Failed to send ICE restart offer');
                    debug("ICE restart offer sent successfully");
                    updateStatus("Reconnection initiated...");
                    
                } catch (err) {
                    debug("ICE restart failed:", err);
                    updateStatus("Reconnection failed - try again");
                    
                    // Fallback: try to restart with just ICE restart on existing connection
                    try {
                        const offer = await peerConnection.createOffer({ iceRestart: true });
                        await peerConnection.setLocalDescription(offer);
                        debug("Fallback ICE restart attempted");
                    } catch (fallbackErr) {
                        debug("Fallback ICE restart also failed:", fallbackErr);
                    }
                } finally {
                    iceRestartPending = false;
                }
            }
            window.restartIce = restartIce;

            // ==================== CALL FUNCTIONS ====================
            window.startCall = async function () {
                debug("Starting call...");
                if (callActive) return;
                callActive = true;
                hideAllButtons();
                updateStatus('<span class="spinner"></span> Requesting microphone...');
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    debug("Microphone access granted");
                    micMuted = false;
                    syncMuteButton();
                    startLocalVolumeMeter(localStream);
                    document.getElementById('testMicBtn').style.display = 'block';

                    if (!playbackAudioContext) {
                        playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                        await playbackAudioContext.resume();
                        debug("Playback AudioContext resumed (caller side)");
                    }
                } catch (err) {
                    debug("Microphone error:", err);
                    alert("Microphone access is required for calls");
                    updateStatus("Microphone access denied");
                    callActive = false;
                    showStartMode();
                    return;
                }
                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                try {
                    const offer = await peerConnection.createOffer({
                        offerToReceiveAudio: true
                    });
                    await peerConnection.setLocalDescription(offer);
                    await waitForIceGatheringComplete(peerConnection);
                    updateStatus("Sending call request...");
                    const response = await fetch('/send-offer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            offer: {
                                type: peerConnection.localDescription.type,
                                sdp: peerConnection.localDescription.sdp
                            },
                            receiverId: otherUserId
                        })
                    });
                    if (!response.ok) throw new Error('Failed to send offer');
                    debug("Offer sent successfully");
                    updateStatus("Call initiated - waiting for answer...");
                    connectionTimeout = setTimeout(() => {
                        if (!callActive) return;
                        debug("No answer received - timeout");
                        updateStatus("❌ No answer - user may be unavailable");
                        endCall();
                    }, 30000);
                } catch (err) {
                    debug("Error:", err);
                    updateStatus("❌ Failed to start call");
                    endCall();
                }
            };

            window.acceptCall = async function () {
                try {
                    debug("========== ACCEPT CALL CLICKED ==========");
                    if (!incomingOffer || !incomingCallerId) {
                        debug("❌ No incoming call to accept");
                        alert("No incoming call to accept");
                        return;
                    }

                    if (incomingOffer.sdp) {
                        incomingOffer.sdp = cleanSDP(incomingOffer.sdp);
                    }

                    if (callActive) {
                        debug("Call already active");
                        return;
                    }

                    callActive = true;
                    hideAllButtons();
                    updateStatus('<span class="spinner"></span> Accessing microphone...');
                    try {
                        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        debug("✅ Microphone access granted");
                        micMuted = false;
                        syncMuteButton();
                        startLocalVolumeMeter(localStream);
                        document.getElementById('testMicBtn').style.display = 'block';
                    } catch (micErr) {
                        debug("❌ Microphone error:", micErr);
                        alert("Microphone access is required for calls. Please check permissions.");
                        updateStatus("❌ Microphone access denied");
                        callActive = false;
                        showAcceptMode();
                        return;
                    }

                    if (!playbackAudioContext) {
                        playbackAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                        await playbackAudioContext.resume();
                        debug("✅ Playback AudioContext resumed (receiver side)");
                    }

                    createPeer();
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                    debug("Setting remote description...");
                    let remoteSet = false;
                    let lastError = null;
                    try {
                        const offerDesc = new RTCSessionDescription({
                            type: incomingOffer.type,
                            sdp: incomingOffer.sdp
                        });
                        await peerConnection.setRemoteDescription(offerDesc);
                        debug("✅ Remote description set successfully");
                        remoteSet = true;
                    } catch (err) {
                        lastError = err;
                        debug("❌ setRemoteDescription failed:", err.message);
                        console.error("SDP error:", err);
                    }

                    if (!remoteSet) {
                        throw new Error(`Failed to set remote description. Last error: ${lastError?.message}`);
                    }

                    debug("Creating answer...");
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    await waitForIceGatheringComplete(peerConnection);
                    debug("✅ Local description set");

                    isRemoteSet = true;

                    debug("Adding buffered ICE candidates:", pendingCandidates.length);
                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                            debug("Added buffered ICE candidate");
                        } catch (err) {
                            debug("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];

                    updateStatus("Sending answer...");
                    const response = await fetch('/send-answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            answer: {
                                type: peerConnection.localDescription.type,
                                sdp: peerConnection.localDescription.sdp
                            },
                            receiverId: incomingCallerId
                        })
                    });
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Failed to send answer: ${response.status} ${text}`);
                    }
                    const result = await response.json();
                    debug("✅ Answer sent successfully:", result);
                    updateStatus("Call connected!");
                } catch (err) {
                    debug("❌ UNCAUGHT ERROR in acceptCall:", err);
                    console.error("FULL UNCAUGHT ERROR:", err);
                    alert("Error: " + err.message);
                    updateStatus("❌ Error: " + err.message);
                    endCall();
                }
            };

            async function handleAnswer(answer) {
                debug("Handling answer");
                if (handlingAnswer) {
                    debug("Already handling answer, skipping duplicate");
                    return;
                }
                if (isRemoteSet) {
                    debug("Remote description already set, skipping handleAnswer");
                    return;
                }
                if (!peerConnection) {
                    debug("No peer connection");
                    return;
                }
                
                handlingAnswer = true;
                
                if (connectionTimeout) {
                    clearTimeout(connectionTimeout);
                    connectionTimeout = null;
                }
                
                if (answer.sdp) {
                    answer.sdp = cleanSDP(answer.sdp);
                }
                try {
                    await peerConnection.setRemoteDescription(
                        new RTCSessionDescription({
                            type: answer.type,
                            sdp: answer.sdp
                        })
                    );
                    debug("✅ Remote description set from answer");
                    isRemoteSet = true;
                    for (const candidate of pendingCandidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        } catch (err) {
                            debug("Error adding ICE:", err);
                        }
                    }
                    pendingCandidates = [];
                    updateStatus("Call connected!");
                } catch (err) {
                    debug("❌ Error setting answer:", err);
                    updateStatus("❌ Connection failed");
                } finally {
                    handlingAnswer = false;
                }
            }

            window.endCall = function (shouldNotify = true, shouldCloseWindow = true) {
                debug("Ending call");
                if (endingCall) return;
                endingCall = true;
                stopIncomingRingtone();

                if (shouldNotify && (callActive || peerConnection || incomingCallerId || incomingOffer)) {
                    notifyCallEnded();
                }

                if (connectionTimeout) clearTimeout(connectionTimeout);
                if (peerConnection) {
                    peerConnection.close();
                    peerConnection = null;
                }
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                    localStream = null;
                }
                micMuted = false;
                syncMuteButton();
                if (remoteAudioElement) {
                    remoteAudioElement.srcObject = null;
                    remoteAudioElement.style.display = "none";
                }
                if (playbackGain) {
                    playbackGain.disconnect();
                    playbackGain = null;
                }
                if (playbackAudioContext) {
                    playbackAudioContext.close();
                    playbackAudioContext = null;
                }
                stopCallTimer();
                stopVolumeMeters();
                pendingCandidates = [];
                isRemoteSet = false;
                callActive = false;
                currentRemoteStream = null;
                handlingAnswer = false;
                window.pendingTrackEvent = null;
                document.getElementById('testMicBtn').style.display = 'none';
                document.getElementById('playRemoteBtn').style.display = 'none';
                const msgDiv = document.getElementById('audioMessage');
                if (msgDiv) msgDiv.style.display = 'none';
                if (incomingCallerId) {
                    document.getElementById("callTitle").textContent = "Call ended";
                    updateStatus("Call ended - close window");
                } else {
                    showStartMode();
                    updateStatus("Call ended");
                }
                incomingOffer = null;
                incomingCallerId = null;
                incomingCallerName = null;
                endingCall = false;

                if (shouldCloseWindow) {
                    closeCallWindow(shouldNotify ? 450 : 250);
                }
            };

            // ==================== PUSHER ====================
            function initPusher() {
                debug("Initializing Pusher...");
                Pusher.logToConsole = true;
                pusher = new Pusher(PUSHER_APP_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });
                pusher.connection.bind('connected', () => {
                    debug("Pusher connected");
                    const params = new URLSearchParams(window.location.search);
                    const isCaller = params.get('mode') === 'caller';
                    debug("Mode:", isCaller ? "CALLER" : "RECEIVER");
                    const hasPending = checkPendingCall();
                    if (isCaller) {
                        debug("STATUS: Auto-starting call as caller...");
                        setTimeout(() => { window.startCall(); }, 500);
                    } else if (!hasPending) {
                        document.getElementById("callTitle").textContent = `Waiting for call from ${otherUserName}...`;
                        updateStatus("Ready to receive calls");
                    }
                });
                pusher.connection.bind('error', (error) => {
                    debug("Pusher error:", error);
                });
                const channelName = 'voice-call.' + userId;
                channel = pusher.subscribe(channelName);
                channel.bind('subscription_succeeded', () => {
                    debug("Subscribed to channel:", channelName);
                });
                channel.bind('CallOffer', (data) => {
                    debug("📞 Call offer received in voice window");
                    const params = new URLSearchParams(window.location.search);
                    const isCaller = params.get('mode') === 'caller';
                    if (!isCaller) {
                        if (data.offer && data.offer.sdp) {
                            data.offer.sdp = cleanSDP(data.offer.sdp);
                        }
                        incomingOffer = data.offer;
                        incomingCallerId = data.callerId;
                        incomingCallerName = data.callerName;
                        showAcceptMode();
                        document.getElementById("callTitle").textContent = `📞 Incoming call from ${incomingCallerName}`;
                        updateStatus("Incoming call - Click Accept");
                    }
                });
                channel.bind('CallAnswer', async (data) => {
                    debug("Call answer received via Pusher");
                    // Only handle if we are the caller (have active peerConnection waiting for answer)
                    const isCaller = new URLSearchParams(window.location.search).get('mode') === 'caller';
                    if (!isCaller || !peerConnection) return;
                    // Skip if already handled via postMessage
                    if (isRemoteSet) { debug("Answer already handled via postMessage, skipping Pusher duplicate"); return; }
                    if (data.answer && data.answer.sdp) {
                        data.answer.sdp = cleanSDP(data.answer.sdp);
                    }
                    await handleAnswer(data.answer);
                });
                channel.bind('IceCandidate', (data) => {
                    debug("ICE candidate received via Pusher");
                    addIceCandidate(data.candidate, 'pusher');
                });
                channel.bind('CallEnded', (data) => {
                    debug("Call ended received via Pusher", data);
                    updateStatus("Other side ended the call");
                    window.endCall(false, true);
                });
            }

            // ==================== INIT ====================
            document.addEventListener("DOMContentLoaded", async function () {
                debug("Voice call page loaded");
                debug("User ID:", userId, "Other User ID:", otherUserId);
                debug("URL params:", window.location.search);
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                debug("CSRF token present:", !!token);

                // Fetch fresh ICE server credentials from backend
                try {
                    const resp = await fetch('/get-ice-servers', {
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    });
                    if (resp.ok) {
                        const data = await resp.json();
                        if (data.iceServers && data.iceServers.length) {
                            iceServers = mergeIceServers(defaultIceServers, data.iceServers);
                            debug("✅ ICE servers loaded:", iceServers.length, "servers after merge");
                        }
                    } else {
                        debug("⚠️ Failed to fetch ICE servers, using defaults");
                    }
                } catch (e) {
                    debug("⚠️ ICE server fetch error:", e.message, "- using defaults");
                }

                initPusher();

                // Browser autoplay policies may block ringtone until first gesture.
                // Retry ringtone immediately after user interacts.
                const unlockEvents = ["click", "touchstart", "keydown"];
                unlockEvents.forEach(eventName => {
                    document.addEventListener(eventName, retryRingtoneAfterUnlock, { passive: true });
                });
            });

            window.addEventListener('beforeunload', () => {
                if (callActive || peerConnection || incomingCallerId || incomingOffer) {
                    notifyCallEnded();
                }
            });
        })();
    </script>
</body>

</html>
