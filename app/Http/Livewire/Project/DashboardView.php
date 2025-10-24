<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use Livewire\Component;

class DashboardView extends Component
{
    public $projectId;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function render()
    {
        return view('livewire.project.dashboard-view');
    }
}
