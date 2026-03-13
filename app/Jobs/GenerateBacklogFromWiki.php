<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\WikiPage;
use App\Models\BacklogItem;
use App\Services\OllamaService;
use App\Services\CloudAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GenerateBacklogFromWiki implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes - AI generation can take time
    public $tries = 1;

    protected $projectId;
    protected $userId;
    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct($projectId, $userId, $jobId)
    {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Determine which AI service to use
            $useCloudAi = config('services.cloud_ai.enabled', false);
            
            if ($useCloudAi) {
                $aiService = app(CloudAiService::class);
                $serviceName = ucfirst($aiService->getProvider());
                $this->updateProgress("Checking {$serviceName} API...", 10);
            } else {
                $aiService = app(OllamaService::class);
                $serviceName = 'Ollama';
                $this->updateProgress('Checking Ollama service...', 10);
            }

            // Check if AI service is available
            if (!$aiService->isAvailable()) {
                $this->updateProgress("{$serviceName} service is not available", 0, 'error');
                return;
            }

            $this->updateProgress('Collecting wiki content...', 20);

            // Collect all wiki content
            $project = Project::findOrFail($this->projectId);
            $wikiContent = $this->collectAllWikiContent($project);

            if (empty($wikiContent)) {
                $this->updateProgress('No wiki content found', 0, 'error');
                return;
            }

            $this->updateProgress('Detecting language...', 30);

            // Detect language
            $language = $aiService->detectLanguage(substr($wikiContent, 0, 1000));

            $this->updateProgress("Analyzing content with {$serviceName} (Language: {$language})...", 40);

            // Generate backlog structure
            $backlogData = $aiService->generateBacklogFromWiki($wikiContent, $project->name);

            $this->updateProgress('Creating backlog items...', 70);

            // Create backlog items
            $createdCount = $this->createBacklogItems($backlogData, $project);

            $this->updateProgress("Successfully created {$createdCount} backlog items!", 100, 'success');

        } catch (\Exception $e) {
            Log::error('Backlog generation failed: ' . $e->getMessage(), [
                'project_id' => $this->projectId,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->updateProgress('Error: ' . $e->getMessage(), 0, 'error');
        }
    }

    /**
     * Update progress in cache
     */
    protected function updateProgress($message, $percentage, $status = 'processing')
    {
        Cache::put("backlog_generation_{$this->jobId}", [
            'message' => $message,
            'percentage' => $percentage,
            'status' => $status,
            'updated_at' => now()->toIso8601String()
        ], 600); // 10 minutes
    }

    /**
     * Collect all wiki content
     */
    protected function collectAllWikiContent($project)
    {
        $pages = WikiPage::where('project_id', $project->id)
            ->orderBy('order')
            ->get();

        $content = "# {$project->name} - Project Documentation\n\n";
        
        foreach ($pages as $page) {
            $content .= "## {$page->title}\n\n";
            $content .= strip_tags($page->content) . "\n\n";
            
            // Include child pages
            foreach ($page->children as $child) {
                $content .= "### {$child->title}\n\n";
                $content .= strip_tags($child->content) . "\n\n";
            }
        }

        return $content;
    }

    /**
     * Create backlog items from generated data
     */
    protected function createBacklogItems($backlogData, $project)
    {
        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($backlogData['epics'] as $epicData) {
                // Create Epic
                $epic = BacklogItem::create([
                    'project_id' => $project->id,
                    'type' => BacklogItem::TYPE_EPIC,
                    'title' => $epicData['title'],
                    'description' => $epicData['description'],
                    'priority' => $this->mapPriority($epicData['priority']),
                    'status' => 'backlog',
                    'created_by' => $this->userId,
                ]);
                $createdCount++;

                // Create Features under Epic
                if (isset($epicData['features'])) {
                    foreach ($epicData['features'] as $featureData) {
                        $feature = BacklogItem::create([
                            'project_id' => $project->id,
                            'parent_id' => $epic->id,
                            'type' => BacklogItem::TYPE_FEATURE,
                            'title' => $featureData['title'],
                            'description' => $featureData['description'],
                            'priority' => $this->mapPriority($featureData['priority']),
                            'status' => 'backlog',
                            'created_by' => $this->userId,
                        ]);
                        $createdCount++;

                        // Create User Stories under Feature
                        if (isset($featureData['user_stories'])) {
                            foreach ($featureData['user_stories'] as $storyData) {
                                $acceptanceCriteria = isset($storyData['acceptance_criteria']) 
                                    ? implode("\n", array_map(fn($c) => "- {$c}", $storyData['acceptance_criteria']))
                                    : '';

                                $userStory = BacklogItem::create([
                                    'project_id' => $project->id,
                                    'parent_id' => $feature->id,
                                    'type' => BacklogItem::TYPE_USER_STORY,
                                    'title' => $storyData['title'],
                                    'description' => $storyData['description'] . "\n\n**Acceptance Criteria:**\n" . $acceptanceCriteria,
                                    'priority' => $this->mapPriority($storyData['priority']),
                                    'status' => 'backlog',
                                    'estimated_hours' => $storyData['estimated_hours'] ?? 8,
                                    'created_by' => $this->userId,
                                ]);
                                $createdCount++;
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $createdCount;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Map priority from AI output to system priority
     */
    protected function mapPriority($aiPriority)
    {
        $priorityMap = [
            'High' => 'high',
            'Medium' => 'medium',
            'Low' => 'low',
        ];

        return $priorityMap[$aiPriority] ?? 'medium';
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Backlog generation job failed', [
            'project_id' => $this->projectId,
            'error' => $exception->getMessage()
        ]);

        $this->updateProgress('Job failed: ' . $exception->getMessage(), 0, 'error');
    }
}
