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
use Illuminate\Support\Facades\Schema;

class GenerateProjectTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $projectId;
    public ?string $language;
    public ?string $context;

    public function __construct(int $projectId, ?string $language = null, ?string $context = null)
    {
        $this->projectId = $projectId;
        $this->language = $language;
        $this->context = $context;
    }

    public function handle(AiTaskGenerator $generator): void
    {
        $project = Project::find($this->projectId);
        if (!$project) {
            return;
        }

        try {
            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $project->ai_generation_status = 'running';
                $project->ai_last_run_at = now();
                $project->ai_last_message = null;
                $project->save();
            }

            $desc = trim(strip_tags((string) $project->description));
            if ($this->context) {
                $desc .= "\n\nAdditional context:\n" . $this->context;
            }

            // Detect language and generate plan
            $lang = $this->language ?: $generator->detectLanguage($desc) ?: app()->getLocale();
            $plan = $generator->generatePlan($project->name, $desc, $lang);
            if (!is_array($plan) || empty($plan)) {
                Log::info('AI task generation returned empty plan', ['project_id' => $project->id]);
                if (Schema::hasColumn('projects', 'ai_generation_status')) {
                    $project->ai_generation_status = 'failed';
                    $project->ai_last_message = 'Empty plan from AI provider';
                    $project->save();
                }
                return;
            }

            // Defaults
            $statusId = $this->defaultStatusIdForProject($project->id);
            $typeId = TicketType::where('is_default', true)->value('id') ?? TicketType::query()->value('id');
            $priorityId = TicketPriority::where('is_default', true)->value('id') ?? TicketPriority::query()->value('id');
            $ownerId = $project->owner_id;

            // Create tasks recursively
            foreach ($plan as $index => $task) {
                $this->createTicketRecursive($project->id, $statusId, $typeId, $priorityId, $ownerId, $task, null, $index);
            }

            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $project->ai_generation_status = 'success';
                $project->ai_last_message = 'Tasks generated';
                $project->save();
            }
        } catch (Exception $e) {
            Log::error('AI task generation failed', ['error' => $e->getMessage()]);
            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $project->ai_generation_status = 'failed';
                $project->ai_last_message = $e->getMessage();
                $project->save();
            }
        }
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
        array $task,
        ?Ticket $parent,
        int $sort
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
            'responsible_id' => null,
            'status_id' => $statusId,
            'project_id' => $projectId,
            'type_id' => $typeId,
            'priority_id' => $priorityId,
            'estimation' => is_numeric($estimate) ? (int)$estimate : null,
        ]);

        // Link to parent as a relation to emulate subtask hierarchy
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
                    $this->createTicketRecursive($projectId, $statusId, $typeId, $priorityId, $ownerId, $sub, $ticket, $i);
                } catch (Exception $e) {
                    Log::warning('Failed to create subtask', ['error' => $e->getMessage()]);
                }
            }
        }

        return $ticket;
    }
}
