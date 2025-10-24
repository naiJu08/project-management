#!/bin/bash

echo "🚀 Configure Mistral for AI Backlog Generation"
echo "=============================================="
echo ""

# Check if ollama is running
if ! curl -s http://localhost:11434/api/tags > /dev/null 2>&1; then
    echo "❌ Ollama is not running!"
    echo "Please start Ollama first: ollama serve"
    exit 1
fi

echo "✅ Ollama is running"
echo ""

# Check if Mistral is available
if ollama list | grep -q "mistral"; then
    echo "✅ Mistral model found"
else
    echo "❌ Mistral model not found"
    echo "Installing Mistral..."
    ollama pull mistral:7b
fi

echo ""

# Update .env
echo "📝 Updating .env configuration..."

if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    exit 1
fi

# Add or update OLLAMA_MODEL
if grep -q "OLLAMA_MODEL" .env; then
    sed -i 's/OLLAMA_MODEL=.*/OLLAMA_MODEL=mistral:7b/' .env
    echo "✅ Updated OLLAMA_MODEL to mistral:7b"
else
    echo "" >> .env
    echo "# Ollama Configuration" >> .env
    echo "OLLAMA_MODEL=mistral:7b" >> .env
    echo "✅ Added OLLAMA_MODEL=mistral:7b"
fi

# Add or update OLLAMA_TIMEOUT
if grep -q "OLLAMA_TIMEOUT" .env; then
    sed -i 's/OLLAMA_TIMEOUT=.*/OLLAMA_TIMEOUT=900/' .env
    echo "✅ Updated OLLAMA_TIMEOUT to 900 (15 minutes)"
else
    echo "OLLAMA_TIMEOUT=900" >> .env
    echo "✅ Added OLLAMA_TIMEOUT=900"
fi

# Add or update OLLAMA_BASE_URL if not present
if ! grep -q "OLLAMA_BASE_URL" .env; then
    echo "OLLAMA_BASE_URL=http://localhost:11434" >> .env
    echo "✅ Added OLLAMA_BASE_URL"
fi

echo ""
echo "🧹 Clearing caches..."
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo "✅ Caches cleared"

echo ""
echo "✅ Configuration Complete!"
echo ""
echo "📋 Current Configuration:"
echo "========================"
grep "OLLAMA" .env

echo ""
echo "📋 Next Steps:"
echo "=============="
echo ""
echo "1. Start queue worker with LONGER timeout (Mistral needs more time):"
echo "   php artisan queue:work --tries=1 --timeout=1000"
echo ""
echo "2. Test AI generation:"
echo "   - Open: http://192.168.0.140:8000/projects/14?activeTab=wiki"
echo "   - Click 'Generate Backlog (AI)'"
echo "   - Wait 8-12 minutes (Mistral is thorough!)"
echo ""
echo "⏰ Expected time with Mistral: 8-12 minutes"
echo "⭐ Quality: EXCELLENT (best available)"
echo ""
echo "💡 TIP: Mistral is slower but produces the BEST quality backlog!"
echo ""
