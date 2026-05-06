<?php

namespace App\Http\Livewire;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\LocalAiService;
use App\Services\AI\ManagementActionHandler;
use App\Services\AI\HrActionHandler;
use App\Services\AI\ReferentialActionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EnhancedAiAssistant extends Component
{
    public array $messages = [];
    public string $input = '';
    public bool $open = false;
    public bool $isProcessing = false;
    public bool $fullscreen = false;
    
    // Section selection
    public ?string $selectedSection = null;
    public array $sections = [];
    
    // Conversation tracking
    public ?int $conversationId = null;
    public bool $hasActiveConversation = false;
    public ?int $lastConversationId = null;
    
    // Quick actions
    public array $quickActions = [];
    public bool $showQuickActions = true;

    protected $listeners = [
        'ai-toggle' => 'toggle',
        'refreshMessages' => '$refresh',
    ];

    public function mount(): void
    {
        $this->sections = config('ai.assistant.sections', []);
        $this->open = false;
        
        // Detect active conversation but do NOT auto-resume. Offer a Resume option in UI.
        try {
            $activeConversation = AiConversation::where('user_id', Auth::id())
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($activeConversation) {
                $this->hasActiveConversation = true;
                $this->lastConversationId = $activeConversation->id;
            }
        } catch (\Exception $e) {
            // Log the error but don't break the page load
            Log::warning('AI Assistant: Could not check for active conversation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
        }
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function toggleFullscreen(): void
    {
        $this->fullscreen = !$this->fullscreen;
    }

    public function selectSection(string $section): void
    {
        $this->selectedSection = $section;
        
        try {
            // Create new conversation
            $conversation = AiConversation::create([
                'user_id' => Auth::id(),
                'section' => $section,
                'status' => 'active',
            ]);

            $this->conversationId = $conversation->id;
            
            // Add welcome message
            $welcomeMessage = $this->getWelcomeMessage($section);
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $welcomeMessage,
            ]);

            $this->loadMessages();
            $this->loadQuickActions();
        } catch (\Exception $e) {
            Log::error('AI Assistant: Could not create conversation', [
                'error' => $e->getMessage(),
                'section' => $section,
                'user_id' => Auth::id()
            ]);
            
            // Show error message to user
            $this->messages = [[
                'role' => 'assistant',
                'content' => 'Sorry, I\'m having trouble connecting to the database. Please try again later.'
            ]];
        }
    }

    public function resumeLastConversation(): void
    {
        if (!$this->lastConversationId) {
            return;
        }

        try {
            $conversation = AiConversation::find($this->lastConversationId);
            if (!$conversation) {
                $this->hasActiveConversation = false;
                $this->lastConversationId = null;
                return;
            }

            $this->conversationId = $conversation->id;
            $this->selectedSection = $conversation->section;
            $this->loadMessages();
            $this->loadQuickActions();
        } catch (\Exception $e) {
            Log::error('AI Assistant: Could not resume conversation', [
                'error' => $e->getMessage(),
                'conversation_id' => $this->lastConversationId,
                'user_id' => Auth::id()
            ]);
            
            $this->hasActiveConversation = false;
            $this->lastConversationId = null;
        }
    }

    public function executeQuickAction(string $action): void
    {
        $this->input = $action;
        $this->send();
    }

    public function send(): void
    {
        if (empty($this->input) || !$this->conversationId) {
            return;
        }

        $this->isProcessing = true;
        $userMessage = trim($this->input);
        $this->input = '';

        try {
            $conversation = AiConversation::find($this->conversationId);
            
            // Save user message
            $conversation->messages()->create([
                'role' => 'user',
                'content' => $userMessage,
            ]);
            
            $this->loadMessages();

            // Process with local AI
            $response = $this->processWithLocalAi($conversation, $userMessage);

            // Save assistant response
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $response['message'],
                'metadata' => $response['metadata'] ?? null,
            ]);

            // Execute action if present
            if (isset($response['action']) && !empty($response['action'])) {
                $actionResult = $this->executeAction(
                    $response['action'],
                    $response['parameters'] ?? [],
                    $conversation->section
                );

                if ($actionResult['success']) {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $actionResult['message'],
                        'metadata' => $actionResult,
                    ]);
                }
            }

            $this->loadMessages();
            $this->showQuickActions = false;

        } catch (\Exception $e) {
            Log::error('AI Assistant Error: ' . $e->getMessage());
            
            try {
                $conversation = AiConversation::find($this->conversationId);
                if ($conversation) {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => "I apologize, but I'm having trouble processing your request. Please try rephrasing or check if the local AI service is running.",
                        'metadata' => ['error' => $e->getMessage()],
                    ]);
                    $this->loadMessages();
                }
            } catch (\Exception $dbError) {
                Log::error('AI Assistant: Could not save error message', [
                    'error' => $dbError->getMessage()
                ]);
                
                // Show error message directly
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => 'I\'m having trouble connecting to the database. Please try again later.'
                ];
            }
        } finally {
            $this->isProcessing = false;
        }
    }

    public function newConversation(): void
    {
        // Mark current as completed
        if ($this->conversationId) {
            try {
                AiConversation::find($this->conversationId)->update(['status' => 'completed']);
            } catch (\Exception $e) {
                Log::warning('AI Assistant: Could not mark conversation as completed', [
                    'error' => $e->getMessage(),
                    'conversation_id' => $this->conversationId
                ]);
            }
        }

        $this->selectedSection = null;
        $this->conversationId = null;
        $this->messages = [];
        $this->input = '';
        $this->showQuickActions = true;
        // Do not auto-resume
        $this->hasActiveConversation = false;
        $this->lastConversationId = null;
    }

    protected function processWithLocalAi(AiConversation $conversation, string $userMessage): array
    {
        $useLocal = config('ai.assistant.use_local', true);
        
        if (!$useLocal) {
            // Fallback to rule-based processing
            return $this->processRuleBased($conversation, $userMessage);
        }

        try {
            $localAi = new LocalAiService();
            
            // Build conversation history
            $messages = $this->buildConversationHistory($conversation);
            
            // Add system prompt
            $systemPrompt = $this->getSystemPrompt($conversation->section);
            array_unshift($messages, [
                'role' => 'system',
                'content' => $systemPrompt
            ]);

            // Generate response
            $aiResponse = $localAi->generate($messages);
            
            // Parse response
            return $this->parseAiResponse($aiResponse, $conversation->section);
            
        } catch (\Exception $e) {
            Log::warning('Local AI failed, falling back to rule-based: ' . $e->getMessage());
            return $this->processRuleBased($conversation, $userMessage);
        }
    }

    protected function processRuleBased(AiConversation $conversation, string $userMessage): array
    {
        $section = $conversation->section;
        $lower = strtolower($userMessage);

        // Simple intent detection
        if (preg_match('/create|add|new/i', $lower)) {
            if ($section === 'management') {
                if (preg_match('/project/i', $lower)) {
                    return [
                        'message' => "I'll help you create a project. What would you like to name it?",
                        'action' => 'createProject',
                        'parameters' => $this->extractProjectParameters($userMessage),
                        'requires_input' => empty($this->extractProjectParameters($userMessage)['name']),
                    ];
                }
                if (preg_match('/ticket/i', $lower)) {
                    return [
                        'message' => "I'll help you create a ticket. Please provide the title and project ID.",
                        'action' => 'createTicket',
                        'parameters' => [],
                        'requires_input' => true,
                    ];
                }
            } elseif ($section === 'hr') {
                if (preg_match('/leave|vacation/i', $lower)) {
                    return [
                        'message' => "I'll help you request leave. Please provide the leave type, start date, and end date.",
                        'action' => 'requestLeave',
                        'parameters' => [],
                        'requires_input' => true,
                    ];
                }
            } elseif ($section === 'referential') {
                if (preg_match('/department/i', $lower)) {
                    return [
                        'message' => "I'll help you create a department. What should it be called?",
                        'action' => 'createDepartment',
                        'parameters' => $this->extractDepartmentParameters($userMessage),
                        'requires_input' => true,
                    ];
                }
            }
        }

        if (preg_match('/list|show|view|get/i', $lower)) {
            if ($section === 'management' && preg_match('/project/i', $lower)) {
                return [
                    'message' => "Fetching your projects...",
                    'action' => 'listProjects',
                    'parameters' => [],
                ];
            }
        }

        if ($section === 'hr') {
            if (preg_match('/check\s*in/i', $lower)) {
                return [
                    'message' => "Checking you in...",
                    'action' => 'checkIn',
                    'parameters' => [],
                ];
            }
            if (preg_match('/check\s*out/i', $lower)) {
                return [
                    'message' => "Checking you out...",
                    'action' => 'checkOut',
                    'parameters' => [],
                ];
            }
        }

        return [
            'message' => "I understand you want to work with {$section}. Could you please be more specific? For example, you can say 'create a project' or 'show my attendance'.",
            'action' => null,
            'requires_input' => false,
        ];
    }

    protected function extractProjectParameters(string $text): array
    {
        $params = [];
        
        // Extract name
        if (preg_match('/project\s+(?:called|named)?\s*["\']?([^"\']+)["\']?/i', $text, $matches)) {
            $params['name'] = trim($matches[1]);
        } elseif (preg_match('/create\s+["\']?([^"\']+)["\']?\s+project/i', $text, $matches)) {
            $params['name'] = trim($matches[1]);
        }
        
        return $params;
    }

    protected function extractDepartmentParameters(string $text): array
    {
        $params = [];
        
        if (preg_match('/department\s+(?:called|named)?\s*["\']?([^"\']+)["\']?/i', $text, $matches)) {
            $params['name'] = trim($matches[1]);
        }
        
        return $params;
    }

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

    protected function getSystemPrompt(string $section): string
    {
        $basePrompt = "You are an AI assistant for a project management system. Respond in JSON format with: {\"action\": \"action_name\", \"parameters\": {...}, \"message\": \"user-friendly message\", \"requires_input\": false}. ";
        
        $sectionPrompts = [
            'management' => "You help manage projects, tickets, and tasks. Available actions: createProject, createTicket, listProjects, assignUserToProject, getProjectStatus. Extract parameters from user messages.",
            'hr' => "You help with HR operations: attendance, leaves, payroll, certificates. Available actions: checkIn, checkOut, requestLeave, approveLeave, generatePayslip, generateCertificate, getAttendanceSummary.",
            'referential' => "You help manage reference data: departments, positions, leave types. Available actions: createDepartment, createPosition, createLeaveType, listDepartments, listPositions, listLeaveTypes.",
        ];

        return $basePrompt . ($sectionPrompts[$section] ?? '');
    }

    protected function parseAiResponse(string $response, string $section): array
    {
        // Try to extract JSON
        $json = (new LocalAiService())->extractJson($response);
        
        if ($json && isset($json['action'])) {
            return [
                'message' => $json['message'] ?? 'Processing your request...',
                'action' => $json['action'],
                'parameters' => $json['parameters'] ?? [],
                'requires_input' => $json['requires_input'] ?? false,
                'metadata' => $json,
            ];
        }

        // Plain text response
        return [
            'message' => $response,
            'action' => null,
            'requires_input' => false,
        ];
    }

    protected function executeAction(string $action, array $parameters, string $section): array
    {
        $handlers = [
            'management' => new ManagementActionHandler(),
            'hr' => new HrActionHandler(),
            'referential' => new ReferentialActionHandler(),
        ];

        $handler = $handlers[$section] ?? null;
        
        if (!$handler || !method_exists($handler, $action)) {
            return [
                'success' => false,
                'message' => "Action '{$action}' is not supported.",
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

    protected function loadMessages(): void
    {
        if (!$this->conversationId) {
            return;
        }

        try {
            $conversation = AiConversation::find($this->conversationId);
            if (!$conversation) {
                $this->messages = [];
                return;
            }
            
            $this->messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content,
                        'time' => $message->created_at->diffForHumans(),
                        'metadata' => $message->metadata,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('AI Assistant: Could not load messages', [
                'error' => $e->getMessage(),
                'conversation_id' => $this->conversationId
            ]);
            
            $this->messages = [];
        }
    }

    protected function loadQuickActions(): void
    {
        $actions = [
            'management' => [
                'Create a new project',
                'Show all active projects',
                'Create a ticket',
                'Assign user to project',
            ],
            'hr' => [
                'Check in for today',
                'Check out',
                'Request leave',
                'Show my attendance summary',
                'Generate payslip',
            ],
            'referential' => [
                'Create a department',
                'Create a position',
                'List all departments',
                'Create a leave type',
            ],
        ];

        $this->quickActions = $actions[$this->selectedSection] ?? [];
    }

    protected function getWelcomeMessage(string $section): string
    {
        $messages = [
            'management' => "👋 Hello! I'm your AI assistant for project management. I can help you create projects, manage tickets, assign tasks, and track progress. What would you like to do?",
            'hr' => "👋 Hello! I'm your AI assistant for HR operations. I can help with attendance, leave requests, payroll, and certificates. How can I assist you today?",
            'referential' => "👋 Hello! I'm your AI assistant for managing reference data. I can help you set up departments, positions, leave types, and other master data. What would you like to configure?",
        ];

        return $messages[$section] ?? "Hello! How can I help you today?";
    }

    public function render()
    {
        return view('livewire.enhanced-ai-assistant');
    }
}
