<?php

namespace App\Filament\Pages;

use App\Models\AiConversation;
use App\Services\AiAssistantService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AiAssistant extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2';

    protected static string $view = 'filament.pages.ai-assistant';

    protected static ?string $navigationLabel = 'AI Assistant';

    protected static ?string $title = 'AI Assistant';

    protected static ?int $navigationSort = 1;

    public $selectedSection = null;
    public $conversationId = null;
    public $messages = [];
    public $userInput = '';
    public $sections = [];
    public $isProcessing = false;

    protected $listeners = ['refreshMessages' => '$refresh'];

    public function mount()
    {
        $service = new AiAssistantService();
        $this->sections = $service->getSections();
        
        // Load active conversation if exists
        $activeConversation = AiConversation::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($activeConversation) {
            $this->conversationId = $activeConversation->id;
            $this->selectedSection = $activeConversation->section;
            $this->loadMessages();
        }
    }

    public function selectSection($section)
    {
        $this->selectedSection = $section;
        
        // Create new conversation
        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'section' => $section,
            'status' => 'active',
        ]);

        $this->conversationId = $conversation->id;
        
        // Add welcome message
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $this->getWelcomeMessage($section),
        ]);

        $this->loadMessages();
    }

    public function sendMessage()
    {
        if (empty($this->userInput) || !$this->conversationId) {
            return;
        }

        $this->isProcessing = true;

        try {
            $service = new AiAssistantService();
            $conversation = AiConversation::find($this->conversationId);

            // Process the message
            $response = $service->processPrompt($conversation, $this->userInput);

            // Execute action if present
            if (isset($response['action']) && !empty($response['action'])) {
                $actionResult = $service->executeAction(
                    $response['action'],
                    $response['parameters'] ?? [],
                    $conversation->section
                );

                // Add action result as assistant message
                if ($actionResult['success']) {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $actionResult['message'],
                        'metadata' => $actionResult,
                    ]);
                }
            }

            $this->userInput = '';
            $this->loadMessages();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error')
                ->body('Failed to process your message. Please try again.')
                ->danger()
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function newConversation()
    {
        // Mark current conversation as completed
        if ($this->conversationId) {
            AiConversation::find($this->conversationId)->update(['status' => 'completed']);
        }

        $this->selectedSection = null;
        $this->conversationId = null;
        $this->messages = [];
        $this->userInput = '';
    }

    protected function loadMessages()
    {
        if (!$this->conversationId) {
            return;
        }

        $conversation = AiConversation::find($this->conversationId);
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
    }

    protected function getWelcomeMessage($section): string
    {
        $messages = [
            'management' => "Hello! I'm your AI assistant for project management. I can help you create projects, assign tasks, track tickets, and manage your team. What would you like to do today?",
            'hr' => "Hello! I'm your AI assistant for HR operations. I can help you manage attendance, process leave requests, generate payslips and certificates, and more. How can I assist you today?",
            'referential' => "Hello! I'm your AI assistant for managing reference data. I can help you set up departments, positions, leave types, ticket categories, and other master data. What would you like to configure?",
        ];

        return $messages[$section] ?? "Hello! How can I help you today?";
    }
}
