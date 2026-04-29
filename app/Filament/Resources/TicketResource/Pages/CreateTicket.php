<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Jobs\GenerateTicketsFromPrompt;
use App\Models\BacklogItem;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
          // Ensure project_id is always set
        if (request()->has('project_id')) {
            $data['project_id'] = request()->get('project_id');
        } elseif (!isset($data['project_id']) && $this->record?->project_id) {
            $data['project_id'] = $this->record->project_id;
        }
        // Pre-populate backlog fields from URL parameters
        if (request()->has('backlog_parent')) {
            $parent = BacklogItem::find(request()->get('backlog_parent'));
            if ($parent) {
                // Find hierarchy
                $userStory = $parent->type === BacklogItem::TYPE_USER_STORY ? $parent : null;
                $feature = null;
                $epic = null;
                
                $current = $parent;
                while ($current) {
                    if ($current->type === BacklogItem::TYPE_USER_STORY) {
                        $userStory = $current;
                    } elseif ($current->type === BacklogItem::TYPE_FEATURE) {
                        $feature = $current;
                    } elseif ($current->type === BacklogItem::TYPE_EPIC) {
                        $epic = $current;
                    }
                    $current = $current->parent;
                }
                
                $data['backlog_epic_id'] = $epic?->id;
                $data['backlog_feature_id'] = $feature?->id;
                $data['backlog_user_story_id'] = $userStory?->id;
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
{
    if (request()->has('project_id')) {
        $data['project_id'] = request()->get('project_id');
    }

    return $data;
}

    protected function afterCreate(): void
    {
        // Get backlog hierarchy from form data
        $formData = $this->form->getRawState();
        $backlogEpicId = $formData['backlog_epic_id'] ?? null;
        $backlogFeatureId = $formData['backlog_feature_id'] ?? null;
        $backlogUserStoryId = $formData['backlog_user_story_id'] ?? null;
        $backlogItemId = $formData['backlog_item_id'] ?? null;
        
        // Filter out placeholder values
        if ($backlogEpicId === '_placeholder') $backlogEpicId = null;
        if ($backlogFeatureId === '_placeholder') $backlogFeatureId = null;
        if ($backlogUserStoryId === '_placeholder') $backlogUserStoryId = null;
        
        try {
            // Case 1: Full backlog integration (Epic -> Feature -> User Story)
            if ($backlogEpicId && $backlogUserStoryId && is_numeric($backlogEpicId) && is_numeric($backlogUserStoryId)) {
                $parentId = $backlogUserStoryId; // Tasks go under User Story
                $type = BacklogItem::TYPE_TASK;
                
                $backlogItem = BacklogItem::create([
                    'project_id' => $this->record->project_id,
                    'parent_id' => $parentId,
                    'type' => $type,
                    'title' => $this->record->name,
                    'description' => $this->record->content,
                    'status' => BacklogItem::STATUS_TODO,
                    'priority' => BacklogItem::PRIORITY_MEDIUM,
                    'assignee_id' => $this->record->responsible_id,
                    'sprint_id' => $this->record->sprint_id,
                    'estimated_hours' => $this->record->estimation,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
                
                $this->record->update(['backlog_item_id' => $backlogItem->id]);
                Filament::notify('success', 'Ticket and backlog item created successfully!');
            }
            // Case 2: Simple Epic selection via backlog_item_id dropdown
            elseif ($backlogItemId && is_numeric($backlogItemId)) {
                $parentEpic = BacklogItem::find($backlogItemId);
                
                if ($parentEpic && $parentEpic->type === BacklogItem::TYPE_EPIC) {
                    // Create a Task directly under the Epic
                    $backlogItem = BacklogItem::create([
                        'project_id' => $this->record->project_id,
                        'parent_id' => $backlogItemId,
                        'type' => BacklogItem::TYPE_TASK,
                        'title' => $this->record->name,
                        'description' => $this->record->content,
                        'status' => BacklogItem::STATUS_TODO,
                        'priority' => BacklogItem::PRIORITY_MEDIUM,
                        'assignee_id' => $this->record->responsible_id,
                        'sprint_id' => $this->record->sprint_id,
                        'estimated_hours' => $this->record->estimation,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                    
                    $this->record->update(['backlog_item_id' => $backlogItem->id]);
                    Filament::notify('success', 'Ticket and backlog item created successfully!');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create backlog item from ticket: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            Filament::notify('warning', 'Ticket created but backlog item creation failed: ' . $e->getMessage());
        }
        
        // Use raw state to include non-dehydrated controls
        $data = $this->form->getRawState();
        $aiGenerate = $data['ai_generate'] ?? false;
        $aiPrompt = $data['ai_prompt'] ?? null;
        $aiResponsibleId = $data['ai_responsible_id'] ?? null;

        if ($aiGenerate && $aiPrompt) {
            // Dispatch job to generate sub-tasks
            $result = GenerateTicketsFromPrompt::dispatchSync(
                $this->record->project_id,
                $aiPrompt,
                $this->record->id, // Parent ticket
                $this->record->owner_id,
                $aiResponsibleId
            );

            if ($result['success']) {
                Filament::notify('success', $result['message']);
            } else {
                Filament::notify('warning', __('AI generation failed: ') . $result['message']);
            }
        }
    }
    
   protected function getRedirectUrl(): string
{
    // If created from backlog
    if (request()->has('backlog_parent')) {
        return route('filament.resources.projects.view', [
            'record' => $this->record->project_id,
        ]) . '?activeTab=backlog';
    }

    // If created from board
    if (request()->has('project_id')) {
        return route('filament.resources.projects.view', [
            'record' => $this->record->project_id,
        ]) . '?activeTab=board';
    }

    return parent::getRedirectUrl();
}
}
