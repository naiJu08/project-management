# 📞 Testing Voice Call Connection - Step by Step

## Your Current Status
✅ **Notification: Working** - Receiver gets CallOffer in user-chat  
✅ **Window Auto-Open: Working** - Receiver's voice-call window opens  
❌ **Offer Delivery: NOT Working** - Receiver's voice-call page not getting offer  

**Goal:** Get the offer from caller's voice-call page to receiver's voice-call page via Pusher.

---

## 🧪 Test Scenario Setup

Use **User 2 (INOVACE)** as **Caller** and **User 10** as **Receiver**:

```
Browser 1 (User 2):  Chat Page Open + Will Click "Call User 10"
Browser 2 (User 10): Chat Page Open + Will Receive Notification
```

---

## 🔄 Step-by-Step Testing

### **STEP 1: Prepare Both Browsers**

**Browser 1 (Caller - User 2):**
1. Open chat page
2. Press **F12** → Console
3. Clear existing logs
4. Keep this open

**Browser 2 (Receiver - User 10):**
1. Open chat page
2. Press **F12** → Console
3. Clear existing logs
4. Keep this open

---

### **STEP 2: Verify Caller's Chat Pusher is Ready**

In **Browser 1 (Caller's Chat) Console**, you should see:
```
✅ Pusher INIT in user-chat
✅ Subscribed to voice-call.2
```

**If you don't see this:**
- Pusher library didn't load
- Check Network tab for https://js.pusher.com/7.2/pusher.min.js
- If 404: CDN issue

---

### **STEP 3: Verify Receiver's Chat Pusher is Ready**

In **Browser 2 (Receiver's Chat) Console**, you should see:
```
✅ Pusher INIT in user-chat
✅ Subscribed to voice-call.10
```

**If you don't see this:**
- Receiver's Pusher not connected
- Open user-chat.blade.php and check Pusher script

---

### **STEP 4: Caller Clicks "Call"**

**In Browser 1:**
1. Find User 10 in chat
2. Click **"Call"** button
3. **Immediately** new window opens

**In Browser 1 (New Voice-Call Window) Console**, you should see:
```
✅ JS LOADED
👤 DIAGNOSTIC: Current user (userId): 2
👤 DIAGNOSTIC: Other user (otherUserId): 10
📍 DIAGNOSTIC: Voice-call page URL: http://localhost/voice-call/10?mode=caller
📍 DIAGNOSTIC: Will subscribe to channel: voice-call.10

🔄 DIAGNOSTIC: Subscribing to 'voice-call.10'...
✅ PUSHER CONNECTED
🔌 DIAGNOSTIC: Pusher WebSocket connected at 10:30:45
📱 Page mode: CALLER - ready to initiate call
📱 DIAGNOSTIC: This is CALLER mode - will call user 10
✅ CHANNEL SUBSCRIBED: voice-call.10
🎧 Listening for call offers on channel: voice-call.10
📢 DIAGNOSTIC: Ready to receive events on this channel
```

**CRITICAL CHECKS:**
- ✅ userId shows **2** (caller ID)
- ✅ otherUserId shows **10** (receiver ID)
- ✅ Channel subscription shows **voice-call.10**
- ❌ If anything is wrong, the connection is broken

---

### **STEP 5: Receiver Get Notification in Chat**

**In Browser 2 (Receiver's Chat) Console**, you should see:
```
📞 INCOMING CALL from user 2: INOVACE
📞 INCOMING CALL auto-opened voice window
```

**Then a new window opens for receiver.**

---

### **STEP 6: Verify Receiver's Voice-Call Page Initialized**

The receiver's new voice-call window should load. Check its console:

**EXPECTED LOG:**
```
✅ JS LOADED
👤 DIAGNOSTIC: Current user (userId): 10
👤 DIAGNOSTIC: Other user (otherUserId): 2
📍 DIAGNOSTIC: Voice-call page URL: http://localhost/voice-call/2
📍 DIAGNOSTIC: Will subscribe to channel: voice-call.10

🔄 DIAGNOSTIC: Subscribing to 'voice-call.10'...
✅ PUSHER CONNECTED
🔌 DIAGNOSTIC: Pusher WebSocket connected at 10:30:47
📱 Page mode: RECEIVER - waiting for incoming call
📱 DIAGNOSTIC: This is RECEIVER mode - expecting voice-call from user 2
📱 DIAGNOSTIC: Will receive offer on channel 'voice-call.10'
✅ CHANNEL SUBSCRIBED: voice-call.10
🎧 Listening for call offers on channel: voice-call.10
📢 DIAGNOSTIC: Ready to receive events on this channel
```

**CRITICAL CHECKS:**
- ✅ userId shows **10** (receiver's own ID)
- ✅ otherUserId shows **2** (caller's ID that page came from)
- ✅ Channel subscription shows **voice-call.10** ← **SAME AS CALLER**
- ✅ Page mode shows **RECEIVER**

**If userId is wrong (not 10), the connection won't work!**

---

### **STEP 7: Caller Clicks "Start Call"**

**In Browser 1 (Caller's Voice-Call Window):**
1. Click **"Start Call"** button
2. Browser asks for microphone permission
3. **Allow** microphone access

**In Browser 1 Console**, you should see:
```
🚀 START BUTTON CLICKED
📊 STATUS: Microphone accessed, creating offer...
🧠 Creating Peer
📊 STATUS: Setting up connection...
📊 STATUS: Notifying recipient and waiting for connection...
📢 Offer created, waiting 2s for receiver to connect to Pusher...
📡 Sending ICE
📊 STATUS: Sending offer to recipient...
✅ Offer sent successfully
📊 STATUS: Offer sent, waiting for answer...
```

---

### **STEP 8: THIS IS THE CRITICAL MOMENT**

**Now check Browser 2 (Receiver's Voice-Call Window) Console for:**

```
📞 ============ OFFER RECEIVED ============
📞 Event fired at: 10:30:50
📞 Full data object keys: ['offer','callerId','callerName','receiverId']
📞 Full data: {...}
📞 DIAGNOSTIC: This event is firing on receiver's voice-call page!
📞 Caller ID: 2 | My ID: 10
📞 Offer structure: {type: "offer", sdp: "v=0\r\n...", hasType: true, hasSdp: true}
📞 Stored offer and caller ID - ready to accept
📞 incomingOffer is now: SET ✅
📞 incomingCallerId is now: SET ✅
📞 Switching to accept mode...
📞 ✅ ACCEPT MODE - Ready to accept call
```

**If you see this:** ✅ **CONNECTION ESTABLISHED - MOVE TO STEP 9**

**If you DON'T see "OFFER RECEIVED":** ❌ **Skip to Troubleshooting Section Below**

---

### **STEP 9: Receiver Clicks "Accept Call"**

**In Browser 2 (Receiver's Voice-Call Window):**
1. You should see **"Accept Call"** button
2. Click it
3. Browser asks for microphone permission
4. **Allow** microphone access

**In Browser 2 Console**, you should see:
```
✅ ACCEPT CLICKED
🧠 Creating Peer
📊 STATUS: Requesting microphone access...
📊 STATUS: Microphone accessed, creating answer...
🧠 Creating Peer
📊 STATUS: Setting up connection...
🔄 About to set remote description with: {type: "offer", ...}
✅ Remote description set
📊 STATUS: Processing offer, creating answer...
✅ Creating answer...
📤 Sending answer...
📤 Send answer response status: 200
📤 Sending answer to caller: 2
✅ Answer sent successfully
✅ Answer sent to receiverId: 2
📊 STATUS: Answer sent, establishing connection...
```

---

### **STEP 10: Both Pages Should Connect**

**In both consoles**, you should eventually see:
```
❄ ICE Connection State: checking
❄ ICE Connection State: connected
🔗 Connection State: connected
🔊 AUDIO RECEIVED
```

**Then both show:**
```
✅ **Audio flowing both directions!**
```

---

## 🆘 Troubleshooting Scenarios

### **SCENARIO A: Receiver Window Opened But No "OFFER RECEIVED" Log**

**Problem:** Receiver's voice-call page doesn't see the offer event.

**Diagnosis Checklist:**

1. **Check receiver's userId:**
   ```javascript
   // In receiver console:
   console.log(userId);  // Should be 10
   console.log(otherUserId);  // Should be 2
   ```
   
2. **Check subscription channel:**
   ```javascript
   // In receiver console:
   console.log("Subscribed to channel: voice-call." + userId);
   ```
   Should show: `voice-call.10` ✅

3. **Check if same Pusher key:**
   Both windows should use same Pusher key: `0c08d7f3f0fa0c883f22`

4. **Check backend is sending to correct channel:**
   The backend should broadcast to: `voice-call.{receiverId}` = `voice-call.10`

**Possible Fixes:**

1. Check auth()->id() in voice-call.blade.php returns correct user:
   ```blade
   {{ auth()->id() }}  // Should be 10 in receiver window
   ```

2. Check $user->id in route is correct:
   ```php
   Route::get('/voice-call/{id}', function ($id) {
       $user = User::find($id);  // $user should be the other person
       return view('voice-call', compact('user'));
   });
   ```

3. Verify Pusher channel in routes/web.php:
   ```php
   broadcast(new CallOffer($offer, auth()->id(), $user->name, $request->receiverId))
       ->toOthers();
   // Should broadcast to: 'voice-call.' . $request->receiverId
   ```

---

### **SCENARIO B: "Offer Received" But Still Waiting (Not Switching to Accept Mode)**

**Problem:** Offer arrived but "Accept Call" button never showed.

**Check console for errors before logging:**
```javascript
console.error()  // Any errors?
```

**Likely cause:** `showAcceptMode()` function failed.

**Fix:**
```javascript
// In receiver console, manually test:
const acceptBtn = document.getElementById("acceptBtn");
console.log("Button exists?", acceptBtn !== null);
console.log("Can I set display?", acceptBtn.style.display = "block");
```

---

### **SCENARIO C: Accept Clicked But No Answer Sent**

**Problem:** "ACCEPT CLICKED" appears but then error.

**Check console for the specific error:**
```
❌ Error setting remote description: ...
❌ Error sending answer: ...
```

**Fix:**
1. Make sure `incomingOffer` has both `type` and `sdp`
2. Make sure browser allows microphone
3. Verify TURN server is reachable

---

## 📊 Diagnostic Commands (Paste in Console)

### **Test Receiver's State:**
```javascript
console.log("=== RECEIVER DIAGNOSTIC ===");
console.log("userId:", userId);
console.log("otherUserId:", otherUserId);
console.log("incomingOffer:", incomingOffer);
console.log("incomingCallerId:", incomingCallerId);
console.log("peerConnection:", peerConnection);
console.log("localStream:", localStream);
console.log("callActive:", callActive);
```

### **Test Button Visibility:**
```javascript
const startBtn = document.getElementById("startBtn");
const acceptBtn = document.getElementById("acceptBtn");
console.log("Start btn visible?", startBtn.style.display !== "none");
console.log("Accept btn visible?", acceptBtn.style.display !== "none");
```

### **Test Pusher Channel:**
```javascript
console.log("Channel name should be: voice-call." + userId);
console.log("Actual subscription:", channel.name);
```

---

## ✅ Success Checklist

```
When everything works:

□ Caller console shows: "✅ Offer sent successfully"
□ Receiver console shows: "📞 ============ OFFER RECEIVED ============"
□ Receiver console shows: "📞 ✅ ACCEPT MODE - Ready to accept call"
□ Receiver sees "Accept Call" button on screen
□ Receiver can click "Accept Call"
□ Both consoles show: "✅ Answer sent successfully"
□ Both consoles show: "🔊 AUDIO RECEIVED"
□ Audio flows both directions ✅
```

---

## 📱 Testing Variations

### **Same User, 2 Tabs:**
1. Auth as User 2 in Tab A (Chat Open)
2. Auth as User 2 in Tab B (Chat Open)
3. Click call in Tab A
4. Accept in Tab B

### **Same User, 2 Browsers:**
1. Browser #1: Auth as User 2, Chat Open
2. Browser #2: Auth as User 2, Chat Open
3. Click call in Browser #1
4. Accept in Browser #2

### **Different Users (Real Test):**
1. Browser #1: Auth as User 2
2. Browser #2: Auth as User 10
3. Call from User 2 to User 10
4. Accept from User 10

---

## 🚀 Next Steps

1. **Run STEP 1-6** and share the console logs
2. **Tell me which step fails** (offer not received?)
3. **Share the diagnostic values** (userId, otherUserId, channel names)
4. **I'll provide specific fixes** based on what's wrong

The logs with these improvements will make it crystal clear where the connection is breaking!
