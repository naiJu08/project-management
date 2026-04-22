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
    public $sprintStatus = 'upcoming';

    // Delete confirmation
    public $showDeleteConfirm = false;
    public $sprintToDelete = null;

    // Filters
    public $filterStatus = 'all'; // all, active, upcoming, completed

    protected $rules = [
        'sprintName' => 'required|string|max:255',
        'sprintDescription' => 'nullable|string',
        'sprintStartDate' => 'required|date|after_or_equal:today',
        'sprintEndDate' => 'required|date|after:sprintStartDate',
        'sprintGoal' => 'nullable|string|max:500',
        'sprintStatus' => 'required|in:upcoming,active,completed',
    ];

    protected function rules()
    {
        $rules = $this->rules;
        
        // Add conditional validation for completed sprints
        if ($this->sprintStatus === 'completed') {
            $rules['sprintEndDate'] = 'required|date|before_or_equal:today';
        }
        
        return $rules;
    }

    protected function messages()
    {
        return [
            'sprintEndDate.before_or_equal' => 'The end date must be today or in the past when the sprint status is completed.',
            'sprintStartDate.after_or_equal' => 'The start date must be today or in the future.',
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
        $query = $this->project->sprints();

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $this->sprints = $query->with(['backlogItems'])->orderBy('starts_at', 'desc')->get();
    }

    public function getSprintStatusProperty()
    {
        return function ($sprint) {
            $now = now()->toDateString();
            if ($sprint->ends_at < $now) {
                return 'completed';
            } elseif ($sprint->starts_at <= $now && $sprint->ends_at >= $now) {
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
        $this->sprintStatus = $sprint->status;
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
            'status' => $this->sprintStatus,
        ]);

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
            'status' => $this->sprintStatus,
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

    public function startSprint($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);
        $sprint->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
        $this->loadSprints();
        session()->flash('success', 'Sprint started!');
    }

    public function completeSprint($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);
        $sprint->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);
        $this->loadSprints();
        session()->flash('success', 'Sprint completed!');
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
        $this->sprintStatus = 'upcoming';
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
