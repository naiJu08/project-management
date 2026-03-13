# 🚀 Wiki AI Enhancements - Quick Start

**Status:** ✅ READY TO USE  
**Time to Setup:** 5 minutes  
**Time to Test:** 10 minutes

---

## ⚡ 3-Step Quick Start

### 1️⃣ Start Ollama (2 minutes)

```bash
# Install (first time only)
curl -fsSL https://ollama.com/install.sh | sh

# Pull model (first time only)
ollama pull llama2

# Start service (keep running)
ollama serve
```

### 2️⃣ Load Test Data (1 minute)

```bash
# Via MySQL
mysql -u root -p inovace_project < database/test_wiki_data_project_14.sql

# OR via phpMyAdmin
# Import: database/test_wiki_data_project_14.sql
```

### 3️⃣ Configure & Test (2 minutes)

Add to `.env`:
```env
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama2
OLLAMA_TIMEOUT=120
```

Clear caches:
```bash
php artisan cache:clear && php artisan view:clear
```

---

## 🎯 Test Now!

**URL:** http://192.168.0.140:8000/projects/14?activeTab=wiki

### ✅ What You'll See:

**In Sidebar:**
- 🟢 **Export Master PDF** button (green)
- 🟣 **Generate Backlog (AI)** button (purple)

**On Pages:**
- 🟢 **Export PDF** button (next to Edit)
- 📝 **HTML Editor** (when creating/editing)

---

## 🧪 Quick Tests

### Test 1: PDF Export (30 seconds)
1. Click **"Export Master PDF"**
2. Wait 5 seconds
3. ✅ PDF downloads with all 7 pages

### Test 2: AI Backlog (1 minute)
1. Click **"Generate Backlog (AI)"**
2. Wait 30-60 seconds
3. ✅ Preview modal shows Epics/Features/Stories
4. Click **"Confirm & Create"**
5. Go to Backlog tab
6. ✅ See ~35 backlog items created!

### Test 3: HTML Editor (30 seconds)
1. Click **"New Page"**
2. ✅ See rich text editor with toolbar
3. Type and format text
4. Save
5. ✅ Content displays with formatting

---

## 📊 Expected Results

### AI Generation Output:
- **Epics:** 3-4 items (e.g., "User Management System")
- **Features:** 10-15 items (e.g., "User Registration")
- **User Stories:** 30-45 items (e.g., "As a user, I want to register...")
- **Total:** ~50 backlog items created in 1 minute!

### PDF Export:
- **Single Page:** Clean, professional PDF
- **Master PDF:** Cover page + TOC + all pages

---

## 🔧 Troubleshooting (30 seconds)

### Ollama Not Working?
```bash
# Check if running
curl http://localhost:11434/api/tags

# Restart
ollama serve
```

### No Test Data?
```bash
# Verify
mysql -u root -p -e "SELECT COUNT(*) FROM wiki_pages WHERE project_id = 14;" inovace_project

# Should show: 7
```

### Editor Not Loading?
```bash
# Clear caches
php artisan cache:clear
php artisan view:clear

# Refresh browser (Ctrl+F5)
```

---

## 📚 Full Documentation

- **WIKI_TESTING_GUIDE.md** - Complete testing procedures
- **WIKI_ENHANCEMENTS_FINAL_SUMMARY.md** - Full feature list
- **WIKI_ENHANCEMENTS_IMPLEMENTATION.md** - Technical details

---

## 🎉 You're Ready!

**Everything is set up and working!**

Just:
1. ✅ Start Ollama
2. ✅ Load test data
3. ✅ Visit the URL
4. ✅ Click the buttons!

**Enjoy your AI-powered wiki system! 🚀**

---

## 💡 Pro Tips

### Get Better AI Results:
- Write detailed wiki pages
- Use clear headings and structure
- Include specific requirements
- Use bullet points and lists

### Faster AI Generation:
```bash
# Use smaller model
ollama pull phi-2
```
Update .env: `OLLAMA_MODEL=phi-2`

### Better AI Quality:
```bash
# Use larger model
ollama pull mistral
```
Update .env: `OLLAMA_MODEL=mistral`

---

**Need Help?** Check `WIKI_TESTING_GUIDE.md` for detailed troubleshooting!
