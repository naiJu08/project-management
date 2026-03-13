<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\CloudAiService;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    protected $aiService;
    protected $useCloudAi;

    public function __construct()
    {
        $this->useCloudAi = config('services.cloud_ai.enabled', false);
        
        if ($this->useCloudAi) {
            $this->aiService = app(CloudAiService::class);
        } else {
            $this->aiService = app(OllamaService::class);
        }
    }

    /**
     * Get available sections with descriptions
     */
    public function getSections(): array
    {
        return [
            'management' => [
                'name' => 'Management',
                'description' => 'Manage projects, tickets, tasks, users, roles, and permissions. Create and track project progress.',
                'icon' => 'heroicon-o-briefcase',
                'capabilities' => [
                    'Create and manage projects',
                    'Assign tasks and tickets',
                    'Track project progress',
                    'Manage team members',
                    'Generate reports'
                ]
            ],
            'hr' => [
                'name' => 'Human Resources',
                'description' => 'Handle employee profiles, attendance, leave requests, payroll, and certificates. Manage HR operations.',
                'icon' => 'heroicon-o-users',
                'capabilities' => [
                    'Manage employee profiles',
                    'Track attendance and leaves',
                    'Process payroll',
                    'Generate certificates',
                    'View HR analytics'
                ]
            ],
            'referential' => [
                'name' => 'Referential Data',
                'description' => 'Manage departments, positions, leave types, ticket types, and other reference data.',
                'icon' => 'heroicon-o-database',
                'capabilities' => [
                    'Manage departments and positions',
                    'Configure leave types',
                    'Set up ticket categories',
                    'Define system settings',
                    'Manage master data'
                ]
            ],
        ];
    }

    /**
     * Process user prompt and determine intent
     */
    public function processPrompt(AiConversation $conversation, string $userMessage): array
    {
        // Save user message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Build conversation history for context
        $messages = $this->buildConversationHistory($conversation);

        // Add system prompt based on section
        $systemPrompt = $this->getSystemPrompt($conversation->section);
        array_unshift($messages, [
            'role' => 'system',
            'content' => $systemPrompt
        ]);

        // Call AI API
        try {
            $response = $this->callAiApi($messages);
            
            // Parse response and determine action
            $parsedResponse = $this->parseAiResponse($response, $conversation->section);
            
            // Save assistant message
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $parsedResponse['message'],
                'metadata' => $parsedResponse['metadata'] ?? null,
            ]);

            return $parsedResponse;
        } catch (\Exception $e) {
            Log::error('AI Assistant Error: ' . $e->getMessage());
            
            // Fallback response
            $fallbackMessage = "I apologize, but I'm having trouble processing your request right now. Could you please rephrase or try again?";
            
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $fallbackMessage,
                'metadata' => ['error' => $e->getMessage()],
            ]);

            return [
                'message' => $fallbackMessage,
                'action' => null,
                'requires_input' => false,
            ];
        }
    }

    /**
     * Build conversation history
     */
    protected function buildConversationHistory(AiConversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content
            ])
            ->toArray();
    }

    /**
     * Get system prompt based on section
     */
    protected function getSystemPrompt(string $section): string
    {
        $basePrompt = "You are an intelligent AI assistant for a project management system. You understand natural language and can respond conversationally.\n\n";
        
        $sectionPrompts = [
            'management' => "You help users manage projects, tickets, tasks, and team members. 

**IMPORTANT - Understand User Intent:**
1. **Information Queries** (\"how many\", \"show me\", \"list\", \"what is\", \"count\"):
   - Execute the action and provide a direct, conversational answer
   - Example: \"active projects count\" → Execute listProjects, then respond: \"You have 15 active projects.\"
   
2. **Action Requests** (\"create\", \"update\", \"delete\", \"assign\"):
   - Execute the action and confirm completion
   - Example: \"create project Mobile App\" → Execute createProject, respond: \"✅ Project 'Mobile App' created successfully!\"

3. **Conversational Questions**:
   - Provide helpful, friendly responses
   - Example: \"how do I create a project?\" → Explain the process

**Response Format:**
For actions that need execution, respond with JSON:
{
  \"action\": \"action_name\",
  \"parameters\": {...},
  \"message\": \"user-friendly message\",
  \"requires_input\": false
}

For simple information or conversation, respond naturally without JSON.

**Available Actions:**
- listProjects: List projects (params: status, owner, limit)
- createProject: Create project (params: name, description)
- updateProject: Update project (params: project_id, name, status)
- getProjectStatus: Get statistics (params: project_id)
- createTicket: Create ticket (params: title, project_id, description)
- listTickets: List tickets (params: project_id, assigned_to, status)
- assignTicket: Assign ticket (params: ticket_id, user_name)
- closeTicket: Close ticket (params: ticket_id)
- bulkUpdateTickets: Bulk update (params: project_id, current_status, new_status)
- createBacklogItem: Create backlog item (params: title, type, project_id)
- createSprint: Create sprint (params: name, project_id)
- getTeamWorkload: Get workload (params: project_id)

**Examples:**
User: \"active projects count\"
→ {\"action\": \"listProjects\", \"parameters\": {\"status\": \"active\"}, \"message\": \"Let me count your active projects...\"}

User: \"how many tickets does John have?\"
→ {\"action\": \"listTickets\", \"parameters\": {\"assigned_to\": \"John\"}, \"message\": \"Checking John's tickets...\"}

User: \"create project Mobile App\"
→ {\"action\": \"createProject\", \"parameters\": {\"name\": \"Mobile App\"}, \"message\": \"Creating project 'Mobile App'...\"}

User: \"what can you do?\"
→ \"I can help you manage projects, tickets, tasks, and team members. I can create, update, list, and analyze your project data. Just ask me naturally!\"",
            
            'hr' => "You help users manage HR operations including employee profiles, attendance, leaves, payroll, and certificates. You can process attendance records, approve leave requests, generate payslips and certificates. When users request actions, extract the necessary parameters and respond in JSON format with: {\"action\": \"action_name\", \"parameters\": {...}, \"message\": \"user-friendly message\", \"requires_input\": false}. If you need more information, set requires_input to true and ask specific questions.",
            
            'referential' => "You help users manage reference data like departments, positions, leave types, ticket types, and system settings. You can create, update, and organize master data. When users request actions, extract the necessary parameters and respond in JSON format with: {\"action\": \"action_name\", \"parameters\": {...}, \"message\": \"user-friendly message\", \"requires_input\": false}. If you need more information, set requires_input to true and ask specific questions.",
        ];

        return $basePrompt . ($sectionPrompts[$section] ?? $sectionPrompts['management']);
    }

    /**
     * Call AI API using CloudAiService or OllamaService
     */
    protected function callAiApi(array $messages): string
    {
        if (!$this->aiService->isAvailable()) {
            throw new \Exception('AI service is not available');
        }

        // Use cloud AI (Cohere, DeepSeek, Groq, OpenAI)
        if ($this->useCloudAi) {
            return $this->callCloudAi($messages);
        }

        // Use local Ollama
        return $this->callLocalAi($messages);
    }

    /**
     * Call Cloud AI (Cohere/DeepSeek/Groq/OpenAI)
     */
    protected function callCloudAi(array $messages): string
    {
        $provider = config('services.cloud_ai.provider');
        $model = config('services.cloud_ai.model');
        $apiKey = config('services.cloud_ai.api_key');
        
        // Get base URL from config
        $baseUrl = match($provider) {
            'deepseek' => 'https://api.deepseek.com/v1',
            'groq' => 'https://api.groq.com/openai/v1',
            'openai' => 'https://api.openai.com/v1',
            'cohere' => 'https://api.cohere.ai/v1',
            default => config('services.cloud_ai.base_url', 'https://api.deepseek.com/v1'),
        };

        // Cohere uses different format
        if ($provider === 'cohere') {
            return $this->callCohereApi($messages, $apiKey, $baseUrl, $model);
        }

        // OpenAI-compatible (DeepSeek, Groq, OpenAI)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Cloud AI API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Cohere API
     */
    protected function callCohereApi(array $messages, string $apiKey, string $baseUrl, string $model): string
    {
        // Extract system and user messages
        $systemMessage = '';
        $lastUserMessage = '';

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemMessage = $message['content'];
            } elseif ($message['role'] === 'user') {
                $lastUserMessage = $message['content'];
            }
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$baseUrl}/chat", [
            'model' => $model,
            'message' => $lastUserMessage,
            'preamble' => $systemMessage,
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Cohere API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['text'] ?? '';
    }

    /**
     * Call Local AI (Ollama)
     */
    protected function callLocalAi(array $messages): string
    {
        // Build prompt from messages
        $prompt = '';
        foreach ($messages as $message) {
            $role = ucfirst($message['role']);
            $prompt .= "{$role}: {$message['content']}\n\n";
        }
        $prompt .= "Assistant:";

        // Use OllamaService
        return $this->aiService->generate($prompt, 1000);
    }

    /**
     * Parse AI response and extract action
     */
    protected function parseAiResponse(string $response, string $section): array
    {
        // Try to parse JSON response
        $decoded = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['action'])) {
            return [
                'message' => $decoded['message'] ?? 'Processing your request...',
                'action' => $decoded['action'],
                'parameters' => $decoded['parameters'] ?? [],
                'requires_input' => $decoded['requires_input'] ?? false,
                'metadata' => $decoded,
            ];
        }

        // Fallback: treat as plain text response
        return [
            'message' => $response,
            'action' => null,
            'requires_input' => false,
        ];
    }

    /**
     * Execute action based on parsed response
     */
    public function executeAction(string $action, array $parameters, string $section): array
    {
        $handler = $this->getActionHandler($section);
        
        if (!$handler || !method_exists($handler, $action)) {
            return [
                'success' => false,
                'message' => "Action '{$action}' is not supported in the {$section} section.",
            ];
        }

        try {
            return $handler->$action($parameters);
        } catch (\Exception $e) {
            Log::error("Action execution error: {$action}", [
                'section' => $section,
                'parameters' => $parameters,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to execute action: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get action handler for section
     */
    protected function getActionHandler(string $section)
    {
        $handlers = [
            'management' => new \App\Services\AI\ManagementActionHandler(),
            'hr' => new \App\Services\AI\HrActionHandler(),
            'referential' => new \App\Services\AI\ReferentialActionHandler(),
        ];

        return $handlers[$section] ?? null;
    }

    /**
     * Format action result into natural language response
     */
    public function formatActionResult(string $action, array $result, string $userQuery): string
    {
        if (!$result['success']) {
            return $result['message'];
        }

        // Format based on action type
        switch ($action) {
            case 'listProjects':
                $count = is_countable($result['data']) ? count($result['data']) : 0;
                if (stripos($userQuery, 'count') !== false || stripos($userQuery, 'how many') !== false) {
                    return "📊 You have **{$count} active project(s)**.";
                }
                $projectList = collect($result['data'])->map(fn($p) => "• {$p->name}")->join("\n");
                return "📋 Found {$count} project(s):\n{$projectList}";

            case 'listTickets':
                $count = is_countable($result['data']) ? count($result['data']) : 0;
                if (stripos($userQuery, 'count') !== false || stripos($userQuery, 'how many') !== false) {
                    return "🎫 Found **{$count} ticket(s)**.";
                }
                return $result['message'];

            case 'getProjectStatus':
                if (isset($result['data']['stats'])) {
                    $stats = $result['data']['stats'];
                    $project = $result['data']['project'];
                    return "📊 **{$project->name}** Statistics:\n" .
                           "• Total Tickets: {$stats['total_tickets']}\n" .
                           "• Open: {$stats['open_tickets']}\n" .
                           "• Closed: {$stats['closed_tickets']}\n" .
                           "• Team Members: {$stats['team_members']}\n" .
                           "• Completion: {$stats['completion_rate']}%";
                }
                return $result['message'];

            case 'getTeamWorkload':
                if (isset($result['data'])) {
                    $workload = collect($result['data'])->map(function($member) {
                        $status = $member['status'] === 'Overloaded' ? '🔴' : '🟢';
                        return "{$status} {$member['name']}: {$member['active_tickets']} tickets";
                    })->join("\n");
                    return "👥 **Team Workload:**\n{$workload}";
                }
                return $result['message'];

            case 'createProject':
            case 'createTicket':
            case 'createBacklogItem':
            case 'createSprint':
                return $result['message'];

            case 'updateProject':
            case 'updateTicket':
            case 'assignTicket':
            case 'closeTicket':
                return $result['message'];

            case 'bulkUpdateTickets':
                if (isset($result['data']['count'])) {
                    return "✅ Successfully updated **{$result['data']['count']} ticket(s)**!";
                }
                return $result['message'];

            default:
                return $result['message'];
        }
    }
}
