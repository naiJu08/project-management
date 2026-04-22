<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use Livewire\Component;

class OverviewView extends Component
{
    public $projectId;
    public $stats;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadStats();
    }

    public function getProjectProperty()
    {
        return Project::with(['owner', 'status', 'users', 'tickets', 'sprints'])->findOrFail($this->projectId);
    }

    public function loadStats()
    {
        $tickets = $this->project->tickets;
        
        // Calculate completion percentage
        $totalTickets = $tickets->count();
        $completedTickets = $tickets->filter(function($ticket) {
            return $ticket->status && (stripos($ticket->status->name, 'Done') !== false || stripos($ticket->status->name, 'Archived') !== false);
        })->count();
        
        $completionPercentage = $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100) : 0;
        
        // Get recent activity
        $recentTickets = $tickets->sortByDesc('updated_at')->take(5);
        
        $this->stats = [
            'total_tickets' => $totalTickets,
            'completed_tickets' => $completedTickets,
            'in_progress_tickets' => $tickets->filter(function($ticket) {
                return $ticket->status && stripos($ticket->status->name, 'progress') !== false;
            })->count(),
            'open_tickets' => $tickets->filter(function($ticket) {
                return $ticket->status && (stripos($ticket->status->name, 'open') !== false || stripos($ticket->status->name, 'new') !== false || stripos($ticket->status->name, 'to do') !== false);
            })->count(),
            'completion_percentage' => $completionPercentage,
            'total_sprints' => $this->project->sprints->count(),
            'active_sprint' => $this->project->currentSprint,
            'team_members' => $this->project->users->count() + 1,
            'wiki_pages' => $this->project->wikiPages()->count(),
            'total_epics' => $this->project->epics->count(),
            'recent_tickets' => $recentTickets,
        ];
    }

    public function render()
    {
        return view('livewire.project.overview-view');
    }
}
