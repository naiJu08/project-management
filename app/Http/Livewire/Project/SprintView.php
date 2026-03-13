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

    // Filters
    public $filterStatus = 'all'; // all, active, upcoming, completed

    protected $rules = [
        'sprintName' => 'required|string|max:255',
        'sprintDescription' => 'nullable|string',
        'sprintStartDate' => 'required|date',
        'sprintEndDate' => 'required|date|after:sprintStartDate',
        'sprintGoal' => 'nullable|string|max:500',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
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
            'status' => 'upcoming',
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
        ]);

        $this->resetForm();
        $this->loadSprints();
        session()->flash('success', 'Sprint updated successfully!');
    }

    public function deleteSprint($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);
        
        // Move backlog items back to backlog
        BacklogItem::where('sprint_id', $sprintId)->update(['sprint_id' => null]);
        
        $sprint->delete();
        $this->resetForm();
        $this->loadSprints();
        session()->flash('success', 'Sprint deleted successfully!');
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

    public function resetForm()
    {
        $this->sprintName = '';
        $this->sprintDescription = '';
        $this->sprintStartDate = '';
        $this->sprintEndDate = '';
        $this->sprintGoal = '';
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->editingSprintId = null;
    }

    public function render()
    {
        return view('livewire.project.sprint-view');
    }
}
