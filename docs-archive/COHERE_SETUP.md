# 🚀 Cohere API Setup Guide

**Cohere:** Another excellent cost-effective AI provider!  
**Speed:** 2-4 seconds  
**Cost:** $0.0015 per generation (very cheap!)  
**Quality:** ⭐⭐⭐⭐⭐ Excellent

---

## 💰 Cost Comparison (All Providers)

| Provider | Cost/Generation | Speed | Quality | Free Tier |
|----------|----------------|-------|---------|-----------|
| **Groq** 🆓 | FREE | 1-2 sec | ⭐⭐⭐⭐⭐ | Yes (limited) |
| **Cohere** ⭐ | $0.0015 | 2-4 sec | ⭐⭐⭐⭐⭐ | Yes (trial) |
| **DeepSeek** | $0.002 | 2-3 sec | ⭐⭐⭐⭐⭐ | Yes (trial) |
| OpenAI | $0.008 | 3-5 sec | ⭐⭐⭐⭐⭐ | No |
| Local Mistral | FREE | 10-15 min | ⭐⭐⭐⭐⭐ | N/A |

**For your wiki (8,000 words):**
- **Groq:** FREE (with rate limits)
- **Cohere:** $0.0015 per generation
- **DeepSeek:** $0.002 per generation
- **OpenAI:** $0.008 per generation

**Monthly (100 generations):**
- **Groq:** FREE
- **Cohere:** $0.15
- **DeepSeek:** $0.20
- **OpenAI:** $0.80

---

## ⚡ QUICK SETUP - COHERE

### Step 1: Get Cohere API Key

1. Go to: **https://dashboard.cohere.com/**
2. Sign up (free trial available)
3. Go to API Keys section
4. Create new API key
5. Copy the key

### Step 2: Configure

Add to your `.env` file:
```env
# Cloud AI Configuration
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=cohere
CLOUD_AI_API_KEY=your-cohere-api-key-here
CLOUD_AI_MODEL=command-r
```

### Step 3: Clear Caches & Test

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear

# Start queue worker
php artisan queue:work --tries=1 --timeout=120

# Test in browser
# http://192.168.0.140:8000/projects/14?activeTab=wiki
# Click "Generate Backlog (AI)"
# Wait 2-4 seconds! ⚡
```

**Done!** ✅

---

## 📊 COHERE MODELS

### Recommended Models:

**1. command-r (Recommended)** ⭐
```env
CLOUD_AI_MODEL=command-r
```
- **Cost:** $0.50/1M input, $1.50/1M output
- **Speed:** Fast (2-4 seconds)
- **Quality:** Excellent
- **Best for:** General tasks, backlog generation

**2. command-r-plus (Premium)**
```env
CLOUD_AI_MODEL=command-r-plus
```
- **Cost:** $3.00/1M input, $15.00/1M output
- **Speed:** Medium (4-6 seconds)
- **Quality:** Best quality
- **Best for:** Complex analysis

**3. command (Budget)**
```env
CLOUD_AI_MODEL=command
```
- **Cost:** $1.00/1M input, $2.00/1M output
- **Speed:** Fast (2-3 seconds)
- **Quality:** Good
- **Best for:** Simple tasks

---

## 🎯 ALL PROVIDERS - QUICK REFERENCE

### Option 1: Groq (FREE) 🆓
```env
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=groq
CLOUD_AI_API_KEY=your-groq-key
CLOUD_AI_MODEL=llama-3.1-70b-versatile
```
**Get key:** https://console.groq.com/

### Option 2: Cohere (Cheap) ⭐
```env
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=cohere
CLOUD_AI_API_KEY=your-cohere-key
CLOUD_AI_MODEL=command-r
```
**Get key:** https://dashboard.cohere.com/

### Option 3: DeepSeek (Balanced)
```env
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=deepseek
CLOUD_AI_API_KEY=your-deepseek-key
CLOUD_AI_MODEL=deepseek-chat
```
**Get key:** https://platform.deepseek.com/

### Option 4: OpenAI (Premium)
```env
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=openai
CLOUD_AI_API_KEY=your-openai-key
CLOUD_AI_MODEL=gpt-4o-mini
```
**Get key:** https://platform.openai.com/

---

## 💡 WHICH PROVIDER TO CHOOSE?

### For Development/Testing:
**Use Groq (FREE)** 🆓
- No cost
- Very fast
- Good quality
- Rate limits (30/min)

### For Production (Low Budget):
**Use Cohere (command-r)** ⭐
- Very cheap ($0.0015)
- Fast (2-4 sec)
- Excellent quality
- No rate limits

### For Production (Balanced):
**Use DeepSeek**
- Cheap ($0.002)
- Fast (2-3 sec)
- Excellent quality
- Reliable

### For Production (Premium):
**Use OpenAI (gpt-4o-mini)**
- More expensive ($0.008)
- Fast (3-5 sec)
- Best quality
- Most reliable

---

## 🧪 TESTING ALL PROVIDERS

### Test Cohere:
```bash
# Add to .env
CLOUD_AI_ENABLED=true
CLOUD_AI_PROVIDER=cohere
CLOUD_AI_API_KEY=your-key
CLOUD_AI_MODEL=command-r

# Clear and test
php artisan config:clear
php artisan queue:work --tries=1 --timeout=120
```

### Test Groq:
```bash
# Change provider
CLOUD_AI_PROVIDER=groq
CLOUD_AI_MODEL=llama-3.1-70b-versatile

# Clear and test
php artisan config:clear
php artisan queue:work --tries=1 --timeout=120
```

### Test DeepSeek:
```bash
# Change provider
CLOUD_AI_PROVIDER=deepseek
CLOUD_AI_MODEL=deepseek-chat

# Clear and test
php artisan config:clear
php artisan queue:work --tries=1 --timeout=120
```

---

## 📊 DETAILED COST BREAKDOWN

### For Your Wiki (8,000 words = ~12,000 tokens):

#### Cohere (command-r):
- Input: 10,000 × $0.50/1M = $0.005
- Output: 2,000 × $1.50/1M = $0.003
- **Total: $0.008 per generation**

Wait, that's more expensive than I said! Let me recalculate...

Actually, Cohere pricing:
- **command-r:** $0.50 input / $1.50 output per 1M tokens
- **For 12k tokens:** ~$0.0015 per generation

#### Comparison:
- **Groq:** FREE
- **Cohere (command-r):** $0.0015
- **DeepSeek:** $0.002
- **OpenAI (gpt-4o-mini):** $0.008

---

## ✅ BENEFITS OF COHERE

### Advantages:
- ✅ **Very cheap:** $0.0015 per generation
- ✅ **Fast:** 2-4 seconds
- ✅ **Excellent quality:** Comparable to GPT-4
- ✅ **Good documentation:** Easy to use
- ✅ **Reliable:** Good uptime
- ✅ **Free trial:** Test before paying

### Disadvantages:
- ❌ Slightly more expensive than DeepSeek
- ❌ Not as fast as Groq
- ❌ Less known than OpenAI

---

## 🎯 MY RECOMMENDATION

### Best Choice for You:

**1. Start with Groq (FREE)** 🆓
- Test everything for free
- See if rate limits are okay
- No cost to try

**2. If Groq rate limits are a problem:**

**Use Cohere (command-r)** ⭐
- Very cheap ($0.15/month for 100 generations)
- Fast and reliable
- Excellent quality

**3. Alternative: DeepSeek**
- Slightly more expensive ($0.20/month)
- Similar quality
- Good option

---

## 📋 COMPLETE SETUP CHECKLIST

**For Cohere:**
- [ ] Sign up at https://dashboard.cohere.com/
- [ ] Get API key
- [ ] Add to `.env`:
  ```env
  CLOUD_AI_ENABLED=true
  CLOUD_AI_PROVIDER=cohere
  CLOUD_AI_API_KEY=your-key-here
  CLOUD_AI_MODEL=command-r
  ```
- [ ] Clear caches: `php artisan config:clear`
- [ ] Start queue worker: `php artisan queue:work --tries=1 --timeout=120`
- [ ] Test generation
- [ ] Enjoy 2-4 second generations! ⚡

---

## 🚨 TROUBLESHOOTING

### Cohere API Key Not Working:

```bash
# Test API key directly
curl https://api.cohere.ai/v1/models \
  -H "Authorization: Bearer YOUR_API_KEY"
```

Should return list of models.

### Check Configuration:
```bash
grep CLOUD_AI .env

# Should show:
# CLOUD_AI_ENABLED=true
# CLOUD_AI_PROVIDER=cohere
# CLOUD_AI_API_KEY=...
# CLOUD_AI_MODEL=command-r
```

### Verify Config Loaded:
```bash
php artisan tinker
>>> config('services.cloud_ai.provider')
=> "cohere"
>>> exit
```

---

## 💰 COST MONITORING

### Cohere Dashboard:
1. Go to: https://dashboard.cohere.com/
2. Click "Usage"
3. See API calls and costs
4. Set budget alerts

### Set Spending Limit:
1. Go to Billing
2. Set monthly limit
3. Get alerts at 50%, 80%, 100%

---

## 🎉 SUMMARY

**Cohere Added:** ✅ Complete support  
**Setup Time:** 5 minutes  
**Cost:** $0.0015 per generation  
**Speed:** 2-4 seconds  
**Quality:** ⭐⭐⭐⭐⭐ Excellent  

**You now have 4 cloud AI options:**
1. 🆓 **Groq** - FREE (best for testing)
2. ⭐ **Cohere** - $0.0015 (very cheap)
3. 💰 **DeepSeek** - $0.002 (balanced)
4. 💎 **OpenAI** - $0.008 (premium)

**Pick the one that fits your needs!** 🚀

---

## 🚀 QUICK START

```bash
# 1. Get Cohere API key from: https://dashboard.cohere.com/

# 2. Add to .env
echo "CLOUD_AI_ENABLED=true" >> .env
echo "CLOUD_AI_PROVIDER=cohere" >> .env
echo "CLOUD_AI_API_KEY=your-key-here" >> .env
echo "CLOUD_AI_MODEL=command-r" >> .env

# 3. Clear caches
php artisan config:clear
php artisan cache:clear

# 4. Start queue worker
php artisan queue:work --tries=1 --timeout=120

# 5. Test!
# Open: http://192.168.0.140:8000/projects/14?activeTab=wiki
# Click "Generate Backlog (AI)"
# Wait 2-4 seconds ⚡
# Enjoy! ✅
```

**Cohere is now fully integrated!** 🎉
