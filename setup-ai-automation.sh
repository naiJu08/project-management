#!/bin/bash

echo "🚀 Setting up AI Automation for Project Management"
echo "=================================================="
echo ""

# Check if Ollama is running
echo "1️⃣ Checking Ollama..."
if ! curl -s http://localhost:11434/api/tags > /dev/null; then
    echo "❌ Ollama is not running!"
    echo "   Start it with: ollama serve"
    exit 1
fi
echo "✅ Ollama is running"
echo ""

# Check if mistral is installed
echo "2️⃣ Checking for Mistral model..."
if ! ollama list | grep -q "mistral"; then
    echo "📥 Downloading Mistral (7B) - this may take a few minutes..."
    ollama pull mistral:7b
else
    echo "✅ Mistral model found"
fi
echo ""

# Build custom model
echo "3️⃣ Building custom project-assistant model..."
cd ollama-training
if ollama create project-assistant -f Modelfile; then
    echo "✅ Custom model created successfully!"
else
    echo "❌ Failed to create model"
    exit 1
fi
cd ..
echo ""

# Update .env
echo "4️⃣ Updating .env configuration..."
if grep -q "AI_LOCAL_MODEL=" .env; then
    sed -i 's/AI_LOCAL_MODEL=.*/AI_LOCAL_MODEL=project-assistant/' .env
    echo "✅ Updated AI_LOCAL_MODEL to project-assistant"
else
    echo "AI_LOCAL_MODEL=project-assistant" >> .env
    echo "✅ Added AI_LOCAL_MODEL to .env"
fi
echo ""

# Clear caches
echo "5️⃣ Clearing Laravel caches..."
php artisan optimize:clear
php artisan view:clear
echo "✅ Caches cleared"
echo ""

# Test the model
echo "6️⃣ Testing AI automation..."
echo ""
echo "Test 1: Create a project"
ollama run project-assistant "Create a project called Test Project" --format json
echo ""

echo "Test 2: Check in"
ollama run project-assistant "Check me in" --format json
echo ""

echo "=================================================="
echo "✅ Setup Complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Refresh your browser (Ctrl+Shift+R)"
echo "   2. Open AI Assistant"
echo "   3. Try: 'Create a project called My New Project'"
echo ""
echo "📚 Read OLLAMA_TRAINING_GUIDE.md for more details"
echo "=================================================="
