<?php

namespace App\Http\Livewire\Ticket;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHour;
use App\Models\TicketRelation;
use Livewire\Component;
use Illuminate\Support\Collection;
use App\Models\User;

class EmployeeTicketDetail extends Component
{
    use \Livewire\WithFileUploads;

    protected $casts = [
        'hoursToLog' => 'float',
        'minutesToLog' => 'integer',
    ];

    // ==================== CORE PROPERTIES ====================
    public Ticket $ticket;
    public string $activeTab = 'overview';
    
    // ==================== TIME TRACKING PROPERTIES ====================
    public $hoursToLog = 0.0;
    public $minutesToLog = 0;
    public string $timeDescription = '';
    public bool $showTimeForm = false;
    public ?int $editingTimeId = null;
    
    // ==================== COMMENTS PROPERTIES ====================
    public string $newComment = '';
    public bool $showCommentForm = false;
    public ?int $editingCommentId = null;
    public string $editingCommentContent = '';
    public $commentAttachment = null;
    public string $searchComments = '';
    
    // ==================== STATUS UPDATE PROPERTIES ====================
    public ?int $newStatus = null;
    public bool $showStatusForm = false;
    public string $statusError = '';
    
    // ==================== RELATIONSHIPS PROPERTIES ====================
    public bool $showRelationForm = false;
    public string $relationType = 'related_to';
    public ?int $relationTicketId = null;
    public string $searchTicket = '';
    public $searchResults = [];
    public bool $showTicketSearch = false;
    
    // ==================== REALTIME TRACKING PROPERTIES ====================
    public bool $isTracking = false;
    public int $trackingSeconds = 0;
    
    // ==================== DATE EDITING PROPERTIES ====================
    public ?string $editStartDate = null;
    public ?string $editDueDate = null;
    public bool $showDateEdit = false;
    public bool $showMasterEdit = false;
    public array $masterEditData = [];
    
    protected $listeners = ['ticketUpdated' => 'refreshTicket'];

    public $responsibleUsers = [];

    public function mount(Ticket $ticket): void
    {
         $this->ticket = $ticket;

    $this->responsibleUsers = User::whereIn(
        'id',
        $this->ticket->responsible_ids ?? []
    )->get();
    
        $this->searchResults = collect();
        $this->masterEditData = [];
        
        $this->ticket->load([
            'comments.user',
            'hours.user',
            'relations.relation',
            'status',
            'priority',
            'type',
            'owner',
            'responsible',
            'sprint',
            'project',
            'backlogItem'
        ]);

        // Initialize tracking state from cache so it survives reloads/logouts
        $this->loadTrackingStateFromCache();
    }

    public function selectTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ==================== TIME TRACKING ====================
    
    public function updatedHoursToLog($value): void
    {
        $this->hoursToLog = (float)$value;
    }

    public function updatedMinutesToLog($value): void
    {
        $this->minutesToLog = (int)$value;
    }
    
    public function logTime(): void
    {
        $this->validate([
            'hoursToLog' => 'nullable|numeric|min:0|max:24',
            'minutesToLog' => 'nullable|integer|min:0|max:59',
            'timeDescription' => 'nullable|string|max:500',
        ]);

        $hours = (float)$this->hoursToLog;
        $minutes = (int)$this->minutesToLog;
        $totalHours = $hours + ($minutes / 60);
        
        if ($totalHours <= 0) {
            $this->notify('error', 'Please enter at least 1 minute to log.');
            return;
        }

        TicketHour::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'value' => round($totalHours, 2),
            'comment' => $this->timeDescription,
        ]);

        $this->resetTimeForm();
        $this->ticket->refresh();
        $this->notify('success', 'Time logged successfully');
    }

    public function editTime(int $timeId): void
    {
        $time = TicketHour::findOrFail($timeId);
        
        if ($time->user_id !== auth()->id()) {
            $this->notify('error', 'You can only edit your own time entries');
            return;
        }

        $this->editingTimeId = $timeId;
        $this->hoursToLog = floor($time->value);
        $this->minutesToLog = (int)round(($time->value - floor($time->value)) * 60);
        $this->timeDescription = (string)($time->comment ?? '');
        $this->showTimeForm = true;
    }

    public function updateTime(): void
    {
        $this->validate([
            'hoursToLog' => 'nullable|numeric|min:0|max:24',
            'minutesToLog' => 'nullable|integer|min:0|max:59',
            'timeDescription' => 'nullable|string|max:500',
        ]);

        $hours = (float)$this->hoursToLog;
        $minutes = (int)$this->minutesToLog;
        $totalHours = $hours + ($minutes / 60);
        
        if ($totalHours <= 0) {
            $this->notify('error', 'Please enter at least 1 minute to log.');
            return;
        }

        $time = TicketHour::findOrFail($this->editingTimeId);
        
        if ($time->user_id !== auth()->id()) {
            $this->notify('error', 'You can only edit your own time entries');
            return;
        }

        $time->update([
            'value' => round($totalHours, 2),
            'comment' => $this->timeDescription,
        ]);

        $this->resetTimeForm();
        $this->ticket->refresh();
        $this->notify('success', 'Time entry updated');
    }

    public function deleteTime(int $timeId): void
    {
        $time = TicketHour::findOrFail($timeId);
        
        if ($time->user_id !== auth()->id()) {
            $this->notify('error', 'You can only delete your own time entries');
            return;
        }

        $time->delete();
        $this->ticket->refresh();
        $this->notify('success', 'Time entry deleted');
    }

    public function resetTimeForm(): void
    {
        $this->hoursToLog = 0.0;
        $this->minutesToLog = 0;
        $this->timeDescription = '';
        $this->showTimeForm = false;
        $this->editingTimeId = null;
    }

    // ==================== COMMENTS ====================

    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'required|string|min:1|max:5000',
            'commentAttachment' => 'nullable|file|max:10240',
        ]);

        try {
            $content = $this->newComment;
            $attachmentPath = null;

            if ($this->commentAttachment) {
                $attachmentPath = $this->commentAttachment->store('comments/attachments', 'public');
            }

            $comment = new TicketComment([
                'content' => $content,
                'user_id' => auth()->id(),
                'attachment_path' => $attachmentPath,
            ]);

            $this->ticket->comments()->save($comment);
            $this->resetCommentForm();
            $this->notify('success', 'Comment added successfully!');
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

    public function toggleCommentForm(): void
    {
        $this->showCommentForm = !$this->showCommentForm;
        $this->dispatchBrowserEvent('showCommentFormToggled');
    }

    public function editComment(int $commentId): void
    {
        $comment = TicketComment::findOrFail($commentId);
        
        if ($comment->user_id !== auth()->id()) {
            $this->notify('error', 'You can only edit your own comments');
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editingCommentContent = $comment->content;
    }

    public function updateComment(): void
    {
        $this->validate([
            'editingCommentContent' => 'required|string|min:1|max:5000',
        ]);

        $comment = TicketComment::findOrFail($this->editingCommentId);
        
        if ($comment->user_id !== auth()->id()) {
            $this->notify('error', 'You can only edit your own comments');
            return;
        }

        $comment->update([
            'content' => $this->editingCommentContent,
        ]);

        $this->editingCommentId = null;
        $this->editingCommentContent = '';
        $this->ticket->refresh();
        $this->notify('success', 'Comment updated');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = TicketComment::findOrFail($commentId);
        
        if ($comment->user_id !== auth()->id()) {
            $this->notify('error', 'You can only delete your own comments');
            return;
        }

        $comment->delete();
        $this->ticket->refresh();
        $this->notify('success', 'Comment deleted');
    }

    private function resetCommentForm(): void
    {
        $this->newComment = '';
        $this->commentAttachment = null;
        $this->showCommentForm = false;
        $this->dispatchBrowserEvent('clearTrixEditor');
        $this->ticket->refresh();
    }

    // ==================== STATUS UPDATES ====================

    public function updateStatus(): void
    {
        try {
            if (!$this->newStatus) {
                $this->statusError = 'Please select a status';
                return;
            }

            $status = \App\Models\TicketStatus::findOrFail($this->newStatus);
            $this->ticket->update(['status_id' => $this->newStatus]);
            $this->newStatus = null;
            $this->showStatusForm = false;
            $this->statusError = '';
            $this->ticket->refresh();
            $this->notify('success', 'Status updated to ' . $status->name);
        } catch (\Exception $e) {
            $this->statusError = 'Failed to update status: ' . $e->getMessage();
        }
    }

    public function toggleStatusForm(): void
    {
        $this->showStatusForm = !$this->showStatusForm;
        if ($this->showStatusForm) {
            $this->dispatchBrowserEvent('showStatusForm');
        }
    }

    // ==================== RELATIONSHIPS ====================

    public function searchTickets(): void
    {
        if (strlen($this->searchTicket) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Ticket::where('project_id', $this->ticket->project_id)
            ->where('id', '!=', $this->ticket->id)
            ->where(function ($query) {
                $query->where('code', 'like', '%' . $this->searchTicket . '%')
                    ->orWhere('name', 'like', '%' . $this->searchTicket . '%');
            })
            ->limit(10)
            ->get();
    }

    public function selectTicketForRelation(int $ticketId): void
    {
        $this->relationTicketId = $ticketId;
        $this->searchResults = [];
        $this->searchTicket = '';
        $this->showTicketSearch = false;
    }

    public function addRelation(): void
    {
        $this->validate([
            'relationTicketId' => 'required|integer|exists:tickets,id',
            'relationType' => 'required|in:related_to,duplicates,blocks,blocked_by',
        ]);

        if ($this->relationTicketId === $this->ticket->id) {
            $this->notify('error', 'Cannot relate ticket to itself');
            return;
        }

        TicketRelation::create([
            'ticket_id' => $this->ticket->id,
            'relation_id' => $this->relationTicketId,
            'type' => $this->relationType,
        ]);

        $this->relationTicketId = null;
        $this->relationType = 'related_to';
        $this->showRelationForm = false;
        $this->searchTicket = '';
        $this->searchResults = [];
        $this->ticket->refresh();
        $this->notify('success', 'Relationship added');
    }

    public function removeRelation(int $relationId): void
    {
        TicketRelation::findOrFail($relationId)->delete();
        $this->ticket->refresh();
        $this->notify('success', 'Relationship removed');
    }

    // ==================== COMPUTED PROPERTIES ====================

    public function getTotalLoggedHoursProperty(): float
    {
        return $this->ticket->hours->sum('value');
    }

    public function getRemainingHoursProperty(): ?float
    {
        if (!$this->ticket->estimated_hours) {
            return null;
        }
        return $this->ticket->estimated_hours - $this->totalLoggedHours;
    }

    public function getProgressPercentageProperty(): float
    {
        if (!$this->ticket->estimated_hours) {
            return 0;
        }
        return min(100, ($this->totalLoggedHours / $this->ticket->estimated_hours) * 100);
    }

    public function getFilteredCommentsProperty(): Collection
    {
        return $this->ticket->comments()
            ->with('user')
            ->latest()
            ->get()
            ->filter(function ($comment) {
                if (empty($this->searchComments)) {
                    return true;
                }
                return stripos($comment->content, $this->searchComments) !== false;
            });
    }

    public function getAvailableStatusesProperty(): Collection
    {
        // Get statuses that are valid transitions from current status
        return \App\Models\TicketStatus::all();
    }

    public function getTicketSearchResultsProperty(): Collection
    {
        $query = Ticket::where('project_id', $this->ticket->project_id)
            ->where('id', '!=', $this->ticket->id)
            ->with('status');
        
        if ($this->searchTicket) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->searchTicket . '%')
                  ->orWhere('name', 'like', '%' . $this->searchTicket . '%');
            });
        }
        
        return $query->limit(20)->get();
    }

    // ==================== REALTIME TRACKING ====================

    public function startTracking(): void
    {
        $key = $this->getTrackingCacheKey();
        $state = cache()->get($key, [
            'is_tracking' => false,
            'started_at' => null,
            'seconds_accumulated' => 0,
        ]);

        if (!$state['is_tracking']) {
            $state['is_tracking'] = true;
            $state['started_at'] = now();
            cache()->put($key, $state, now()->addDay());
        }

        $this->isTracking = true;
        $this->updateTrackingSecondsFromState($state);
        $this->dispatchBrowserEvent('trackingStarted');
    }

    public function stopTracking(): void
    {
        $key = $this->getTrackingCacheKey();
        $state = cache()->get($key);

        $totalSeconds = $this->trackingSeconds;
        if ($state && $state['is_tracking']) {
            $elapsed = now()->diffInSeconds($state['started_at']);
            $totalSeconds = $state['seconds_accumulated'] + $elapsed;
        }

        $this->isTracking = false;
        $this->dispatchBrowserEvent('trackingStopped');

        if ($totalSeconds > 0) {
            try {
                $hours = $totalSeconds / 3600;

                TicketHour::create([
                    'ticket_id' => $this->ticket->id,
                    'user_id' => auth()->id(),
                    'value' => round($hours, 2),
                    'comment' => 'Tracked time - ' . now()->format('Y-m-d H:i'),
                ]);

                $this->ticket->refresh();
                $this->trackingSeconds = 0;
                $this->notify('success', 'Tracked time saved successfully!');
            } catch (\Exception $e) {
                $this->notify('error', 'Failed to save tracked time: ' . $e->getMessage());
            }
        }

        // Clear tracking state
        cache()->forget($key);
    }

    public function syncTrackingState(): void
    {
        $this->pollTracking();
    }

    public function pollTracking(): void
    {
        $key = $this->getTrackingCacheKey();
        $state = cache()->get($key);
        if ($state && $state['is_tracking']) {
            $this->isTracking = true;
            $this->updateTrackingSecondsFromState($state);
        } else {
            $this->isTracking = false;
        }
    }

    private function getTrackingCacheKey(): string
    {
        return 'ticket_tracking_' . auth()->id() . '_' . $this->ticket->id;
    }

    private function loadTrackingStateFromCache(): void
    {
        $key = $this->getTrackingCacheKey();
        $state = cache()->get($key);
        if ($state && $state['is_tracking']) {
            $this->isTracking = true;
            $this->updateTrackingSecondsFromState($state);
        } else {
            $this->isTracking = false;
            $this->trackingSeconds = 0;
        }
    }

    private function updateTrackingSecondsFromState(array $state): void
    {
        $elapsed = 0;
        if (!empty($state['started_at'])) {
            $elapsed = now()->diffInSeconds($state['started_at']);
        }
        $this->trackingSeconds = (int)($state['seconds_accumulated'] + $elapsed);
    }

    // ==================== DATES EDITING ====================

    public function editDates(): void
    {
        try {
            $this->editStartDate = $this->ticket->start_date instanceof \DateTime 
                ? $this->ticket->start_date->format('Y-m-d')
                : (is_string($this->ticket->start_date) ? $this->ticket->start_date : null);
            
            $this->editDueDate = $this->ticket->due_date instanceof \DateTime
                ? $this->ticket->due_date->format('Y-m-d')
                : (is_string($this->ticket->due_date) ? $this->ticket->due_date : null);
            
            $this->showDateEdit = true;
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to load dates: ' . $e->getMessage());
        }
    }

    public function saveDates(): void
    {
        try {
            $this->validate([
                'editStartDate' => 'nullable|date_format:Y-m-d',
                'editDueDate' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            ]);

            $updateData = [];
            
            if (!empty($this->editStartDate)) {
                $updateData['start_date'] = $this->editStartDate;
            }
            
            if (!empty($this->editDueDate)) {
                $updateData['due_date'] = $this->editDueDate;
            }

            if (!empty($updateData)) {
                $this->ticket->update($updateData);
            }

            $this->showDateEdit = false;
            $this->editStartDate = null;
            $this->editDueDate = null;
            $this->ticket->refresh();
            $this->notify('success', 'Dates updated successfully');
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to update dates: ' . $e->getMessage());
        }
    }

    public function openMasterEdit(): void
    {
        try {
            $this->masterEditData = [
                'name' => (string)$this->ticket->name,
                'content' => (string)($this->ticket->content ?? ''),
                'estimated_hours' => (float)($this->ticket->estimated_hours ?? 0),
                'priority_id' => (int)($this->ticket->priority_id ?? 0),
                'status_id' => (int)$this->ticket->status_id,
                'start_date' => $this->ticket->start_date instanceof \DateTime 
                    ? $this->ticket->start_date->format('Y-m-d')
                    : (is_string($this->ticket->start_date) ? $this->ticket->start_date : null),
                'due_date' => $this->ticket->due_date instanceof \DateTime
                    ? $this->ticket->due_date->format('Y-m-d')
                    : (is_string($this->ticket->due_date) ? $this->ticket->due_date : null),
            ];
            $this->showMasterEdit = true;
            $this->dispatchBrowserEvent('masterEditOpened');
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to open master edit: ' . $e->getMessage());
        }
    }

    public function saveMasterEdit()
    {
        try {
            // Explicit check for zero estimated hours
            if (isset($this->masterEditData['estimated_hours']) && (float)$this->masterEditData['estimated_hours'] <= 0) {
                $this->addError('masterEditData.estimated_hours', 'Estimation time must be greater than 0.');
                return;
            }

            $this->validate([
                'masterEditData.name' => 'required|string|max:255',
                'masterEditData.content' => 'nullable|string|max:5000',
                'masterEditData.start_date' => 'nullable|date_format:Y-m-d',
                'masterEditData.due_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
                'masterEditData.estimated_hours' => 'required|numeric|min:0.01|max:999',
                'masterEditData.priority_id' => 'nullable|integer|exists:ticket_priorities,id',
                'masterEditData.status_id' => 'required|integer|exists:ticket_statuses,id',
            ], [
                'masterEditData.estimated_hours.required' => 'Estimation time is required.',
                'masterEditData.estimated_hours.min' => 'Estimation time must be greater than 0.',
            ]);

            $updateData = [];
            
            // Only update fields that have changed
            if ($this->masterEditData['name'] !== $this->ticket->name) {
                $updateData['name'] = $this->masterEditData['name'];
            }
            if ($this->masterEditData['content'] !== ($this->ticket->content ?? '')) {
                $updateData['content'] = $this->masterEditData['content'];
            }
            if (!empty($this->masterEditData['start_date'])) {
                $updateData['start_date'] = $this->masterEditData['start_date'];
            }
            if (!empty($this->masterEditData['due_date'])) {
                $updateData['due_date'] = $this->masterEditData['due_date'];
            }
            if ($this->masterEditData['estimated_hours'] != $this->ticket->estimated_hours) {
                $updateData['estimated_hours'] = $this->masterEditData['estimated_hours'];
            }
            if ($this->masterEditData['priority_id'] != $this->ticket->priority_id) {
                $updateData['priority_id'] = $this->masterEditData['priority_id'] ?: null;
            }
            if ($this->masterEditData['status_id'] != $this->ticket->status_id) {
                $updateData['status_id'] = $this->masterEditData['status_id'];
            }

            if (!empty($updateData)) {
                $this->ticket->update($updateData);
            }

           $this->showMasterEdit = false;
            $this->masterEditData = [];
            $this->ticket->refresh();
            $this->notify('success', 'Ticket updated successfully');
            return redirect()->to('/tickets/' . $this->ticket->id . '?tab=dates');
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }

    public function refreshTicket(): void
    {
        $this->ticket->refresh();
    }

    public function notify(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('notify', [
            'type' => $type,
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.ticket.employee-ticket-detail', [
            'totalLoggedHours' => $this->totalLoggedHours,
            'remainingHours' => $this->remainingHours,
            'progressPercentage' => $this->progressPercentage,
            'filteredComments' => $this->filteredComments,
            'availableStatuses' => $this->availableStatuses,
            'ticketSearchResults' => $this->ticketSearchResults,
        ]);
    }
}
