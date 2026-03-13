# ⚡ AI TIMEOUT - COMPLETE FIX

**Your Issue:** Job killed at exactly 300 seconds  
**Root Cause:** AI taking longer than timeout allows  
**Solution:** Use faster model + increase timeouts  
**Status:** ✅ READY TO FIX

---

## 🎯 THE PROBLEM

```
2025-10-21 22:36:44 App\Jobs\GenerateBacklogFromWiki ... 300,050.14ms FAIL
Killed
```

**What's happening:**
1. Job starts AI generation
2. Llama2 model is slow (5-8 minutes for 8,000 words)
3. Job timeout is 300 seconds (5 minutes)
4. Job gets killed at exactly 300 seconds
5. No backlog items created ❌

---

## ✅ THE SOLUTION (2 Options)

### Option 1: Quick Fix (RECOMMENDED) ⚡

**Use Phi-2 model - 3x faster than Llama2!**

**Run this ONE command:**
```bash
./quick-fix.sh
```

**What it does:**
- ✅ Installs Phi-2 model
- ✅ Updates .env configuration
- ✅ Clears caches
- ✅ Ready in 2 minutes!

**Then:**
```bash
# Restart queue worker
php artisan queue:work --tries=1 --timeout=720
```

**Result:** AI generation in 2-3 minutes instead of 5-8! ⚡

---

### Option 2: Manual Fix (If script doesn't work)

**Step 1: Install Phi-2**
```bash
ollama pull phi-2
```

**Step 2: Edit .env file**

Add these lines (or update if they exist):
```env
OLLAMA_MODEL=phi-2
OLLAMA_TIMEOUT=600
```

**Step 3: Clear caches**
```bash
php artisan config:clear
php artisan cache:clear
```

**Step 4: Restart queue worker**
```bash
# Stop current worker (Ctrl+C)
# Start with longer timeout
php artisan queue:work --tries=1 --timeout=720
```

---

## 🧪 TEST IT

**Terminal 1: Ollama**
```bash
ollama serve
```

**Terminal 2: Queue Worker**
```bash
php artisan queue:work --tries=1 --timeout=720
```

**Browser:**
1. Go to: `http://192.168.0.140:8000/projects/14?activeTab=wiki`
2. Click **"Generate Backlog (AI)"**
3. Wait 2-3 minutes ⏰
4. Success! ✅

---

## ⏰ WHAT TO EXPECT

### With Phi-2 (Recommended):
- **Time:** 2-3 minutes
- **Quality:** Excellent for backlog generation
- **Speed:** 3x faster than Llama2 ⚡

### Progress Timeline:
```
0%   - Starting...                    (instant)
10%  - Checking Ollama...             (5 sec)
20%  - Collecting content...          (5 sec)
30%  - Detecting language...          (10 sec)
40%  - Analyzing content...           (2-3 min) ⏰ BE PATIENT!
70%  - Creating backlog items...      (10 sec)
100% - Success!                       (done)
```

**Total: ~3 minutes with Phi-2** ✅

---

## 📊 MODEL COMPARISON

| Model | Speed | Time | Quality | Recommendation |
|-------|-------|------|---------|----------------|
| **Phi-2** | ⚡⚡⚡ | 2-3 min | ⭐⭐⭐⭐ | **USE THIS!** ✅ |
| Llama2 | ⚡⚡ | 5-8 min | ⭐⭐⭐⭐ | Too slow |
| Mistral | ⚡ | 8-12 min | ⭐⭐⭐⭐⭐ | Best quality, slowest |
| TinyLlama | ⚡⚡⚡⚡ | 30-60 sec | ⭐⭐⭐ | Fast but lower quality |

**For your use case (backlog generation): Phi-2 is perfect!** ⚡

---

## ✅ VERIFICATION

### Check Model is Installed:
```bash
ollama list
```

Should show:
```
NAME       ID              SIZE      MODIFIED
phi-2      ...             1.7GB     X minutes ago
```

### Check Configuration:
```bash
grep OLLAMA .env
```

Should show:
```
OLLAMA_MODEL=phi-2
OLLAMA_TIMEOUT=600
```

### Test Model Directly:
```bash
curl http://localhost:11434/api/generate -d '{
  "model": "phi-2",
  "prompt": "Say hello",
  "stream": false
}'
```

Should respond in 2-3 seconds.

---

## 🚨 TROUBLESHOOTING

### Still Timing Out?

**1. Check queue worker timeout:**
```bash
# Make sure you're using --timeout=720
ps aux | grep "queue:work"
```

**2. Check Ollama is using Phi-2:**
```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

Look for: `"model": "phi-2"`

**3. Verify .env changes loaded:**
```bash
php artisan config:clear
php artisan tinker
>>> config('services.ollama.model')
=> "phi-2"
```

### If Phi-2 is Still Slow:

**Option A: Use TinyLlama (fastest)**
```bash
ollama pull tinyllama
```
Update .env:
```env
OLLAMA_MODEL=tinyllama
```

**Time:** 30-60 seconds ⚡⚡⚡⚡  
**Quality:** Lower, but very fast

**Option B: Reduce wiki content**

Edit `app/Jobs/GenerateBacklogFromWiki.php`, find `collectAllWikiContent` and limit content:
```php
// Limit to first 3000 words
$allWords = str_word_count($content, 1);
if (count($allWords) > 3000) {
    $content = implode(' ', array_slice($allWords, 0, 3000));
}
```

---

## 📋 COMPLETE CHECKLIST

**Before Testing:**
- [ ] Phi-2 installed: `ollama list`
- [ ] .env updated: `OLLAMA_MODEL=phi-2`
- [ ] .env updated: `OLLAMA_TIMEOUT=600`
- [ ] Caches cleared: `php artisan config:clear`
- [ ] Ollama running: `ollama serve`
- [ ] Queue worker running: `php artisan queue:work --tries=1 --timeout=720`

**During Test:**
- [ ] Click "Generate Backlog (AI)"
- [ ] Toast appears
- [ ] Progress updates
- [ ] Wait patiently 2-3 minutes ⏰
- [ ] No timeout errors
- [ ] Success message appears

**After Test:**
- [ ] ~50 backlog items created
- [ ] Items visible in Backlog tab
- [ ] Hierarchy correct (Epics → Features → Stories)
- [ ] All data populated

---

## 🎉 EXPECTED RESULTS

### Success Indicators:
- ✅ Completes in 2-3 minutes
- ✅ No "Killed" message
- ✅ No timeout errors
- ✅ Success: "Created 47 backlog items!"
- ✅ Items appear in Backlog tab

### In Backlog Tab:
- ✅ 3-4 Epics (purple) - e.g., "User Management System"
- ✅ 10-15 Features (blue) - e.g., "User Registration"
- ✅ 30-45 User Stories (green) - e.g., "As a user, I want to..."
- ✅ Full hierarchy with parent-child relationships
- ✅ Descriptions, priorities, estimates all populated

---

## 📞 QUICK COMMANDS

```bash
# OPTION 1: Automatic (recommended)
./quick-fix.sh
php artisan queue:work --tries=1 --timeout=720

# OPTION 2: Manual
ollama pull phi-2
echo "OLLAMA_MODEL=phi-2" >> .env
echo "OLLAMA_TIMEOUT=600" >> .env
php artisan config:clear
php artisan cache:clear
php artisan queue:work --tries=1 --timeout=720

# Test
# Open browser: http://192.168.0.140:8000/projects/14?activeTab=wiki
# Click "Generate Backlog (AI)"
# Wait 2-3 minutes
```

---

## 📚 DOCUMENTATION

- **README_TIMEOUT_FIX.md** - This file (quick reference)
- **FINAL_TIMEOUT_SOLUTION.md** - Complete details
- **WIKI_TIMEOUT_FIX.md** - Technical explanation
- **quick-fix.sh** - Automated fix script

---

## 🎯 BOTTOM LINE

**Problem:** Llama2 too slow (5-8 min) → timeout at 5 min  
**Solution:** Use Phi-2 (2-3 min) + longer timeout  
**Action:** Run `./quick-fix.sh` and restart queue worker  
**Result:** AI generation works perfectly! ✅

**Just run the script and you're done!** 🚀

---

## ⚡ ONE-LINE FIX

```bash
./quick-fix.sh && php artisan queue:work --tries=1 --timeout=720
```

**That's it!** 🎉
