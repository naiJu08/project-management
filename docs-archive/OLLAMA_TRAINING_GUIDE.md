# 🎓 Ollama Training Guide for Software Automation

## Current Issue: AI Not Automating Functions

Your AI assistant is currently using **rule-based fallback** or **generic llama2 responses**. To make it truly automate your software functions, you need to:

1. **Fine-tune the model** with your specific data
2. **Improve system prompts** for better instruction following
3. **Create training datasets** from your application

---

## 🎯 Quick Wins (No Training Required)

### **Step 1: Switch to a Better Model**

Llama2 is general-purpose. For automation tasks, use **CodeLlama** or **Mistral**:

```bash
# Install CodeLlama (better for structured tasks)
ollama pull codellama:7b

# Or Mistral (better instruction following)
ollama pull mistral:7b

# Update .env
AI_LOCAL_MODEL=mistral:7b
# or
AI_LOCAL_MODEL=codellama:7b

# Restart and test
php artisan optimize:clear
```

### **Step 2: Improve System Prompts**

Edit `app/Http/Livewire/EnhancedAiAssistant.php` line 323-334:

```php
protected function getSystemPrompt(string $section): string
{
    $basePrompt = "You are an AI assistant for a Laravel project management system. You MUST respond ONLY in valid JSON format with this exact structure: {\"action\": \"action_name\", \"parameters\": {...}, \"message\": \"user-friendly message\", \"requires_input\": false}. Never include explanations outside the JSON. ";
    
    $sectionPrompts = [
        'management' => "AVAILABLE ACTIONS: createProject (params: name, description, type='kanban'), createTicket (params: title, project_id, description), listProjects (no params), assignUserToProject (params: user_id, project_id), getProjectStatus (params: project_id). EXTRACT ALL parameters from the user's message. Example: 'Create project Website Redesign' -> {\"action\":\"createProject\",\"parameters\":{\"name\":\"Website Redesign\"},\"message\":\"Creating project...\"}",
        
        'hr' => "AVAILABLE ACTIONS: checkIn (no params), checkOut (no params), requestLeave (params: leave_type_id, start_date, end_date, reason), approveLeave (params: leave_request_id), generatePayslip (params: month, year), generateCertificate (params: type='employment'|'salary'), getAttendanceSummary (params: start_date, end_date). Extract dates in YYYY-MM-DD format.",
        
        'referential' => "AVAILABLE ACTIONS: createDepartment (params: name, description), createPosition (params: name, department_id, description), createLeaveType (params: name, days_per_year, is_paid), listDepartments, listPositions, listLeaveTypes. Always extract the name from the user's message.",
    ];

    return $basePrompt . ($sectionPrompts[$section] ?? '');
}
```

---

## 🔥 Training Ollama for Your Software

### **Method 1: Create a Modelfile (Recommended)**

This customizes the model without full retraining.

#### **Step 1: Create Training Data**

Create `ollama-training/project-management-examples.txt`:

```
### Example 1: Create Project
User: Create a new project called Mobile App Development
Assistant: {"action":"createProject","parameters":{"name":"Mobile App Development","type":"kanban"},"message":"Creating project 'Mobile App Development'...","requires_input":false}

### Example 2: Create Ticket
User: Create a ticket for bug fixing in project 5
Assistant: {"action":"createTicket","parameters":{"title":"Bug Fixing","project_id":5},"message":"Creating ticket in project 5...","requires_input":false}

### Example 3: List Projects
User: Show me all projects
Assistant: {"action":"listProjects","parameters":{},"message":"Fetching all projects...","requires_input":false}

### Example 4: Check In
User: Check me in for today
Assistant: {"action":"checkIn","parameters":{},"message":"Checking you in...","requires_input":false}

### Example 5: Request Leave
User: I need sick leave from 2025-10-20 to 2025-10-22
Assistant: {"action":"requestLeave","parameters":{"leave_type_id":1,"start_date":"2025-10-20","end_date":"2025-10-22"},"message":"Requesting sick leave...","requires_input":false}

### Example 6: Create Department
User: Create a department called Marketing
Assistant: {"action":"createDepartment","parameters":{"name":"Marketing"},"message":"Creating department 'Marketing'...","requires_input":false}

### Example 7: Complex Request
User: Create a project named Website Redesign with description "Redesign company website with modern UI"
Assistant: {"action":"createProject","parameters":{"name":"Website Redesign","description":"Redesign company website with modern UI","type":"kanban"},"message":"Creating project with description...","requires_input":false}

### Example 8: Assign User
User: Assign user 3 to project 5
Assistant: {"action":"assignUserToProject","parameters":{"user_id":3,"project_id":5},"message":"Assigning user to project...","requires_input":false}

### Example 9: Generate Payslip
User: Generate my payslip for October 2025
Assistant: {"action":"generatePayslip","parameters":{"month":"10","year":"2025"},"message":"Generating payslip for October 2025...","requires_input":false}

### Example 10: List Departments
User: Show all departments
Assistant: {"action":"listDepartments","parameters":{},"message":"Fetching departments...","requires_input":false}
```

#### **Step 2: Create Modelfile**

Create `ollama-training/Modelfile`:

```dockerfile
FROM mistral:7b

# Set temperature (lower = more deterministic)
PARAMETER temperature 0.3

# Set top_p for focused responses
PARAMETER top_p 0.9

# System message
SYSTEM """You are an AI assistant for a Laravel project management system. You MUST respond ONLY in valid JSON format.

Your response format:
{
  "action": "action_name",
  "parameters": {...},
  "message": "user-friendly message",
  "requires_input": false
}

MANAGEMENT ACTIONS:
- createProject: {name, description?, type?}
- createTicket: {title, project_id, description?}
- listProjects: {}
- assignUserToProject: {user_id, project_id}
- getProjectStatus: {project_id}

HR ACTIONS:
- checkIn: {}
- checkOut: {}
- requestLeave: {leave_type_id, start_date, end_date, reason?}
- approveLeave: {leave_request_id}
- generatePayslip: {month, year}
- generateCertificate: {type}
- getAttendanceSummary: {start_date?, end_date?}

REFERENTIAL ACTIONS:
- createDepartment: {name, description?}
- createPosition: {name, department_id?, description?}
- createLeaveType: {name, days_per_year, is_paid}
- listDepartments: {}
- listPositions: {}
- listLeaveTypes: {}

Extract ALL parameters from the user's message. Use YYYY-MM-DD for dates.
"""

# Add examples (this teaches the model)
MESSAGE user Create a project called Website Redesign
MESSAGE assistant {"action":"createProject","parameters":{"name":"Website Redesign"},"message":"Creating project 'Website Redesign'...","requires_input":false}

MESSAGE user Check me in
MESSAGE assistant {"action":"checkIn","parameters":{},"message":"Checking you in...","requires_input":false}

MESSAGE user Request sick leave from 2025-10-20 to 2025-10-22
MESSAGE assistant {"action":"requestLeave","parameters":{"leave_type_id":1,"start_date":"2025-10-20","end_date":"2025-10-22"},"message":"Requesting leave...","requires_input":false}

MESSAGE user Create a department called Marketing
MESSAGE assistant {"action":"createDepartment","parameters":{"name":"Marketing"},"message":"Creating department 'Marketing'...","requires_input":false}

MESSAGE user Show all projects
MESSAGE assistant {"action":"listProjects","parameters":{},"message":"Fetching projects...","requires_input":false}
```

#### **Step 3: Build Custom Model**

```bash
cd /opt/lampp/htdocs/project-management/ollama-training

# Create the custom model
ollama create project-assistant -f Modelfile

# Test it
ollama run project-assistant "Create a project called Test Project"

# If it works, update .env
AI_LOCAL_MODEL=project-assistant
```

---

## 🚀 Method 2: Full Fine-Tuning (Advanced)

For production-grade automation, fine-tune with your actual data.

### **Step 1: Collect Real Conversations**

Export from your `ai_messages` table:

```bash
php artisan tinker
```

```php
use App\Models\AiMessage;
use App\Models\AiConversation;

$conversations = AiConversation::with('messages')->get();

$trainingData = [];
foreach ($conversations as $conv) {
    foreach ($conv->messages as $msg) {
        $trainingData[] = [
            'role' => $msg->role,
            'content' => $msg->content,
        ];
    }
}

file_put_contents('ollama-training/real-data.json', json_encode($trainingData, JSON_PRETTY_PRINT));
```

### **Step 2: Convert to Training Format**

Create `ollama-training/prepare-training.php`:

```php
<?php

$data = json_decode(file_get_contents('real-data.json'), true);
$output = fopen('training-data.jsonl', 'w');

$conversation = [];
foreach ($data as $msg) {
    if ($msg['role'] === 'user') {
        if (!empty($conversation)) {
            fwrite($output, json_encode(['messages' => $conversation]) . "\n");
            $conversation = [];
        }
    }
    $conversation[] = $msg;
}

if (!empty($conversation)) {
    fwrite($output, json_encode(['messages' => $conversation]) . "\n");
}

fclose($output);
echo "Training data prepared!\n";
```

Run:
```bash
php ollama-training/prepare-training.php
```

### **Step 3: Fine-Tune with Ollama**

```bash
# Install ollama-python for fine-tuning
pip install ollama

# Create fine-tuning script
cat > ollama-training/finetune.py << 'EOF'
import ollama
import json

# Load training data
with open('training-data.jsonl', 'r') as f:
    training_data = [json.loads(line) for line in f]

# Fine-tune
ollama.finetune(
    model='mistral:7b',
    dataset=training_data,
    output='project-assistant-finetuned',
    epochs=3,
    learning_rate=0.0001
)

print("Fine-tuning complete!")
EOF

python ollama-training/finetune.py
```

---

## 🎨 Fix Gradient Color Issue

The gradient might not show due to Tailwind not being compiled. Let's fix it:

### **Option 1: Use Inline Styles (Quick Fix)**

```bash
# Edit the view
nano resources/views/livewire/enhanced-ai-assistant.blade.php
```

Change line 7:
```blade
class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-full p-4 shadow-2xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-white/60"
```

To:
```blade
style="background: linear-gradient(to right, #9333ea, #db2777); position: fixed !important; bottom: 24px !important; right: 24px !important; z-index: 9999 !important;"
class="text-white rounded-full p-4 shadow-2xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-white/60"
```

### **Option 2: Compile Tailwind (Proper Fix)**

```bash
cd /opt/lampp/htdocs/project-management

# Install dependencies
npm install

# Build Tailwind
npm run dev

# Or for production
npm run build

# Clear caches
php artisan view:clear
php artisan optimize:clear
```

---

## 📊 Test Your Trained Model

Create `test-ai-automation.php`:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\LocalAiService;

$ai = new LocalAiService();

$tests = [
    "Create a project called Website Redesign",
    "Check me in for today",
    "Request sick leave from 2025-10-20 to 2025-10-22",
    "Create a department called Marketing",
    "Show all active projects",
];

echo "🧪 Testing AI Automation\n\n";

foreach ($tests as $test) {
    echo "📝 User: $test\n";
    
    $messages = [
        ['role' => 'system', 'content' => 'You are an AI assistant. Respond in JSON format with: {"action": "action_name", "parameters": {...}, "message": "response"}'],
        ['role' => 'user', 'content' => $test]
    ];
    
    $response = $ai->generate($messages);
    echo "🤖 AI: $response\n";
    
    $json = $ai->extractJson($response);
    if ($json && isset($json['action'])) {
        echo "✅ Action detected: {$json['action']}\n";
        echo "📦 Parameters: " . json_encode($json['parameters']) . "\n";
    } else {
        echo "❌ No valid action detected\n";
    }
    echo "\n" . str_repeat('-', 80) . "\n\n";
}
```

Run:
```bash
php test-ai-automation.php
```

---

## 🎯 Expected Results

After training, your AI should respond like this:

**Input:** "Create a project called Mobile App"

**Output:**
```json
{
  "action": "createProject",
  "parameters": {
    "name": "Mobile App",
    "type": "kanban"
  },
  "message": "Creating project 'Mobile App'...",
  "requires_input": false
}
```

The `EnhancedAiAssistant` component will then:
1. Parse the JSON
2. Call `ManagementActionHandler->createProject(['name' => 'Mobile App', 'type' => 'kanban'])`
3. Create the project in your database
4. Show success message

---

## 🔧 Troubleshooting

### **AI Returns Plain Text Instead of JSON**

**Solution:** Use the Modelfile approach with strict JSON examples.

### **AI Doesn't Extract Parameters**

**Solution:** Add more examples to the Modelfile with various phrasings.

### **Actions Don't Execute**

**Solution:** Check `storage/logs/laravel.log` for errors in action handlers.

### **Gradient Still Not Showing**

**Solution:** 
```bash
# Check if Tailwind is loaded
curl -I http://localhost/css/app.css

# If 404, run:
npm run build
```

---

## 📚 Next Steps

1. **Create the Modelfile** (15 minutes)
2. **Build custom model** (5 minutes)
3. **Test with real prompts** (10 minutes)
4. **Collect more examples** (ongoing)
5. **Iterate and improve** (ongoing)

---

## 🎓 Learning Resources

- **Ollama Modelfile Docs**: https://github.com/ollama/ollama/blob/main/docs/modelfile.md
- **Fine-tuning Guide**: https://github.com/ollama/ollama/blob/main/docs/faq.md#how-do-i-fine-tune-a-model
- **JSON Mode**: Use `ollama run mistral:7b --format json`

---

## 💡 Pro Tips

1. **Start with Modelfile** - It's faster and often sufficient
2. **Use mistral:7b** - Better instruction following than llama2
3. **Add 20-30 examples** - Covers most use cases
4. **Test frequently** - Iterate based on results
5. **Log everything** - Use `ai_messages` table to improve

---

**Your AI will automate your software once you:**
1. ✅ Switch to mistral/codellama
2. ✅ Create a Modelfile with examples
3. ✅ Build the custom model
4. ✅ Update .env to use it

**Estimated time: 30 minutes to working automation!** 🚀
