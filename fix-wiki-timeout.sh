#!/bin/bash

# Wiki AI Timeout Fix Script
# This script applies the timeout fix for Ollama AI generation

echo "🔧 Wiki AI Timeout Fix"
echo "======================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "Please create .env file first."
    exit 1
fi

# Check if OLLAMA_TIMEOUT already exists
if grep -q "OLLAMA_TIMEOUT" .env; then
    echo "✅ OLLAMA_TIMEOUT already exists in .env"
    echo "Current value:"
    grep "OLLAMA_TIMEOUT" .env
    echo ""
    read -p "Do you want to update it to 300? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        sed -i 's/OLLAMA_TIMEOUT=.*/OLLAMA_TIMEOUT=300/' .env
        echo "✅ Updated OLLAMA_TIMEOUT to 300"
    fi
else
    echo "➕ Adding OLLAMA_TIMEOUT to .env..."
    echo "" >> .env
    echo "# Ollama AI Timeout (seconds)" >> .env
    echo "OLLAMA_TIMEOUT=300" >> .env
    echo "✅ Added OLLAMA_TIMEOUT=300 to .env"
fi

echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
echo "✅ Caches cleared"

echo ""
echo "📋 Current Ollama Configuration:"
echo "================================"
grep "OLLAMA" .env

echo ""
echo "✅ Fix Applied Successfully!"
echo ""
echo "📝 Next Steps:"
echo "1. Restart your queue worker:"
echo "   php artisan queue:work --tries=1 --timeout=360"
echo ""
echo "2. Make sure Ollama is running:"
echo "   ollama serve"
echo ""
echo "3. Test AI generation:"
echo "   http://192.168.0.140:8000/projects/14?activeTab=wiki"
echo "   Click 'Generate Backlog (AI)'"
echo ""
echo "⏰ Expected time: 3-5 minutes (this is normal!)"
echo "📊 Progress will pause at 40% while AI works"
echo ""
echo "📖 For more details, see: WIKI_TIMEOUT_FIX.md"
echo ""
