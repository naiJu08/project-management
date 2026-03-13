<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

class ListView extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $projectId;
    public $search = '';
    public $filterStatus = '';
    public $filterPriority = '';

    public $showCreateTask = false;

    protected $queryString = ['search', 'filterStatus', 'filterPriority'];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getTicketsProperty()
    {
        $query = $this->project->tickets()
            ->with(['responsible', 'priority', 'type', 'status', 'sprint']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status_id', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query->where('priority_id', $this->filterPriority);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function render()
    {
        return view('livewire.project.list-view', [
            'tickets' => $this->tickets,
        ]);
    }

    public function createTask()
{
    $this->showCreateTask = true;
}

public function updatingFilterStatus()
{
    $this->resetPage();
}

public function updatingFilterPriority()
{
    $this->resetPage();
}

    
}
