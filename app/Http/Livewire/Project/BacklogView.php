<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\BacklogItem;
use App\Models\BacklogItemComment;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class BacklogView extends Component
{
    public $projectId;
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
    
    // Menu state
    public $showNewMenu = false;
    
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
    
    protected $listeners = [
        'itemMoved' => 'handleItemMoved',
        'refreshBacklog' => 'loadData',
        'showInlineCreate',
        'showQuickAddForm',
        'toggleItemSelection',
        'toggleExpand',
        'selectItem',
        'startEditing',
        'assignToSprint',
        'removeFromSprint',
        'deleteItem',
        'createInlineItem',
        'cancelInlineCreate',
        'selectAll',
        'deselectAll',
        'expandAll',
        'collapseAll',
        'quickAddItem',
        'cancelQuickAdd',
        'saveEdit',
        'cancelEdit',
        'addComment',
        'replyToComment',
        'cancelReply',
        'deleteComment',
        'applyBulkAction',
        'bulkDelete',
        'exportToCSV',
        'exportToJSON',
        'setViewMode',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        // Expand all epics by default
        $this->expandedItems = $this->project->backlogItems()
            ->where('type', BacklogItem::TYPE_EPIC)
            ->pluck('id')
            ->toArray();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }
    
    public function getBacklogItemsProperty()
    {
        $query = $this->project->backlogItems()
            ->with(['parent', 'children', 'assignee', 'sprint'])
            ->whereNull('deleted_at');
        
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
            if ($this->filterSprint === 'backlog') {
                $query->whereNull('sprint_id');
            } else {
                $query->where('sprint_id', $this->filterSprint);
            }
        }
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // For flat view, order by type hierarchy then order_index
        if ($this->viewMode === 'flat') {
            $query->orderByRaw("FIELD(type, 'Epic', 'Feature', 'UserStory', 'Task', 'Subtask')")
                  ->orderBy('order_index');
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
        return $this->project->users;
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
        // Close the menu
        $this->showNewMenu = false;
        
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
        
        // Auto-create linked Ticket for Task and Subtask types
        if (in_array($this->inlineCreateType, ['Task', 'Subtask'])) {
            $this->createLinkedTicket($item);
        }
        
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
        
        // Auto-create linked Ticket for Task and Subtask types
        if (in_array($this->quickAddType, ['Task', 'Subtask'])) {
            $this->createLinkedTicket($item);
        }
        
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
    
    // Alias for cancelEdit listener
    public function cancelEdit()
    {
        $this->cancelEditing();
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
            'editStartDate' => 'nullable|date',
            'editDueDate' => 'nullable|date|after_or_equal:editStartDate',
        ]);
        
        $item = BacklogItem::find($this->editingItemId);
        if (!$item) return;
        
        $item->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'status' => $this->editStatus,
            'priority' => $this->editPriority,
            'assignee_id' => $this->editAssigneeId,
            'sprint_id' => $this->editSprintId,
            'estimated_hours' => $this->editEstimatedHours,
            'start_date' => $this->editStartDate,
            'due_date' => $this->editDueDate,
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
    public function deleteItem($itemId)
    {
        $item = BacklogItem::find($itemId);
        if (!$item || $item->project_id !== $this->projectId) {
            return;
        }
        
        $item->delete();
        
        if ($this->selectedItemId === $itemId) {
            $this->selectedItemId = null;
        }
        
        session()->flash('success', 'Item deleted successfully!');
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
            // Get default status (To Do)
            $defaultStatus = TicketStatus::where('name', 'To Do')
                ->orWhere('name', 'Open')
                ->orWhere('name', 'New')
                ->first();
            
            if (!$defaultStatus) {
                $defaultStatus = TicketStatus::first();
            }
            
            // Create ticket linked to backlog item
            $ticket = Ticket::create([
                'name' => $backlogItem->title,
                'content' => $backlogItem->description ?? '',
                'project_id' => $backlogItem->project_id,
                'owner_id' => Auth::id(),
                'responsible_id' => $backlogItem->assignee_id,
                'status_id' => $defaultStatus?->id,
                'sprint_id' => $backlogItem->sprint_id,
                'estimation' => $backlogItem->estimated_hours,
            ]);
            
            // Link ticket to backlog item
            $ticket->update(['backlog_item_id' => $backlogItem->id]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create linked ticket: ' . $e->getMessage());
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

    public function render()
    {
        return view('livewire.project.backlog-view');
    }
}
