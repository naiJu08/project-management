<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\BacklogItem;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SprintView extends Component
{
    public $projectId;
    public $sprints = [];
    public $selectedSprintId = null;
    public $showCreateForm = false;
    public $showEditForm = false;
    public $editingSprintId = null;

    // Form fields
    public $sprintName = '';
    public $sprintDescription = '';
    public $sprintStartDate = '';
    public $sprintEndDate = '';
    public $sprintGoal = '';

    // Delete confirmation
    public $showDeleteConfirm = false;
    public $sprintToDelete = null;

    // Filters
    public $filterStatus = 'all'; // all, active, upcoming, completed

    protected $rules = [
        'sprintName' => 'required|string|max:255',
        'sprintDescription' => 'nullable|string',
        'sprintStartDate' => 'required|date',
        'sprintEndDate' => 'required|date|after:sprintStartDate',
        'sprintGoal' => 'nullable|string|max:500',
    ];

    protected function messages()
    {
        return [
            'sprintEndDate.after' => 'The end date must be after the start date.',
        ];
    }

    // Ensure these properties are excluded from validation and don't cause hydration issues
    protected $except = [
        'showDeleteConfirm',
        'sprintToDelete',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->sprintStartDate = now()->toDateString();
        $this->sprintEndDate = now()->addDays(14)->toDateString();
        $this->loadSprints();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadSprints()
    {
        $allSprints = $this->project->sprints()->with(['backlogItems'])->orderBy('starts_at', 'desc')->get();

        // Add progress data to each sprint based on dates
        $statusCalculator = $this->getSprintStatusProperty();
        $now = now();
        $allSprints->each(function ($sprint) use ($statusCalculator, $now) {
            $backlogItems = $sprint->backlogItems;
            $itemCount = $backlogItems->count();
            $completedCount = $backlogItems->where('status', 'Done')->count();

            // Calculate status based on dates
            $status = $statusCalculator($sprint);

            // Calculate progress based on time elapsed
            $completionPercent = 0;
            if ($status === 'upcoming') {
                $completionPercent = 0;
            } elseif ($status === 'completed') {
                $completionPercent = 100;
            } elseif ($status === 'active') {
                // Calculate percentage of time elapsed using Carbon methods
                $startDate = $sprint->starts_at instanceof \Carbon\Carbon ? $sprint->starts_at : \Carbon\Carbon::parse($sprint->starts_at);
                $endDate = $sprint->ends_at instanceof \Carbon\Carbon ? $sprint->ends_at : \Carbon\Carbon::parse($sprint->ends_at);
                $totalDuration = $startDate->diffInRealSeconds($endDate);
                $elapsedDuration = $startDate->diffInRealSeconds($now);
                if ($totalDuration > 0 && $elapsedDuration >= 0) {
                    $completionPercent = round(($elapsedDuration / $totalDuration) * 100);
                    $completionPercent = max(0, min(100, $completionPercent)); // Ensure between 0-100
                } else if ($elapsedDuration < 0) {
                    $completionPercent = 0; // Haven't started yet
                } else {
                    $completionPercent = 50; // If same time, show 50%
                }
            }

            $sprint->progress = [
                'itemCount' => $itemCount,
                'completedCount' => $completedCount,
                'completionPercent' => $completionPercent,
                'status' => $status,
            ];
        });

        if ($this->filterStatus === 'all') {
            $this->sprints = $allSprints;
        } else {
            $this->sprints = $allSprints->filter(function ($sprint) use ($statusCalculator) {
                $status = $sprint->progress['status'] ?? $statusCalculator($sprint);
                return $status === $this->filterStatus;
            })->values();
        }
    }

    public function getSprintStatusProperty()
    {
        return function ($sprint) {
            // Always calculate status based on dates using Carbon
            $now = now();
            $startDate = $sprint->starts_at instanceof \Carbon\Carbon ? $sprint->starts_at : \Carbon\Carbon::parse($sprint->starts_at);
            $endDate = $sprint->ends_at instanceof \Carbon\Carbon ? $sprint->ends_at : \Carbon\Carbon::parse($sprint->ends_at);

            if ($endDate->isPast()) {
                return 'completed';
            } elseif ($startDate->isPast() && $endDate->isFuture()) {
                return 'active';
            } elseif ($startDate->isPast() && $endDate->isToday()) {
                return 'active';
            } else {
                return 'upcoming';
            }
        };
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
        $this->loadSprints();
    }

    public function selectSprint($sprintId)
    {
        $this->selectedSprintId = $sprintId;
    }

    public function showCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function showEdit($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);
        $this->editingSprintId = $sprintId;
        $this->sprintName = $sprint->name;
        $this->sprintDescription = $sprint->description;
        $this->sprintStartDate = $sprint->starts_at->format('Y-m-d');
        $this->sprintEndDate = $sprint->ends_at->format('Y-m-d');
        $this->sprintGoal = $sprint->goal ?? '';
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function createSprint()
    {
        $this->validate();

        Sprint::create([
            'project_id' => $this->projectId,
            'name' => $this->sprintName,
            'description' => $this->sprintDescription,
            'starts_at' => $this->sprintStartDate,
            'ends_at' => $this->sprintEndDate,
            'goal' => $this->sprintGoal,
        ]);

        // Calculate status to determine which filter to show
        $now = now()->toDateString();
        $status = 'upcoming';
        if ($this->sprintStartDate <= $now && $this->sprintEndDate >= $now) {
            $status = 'active';
        } elseif ($this->sprintEndDate < $now) {
            $status = 'completed';
        }

        // Set filter to show the newly created sprint in its correct status tab
        $this->filterStatus = $status;
        $this->resetForm();
        $this->loadSprints();
        session()->flash('success', 'Sprint created successfully!');
    }

    public function updateSprint()
    {
        $this->validate();

        $sprint = Sprint::findOrFail($this->editingSprintId);
        $sprint->update([
            'name' => $this->sprintName,
            'description' => $this->sprintDescription,
            'starts_at' => $this->sprintStartDate,
            'ends_at' => $this->sprintEndDate,
            'goal' => $this->sprintGoal,
        ]);

        $this->resetForm();
        $this->loadSprints();
        session()->flash('success', 'Sprint updated successfully!');
    }

    public function confirmDeleteSprint($sprintId)
    {
        $this->sprintToDelete = $sprintId;
        $this->showDeleteConfirm = true;
    }

    public function cancelDeleteSprint()
    {
        $this->showDeleteConfirm = false;
        $this->sprintToDelete = null;
    }

    public function deleteSprint()
    {
        if (!$this->sprintToDelete) {
            return;
        }

        $sprint = Sprint::findOrFail($this->sprintToDelete);
        
        // Move backlog items back to backlog
        BacklogItem::where('sprint_id', $this->sprintToDelete)->update(['sprint_id' => null]);
        
        $sprint->delete();
        $this->resetForm();
        $this->loadSprints();
        session()->flash('success', 'Sprint deleted successfully!');
        
        $this->showDeleteConfirm = false;
        $this->sprintToDelete = null;
    }

    // Reset delete confirmation properties when form is reset
    protected function resetDeleteConfirmation()
    {
        $this->showDeleteConfirm = false;
        $this->sprintToDelete = null;
    }

    public function resetForm()
    {
        $this->sprintName = '';
        $this->sprintDescription = '';
        $this->sprintStartDate = now()->toDateString();
        $this->sprintEndDate = now()->addDays(14)->toDateString();
        $this->sprintGoal = '';
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->editingSprintId = null;
        $this->resetDeleteConfirmation();
    }

    public function updatedFilterStatus()
    {
        $this->loadSprints();
    }

    public function render()
    {
        return view('livewire.project.sprint-view');
    }
}
