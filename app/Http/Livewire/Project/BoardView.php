<?php

namespace App\Http\Livewire\Project;

use App\Helpers\KanbanScrumHelper;
use App\Models\Project;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class BoardView extends Component implements HasForms
{
    use InteractsWithForms, KanbanScrumHelper;

    public $projectId;

    protected $listeners = [
        'recordUpdated',
        'closeTicketDialog'
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::findOrFail($projectId);
        
        // Check if user is owner or member of the project
        if ($this->project->owner_id != auth()->id() && !$this->project->users->contains(auth()->id())) {
            abort(403, 'You do not have access to this project.');
        }
        
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return $this->formSchema();
    }

    public function render()
    {
        return view('livewire.project.board-view');
    }
}
