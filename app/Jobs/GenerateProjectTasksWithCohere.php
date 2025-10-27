<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Services\CloudAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GenerateProjectTasksWithCohere implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        public int $projectId,
        public ?string $language = null,
        public ?string $context = null
    ) {}

    public function handle(CloudAiService $aiService): void
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

            // Check if AI service is available
            if (!$aiService->isAvailable()) {
                throw new \Exception('Cloud AI service is not available. Please check your API key and configuration.');
            }

            // Prepare description with context
            $desc = trim(strip_tags((string) $project->description));
            if ($this->context) {
                $desc .= "\n\nAdditional context:\n" . $this->context;
            }

            if (empty($desc)) {
                throw new \Exception('Project description is empty. Please provide a description for AI task generation.');
            }

            // Generate backlog from wiki/description using Cohere
            $backlog = $aiService->generateBacklogFromWiki($desc, $project->name);

            if (!is_array($backlog) || empty($backlog)) {
                throw new \Exception('Empty backlog generated from AI');
            }

            // Get defaults
            $statusId = $this->getDefaultStatusIdForProject($project->id);
            $typeId = TicketType::where('is_default', true)->value('id') ?? TicketType::query()->value('id');
            $priorityId = TicketPriority::where('is_default', true)->value('id') ?? TicketPriority::query()->value('id');
            $ownerId = $project->owner_id;

            // Create tickets from backlog structure
            if (isset($backlog['epics']) && is_array($backlog['epics'])) {
                foreach ($backlog['epics'] as $epicIndex => $epic) {
                    $this->createTicketFromBacklogItem(
                        $project->id,
                        $statusId,
                        $typeId,
                        $priorityId,
                        $ownerId,
                        $epic,
                        null,
                        $epicIndex,
                        'epic'
                    );
                }
            }

            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $project->ai_generation_status = 'success';
                $project->ai_last_message = 'Tasks generated successfully using Cohere AI';
                $project->save();
            }

            Log::info('Project tasks generated successfully', [
                'project_id' => $project->id,
                'provider' => $aiService->getProvider()
            ]);

        } catch (\Exception $e) {
            Log::error('AI task generation failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $project->ai_generation_status = 'failed';
                $project->ai_last_message = $e->getMessage();
                $project->save();
            }

            throw $e;
        }
    }

    private function createTicketFromBacklogItem(
        int $projectId,
        int $statusId,
        int $typeId,
        int $priorityId,
        int $ownerId,
        array $item,
        ?int $parentId,
        int $order,
        string $level = 'feature'
    ): ?Ticket {
        try {
            $ticket = Ticket::create([
                'project_id' => $projectId,
                'name' => $item['title'] ?? 'Untitled',
                'content' => $item['description'] ?? null,
                'status_id' => $statusId,
                'type_id' => $typeId,
                'priority_id' => $this->getPriorityIdFromLevel($item['priority'] ?? 'Medium', $priorityId),
                'owner_id' => $ownerId,
                'order' => $order,
                'estimation' => $item['estimated_hours'] ?? 8,
            ]);

            // Create child items (features, user stories, etc.)
            if (isset($item['features']) && is_array($item['features'])) {
                foreach ($item['features'] as $featureIndex => $feature) {
                    $this->createTicketFromBacklogItem(
                        $projectId,
                        $statusId,
                        $typeId,
                        $priorityId,
                        $ownerId,
                        $feature,
                        $ticket->id,
                        $featureIndex,
                        'feature'
                    );
                }
            }

            if (isset($item['user_stories']) && is_array($item['user_stories'])) {
                foreach ($item['user_stories'] as $storyIndex => $story) {
                    $this->createTicketFromBacklogItem(
                        $projectId,
                        $statusId,
                        $typeId,
                        $priorityId,
                        $ownerId,
                        $story,
                        $ticket->id,
                        $storyIndex,
                        'user_story'
                    );
                }
            }

            return $ticket;

        } catch (\Exception $e) {
            Log::error('Failed to create ticket from backlog item', [
                'error' => $e->getMessage(),
                'item' => $item
            ]);
            return null;
        }
    }

    private function getPriorityIdFromLevel(string $priority, int $default): int
    {
        $priorityMap = [
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
        ];

        $normalizedPriority = $priorityMap[strtolower($priority)] ?? 'Medium';
        return TicketPriority::where('name', $normalizedPriority)->value('id') ?? $default;
    }

    private function getDefaultStatusIdForProject(int $projectId): int
    {
        return TicketStatus::where('project_id', $projectId)
            ->where('is_default', true)
            ->value('id') ?? TicketStatus::where('project_id', $projectId)->value('id') ?? 1;
    }
}
