# Voice Call System - Production Configuration

## 🚨 Current Issues Preventing Answer

Looking at your logs:
- ✅ Offer sent successfully
- ❌ No answer received within 30 seconds

This means the **receiver side is failing**. Check:

1. **Is receiver's window opening?**
   - Check if browser popup blocker is blocking the auto-open
   - Look for "blocked popup" notification in browser

2. **Is receiver's Pusher connecting?**
   - Open receiver's chat page, press F12 (DevTools)
   - Should see: `✅ Subscribed to voice-call.{userId}`
   - If not: Receiver's user-chat.blade.php Pusher isn't connecting

3. **Is receiver getting the CallOffer event?**
   - Receiver's voice-call page should log: `📞 OFFER RECEIVED`
   - If missing: Event isn't reaching voice-call page

---

## 🔧 Required Production Configuration

### 💾 1. Environment Variables (.env)

```plaintext
# Pusher Configuration (DO NOT hardcode these!)
PUSHER_APP_ID=your_actual_app_id
PUSHER_APP_KEY=0c08d7f3f0fa0c883f22  # ⚠️ Currently hardcoded - MOVE TO ENV
PUSHER_APP_SECRET=your_actual_secret
PUSHER_APP_CLUSTER=ap2
PUSHER_APP_ENCRYPTED=true  # Important for production

# TURN Server Configuration
TURN_SERVER=turn:pm.inovace.in:3478
TURN_USERNAME=webrtcuser
TURN_PASSWORD=strongpassword123  # ⚠️ Currently hardcoded - MOVE TO ENV
TURN_ALTERNATE=turn:pm.inovace.in:3478?transport=tcp

# Voice Call Settings
VOICE_CALL_OFFER_TIMEOUT=30000
VOICE_CALL_CONNECTION_TIMEOUT=60000
VOICE_CALL_ICE_TIMEOUT=15000
```

### 🔐 2. Update voice-call.blade.php for Environment Variables

Replace hardcoded Pusher and TURN credentials:

```php
// BEFORE (Hardcoded):
const pusher = new Pusher("0c08d7f3f0fa0c883f22", {
    cluster: "ap2",
    forceTLS: true
});

const iceServers = [
    { urls: "stun:stun.l.google.com:19302" },
    {
        urls: [
            "turn:pm.inovace.in:3478?transport=udp",
            "turn:pm.inovace.in:3478?transport=tcp"
        ],
        username: "webrtcuser",
        credential: "strongpassword123"
    }
];

// AFTER (From environment config):
const configData = {
    pusherKey: "{{ config('broadcasting.connections.pusher.key') }}",
    pusherCluster: "{{ config('broadcasting.connections.pusher.cluster') }}",
    turnServer: "{{ config('voice-call.turn_server') }}",
    turnUsername: "{{ config('voice-call.turn_username') }}",
    turnPassword: "{{ config('voice-call.turn_password') }}"
};

const pusher = new Pusher(configData.pusherKey, {
    cluster: configData.pusherCluster,
    forceTLS: true
});

const iceServers = [
    { urls: "stun:stun.l.google.com:19302" },
    { urls: "stun:stun1.l.google.com:19302" },  // Fallback
    {
        urls: [
            configData.turnServer + "?transport=udp",
            configData.turnServer + "?transport=tcp"
        ],
        username: configData.turnUsername,
        credential: configData.turnPassword,
        credentialType: "password"
    }
];
```

### 📋 3. Create config/voice-call.php

```php
<?php

return [
    'offer_timeout' => env('VOICE_CALL_OFFER_TIMEOUT', 30000),
    'connection_timeout' => env('VOICE_CALL_CONNECTION_TIMEOUT', 60000),
    'ice_timeout' => env('VOICE_CALL_ICE_TIMEOUT', 15000),
    
    'turn_server' => env('TURN_SERVER', 'turn:pm.inovace.in:3478'),
    'turn_username' => env('TURN_USERNAME', 'webrtcuser'),
    'turn_password' => env('TURN_PASSWORD', 'strongpassword123'),
    
    'stun_servers' => [
        'stun:stun.l.google.com:19302',
        'stun:stun1.l.google.com:19302',
        'stun:stun2.l.google.com:19302',
    ],
    
    'enable_logging' => env('VOICE_CALL_LOGGING', true),
    'log_channel' => env('VOICE_CALL_LOG_CHANNEL', 'single'),
];
```

### 🌐 4. HTTPS Requirement

**⚠️ CRITICAL FOR PRODUCTION:**

getUserMedia() **requires HTTPS**. In development, only localhost:3000 works.

```nginx
# Nginx Configuration
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Disable mixed content warning
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    root /var/www/project-management/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 🔒 5. Enable Pusher Encrypted Channel (Optional but Recommended)

In `broadcasting.connections.pusher`:

```php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,  // Enable encryption
            'useTLS' => true,      // Use TLS
        ],
    ],
],
```

### 🚪 6. Firewall Rules (AWS Security Group / DigitalOcean Firewall)

```
Inbound Rules:
┌────────────────┬──────────┬──────────┬──────────────┐
│ Protocol       │ Port     │ Source   │ Description  │
├────────────────┼──────────┼──────────┼──────────────┤
│ TCP            │ 443      │ 0.0.0.0  │ HTTPS        │
│ TCP            │ 80       │ 0.0.0.0  │ HTTP redirect│
│ TCP/UDP        │ 3478     │ 0.0.0.0  │ TURN server  │
│ TCP            │ 5349     │ 0.0.0.0  │ TURN TLS     │
│ UDP            │ 49152-65535│0.0.0.0 │ WebRTC ICE   │
└────────────────┴──────────┴──────────┴──────────────┘
```

### 📊 7. Logging Configuration

Add to config/logging.php:

```php
'channels' => [
    // ... existing channels ...
    
    'voice_call' => [
        'driver' => 'single',
        'path' => storage_path('logs/voice-call.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

In routes/web.php, add better logging:

```php
Route::post('/send-offer', function (Request $request) {
    \Log::channel('voice_call')->info('Offer received', [
        'caller_id' => auth()->id(),
        'receiver_id' => $request->receiverId,
        'offer_type' => $request->offer['type'] ?? null,
        'sdp_length' => strlen($request->offer['sdp'] ?? ''),
    ]);
    
    // ... rest of code
});

Route::post('/send-answer', function (Request $request) {
    \Log::channel('voice_call')->info('Answer received', [
        'receiver_id' => auth()->id(),
        'caller_id' => $request->receiverId,
        'answer_type' => $request->answer['type'] ?? null,
    ]);
    
    // ... rest of code
});
```

---

## ⚠️ Browser Compatibility

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome  | ✅ Yes  | Full WebRTC + getUserMedia |
| Firefox | ✅ Yes  | Full WebRTC + getUserMedia |
| Safari  | ⚠️ Partial | iOS 14.5+, requires HTTPS |
| Edge    | ✅ Yes  | Same as Chrome |
| IE 11   | ❌ No   | Not supported |

### Safari Specific:
```javascript
// Add Safari compatibility
const getRTCPeerConnection = () => {
    return window.RTCPeerConnection || 
           window.webkitRTCPeerConnection || 
           window.mozRTCPeerConnection;
};

const RTCPeerConnection = getRTCPeerConnection();
```

---

## 🛡️ Rate Limiting (Prevent Abuse)

In `routes/web.php`:

```php
Route::middleware('throttle:10,1')->post('/send-offer', function (Request $request) {
    // Max 10 offers per minute per user
    // ... rest of code
});

Route::middleware('throttle:10,1')->post('/send-answer', function (Request $request) {
    // Max 10 answers per minute per user
    // ... rest of code
});

Route::middleware('throttle:20,1')->post('/send-ice', function (Request $request) {
    // Max 20 ICE candidates per minute per user
    // ... rest of code
});
```

---

## 🔍 Monitoring & Health Checks

Add AJAX health check endpoint:

```php
Route::get('/api/voice-call/health', function () {
    return response()->json([
        'status' => 'ok',
        'pusher_connected' => true,
        'turn_server' => config('voice-call.turn_server'),
        'timestamp' => now(),
    ]);
});
```

In JavaScript, ping regularly:

```javascript
setInterval(async () => {
    try {
        const response = await fetch('/api/voice-call/health');
        if (!response.ok) {
            console.warn('❌ Voice call service unhealthy');
            updateStatus('⚠️ Connection issue - reconnecting...');
        }
    } catch (err) {
        console.error('Health check failed:', err);
    }
}, 30000); // Every 30 seconds
```

---

## 📱 CORS Configuration

In `config/cors.php`:

```php
'paths' => ['api/*', 'voice-call/*', 'send-*'],
'allowed_methods' => ['*'],
'allowed_origins' => [
    env('APP_URL'),
    // Allow your production domain
],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 86400,
'supports_credentials' => true,
```

---

## 🚀 Deployment Checklist

- [ ] Move all hardcoded keys to .env
- [ ] Enable HTTPS (SSL certificate)
- [ ] Configure TURN server credentials
- [ ] Update Pusher keys for production
- [ ] Configure firewall rules
- [ ] Set up logging channels
- [ ] Enable rate limiting
- [ ] Test from multiple browsers
- [ ] Test on mobile devices
- [ ] Monitor logs for errors
- [ ] Set up health checks
- [ ] Document API endpoints
- [ ] Backup encryption keys


---

## 🧪 Testing in Production

```bash
# 1. Test HTTPS
curl -I https://your-domain.com

# 2. Test Pusher connectivity
# Open browser console and check: Pusher.log = function(msg) { console.log('PUSHER:', msg); }

# 3. Test TURN server
# Can use online tools like: https://webrtc.github.io/samples/src/content/peerconnection/trickle-ice/

# 4. Check logs
tail -f storage/logs/voice-call.log

# 5. Monitor Pusher
# Go to https://dashboard.pusher.com and check active connections
```

---

## 🆘 Troubleshooting Production Issues

| Issue | Solution |
|-------|----------|
| No answer received | Check receiver's browser console, verify Pusher connected |
| Audio not working | Verify microphone permissions, check TURN server accessibility |
| High latency | Use regional TURN server closer to users |
| Dropped calls | Check network stability, increase timer values |
| Memory leak | Monitor browser memory, check for unclosed peer connections |

---

## Summary

✅ Move credentials to environment variables  
✅ Enable HTTPS certificate  
✅ Configure TURN server properly  
✅ Set up proper logging  
✅ Add rate limiting  
✅ Configure firewall rules  
✅ Test across browsers  
✅ Monitor health metrics  

**Once configured, test thoroughly before going live!**
