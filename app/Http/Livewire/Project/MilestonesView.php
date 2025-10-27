<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Milestone;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MilestonesView extends Component
{
    public $projectId;
    public $milestones = [];
    public $showForm = false;
    public $editingMilestoneId = null;
    public $filterStatus = 'all';

    // Form fields
    public $name = '';
    public $description = '';
    public $targetDate = '';
    public $status = 'planned';
    public $version = 1;
    public $releaseNotes = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'targetDate' => 'required|date|after_or_equal:today',
        'status' => 'required|in:planned,in_progress,completed,cancelled',
        'version' => 'required|integer|min:1',
        'releaseNotes' => 'nullable|string|max:5000',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadMilestones();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadMilestones()
    {
        $query = Milestone::forProject($this->projectId)
            ->with('tickets')
            ->orderBy('target_date', 'asc');

        if ($this->filterStatus !== 'all') {
            $query->byStatus($this->filterStatus);
        }

        $this->milestones = $query->get();
    }

    public function showCreate()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function createMilestone()
    {
        $this->validate();

        Milestone::create([
            'project_id' => $this->projectId,
            'name' => $this->name,
            'description' => $this->description,
            'target_date' => $this->targetDate,
            'status' => $this->status,
            'version' => $this->version,
            'release_notes' => $this->releaseNotes,
        ]);

        $this->resetForm();
        $this->loadMilestones();
        session()->flash('success', 'Milestone created successfully!');
    }

    public function editMilestone($milestoneId)
    {
        $milestone = Milestone::find($milestoneId);
        $this->editingMilestoneId = $milestoneId;
        $this->name = $milestone->name;
        $this->description = $milestone->description;
        $this->targetDate = $milestone->target_date->toDateString();
        $this->status = $milestone->status;
        $this->version = $milestone->version;
        $this->releaseNotes = $milestone->release_notes;
        $this->showForm = true;
    }

    public function updateMilestone()
    {
        $this->validate();

        $milestone = Milestone::find($this->editingMilestoneId);
        $milestone->update([
            'name' => $this->name,
            'description' => $this->description,
            'target_date' => $this->targetDate,
            'status' => $this->status,
            'version' => $this->version,
            'release_notes' => $this->releaseNotes,
        ]);

        $this->resetForm();
        $this->loadMilestones();
        session()->flash('success', 'Milestone updated successfully!');
    }

    public function deleteMilestone($milestoneId)
    {
        Milestone::find($milestoneId)->delete();
        $this->loadMilestones();
        session()->flash('success', 'Milestone deleted successfully!');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->targetDate = '';
        $this->status = 'planned';
        $this->version = 1;
        $this->releaseNotes = '';
        $this->showForm = false;
        $this->editingMilestoneId = null;
    }

    public function render()
    {
        return view('livewire.project.milestones-view', [
            'tickets' => $this->project->tickets,
        ]);
    }
}
