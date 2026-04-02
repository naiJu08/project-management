<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectChat as ProjectChatModel;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProjectChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2';

    protected static string $view = 'filament.pages.project-chat';

    protected static ?int $navigationSort = 3;

    public ?int $selectedProjectId = null;
    public string $message = '';
    public ?int $replyToId = null;
    public array $messages = [];

    protected static function getNavigationLabel(): string
    {
        return __('Project Chat');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public function mount(): void
    {
        // Auto-select first accessible project
        $firstProject = $this->getAccessibleProjects()->first();
        if ($firstProject) {
            $this->selectedProjectId = $firstProject->id;
            $this->loadMessages();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedProjectId')
                ->label(__('Select Project'))
                ->options($this->getAccessibleProjects()->pluck('name', 'id'))
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->loadMessages();
                })
                ->required(),
        ];
    }

    public function getAccessibleProjects()
    {
        return Project::where('owner_id', Auth::id())
            ->orWhereHas('users', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->orderBy('name')
            ->get();
    }

    public function loadMessages(): void
    {
        if (!$this->selectedProjectId) {
            $this->messages = [];
            return;
        }

        // ✅ MARK AS SEEN
        ProjectChatModel::where('project_id', $this->selectedProjectId)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        // Check access
        $project = Project::where('id', $this->selectedProjectId)
            ->where(function ($query) {
                $query->where('owner_id', Auth::id())
                    ->orWhereHas('users', function ($q) {
                        $q->where('users.id', Auth::id());
                    });
            })
            ->first();

        if (!$project) {
            $this->messages = [];
            return;
        }
        
        $this->messages = ProjectChatModel::forProject($this->selectedProjectId)
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'user_id' => $chat->user_id,
                    'user_name' => $chat->user->name,
                    'user_avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($chat->user->name) . '&background=3f84f3&color=ffffff',
                    'message' => $chat->message,
                    'created_at' => $chat->created_at->diffForHumans(),
                    'created_at_full' => $chat->created_at->format('Y-m-d H:i:s'),
                    'is_own' => $chat->user_id === Auth::id(),
                    'is_edited' => $chat->is_edited,
                    'seen_at' => $chat->seen_at,
                    'reply_to' => $chat->replyTo ? [
                        'user_name' => $chat->replyTo->user->name,
                        'message' => $chat->replyTo->message,
                    ] : null,
                ];
            })
            ->toArray();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|string|max:5000',
            'selectedProjectId' => 'required|exists:projects,id',
        ]);

        // Verify access
        $project = Project::where('id', $this->selectedProjectId)
            ->where(function ($query) {
                $query->where('owner_id', Auth::id())
                    ->orWhereHas('users', function ($q) {
                        $q->where('users.id', Auth::id());
                    });
            })
            ->first();

        if (!$project) {
            $this->notify('danger', __('You do not have access to this project'));
            return;
        }

        ProjectChatModel::create([
            'project_id' => $this->selectedProjectId,
            'user_id' => Auth::id(),
            'message' => $this->message,
            'reply_to_id' => $this->replyToId,
        ]);

        $this->message = '';
        $this->replyToId = null;
        $this->loadMessages();

        $this->dispatchBrowserEvent('message-sent');
    }

    public function setReplyTo(int $messageId): void
    {
        $this->replyToId = $messageId;
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
    }

    public function deleteMessage(int $messageId): void
    {
        $message = ProjectChatModel::where('id', $messageId)
            ->where('user_id', Auth::id())
            ->first();

        if ($message) {
            $message->delete();
            $this->loadMessages();
            $this->notify('success', __('Message deleted'));
        }
    }

    public function editMessage(int $messageId, string $newMessage): void
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        $message = ProjectChatModel::where('id', $messageId)
            ->where('user_id', Auth::id())
            ->first();

        if ($message) {
            $message->update([
                'message' => $newMessage,
                'is_edited' => true,
                'edited_at' => now(),
            ]);
            $this->loadMessages();
            $this->notify('success', __('Message updated'));
        }
    }

    public function refreshMessages(): void
    {
        $this->loadMessages();
    }
}
