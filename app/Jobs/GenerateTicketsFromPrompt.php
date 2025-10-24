<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Services\AiTaskGenerator;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateTicketsFromPrompt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $projectId;
    public string $prompt;
    public ?int $parentTicketId;
    public ?int $ownerId;
    public ?int $responsibleId;

    public function __construct(
        int $projectId,
        string $prompt,
        ?int $parentTicketId = null,
        ?int $ownerId = null,
        ?int $responsibleId = null
    ) {
        $this->projectId = $projectId;
        $this->prompt = $prompt;
        $this->parentTicketId = $parentTicketId;
        $this->ownerId = $ownerId;
        $this->responsibleId = $responsibleId;
    }

    public function handle(AiTaskGenerator $generator): array
    {
        $project = Project::find($this->projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found'];
        }

        try {
            // Get existing tickets for duplicate detection
            $existingTickets = Ticket::where('project_id', $this->projectId)
                ->get(['name', 'content'])
                ->map(function ($ticket) {
                    return [
                        'name' => strtolower($ticket->name),
                        'content' => strtolower(strip_tags($ticket->content))
                    ];
                })
                ->toArray();

            // Build context with existing tickets
            $context = "Existing tickets in project:\n";
            foreach (array_slice($existingTickets, 0, 20) as $existing) {
                $context .= "- " . $existing['name'] . "\n";
            }
            $context .= "\nNew requirement:\n" . $this->prompt;

            // Detect language and generate plan
            $lang = $generator->detectLanguage($this->prompt) ?: 'en';
            $plan = $generator->generatePlan($project->name, $context, $lang);

            if (!is_array($plan) || empty($plan)) {
                Log::info('AI ticket generation returned empty plan', [
                    'project_id' => $project->id,
                    'prompt' => $this->prompt
                ]);
                return ['success' => false, 'message' => 'Empty plan from AI provider'];
            }

            // Defaults
            $statusId = $this->defaultStatusIdForProject($project->id);
            $typeId = TicketType::where('is_default', true)->value('id') ?? TicketType::query()->value('id');
            $priorityId = TicketPriority::where('is_default', true)->value('id') ?? TicketPriority::query()->value('id');
            $ownerId = $this->ownerId ?? $project->owner_id;

            // Filter out duplicates and create tickets
            $createdTickets = [];
            foreach ($plan as $index => $task) {
                if (!$this->isDuplicate($task, $existingTickets)) {
                    $ticket = $this->createTicketRecursive(
                        $project->id,
                        $statusId,
                        $typeId,
                        $priorityId,
                        $ownerId,
                        $this->responsibleId,
                        $task,
                        $this->parentTicketId ? Ticket::find($this->parentTicketId) : null,
                        $index,
                        $existingTickets
                    );
                    $createdTickets[] = $ticket;
                }
            }

            return [
                'success' => true,
                'message' => 'Generated ' . count($createdTickets) . ' tickets',
                'tickets' => $createdTickets
            ];
        } catch (Exception $e) {
            Log::error('AI ticket generation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function isDuplicate(array $task, array $existingTickets): bool
    {
        $taskTitle = strtolower($task['title'] ?? '');
        $taskDesc = strtolower($task['description'] ?? '');

        foreach ($existingTickets as $existing) {
            // Check for high similarity in title
            similar_text($taskTitle, $existing['name'], $titleSimilarity);
            if ($titleSimilarity > 80) {
                return true;
            }

            // Check if task title is contained in existing ticket name or vice versa
            if (strlen($taskTitle) > 10 && (
                str_contains($existing['name'], $taskTitle) ||
                str_contains($taskTitle, $existing['name'])
            )) {
                return true;
            }
        }

        return false;
    }

    protected function defaultStatusIdForProject(int $projectId): ?int
    {
        $project = Project::find($projectId);
        if (!$project) return null;
        if ($project->status_type === 'custom') {
            return TicketStatus::where('project_id', $projectId)->where('is_default', true)->value('id')
                ?: TicketStatus::where('project_id', $projectId)->value('id');
        }
        return TicketStatus::whereNull('project_id')->where('is_default', true)->value('id')
            ?: TicketStatus::whereNull('project_id')->value('id');
    }

    protected function createTicketRecursive(
        int $projectId,
        ?int $statusId,
        ?int $typeId,
        ?int $priorityId,
        int $ownerId,
        ?int $responsibleId,
        array $task,
        ?Ticket $parent,
        int $sort,
        array &$existingTickets
    ): Ticket {
        $title = (string) ($task['title'] ?? 'Task');
        $description = (string) ($task['description'] ?? '');
        $priority = strtolower((string) ($task['priority'] ?? ''));
        $estimate = $task['estimate_hours'] ?? null;

        // Map AI priority to existing priority if possible
        if ($priority && in_array($priority, ['low','medium','high'])) {
            $priorityId = TicketPriority::whereRaw('LOWER(name) = ?', [$priority])->value('id') ?? $priorityId;
        }

        $ticket = Ticket::create([
            'name' => $title,
            'content' => $description ?: $title,
            'owner_id' => $ownerId,
            'responsible_id' => $responsibleId,
            'status_id' => $statusId,
            'project_id' => $projectId,
            'type_id' => $typeId,
            'priority_id' => $priorityId,
            'estimation' => is_numeric($estimate) ? (int)$estimate : null,
        ]);

        // Add to existing tickets to prevent duplicates in subtasks
        $existingTickets[] = [
            'name' => strtolower($ticket->name),
            'content' => strtolower(strip_tags($ticket->content))
        ];

        // Link to parent as a relation
        if ($parent) {
            TicketRelation::create([
                'ticket_id' => $parent->id,
                'type' => config('system.tickets.relations.default', 'related_to'),
                'relation_id' => $ticket->id,
                'sort' => $sort,
            ]);
        }

        // Recurse for subtasks
        $subs = $task['subtasks'] ?? [];
        if (is_array($subs)) {
            foreach (array_values($subs) as $i => $sub) {
                try {
                    if (!$this->isDuplicate($sub, $existingTickets)) {
                        $this->createTicketRecursive(
                            $projectId,
                            $statusId,
                            $typeId,
                            $priorityId,
                            $ownerId,
                            $responsibleId,
                            $sub,
                            $ticket,
                            $i,
                            $existingTickets
                        );
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to create subtask', ['error' => $e->getMessage()]);
                }
            }
        }

        return $ticket;
    }
}
