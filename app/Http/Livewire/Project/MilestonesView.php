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
    public $showDeleteConfirm = false;
    public $milestoneToDelete = null;

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

    protected function rules()
    {
        $rules = $this->rules;
        
        // Add conditional validation for completed milestones
        if ($this->status === 'completed') {
            $rules['targetDate'] = 'required|date|before_or_equal:today';
        }
        
        return $rules;
    }

    protected function messages()
    {
        return [
            'targetDate.before_or_equal' => 'The target date must be today or in the past when the milestone status is completed.',
            'targetDate.after_or_equal' => 'The target date must be today or in the future.',
        ];
    }

    // Ensure these properties are excluded from validation and don't cause hydration issues
    protected $except = [
        'showDeleteConfirm',
        'milestoneToDelete',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->targetDate = now()->toDateString();
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

    public function confirmDeleteMilestone($milestoneId)
    {
        $this->milestoneToDelete = $milestoneId;
        $this->showDeleteConfirm = true;
    }

    public function deleteMilestone()
    {
        Milestone::find($this->milestoneToDelete)->delete();
        $this->loadMilestones();
        session()->flash('success', 'Milestone deleted successfully!');

        $this->showDeleteConfirm = false;
        $this->milestoneToDelete = null;
    }

    public function cancelDeleteMilestone()
    {
        $this->showDeleteConfirm = false;
        $this->milestoneToDelete = null;
    }

    // Reset delete confirmation properties when form is reset
    protected function resetDeleteConfirmation()
    {
        $this->showDeleteConfirm = false;
        $this->milestoneToDelete = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->targetDate = now()->toDateString();
        $this->status = 'planned';
        $this->version = 1;
        $this->releaseNotes = '';
        $this->showForm = false;
        $this->editingMilestoneId = null;
        $this->resetDeleteConfirmation();
    }

    public function updatedFilterStatus()
    {
        $this->loadMilestones();
    }

    public function render()
    {
        return view('livewire.project.milestones-view', [
            'tickets' => $this->project->tickets,
        ]);
    }
}
