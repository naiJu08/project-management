# 🤖 Local AI Setup Guide - Self-Hosted Models

## Overview
This guide helps you set up locally trained AI models for your project management system, eliminating the need for external API keys like OpenAI. You'll have full control over your AI assistant.

---

## 🎯 Supported Local AI Providers

### **1. Ollama (Recommended - Easiest)**
- **Best for**: Quick setup, multiple models, CPU/GPU support
- **Models**: Llama 2, Mistral, CodeLlama, and 50+ others
- **Website**: https://ollama.com

### **2. LM Studio**
- **Best for**: GUI-based model management, Windows/Mac users
- **Models**: Any GGUF format models
- **Website**: https://lmstudio.ai

### **3. LocalAI**
- **Best for**: Docker deployments, OpenAI-compatible API
- **Models**: Multiple formats (GGUF, GGML, etc.)
- **Website**: https://localai.io

### **4. Custom Endpoint**
- **Best for**: Your own trained models, custom infrastructure
- **Requires**: Custom API implementation

---

## 🚀 Quick Start with Ollama (Recommended)

### **Step 1: Install Ollama**

**Linux:**
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

**macOS:**
```bash
brew install ollama
```

**Windows:**
Download from https://ollama.com/download

### **Step 2: Pull a Model**

```bash
# Recommended for project management (balanced speed/quality)
ollama pull llama2

# For better quality (slower, needs more RAM)
ollama pull mistral

# For coding tasks
ollama pull codellama

# Lightweight option (faster, less RAM)
ollama pull phi
```

### **Step 3: Start Ollama Server**

```bash
ollama serve
```

The server runs on `http://localhost:11434` by default.

### **Step 4: Test the Model**

```bash
ollama run llama2
```

Type a message and press Enter. If you get a response, it's working!

### **Step 5: Configure Your Laravel App**

Add to `.env`:
```env
AI_USE_LOCAL=true
AI_LOCAL_PROVIDER=ollama
AI_LOCAL_ENDPOINT=http://localhost:11434
AI_LOCAL_MODEL=llama2
AI_LOCAL_TIMEOUT=60
```

### **Step 6: Run Migration**

```bash
php artisan migrate
```

### **Step 7: Clear Caches**

```bash
php artisan optimize:clear
```

### **Step 8: Test the AI Assistant**

1. Open your application
2. Click the AI Assistant button (bottom-right)
3. Select a section (Management, HR, or Referential)
4. Start chatting!

---

## 🎨 Alternative Setup: LM Studio

### **Step 1: Download LM Studio**
Visit https://lmstudio.ai and download for your OS.

### **Step 2: Download a Model**
1. Open LM Studio
2. Go to "Search" tab
3. Search for "Llama 2" or "Mistral"
4. Click download (choose 7B or 13B variant)

### **Step 3: Start Local Server**
1. Go to "Local Server" tab
2. Select your downloaded model
3. Click "Start Server"
4. Note the endpoint (usually `http://localhost:1234`)

### **Step 4: Configure Laravel**

Add to `.env`:
```env
AI_USE_LOCAL=true
AI_LOCAL_PROVIDER=lmstudio
AI_LOCAL_ENDPOINT=http://localhost:1234
AI_LOCAL_MODEL=your-model-name
AI_LOCAL_TIMEOUT=60
```

---

## 🐳 Docker Setup: LocalAI

### **Step 1: Run LocalAI Container**

```bash
docker run -p 8080:8080 \
  -v $PWD/models:/models \
  localai/localai:latest
```

### **Step 2: Download Models**

```bash
# Download Llama 2
curl -L https://huggingface.co/TheBloke/Llama-2-7B-Chat-GGUF/resolve/main/llama-2-7b-chat.Q4_K_M.gguf \
  -o models/llama-2-7b-chat.gguf
```

### **Step 3: Configure Laravel**

Add to `.env`:
```env
AI_USE_LOCAL=true
AI_LOCAL_PROVIDER=localai
AI_LOCAL_ENDPOINT=http://localhost:8080
AI_LOCAL_MODEL=llama-2-7b-chat.gguf
AI_LOCAL_TIMEOUT=60
```

---

## 🧠 Fine-Tuning Your Own Model

### **Why Fine-Tune?**
- Better understanding of your domain (project management, HR)
- More accurate responses
- Learns your company's terminology
- No external dependencies

### **Preparation**

1. **Collect Training Data**
   - Export conversation logs from `ai_messages` table
   - Create examples of ideal conversations
   - Include domain-specific terminology

2. **Format Data (JSONL)**
```json
{"messages": [{"role": "system", "content": "You are a project management assistant."}, {"role": "user", "content": "Create a project called Website Redesign"}, {"role": "assistant", "content": "{\"action\": \"createProject\", \"parameters\": {\"name\": \"Website Redesign\"}, \"message\": \"Creating project...\"}"}]}
```

### **Fine-Tuning Options**

#### **Option 1: Using Ollama (Easiest)**

1. **Create Modelfile**
```bash
# Modelfile
FROM llama2

# Set parameters
PARAMETER temperature 0.7
PARAMETER top_p 0.9

# System prompt
SYSTEM You are an AI assistant for a project management system. You help with projects, HR, and reference data management.
```

2. **Create Custom Model**
```bash
ollama create my-pm-assistant -f Modelfile
```

3. **Update .env**
```env
AI_LOCAL_MODEL=my-pm-assistant
```

#### **Option 2: Using Python (Advanced)**

1. **Install Dependencies**
```bash
pip install transformers datasets peft bitsandbytes accelerate
```

2. **Fine-Tune Script** (`scripts/finetune_model.py`)
```python
from transformers import AutoModelForCausalLM, AutoTokenizer, TrainingArguments
from peft import LoraConfig, get_peft_model
from datasets import load_dataset

# Load base model
model_name = "meta-llama/Llama-2-7b-chat-hf"
model = AutoModelForCausalLM.from_pretrained(model_name, load_in_8bit=True)
tokenizer = AutoTokenizer.from_pretrained(model_name)

# Configure LoRA
lora_config = LoraConfig(
    r=16,
    lora_alpha=32,
    target_modules=["q_proj", "v_proj"],
    lora_dropout=0.05,
    bias="none",
    task_type="CAUSAL_LM"
)

model = get_peft_model(model, lora_config)

# Load your training data
dataset = load_dataset("json", data_files="training_data.jsonl")

# Training arguments
training_args = TrainingArguments(
    output_dir="./fine-tuned-model",
    num_train_epochs=3,
    per_device_train_batch_size=4,
    learning_rate=2e-4,
    logging_steps=10,
)

# Train (implement trainer logic)
# ...

# Save model
model.save_pretrained("./fine-tuned-model")
```

3. **Run Training**
```bash
python scripts/finetune_model.py
```

4. **Deploy with Ollama**
```bash
ollama create my-custom-model -f ./fine-tuned-model
```

---

## 📊 Model Comparison

| Model | Size | RAM Required | Speed | Quality | Best For |
|-------|------|--------------|-------|---------|----------|
| **Llama 2 7B** | 3.8GB | 8GB | Fast | Good | General use, balanced |
| **Llama 2 13B** | 7.3GB | 16GB | Medium | Better | Higher quality responses |
| **Mistral 7B** | 4.1GB | 8GB | Fast | Excellent | Best quality at 7B size |
| **CodeLlama 7B** | 3.8GB | 8GB | Fast | Good | Code generation tasks |
| **Phi-2** | 1.7GB | 4GB | Very Fast | Decent | Low-resource environments |
| **Mixtral 8x7B** | 26GB | 32GB | Slow | Excellent | Maximum quality |

---

## 🎯 Optimizing for Your Use Case

### **For Project Management**
```env
AI_LOCAL_MODEL=mistral
# Mistral excels at structured outputs (JSON)
```

### **For HR Operations**
```env
AI_LOCAL_MODEL=llama2
# Llama 2 is well-balanced for conversational tasks
```

### **For Low-Resource Servers**
```env
AI_LOCAL_MODEL=phi
# Phi is lightweight but capable
```

### **For Maximum Quality**
```env
AI_LOCAL_MODEL=mixtral
# Requires powerful hardware
```

---

## 🔧 Advanced Configuration

### **Customize Model Parameters**

In `config/ai.php`, you can pass custom options:

```php
'local' => [
    'provider' => 'ollama',
    'endpoint' => 'http://localhost:11434',
    'model' => 'llama2',
    'timeout' => 60,
    'options' => [
        'temperature' => 0.7,  // Creativity (0.0-1.0)
        'top_p' => 0.9,        // Nucleus sampling
        'top_k' => 40,         // Top-k sampling
        'num_predict' => 1000, // Max tokens
    ],
],
```

### **System Prompts per Section**

Edit `app/Services/LocalAiService.php` or `app/Http/Livewire/EnhancedAiAssistant.php`:

```php
protected function getSystemPrompt(string $section): string
{
    $prompts = [
        'management' => "You are an expert project manager. Help create projects, assign tasks, and track progress. Always respond in JSON format with action, parameters, and message fields.",
        'hr' => "You are an HR specialist. Help with attendance, leaves, payroll, and employee management. Be professional and empathetic.",
        'referential' => "You are a data administrator. Help configure departments, positions, and system settings. Be precise and organized.",
    ];
    
    return $prompts[$section] ?? '';
}
```

---

## 🐛 Troubleshooting

### **"Connection refused" Error**
- Ensure Ollama/LM Studio server is running
- Check endpoint URL in `.env`
- Verify firewall settings

```bash
# Test endpoint
curl http://localhost:11434/api/tags
```

### **Slow Responses**
- Use smaller model (phi, llama2-7b)
- Reduce `num_predict` parameter
- Enable GPU acceleration (if available)

```bash
# Check if GPU is being used (Ollama)
ollama ps
```

### **Out of Memory**
- Use quantized models (Q4, Q5)
- Reduce context window
- Close other applications

### **Poor Quality Responses**
- Try larger model (mistral, llama2-13b)
- Increase temperature for creativity
- Fine-tune on your data

### **Model Not Found**
```bash
# List available models
ollama list

# Pull missing model
ollama pull llama2
```

---

## 📈 Monitoring & Analytics

### **Track AI Performance**

Check `ai_messages` table:
```sql
SELECT 
    section,
    COUNT(*) as total_messages,
    AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_response_time
FROM ai_messages
WHERE role = 'assistant'
GROUP BY section;
```

### **Monitor Model Usage**

```bash
# Ollama
ollama ps

# Check logs
tail -f ~/.ollama/logs/server.log
```

---

## 🎓 Training Data Collection

### **Export Conversations for Training**

```bash
php artisan tinker
```

```php
use App\Models\AiConversation;

$conversations = AiConversation::with('messages')
    ->where('status', 'completed')
    ->get();

$trainingData = [];
foreach ($conversations as $conv) {
    $messages = $conv->messages->map(fn($m) => [
        'role' => $m->role,
        'content' => $m->content
    ])->toArray();
    
    $trainingData[] = ['messages' => $messages];
}

file_put_contents('training_data.jsonl', 
    implode("\n", array_map('json_encode', $trainingData))
);
```

---

## 🚀 Production Deployment

### **Docker Compose Setup**

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      AI_USE_LOCAL: "true"
      AI_LOCAL_ENDPOINT: "http://ollama:11434"
    depends_on:
      - ollama
  
  ollama:
    image: ollama/ollama:latest
    ports:
      - "11434:11434"
    volumes:
      - ollama_data:/root/.ollama
    command: serve

volumes:
  ollama_data:
```

### **Systemd Service (Linux)**

Create `/etc/systemd/system/ollama.service`:
```ini
[Unit]
Description=Ollama Service
After=network.target

[Service]
Type=simple
User=www-data
ExecStart=/usr/local/bin/ollama serve
Restart=always

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable ollama
sudo systemctl start ollama
```

---

## 📝 Summary

You now have:
- ✅ Local AI running (no external API keys)
- ✅ Multiple provider options (Ollama, LM Studio, LocalAI)
- ✅ Section-based AI assistant (Management, HR, Referential)
- ✅ Quick action buttons for common tasks
- ✅ Conversation history and context
- ✅ Rule-based fallback if AI fails
- ✅ Fine-tuning guide for custom models
- ✅ Production deployment options

**Your AI assistant is now fully self-hosted and under your control!** 🎉

---

## 🔗 Useful Resources

- **Ollama**: https://ollama.com
- **LM Studio**: https://lmstudio.ai
- **LocalAI**: https://localai.io
- **Hugging Face Models**: https://huggingface.co/models
- **Fine-Tuning Guide**: https://huggingface.co/docs/transformers/training
- **PEFT (LoRA)**: https://github.com/huggingface/peft

---

## 💡 Next Steps

1. **Install Ollama** and pull a model
2. **Configure .env** with local settings
3. **Test the assistant** in your application
4. **Collect conversation data** for fine-tuning
5. **Train custom model** on your domain
6. **Deploy to production** with Docker/systemd

**Happy AI automation!** 🚀
