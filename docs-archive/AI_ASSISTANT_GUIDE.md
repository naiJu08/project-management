# 🤖 AI Assistant Integration Guide

## Overview
Your project management system now includes a fully functional AI Assistant that can execute actions through natural language prompts. The AI understands context, asks clarifying questions, and performs tasks across three main sections.

---

## 🎯 Features

### **1. Section-Based Intelligence**
- **Management Section**: Projects, tickets, tasks, team assignments
- **HR Section**: Attendance, leaves, payroll, certificates
- **Referential Section**: Departments, positions, leave types, master data

### **2. Natural Language Processing**
- Understands conversational prompts
- Extracts parameters automatically
- Asks follow-up questions when needed
- Maintains conversation context

### **3. Action Execution**
- Creates, updates, and retrieves data
- Generates reports and summaries
- Processes approvals and workflows
- Provides real-time feedback

---

## 📦 Installation Steps

### **1. Run Migration**
```bash
php artisan migrate
```
This creates `ai_conversations` and `ai_messages` tables.

### **2. Add OpenAI API Key**
Add to your `.env` file:
```env
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_MODEL=gpt-4
# Optional: Use gpt-3.5-turbo for faster/cheaper responses
```

Get your API key from: https://platform.openai.com/api-keys

### **3. Clear Caches**
```bash
php artisan optimize:clear
```

---

## 🚀 How to Use

### **Access the AI Assistant**
1. Navigate to **AI Assistant** in the sidebar (top of navigation)
2. Choose a section: Management, HR, or Referential
3. Start chatting with natural language prompts

### **Example Prompts**

#### **Management Section**
```
"Create a new project called Website Redesign"
"Assign John Doe to the Mobile App project"
"Show me all active projects"
"Create a bug ticket for the login issue in the Website project"
"What's the status of project ID 5?"
```

#### **HR Section**
```
"Check me in for today"
"Check me out"
"Request 3 days of sick leave from Dec 20 to Dec 22"
"Approve leave request #15"
"Generate payslip for employee ID 10 for December 2025"
"Show my attendance summary for this month"
"Generate an experience certificate for John Doe"
```

#### **Referential Section**
```
"Create a new department called Marketing"
"Add a position called Senior Developer in the IT department"
"Create a leave type called Maternity Leave with 90 days per year"
"List all departments"
"Show positions in the Engineering department"
```

---

## 🎨 UI Features

### **Section Selection Screen**
- Beautiful card-based interface
- Clear descriptions and capabilities for each section
- Hover effects and smooth transitions

### **Chat Interface**
- Real-time message streaming
- User and AI avatars
- Typing indicators
- Auto-scroll to latest messages
- Keyboard shortcuts (Enter to send, Shift+Enter for new line)

### **Conversation Management**
- Start new conversations anytime
- Maintains context within a conversation
- Conversation history stored in database

---

## 🔧 Technical Architecture

### **Components Created**

#### **1. Database Models**
- `app/Models/AiConversation.php` - Stores conversations
- `app/Models/AiMessage.php` - Stores individual messages

#### **2. Services**
- `app/Services/AiAssistantService.php` - Main AI orchestration
- `app/Services/AI/ManagementActionHandler.php` - Management actions
- `app/Services/AI/HrActionHandler.php` - HR actions
- `app/Services/AI/ReferentialActionHandler.php` - Referential actions

#### **3. Filament Page**
- `app/Filament/Pages/AiAssistant.php` - Livewire page controller
- `resources/views/filament/pages/ai-assistant.blade.php` - UI view

#### **4. Configuration**
- `config/services.php` - OpenAI API configuration

---

## 🧠 How It Works

### **1. User Sends Prompt**
```
User: "Create a project called Website Redesign"
```

### **2. AI Processes Intent**
- Identifies action: `createProject`
- Extracts parameters: `{name: "Website Redesign"}`
- Checks for missing required fields

### **3. Action Execution**
- Calls `ManagementActionHandler::createProject()`
- Creates database record
- Returns success message and data

### **4. AI Responds**
```
AI: "Project 'Website Redesign' has been created successfully!"
```

### **5. Follow-up Questions (if needed)**
```
User: "Create a project"
AI: "I'd be happy to help! What would you like to name the project?"
User: "Marketing Campaign 2025"
AI: "Great! Would you like to add a description?"
```

---

## 🎯 Supported Actions

### **Management Section**
- `createProject` - Create new project
- `createTicket` - Create new ticket
- `listProjects` - List projects with filters
- `assignUserToProject` - Assign team member
- `getProjectStatus` - Get project statistics

### **HR Section**
- `checkIn` - Record attendance check-in
- `checkOut` - Record attendance check-out
- `requestLeave` - Submit leave request
- `approveLeave` - Approve leave request
- `generatePayslip` - Create payslip
- `generateCertificate` - Create certificate
- `getAttendanceSummary` - Get attendance stats

### **Referential Section**
- `createDepartment` - Create department
- `createPosition` - Create position
- `createLeaveType` - Create leave type
- `createTicketType` - Create ticket type
- `listDepartments` - List all departments
- `listPositions` - List positions (with filters)
- `listLeaveTypes` - List active leave types

---

## 🔐 Security & Permissions

- AI Assistant respects existing user permissions
- Actions execute with the logged-in user's context
- Spatie permissions are checked before execution
- Conversation history is user-specific

---

## 🎨 Customization

### **Add New Actions**

1. **Add method to action handler**:
```php
// In app/Services/AI/ManagementActionHandler.php
public function updateProject(array $parameters): array
{
    // Validate parameters
    // Execute action
    // Return result
}
```

2. **Update system prompt** (optional):
```php
// In app/Services/AiAssistantService.php
protected function getSystemPrompt(string $section): string
{
    // Add new action to capabilities
}
```

3. **AI will automatically recognize and use it!**

### **Change AI Model**
In `.env`:
```env
# Faster, cheaper
OPENAI_MODEL=gpt-3.5-turbo

# More capable, slower
OPENAI_MODEL=gpt-4

# Latest and greatest
OPENAI_MODEL=gpt-4-turbo-preview
```

### **Use Different AI Provider**
Modify `app/Services/AiAssistantService.php`:
- Change API URL
- Adjust request/response format
- Update authentication method

---

## 📊 Conversation Storage

All conversations are stored in the database:
- **ai_conversations**: Conversation metadata
- **ai_messages**: Individual messages with role (user/assistant/system)

This enables:
- Conversation history
- Context retention
- Analytics and insights
- Training data collection

---

## 🚀 Future Enhancements

### **Planned Features**
- [ ] Voice input/output
- [ ] File upload support (analyze documents)
- [ ] Multi-language support
- [ ] Suggested actions/quick replies
- [ ] Conversation export
- [ ] AI-powered analytics dashboard
- [ ] Integration with external tools (Slack, Teams)
- [ ] Custom AI training on your data

---

## 🐛 Troubleshooting

### **"OpenAI API key not configured"**
- Add `OPENAI_API_KEY` to `.env`
- Run `php artisan config:clear`

### **"Failed to process your message"**
- Check OpenAI API key is valid
- Check internet connection
- Check Laravel logs: `storage/logs/laravel.log`

### **Actions not executing**
- Verify user has required permissions
- Check action handler method exists
- Review Laravel logs for errors

### **Slow responses**
- Consider using `gpt-3.5-turbo` instead of `gpt-4`
- Check OpenAI API status
- Optimize action handler queries

---

## 💡 Tips for Best Results

1. **Be specific**: "Create project Website Redesign" vs "Create project"
2. **Provide context**: "Assign John Doe (ID 5) to project Website Redesign (ID 10)"
3. **Use natural language**: The AI understands conversational tone
4. **Ask for help**: "What can you help me with?" or "Show me examples"
5. **Iterate**: If the AI misunderstands, rephrase and try again

---

## 📝 Example Conversation Flow

```
User: Hi, I need to set up a new project

AI: Hello! I'd be happy to help you create a new project. 
    What would you like to name it?

User: Q4 Marketing Campaign

AI: Great! Would you like to add a description for the 
    "Q4 Marketing Campaign" project?

User: Yes, this is for our end-of-year promotional activities

AI: Perfect! I've created the project "Q4 Marketing Campaign" 
    with the description "This is for our end-of-year promotional 
    activities". The project is now active and ready to use!

User: Thanks! Can you assign Sarah Johnson to this project?

AI: I'd be happy to assign Sarah Johnson to the project. 
    Could you provide her user ID?

User: Her ID is 12

AI: Done! Sarah Johnson has been assigned to the 
    "Q4 Marketing Campaign" project.
```

---

## 🎉 Summary

You now have a fully functional AI Assistant that:
- ✅ Understands natural language
- ✅ Executes actions across your application
- ✅ Asks clarifying questions
- ✅ Maintains conversation context
- ✅ Provides a beautiful, modern UI
- ✅ Respects user permissions
- ✅ Stores conversation history

**This is the foundation for a truly AI-powered project management system!**

---

## 📞 Need Help?

If you encounter issues or want to extend functionality:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review conversation metadata in `ai_messages` table
3. Test actions directly in action handlers
4. Verify OpenAI API usage and limits

**Happy chatting with your AI Assistant!** 🚀
