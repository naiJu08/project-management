<?php

namespace App\Http\Livewire;

use App\Jobs\GenerateProjectTasks;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AiAssistantPanel extends Component
{
    public array $messages = [];
    public string $input = '';

    // State for creation flow
    public string $mode = 'idle'; // idle | creating
    public ?string $pendingField = null;
    public array $draft = [];
    public bool $autoGenerateTasks = false;
    public ?string $aiContext = null;

    public bool $open = false;

    protected $listeners = [
        'ai-toggle' => 'toggle',
    ];

    public function mount(): void
    {
        $this->messages = [
            ['role' => 'assistant', 'text' => 'Hi! Describe the project you want to create. I will ask for any missing details.'],
        ];
        $this->draft = [];
        $this->open = false;
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function send(): void
    {
        $text = trim($this->input);
        if ($text === '') return;

        $this->messages[] = ['role' => 'user', 'text' => $text];
        $this->input = '';

        if ($this->mode === 'idle') {
            if ($this->detectCreateIntent($text)) {
                $this->mode = 'creating';
                $this->assistantRespond('Got it — creating a new project from your prompt...');
                $this->startProjectDraftFromPrompt($text);
                return;
            }
            // Not a creation intent; guide the user succinctly
            $this->assistantRespond('To create a project, say something like: "Create project <Name> ..." You can include a description to auto-generate tickets.');
            return;
        }

        if ($this->mode === 'creating') {
            $this->handleCreationAnswer($text);
            return;
        }
    }

    protected function assistantRespond(string $text): void
    {
        $this->messages[] = ['role' => 'assistant', 'text' => $text];
    }

    protected function startProjectDraftFromPrompt(string $prompt): void
    {
        // Very simple parsing heuristics
        $name = $this->extractName($prompt) ?? $prompt;
        $type = $this->extractType($prompt) ?? 'kanban';
        $statusType = 'default';
        $ownerId = Auth::id();
        $statusId = ProjectStatus::where('is_default', true)->value('id') ?? ProjectStatus::query()->value('id');
        $ticketPrefix = $this->generateTicketPrefix($name);
        $description = trim($prompt);

        $this->draft = [
            'name' => $name,
            'ticket_prefix' => $ticketPrefix,
            'owner_id' => $ownerId,
            'status_id' => $statusId,
            'type' => $type,
            'status_type' => $statusType,
            'description' => $description,
        ];

        // If prompt contains a meaningful description, enable auto generation implicitly
        if ($this->hasMeaningfulDescription($description)) {
            $this->autoGenerateTasks = true;
        }

        $missing = $this->validateDraft(returnMissing: true);
        if (!empty($missing)) {
            $this->askNext($missing);
            return;
        }

        $this->finalizeCreation();
    }

    protected function handleCreationAnswer(string $answer): void
    {
        if (!$this->pendingField) {
            // No pending question; treat as improvements to description
            $this->draft['description'] = trim(($this->draft['description'] ?? '') . "\n\n" . $answer);
        } else {
            switch ($this->pendingField) {
                case 'name':
                    $this->draft['name'] = $answer;
                    if (empty($this->draft['ticket_prefix'])) {
                        $this->draft['ticket_prefix'] = $this->generateTicketPrefix($answer);
                    }
                    break;
                case 'ticket_prefix':
                    $this->draft['ticket_prefix'] = strtoupper(preg_replace('/[^A-Z]/', '', strtoupper($answer)));
                    break;
                case 'type':
                    $type = strtolower(trim($answer));
                    $this->draft['type'] = in_array($type, ['kanban','scrum']) ? $type : 'kanban';
                    break;
                case 'status_id':
                    $this->draft['status_id'] = $this->matchStatusId($answer) ?? $this->draft['status_id'] ?? null;
                    break;
                case 'owner_id':
                    $this->draft['owner_id'] = $this->matchUserId($answer) ?? $this->draft['owner_id'] ?? null;
                    break;
                case 'description':
                    $this->draft['description'] = $answer;
                    break;
                default:
                    // Append to description by default
                    $this->draft['description'] = trim(($this->draft['description'] ?? '') . "\n\n" . $answer);
            }
        }

        $this->pendingField = null;
        $missing = $this->validateDraft(returnMissing: true);
        if (!empty($missing)) {
            $this->askNext($missing);
            return;
        }

        $this->finalizeCreation();
    }

    protected function askNext(array $missing): void
    {
        $field = array_shift($missing);
        $this->pendingField = $field;

        switch ($field) {
            case 'name':
                $this->assistantRespond('What should be the project name?');
                break;
            case 'ticket_prefix':
                $this->assistantRespond('Provide a unique 1-3 letter ticket prefix (A-Z). Example: PRJ');
                break;
            case 'owner_id':
                $this->assistantRespond('Who is the project owner? You can reply with a name or email.');
                break;
            case 'status_id':
                $this->assistantRespond('Which initial project status? Reply with a status name (e.g. Active, Planned).');
                break;
            case 'type':
                $this->assistantRespond('Choose project type: kanban or scrum.');
                break;
            case 'description':
                $this->assistantRespond('Add a short project description.');
                break;
            default:
                $this->assistantRespond('Please provide: ' . $field);
        }
    }

    protected function validateDraft(bool $returnMissing = false)
    {
        $data = $this->draft;
        $rules = [
            'name' => ['required','string','max:255'],
            'ticket_prefix' => ['required','string','min:1','max:3', Rule::unique('projects', 'ticket_prefix')],
            'owner_id' => ['required','integer','exists:users,id'],
            'status_id' => ['required','integer','exists:project_statuses,id'],
            'type' => ['required','in:kanban,scrum'],
            'status_type' => ['required','in:default,custom'],
            'description' => ['nullable','string'],
        ];

        if ($returnMissing) {
            $missing = [];
            foreach ($rules as $key => $ruleSet) {
                if (!array_key_exists($key, $data) || ($data[$key] === null || $data[$key] === '')) {
                    $missing[] = $key;
                }
            }
            // Additionally pre-validate current values, e.g., ticket_prefix uniqueness
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $msgs) {
                    // If value present but invalid, prioritize asking this field next
                    if (!in_array($key, $missing)) {
                        array_unshift($missing, $key);
                        $this->assistantRespond($msgs[0]);
                    }
                }
            }
            return $missing;
        }

        return Validator::make($data, $rules)->validate();
    }

    protected function finalizeCreation(): void
    {
        // Validate and create
        $data = $this->validateDraft(returnMissing: false);

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status_id' => $data['status_id'],
            'owner_id' => $data['owner_id'],
            'ticket_prefix' => strtoupper($data['ticket_prefix']),
            'status_type' => $data['status_type'],
            'type' => $data['type'],
        ]);

        // Dispatch task generation when either explicitly requested or implied via description presence
        if ($this->autoGenerateTasks || $this->hasMeaningfulDescription($data['description'] ?? '')) {
            // Mark AI status and dispatch background task generation
            $project->ai_generation_status = 'running';
            $project->ai_last_run_at = now();
            $project->ai_last_message = null;
            $project->save();
            GenerateProjectTasks::dispatch($project->id, null, $this->aiContext);
        }

        Filament::notify('success', 'Project created: ' . $project->name);
        $this->assistantRespond('Project created successfully. You can continue chatting or close this panel.');

        // Reset for next session
        $this->mode = 'idle';
        $this->pendingField = null;
        $this->draft = [];
        $this->autoGenerateTasks = false;
        $this->aiContext = null;
    }

    protected function generateTicketPrefix(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Z]/', '', strtoupper($name)));
        $base = substr($base, 0, 3) ?: 'PRJ';
        $prefix = $base;
        $i = 0;
        while (Project::where('ticket_prefix', $prefix)->exists()) {
            $i++;
            $suffix = strtoupper(base_convert($i, 10, 36));
            $prefix = substr($base, 0, max(1, 3 - strlen($suffix))) . $suffix;
            $prefix = substr($prefix, 0, 3);
        }
        return $prefix;
    }

    protected function extractType(string $text): ?string
    {
        $t = strtolower($text);
        if (str_contains($t, 'scrum')) return 'scrum';
        if (str_contains($t, 'kanban')) return 'kanban';
        return null;
    }

    protected function extractName(string $text): ?string
    {
        // Heuristic: quoted text or before keywords
        if (preg_match('/\"([^\"]{3,})\"/', $text, $m)) return trim($m[1]);
        if (preg_match('/project\s+([A-Za-z0-9 _\-]{3,})/i', $text, $m)) return trim($m[1]);
        return null;
    }

    protected function detectCreateIntent(string $text): bool
    {
        $t = strtolower($text);
        return (bool) preg_match('/\b(create|new|start|setup|spin up|open)\b.*\bproject\b|\bproject\b.*\b(create|new|start)\b/i', $t);
    }

    protected function hasMeaningfulDescription(string $text): bool
    {
        $t = trim(strip_tags($text));
        // consider meaningful if longer than a few words or contains sentence punctuation
        return strlen($t) >= 20 || substr_count($t, ' ') >= 3 || preg_match('/[\.!?]/', $t);
    }

    protected function matchStatusId(string $name): ?int
    {
        $q = trim($name);
        return ProjectStatus::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($q)])
            ->value('id');
    }

    protected function matchUserId(string $query): ?int
    {
        $q = trim($query);
        $user = User::query()
            ->where('email', $q)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($q)])
            ->first();
        return $user?->id;
    }

    public function render()
    {
        return view('livewire.ai-assistant-panel', [
            'statuses' => ProjectStatus::all(['id','name']),
            'users' => User::all(['id','name','email']),
        ]);
    }
}
