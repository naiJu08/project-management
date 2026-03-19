# Voice Call Diagnosis - Broadcast Test Guide

## 🔍 The Issue

Your logs confirm:
- ✅ Backend receives offers correctly  
- ✅ Logs show offers with proper structure
- ❌ **Frontend NOT receiving offers via Pusher**

The offer is being received but NOT broadcast to the receiver's browser.

---

## 🧪 Step 1: Test Basic Broadcast

This test will verify if **Pusher broadcasting works at all**.

### On Receiver's Browser:

1. Open voice-call page
2. Press **F12** to open DevTools Console
3. Click the **"🧪 Test"** button in the middle of the screen
4. **Immediately** look for this log in console:

```
✅ TEST MESSAGE RECEIVED!
✅ Test message: Test message from [Caller Name]
✅ BROADCAST SYSTEM IS WORKING!
```

### Test Results Interpretation:

✅ **If you see "TEST MESSAGE RECEIVED!":**
- Pusher broadcast system works correctly ✅
- Problem is NOT with broadcasting
- It's specifically a CallOffer event issue

❌ **If you DON'T see test message:**
- Pusher broadcast is broken
- Need to check Pusher credentials or network

---

## 🔧 If Test Shows Broadcast Works

If test message arrives but CallOffer doesn't, check:

### 1. **Check Backend Logs**
```bash
tail -f storage/logs/laravel-2026-03-19.log | grep "BROADCAST"
```

You should see:
```
[TIMESTAMP] BROADCAST: About to broadcast CallOffer event
[TIMESTAMP] BROADCAST: CallOffer event broadcasted successfully
```

**If you don't see "About to broadcast" - backend route has issue**

### 2. **Check Frontend Event Binding**

In browser console, check if CallOffer handler is registered:

```javascript
// In receiver's voice-call console, type:
console.log("Channel name:", channel.name);
console.log("Channels subscribed:", Object.keys(pusher.channels));
console.log("Events on channel:", channel.callbacks);
```

This will show all events bound to the channel.

### 3. **Test with Different User**

Try calling from **different user account** to rule out self-calling issue:
- User 2 calls User 10
- Check User 10's console for offer

---

## 🆘 If Broadcast Test FAILS

If test message doesn't arrive, the broadcasting system is broken.

### **Option 1: Check Network**
```bash
# In browser console:
console.log("Pusher connected?", pusher.connection.state);
console.log("Socket ID:", pusher.connection.socket_id);
console.log("Channel subscribed?", channel.state);
```

### **Option 2: Check Server Logs**
```bash
tail -f storage/logs/laravel-2026-03-19.log
```

Look for any errors related to Pusher or broadcasting.

### **Option 3: Verify Pusher Credentials**

Check that `.env` has ALL these set:
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2128840
PUSHER_APP_KEY=0c08d7f3f0fa0c883f22
PUSHER_APP_SECRET=f156493fd377aa7754c8
PUSHER_APP_CLUSTER=ap2
```

If changed, run:
```bash
cd /var/www/html/project-management
php artisan cache:clear
php artisan config:clear
```

### **Option 4: Check Pusher Dashboard**
1. Go to https://dashboard.pusher.com
2. Login with your credentials
3. Check your app
4. Should show "Active connections: 2" (caller + receiver)
5. Check if messages are being received

### **Option 5: Test Manual Broadcasting**

Create a simple test:
```php
// In routes/web.php
Route::get('/test-pusher', function() {
    broadcast(new \App\Events\TestBroadcast(2, "Direct test"))->toOthers();
    return "Message sent to user 2";
});
```

Then visit: `https://your-domain.com/test-pusher` and check if receiver gets message.

---

## 📋 Comprehensive Diagnostic Checklist

Run through these checks:

```
STEP 1: Test Basic Broadcast
□ Receiver opens voice-call page
□ Receiver clicks "🧪 Test" button
□ Receiver checks console for "TEST MESSAGE RECEIVED"

STEP 2: Check Server Logs
□ tail -f storage/logs/laravel-2026-03-19.log
□ Make a call
□ Look for "CallOffer received" log
□ Look for "BROADCAST: About to broadcast"
□ Look for "BROADCAST: Successfully"

STEP 3: Check Pusher Connection
□ Browser console: pusher.connection.state should be "connected"
□ Browser console: channel.state should be "subscribed"
□ No errors in browser console

STEP 4: Check Frontend Binding
□ CallOffer event handler should exist
□ No JavaScript errors in console

STEP 5: Check .env
□ BROADCAST_DRIVER=pusher
□ PUSHER_APP_KEY set
□ PUSHER_APP_SECRET set
□ PUSHER_APP_ID set
```

---

## 🚀 Manual Debugging Commands

### In Browser Console:

```javascript
// Check Pusher state
console.log("=== PUSHER STATE ===");
console.log("Connected:", pusher.connection.state);  // Should be "connected"
console.log("Socket ID:", pusher.connection.socket_id);
console.log("Channel name:", channel.name);
console.log("Channel state:", channel.state);  // Should be "subscribed"
console.log("Auth status:", channel.auth_status);

// Try manual event
console.log("=== MANUAL EVENT TEST ===");
channel.emit('TestBroadcast', {message: 'Manual test'});
console.log("Manual event sent");
```

### In Server Terminal:

```bash
# Check Laravel logs in real-time
tail -f storage/logs/laravel-2026-03-19.log | grep -i "broadcast\|offer"

# Check if Pusher credentials are set
grep PUSHER .env

# Test artisan command
php artisan tinker
# Then in tinker:
broadcast(new App\Events\TestBroadcast(2, "Test"));
exit;
```

---

## 🔄 Alternative: Use Private Channels

If public channels aren't working, try private channels:

### In app/Events/CallOffer.php:
```php
use Illuminate\Broadcasting\PrivateChannel;

public function broadcastOn()
{
    return new PrivateChannel('voice-call.' . $this->receiverId);
}
```

Then configure channel authorization in `routes/channels.php`:
```php
Broadcast::channel('voice-call.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
```

---

## ✅ Success Criteria

When everything works:

```
✅ Test message arrives immediately when clicking "🧪 Test"
✅ CallOffer arrives when clicking "Start Call"  
✅ Both use same 'voice-call.X' channel name
✅ Browser shows no errors
✅ Server logs show "BROADCAST: Successfully"
✅ Connection established after answering
✅ Audio flows both directions
```

---

## 📞 Next Steps

1. **Click "🧪 Test" button** and tell me if you see the test message
2. **Share server logs** showing what happens during call
3. **Share browser console logs** from both sides
4. Based on results, I'll provide specific fixes

The test button will tell us exactly where the system breaks! 🎯
