#!/bin/bash

echo "🚀 Quick Fix for AI Timeout"
echo "============================"
echo ""
echo "This will:"
echo "1. Install Phi-2 (faster AI model)"
echo "2. Update your configuration"
echo "3. Clear caches"
echo ""

# Check if ollama is running
if ! curl -s http://localhost:11434/api/tags > /dev/null 2>&1; then
    echo "❌ Ollama is not running!"
    echo "Please start Ollama first: ollama serve"
    exit 1
fi

echo "✅ Ollama is running"
echo ""

# Pull Phi-2 model
echo "📥 Installing Phi-2 model (this may take a few minutes)..."
ollama pull phi-2

if [ $? -eq 0 ]; then
    echo "✅ Phi-2 installed successfully"
else
    echo "❌ Failed to install Phi-2"
    exit 1
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
    sed -i 's/OLLAMA_MODEL=.*/OLLAMA_MODEL=phi-2/' .env
    echo "✅ Updated OLLAMA_MODEL to phi-2"
else
    echo "OLLAMA_MODEL=phi-2" >> .env
    echo "✅ Added OLLAMA_MODEL=phi-2"
fi

# Add or update OLLAMA_TIMEOUT
if grep -q "OLLAMA_TIMEOUT" .env; then
    sed -i 's/OLLAMA_TIMEOUT=.*/OLLAMA_TIMEOUT=600/' .env
    echo "✅ Updated OLLAMA_TIMEOUT to 600"
else
    echo "OLLAMA_TIMEOUT=600" >> .env
    echo "✅ Added OLLAMA_TIMEOUT=600"
fi

echo ""
echo "🧹 Clearing caches..."
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo "✅ Caches cleared"

echo ""
echo "✅ All Done!"
echo ""
echo "📋 Next Steps:"
echo "=============="
echo ""
echo "1. Start queue worker with longer timeout:"
echo "   php artisan queue:work --tries=1 --timeout=720"
echo ""
echo "2. Test AI generation:"
echo "   - Open: http://192.168.0.140:8000/projects/14?activeTab=wiki"
echo "   - Click 'Generate Backlog (AI)'"
echo "   - Wait 2-3 minutes (Phi-2 is much faster!)"
echo ""
echo "⏰ Expected time with Phi-2: 2-3 minutes (vs 5-8 with Llama2)"
echo ""
echo "📖 For more details, see: FINAL_TIMEOUT_SOLUTION.md"
echo ""
