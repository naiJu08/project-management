<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\BacklogItem;
use App\Models\BacklogItemComment;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\TicketResource;

class ProjectDetail extends Component implements HasForms
{
    use InteractsWithForms;
    public $projectId;
    public $activeTab = 'board';
    public $enabledTabs = [];
    public $tabOrder = [];
    
    // Backlog properties
    public $selectedItemId;
    public $expandedItems = [];
    public $filterType = 'all';
    public $filterStatus = 'all';
    public $filterAssignee = 'all';
    public $filterSprint = 'all';
    public $searchTerm = '';
    
    // Quick add properties
    public $showQuickAdd = false;
    public $quickAddType = 'Epic';
    public $quickAddTitle = '';
    public $quickAddParentId = null;
    
    // Inline creation properties
    public $inlineCreateParentId = null;
    public $inlineCreateType = '';
    public $inlineCreateTitle = '';
    
    // Edit properties
    public $editingItemId = null;
    public $editTitle = '';
    public $editDescription = '';
    public $editStatus = '';
    public $editPriority = '';
    public $editAssigneeId = null;
    public $editSprintId = null;
    public $editEstimatedHours = null;
    public $editStartDate = null;
    public $editDueDate = null;
    
    // Comment properties
    public $newComment = '';
    public $replyToCommentId = null;
    
    // View mode
    public $viewMode = 'tree'; // tree or flat
    
    // Selection mode
    public $selectionMode = false;
    
    // Bulk operations
    public $selectedItems = [];
    public $bulkAction = '';
    public $bulkStatus = '';
    public $bulkPriority = '';
    public $bulkSprintId = null;
    public $showBulkPanel = false;
    public $bulkRemoveSprint = false;
    
    // Delete confirmation
    public $showDeleteConfirm = false;
    public $itemToDelete = null;

    protected $queryString = ['activeTab' => ['except' => 'board']];
    
    protected $listeners = ['tabPreferencesUpdated' => 'reloadTabPreferences'];
    

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        
        // Verify project exists and user has access
        $project = Project::findOrFail($projectId);
        
        if (!$this->canAccessProject($project)) {
            abort(403, 'You do not have access to this project.');
        }
        
        // Load tab preferences
        $this->loadTabPreferences();
        
        // Expand all epics by default for backlog
        $this->expandedItems = $project->backlogItems()
            ->where('type', BacklogItem::TYPE_EPIC)
            ->pluck('id')
            ->toArray();
    }

    public function getProjectProperty()
    {
        return Project::with(['owner', 'status', 'users'])
            ->findOrFail($this->projectId);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getBacklogItemsProperty()
    {
        $query = $this->project->backlogItems()
            ->with(['parent', 'children', 'assignee', 'sprint', 'tickets'])
            ->orderBy('order_index');
        
        // Apply filters
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }
        
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }
        
        if ($this->filterAssignee !== 'all') {
            $query->where('assignee_id', $this->filterAssignee);
        }
        
        if ($this->filterSprint !== 'all') {
            if ($this->filterSprint === 'none') {
                $query->whereNull('sprint_id');
            } else {
                $query->where('sprint_id', $this->filterSprint);
            }
        }
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        return $query->get();
    }
    
    public function getSelectedItemProperty()
    {
        if (!$this->selectedItemId) {
            return null;
        }
        
        return BacklogItem::with(['parent', 'children', 'assignee', 'sprint', 'createdBy', 'updatedBy'])
            ->find($this->selectedItemId);
    }
    
    public function getSprintsProperty()
    {
        return $this->project->sprints()->orderBy('starts_at', 'desc')->get();
    }
    
    public function getTeamMembersProperty()
    {
        // Get project members
        $members = $this->project->users;
        
        // Add project owner if not already in the list
        $owner = $this->project->owner;
        if ($owner && !$members->contains('id', $owner->id)) {
            $members->prepend($owner);
        }
        
        return $members;
    }
    
    public function getAvailableParentsProperty()
    {
        // Get available parents based on quick add type
        if (!$this->quickAddType) {
            return collect([]);
        }
        
        return match($this->quickAddType) {
            'Epic' => collect([]), // Epics have no parent
            'Feature' => $this->backlogItems->where('type', BacklogItem::TYPE_EPIC),
            'UserStory' => $this->backlogItems->where('type', BacklogItem::TYPE_FEATURE),
            'Task' => $this->backlogItems->where('type', BacklogItem::TYPE_USER_STORY),
            'Subtask' => $this->backlogItems->where('type', BacklogItem::TYPE_TASK),
            default => collect([]),
        };
    }
    
    public function getCommentsProperty()
    {
        if (!$this->selectedItemId) {
            return collect([]);
        }
        
        return BacklogItemComment::where('backlog_item_id', $this->selectedItemId)
            ->whereNull('parent_comment_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }
    
    public function getHistoryProperty()
    {
        if (!$this->selectedItemId) {
            return collect([]);
        }
        
        return $this->selectedItem->histories()
            ->with('user')
            ->latest()
            ->get();
    }

    // Item selection and navigation
    public function selectItem($itemId)
    {
        $this->selectedItemId = $itemId;
        $this->resetEditForm();
    }
    
    public function toggleExpand($itemId)
    {
        if (in_array($itemId, $this->expandedItems)) {
            $this->expandedItems = array_diff($this->expandedItems, [$itemId]);
        } else {
            $this->expandedItems[] = $itemId;
        }
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }
    
    public function expandAll()
    {
        $this->expandedItems = $this->backlogItems->pluck('id')->toArray();
    }
    
    public function collapseAll()
    {
        $this->expandedItems = [];
    }

    // Quick add functionality
    public function showQuickAddForm($type = 'Epic', $parentId = null)
    {
        // For header dropdown without parent, show the quick add panel with parent selection
        if ($parentId === null && $type !== 'Epic') {
            $this->showQuickAdd = true;
            $this->quickAddType = $type;
            $this->quickAddParentId = $parentId;
            $this->quickAddTitle = '';
        } else {
            // For items with parent or Epic, use inline creation
            $this->showInlineCreate($parentId, $type);
        }
    }
    
    public function cancelQuickAdd()
    {
        $this->showQuickAdd = false;
        $this->quickAddTitle = '';
        $this->quickAddParentId = null;
    }
    
    // Inline creation methods
    public function showInlineCreate($parentId, $type)
    {
        // Note: Task/Subtask now use direct links to ticket form in blade templates
        // This method only handles Epic, Feature, User Story inline creation
        
        // Cancel any existing inline creation
        $this->cancelInlineCreate();
        
        $this->inlineCreateParentId = $parentId;
        $this->inlineCreateType = $type;
        $this->inlineCreateTitle = '';
        
        // Expand parent if not already expanded
        if ($parentId && !in_array($parentId, $this->expandedItems)) {
            $this->expandedItems[] = $parentId;
        }
    }
    
    public function cancelInlineCreate()
    {
        $this->inlineCreateParentId = null;
        $this->inlineCreateType = '';
        $this->inlineCreateTitle = '';
    }
    
    public function createInlineItem()
    {
        if (!$this->inlineCreateTitle) {
            return;
        }
        
        $this->validate([
            'inlineCreateTitle' => 'required|min:3|max:255',
        ]);
        
        // Validate parent-child relationship
        if ($this->inlineCreateParentId) {
            $parent = BacklogItem::find($this->inlineCreateParentId);
            if (!$parent || !$parent->canBeParentOf($this->inlineCreateType)) {
                session()->flash('error', 'Invalid parent-child relationship.');
                return;
            }
        }
        
        $item = BacklogItem::create([
            'project_id' => $this->projectId,
            'parent_id' => $this->inlineCreateParentId,
            'type' => $this->inlineCreateType,
            'title' => $this->inlineCreateTitle,
            'status' => BacklogItem::STATUS_TODO,
            'priority' => BacklogItem::PRIORITY_MEDIUM,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        
        // Note: Task/Subtask creation is now handled via ticket form
        // No auto-ticket creation here
        
        $this->cancelInlineCreate();
        $this->selectItem($item->id);
        
        session()->flash('success', ucfirst($this->inlineCreateType) . ' created successfully!');
    }
    
    public function quickAddItem()
    {
        $this->validate([
            'quickAddTitle' => 'required|min:3|max:255',
        ]);
        
        // Validate parent requirement for non-Epic items
        if ($this->quickAddType !== 'Epic' && !$this->quickAddParentId) {
            session()->flash('error', $this->quickAddType . ' must have a parent. Please select a parent.');
            return;
        }
        
        // Validate parent-child relationship
        if ($this->quickAddParentId) {
            $parent = BacklogItem::find($this->quickAddParentId);
            if (!$parent || !$parent->canBeParentOf($this->quickAddType)) {
                session()->flash('error', 'Invalid parent-child relationship.');
                return;
            }
        }
        
        $item = BacklogItem::create([
            'project_id' => $this->projectId,
            'parent_id' => $this->quickAddParentId,
            'type' => $this->quickAddType,
            'title' => $this->quickAddTitle,
            'status' => BacklogItem::STATUS_TODO,
            'priority' => BacklogItem::PRIORITY_MEDIUM,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        
        // Note: Task/Subtask creation should use ticket form instead
        // No auto-ticket creation here
        
        $this->cancelQuickAdd();
        $this->selectItem($item->id);
        
        // Expand parent if exists
        if ($this->quickAddParentId && !in_array($this->quickAddParentId, $this->expandedItems)) {
            $this->expandedItems[] = $this->quickAddParentId;
        }
        
        session()->flash('success', ucfirst($this->quickAddType) . ' created successfully!');
    }

    // Edit functionality
    public function startEditing($itemId)
    {
        $item = BacklogItem::find($itemId);
        if (!$item) return;
        
        $this->editingItemId = $itemId;
        $this->editTitle = $item->title;
        $this->editDescription = $item->description ?? '';
        $this->editStatus = $item->status;
        $this->editPriority = $item->priority;
        $this->editAssigneeId = $item->assignee_id;
        $this->editSprintId = $item->sprint_id;
        $this->editEstimatedHours = $item->estimated_hours;
        $this->editStartDate = $item->start_date?->format('Y-m-d');
        $this->editDueDate = $item->due_date?->format('Y-m-d');
    }
    
    public function cancelEditing()
    {
        $this->resetEditForm();
    }
    
    public function saveItem()
    {
        $this->validate([
            'editTitle' => 'required|min:3|max:255',
            'editStatus' => 'required|in:' . implode(',', [
                BacklogItem::STATUS_TODO,
                BacklogItem::STATUS_IN_PROGRESS,
                BacklogItem::STATUS_DONE,
                BacklogItem::STATUS_BLOCKED,
            ]),
            'editPriority' => 'required|in:' . implode(',', [
                BacklogItem::PRIORITY_CRITICAL,
                BacklogItem::PRIORITY_HIGH,
                BacklogItem::PRIORITY_MEDIUM,
                BacklogItem::PRIORITY_LOW,
            ]),
            'editEstimatedHours' => 'nullable|numeric|min:0',
        ]);
        
        $item = BacklogItem::find($this->editingItemId);
        if (!$item) return;
        
        $item->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'status' => $this->editStatus,
            'priority' => $this->editPriority,
            'assignee_id' => $this->editAssigneeId === '' ? null : $this->editAssigneeId,
            'sprint_id' => $this->editSprintId === '' ? null : $this->editSprintId,
            'estimated_hours' => $this->editEstimatedHours === '' ? null : $this->editEstimatedHours,
            'start_date' => $this->editStartDate === '' ? null : $this->editStartDate,
            'due_date' => $this->editDueDate === '' ? null : $this->editDueDate,
            'updated_by' => Auth::id(),
        ]);
        
        $this->selectedItemId = $item->id;
        $this->resetEditForm();
        
        session()->flash('success', 'Item updated successfully!');
    }
    
    public function updateField($itemId, $field, $value)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $item->update([
            $field => $value,
            'updated_by' => Auth::id(),
        ]);
        
        $this->emit('itemUpdated', $itemId);
    }
    
    protected function resetEditForm()
    {
        $this->editingItemId = null;
        $this->editTitle = '';
        $this->editDescription = '';
        $this->editStatus = '';
        $this->editPriority = '';
        $this->editAssigneeId = null;
        $this->editSprintId = null;
        $this->editEstimatedHours = null;
        $this->editStartDate = null;
        $this->editDueDate = null;
    }

    // Delete functionality
    public function confirmDeleteItem($itemId)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $this->itemToDelete = $itemId;
        $this->showDeleteConfirm = true;
    }
    
    public function deleteItem()
    {
        $item = BacklogItem::find($this->itemToDelete);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $item->delete();
        
        if ($this->selectedItemId === $this->itemToDelete) {
            $this->selectedItemId = null;
        }
        
        session()->flash('success', 'Item deleted successfully!');
        
        $this->showDeleteConfirm = false;
        $this->itemToDelete = null;
    }
    
    public function cancelDeleteItem()
    {
        $this->showDeleteConfirm = false;
        $this->itemToDelete = null;
    }

    // Comment functionality
    public function addComment()
    {
        if (!$this->selectedItemId || !$this->newComment) {
            return;
        }
        
        $this->validate([
            'newComment' => 'required|min:1|max:2000',
        ]);
        
        BacklogItemComment::create([
            'backlog_item_id' => $this->selectedItemId,
            'user_id' => Auth::id(),
            'parent_comment_id' => $this->replyToCommentId,
            'content' => $this->newComment,
        ]);
        
        $this->newComment = '';
        $this->replyToCommentId = null;
        
        session()->flash('success', 'Comment added!');
    }
    
    public function replyToComment($commentId)
    {
        $this->replyToCommentId = $commentId;
    }
    
    public function cancelReply()
    {
        $this->replyToCommentId = null;
    }
    
    public function deleteComment($commentId)
    {
        $comment = BacklogItemComment::find($commentId);
        if ($comment && ($comment->user_id === Auth::id() || Auth::user()->can('Delete backlog item'))) {
            $comment->delete();
            session()->flash('success', 'Comment deleted!');
        }
    }

    // Drag and drop handler
    public function handleItemMoved($itemId, $newParentId, $newOrderIndex)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        // Validate parent-child relationship
        if ($newParentId) {
            $newParent = BacklogItem::find($newParentId);
            if (!$newParent || !$newParent->canBeParentOf($item->type)) {
                session()->flash('error', 'Invalid parent-child relationship.');
                return;
            }
        }
        
        try {
            $item->reorder($newOrderIndex, $newParentId);
            session()->flash('success', 'Item moved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to move item: ' . $e->getMessage());
        }
    }

    // Sprint assignment
    public function assignToSprint($itemId, $sprintId)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $item->moveToSprint($sprintId);
        session()->flash('success', 'Item assigned to sprint!');
    }
    
    public function removeFromSprint($itemId)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $item->update(['sprint_id' => null, 'updated_by' => Auth::id()]);
        session()->flash('success', 'Item removed from sprint!');
    }

    // Bulk operations
    public function toggleItemSelection($itemId)
    {
        if (in_array($itemId, $this->selectedItems)) {
            $this->selectedItems = array_diff($this->selectedItems, [$itemId]);
        } else {
            $this->selectedItems[] = $itemId;
        }
        
        $this->showBulkPanel = count($this->selectedItems) > 0;
    }
    
    public function toggleSelectionMode()
    {
        $this->selectionMode = !$this->selectionMode;
        if (!$this->selectionMode) {
            $this->selectedItems = [];
            $this->showBulkPanel = false;
        }
    }
    
    public function selectAll()
    {
        $this->selectionMode = true;
        $this->selectedItems = $this->backlogItems->pluck('id')->toArray();
        $this->showBulkPanel = true;
    }
    
    public function deselectAll()
    {
        $this->selectedItems = [];
        $this->showBulkPanel = false;
    }
    
    public function applyBulkAction()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'No items selected');
            return;
        }
        
        $count = 0;
        
        foreach ($this->selectedItems as $itemId) {
            $item = BacklogItem::find($itemId);
            if (!$item || $item->project_id !== $this->projectId) {
                continue;
            }
            
            $updates = ['updated_by' => Auth::id()];
            
            if ($this->bulkStatus) {
                $updates['status'] = $this->bulkStatus;
            }
            
            if ($this->bulkPriority) {
                $updates['priority'] = $this->bulkPriority;
            }
            
            if ($this->bulkSprintId !== null) {
                $updates['sprint_id'] = $this->bulkSprintId === '' ? null : $this->bulkSprintId;
            }
            
            if (count($updates) > 1) { // More than just updated_by
                $item->update($updates);
                $count++;
            }
        }
        
        $this->deselectAll();
        $this->bulkStatus = '';
        $this->bulkPriority = '';
        $this->bulkSprintId = null;
        
        session()->flash('success', "Updated {$count} items successfully!");
    }
    
    public function bulkDelete()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'No items selected');
            return;
        }
        
        $count = 0;
        
        foreach ($this->selectedItems as $itemId) {
            $item = BacklogItem::find($itemId);
            if ($item && $item->project_id === $this->projectId) {
                $item->delete();
                $count++;
            }
        }
        
        $this->deselectAll();
        session()->flash('success', "Deleted {$count} items successfully!");
    }
    
    // Helper method to create linked ticket for Task/Subtask
    protected function createLinkedTicket(BacklogItem $backlogItem)
    {
        try {
            $project = Project::find($backlogItem->project_id);
            
            // Get default status based on project type
            if ($project?->status_type === 'custom') {
                $defaultStatus = TicketStatus::where('project_id', $project->id)
                    ->where('is_default', true)
                    ->first();
            } else {
                $defaultStatus = TicketStatus::whereNull('project_id')
                    ->where('is_default', true)
                    ->first();
            }
            
            // Fallback to any status if no default found
            if (!$defaultStatus) {
                $defaultStatus = TicketStatus::whereNull('project_id')
                    ->orWhere('project_id', $project->id)
                    ->first();
            }
            
            if (!$defaultStatus) {
                \Log::warning('No ticket status found for project ' . $backlogItem->project_id);
                return;
            }
            
            // Find Epic in hierarchy (Epic -> Feature -> User Story -> Task/Subtask)
            $epicId = null;
            $current = $backlogItem;
            while ($current) {
                if ($current->type === BacklogItem::TYPE_EPIC) {
                    $epicId = $current->id;
                    break;
                }
                $current = $current->parent;
            }
            
            // Create ticket linked to backlog item
            $ticket = Ticket::create([
                'name' => $backlogItem->title,
                'content' => $backlogItem->description ?? '',
                'project_id' => $backlogItem->project_id,
                'owner_id' => Auth::id(),
                'responsible_id' => $backlogItem->assignee_id,
                'status_id' => $defaultStatus->id,
                'sprint_id' => $backlogItem->sprint_id,
                'estimation' => $backlogItem->estimated_hours,
                'epic_id' => $epicId,
                'backlog_item_id' => $backlogItem->id,
            ]);
            
            \Log::info('Created ticket ' . $ticket->code . ' for backlog item ' . $backlogItem->code);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create linked ticket: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            // Don't fail the backlog item creation if ticket creation fails
        }
    }
    
    // Export functionality
    public function exportToCSV()
    {
        $items = $this->backlogItems;
        
        $filename = 'backlog_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Code', 'Type', 'Title', 'Description', 'Status', 'Priority',
                'Assignee', 'Sprint', 'Estimated Hours', 'Start Date', 'Due Date',
                'Parent Code', 'Created At', 'Updated At'
            ]);
            
            // Data
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->code,
                    $item->type,
                    $item->title,
                    $item->description,
                    $item->status,
                    $item->priority,
                    $item->assignee ? $item->assignee->name : '',
                    $item->sprint ? $item->sprint->name : '',
                    $item->estimated_hours,
                    $item->start_date ? $item->start_date->format('Y-m-d') : '',
                    $item->due_date ? $item->due_date->format('Y-m-d') : '',
                    $item->parent ? $item->parent->code : '',
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    public function exportToJSON()
    {
        $items = $this->backlogItems->map(function($item) {
            return [
                'code' => $item->code,
                'type' => $item->type,
                'title' => $item->title,
                'description' => $item->description,
                'status' => $item->status,
                'priority' => $item->priority,
                'assignee' => $item->assignee ? $item->assignee->name : null,
                'sprint' => $item->sprint ? $item->sprint->name : null,
                'estimated_hours' => $item->estimated_hours,
                'start_date' => $item->start_date ? $item->start_date->format('Y-m-d') : null,
                'due_date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
                'parent_code' => $item->parent ? $item->parent->code : null,
                'children_count' => $item->children->count(),
                'completion_percentage' => $item->getCompletionPercentage(),
                'total_estimated_hours' => $item->getTotalEstimatedHours(),
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        
        $filename = 'backlog_export_' . date('Y-m-d_His') . '.json';
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->json($items, 200, $headers);
    }

    public function loadTabPreferences()
    {
        $userId = Auth::id();
        $cacheKey = "project_tabs_{$this->projectId}_user_{$userId}";
        
        if ($this->isClientWikiUser()) {
            $preferences = [
                'enabled' => ['client-wiki'],
                'order' => ['client-wiki'],
            ];
            $this->activeTab = 'client-wiki';
        } else {
            $preferences = cache($cacheKey, [
                'enabled' => ['board', 'overview', 'list', 'backlog', 'sprint', 'dashboard', 'calendar', 'wiki', 'gantt', 'chat', 'time-tracking', 'reports', 'milestones', 'budget'],
                'order' => ['board', 'overview', 'list', 'backlog', 'sprint', 'dashboard', 'calendar', 'wiki', 'gantt', 'chat', 'time-tracking', 'reports', 'milestones', 'budget'],
            ]);
        }

        $this->enabledTabs = $preferences['enabled'];
        $this->tabOrder = $preferences['order'];
        
        // Set active tab to first enabled tab if current tab is not enabled
        if (!in_array($this->activeTab, $this->enabledTabs) && !empty($this->enabledTabs)) {
            $this->activeTab = $this->enabledTabs[0];
        }
    }

    public function reloadTabPreferences()
    {
        $this->loadTabPreferences();
    }

    private function canAccessProject(Project $project): bool
    {
        $user = auth()->user();

        if ($this->isClientWikiUser()) {
            return \App\Models\WikiPage::where('project_id', $project->id)
                ->clientVisible()
                ->exists();
        }

        return $project->owner_id == $user->id
            || $project->users()->where('users.id', $user->id)->exists();
    }

    private function isClientWikiUser(): bool
    {
        $user = Auth::user();

        return $user->roles->contains(fn ($role) => strtolower($role->name) === 'client')
            && $user->can('View client wiki');
    }

    public function render()
{
    return view('livewire.project-detail');
}

    public function form(Form $form): Form
    {
        return $form
            ->schema(TicketResource::getFormSchema())
            ->model(Ticket::class);
    }
}

