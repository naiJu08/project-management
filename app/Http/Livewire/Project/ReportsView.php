<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TimeLog;
use App\Models\Sprint;
use Carbon\Carbon;
use Livewire\Component;

class ReportsView extends Component
{
    public $projectId;
    public $selectedReport = 'overview';
    public $dateRange = '30'; // days
    public $startDate = '';
    public $endDate = '';

    protected $reportTypes = [
        'overview' => 'Project Overview',
        'tickets' => 'Ticket Analytics',
        'hours' => 'Hours & Productivity',
        'team' => 'Team Performance',
        'sprints' => 'Sprint Analytics',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function setDateRange($days)
    {
        $this->dateRange = $days;
        $this->startDate = now()->subDays($days)->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getOverviewStatsProperty()
    {
        $tickets = $this->project->tickets;
        $totalTickets = $tickets->count();
        $completedTickets = $tickets->filter(function($ticket) {
            return in_array($ticket->status->name ?? '', ['Completed', 'Closed']);
        })->count();

        $sprints = $this->project->sprints;
        $activeSprints = $sprints->where('status', 'active')->count();
        $completedSprints = $sprints->where('status', 'completed')->count();

        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->get();
        $totalHours = $timeLogs->sum('hours');
        $billableHours = $timeLogs->where('is_billable', true)->sum('hours');

        return [
            'total_tickets' => $totalTickets,
            'completed_tickets' => $completedTickets,
            'completion_rate' => $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100) : 0,
            'active_sprints' => $activeSprints,
            'completed_sprints' => $completedSprints,
            'total_hours' => round($totalHours, 2),
            'billable_hours' => round($billableHours, 2),
            'team_members' => $this->project->users->count(),
        ];
    }

    public function getTicketStatsProperty()
    {
        $tickets = $this->project->tickets;

        return [
            'by_status' => $tickets->groupBy('status.name')->map(fn($group) => $group->count()),
            'by_priority' => $tickets->groupBy('priority.name')->map(fn($group) => $group->count()),
            'by_type' => $tickets->groupBy('type.name')->map(fn($group) => $group->count()),
            'average_resolution_time' => $this->calculateAverageResolutionTime(),
            'overdue_tickets' => $tickets->where('due_date', '<', now())->where('status.name', '!=', 'Completed')->count(),
        ];
    }

    public function getHoursStatsProperty()
    {
        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->with('user', 'category')
            ->get();

        $byCategory = $timeLogs->groupBy('category')->map(fn($group) => $group->sum('hours'));
        $byUser = $timeLogs->groupBy('user_id')->map(function($group) {
            $user = $group->first()->user;
            return [
                'name' => $user->name,
                'hours' => $group->sum('hours'),
                'billable' => $group->where('is_billable', true)->sum('hours'),
            ];
        });

        $totalHours = $timeLogs->sum('hours');
        $billableHours = $timeLogs->where('is_billable', true)->sum('hours');
        $groupedByDate = $timeLogs->groupBy('logged_date');
        
        return [
            'total_hours' => round($totalHours, 2),
            'billable_hours' => round($billableHours, 2),
            'non_billable_hours' => round($timeLogs->where('is_billable', false)->sum('hours'), 2),
            'billable_percentage' => $totalHours > 0 ? round(($billableHours / $totalHours) * 100) : 0,
            'by_category' => $byCategory,
            'by_user' => $byUser,
            'daily_average' => $groupedByDate->count() > 0 ? round($totalHours / $groupedByDate->count(), 2) : 0,
        ];
    }

    public function getTeamStatsProperty()
    {
        $teamMembers = $this->project->users;
        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->get();

        return $teamMembers->map(function($member) use ($timeLogs) {
            $memberLogs = $timeLogs->where('user_id', $member->id);
            $memberTickets = $this->project->tickets->where('responsible_id', $member->id);

            return [
                'name' => $member->name,
                'hours_logged' => round($memberLogs->sum('hours'), 2),
                'billable_hours' => round($memberLogs->where('is_billable', true)->sum('hours'), 2),
                'tickets_assigned' => $memberTickets->count(),
                'tickets_completed' => $memberTickets->where('status.name', 'Completed')->count(),
                'productivity_score' => $this->calculateProductivityScore($member),
            ];
        })->sortByDesc('hours_logged');
    }

    public function getSprintStatsProperty()
    {
        $sprints = $this->project->sprints()->orderBy('starts_at', 'desc')->limit(5)->get();

        return $sprints->map(function($sprint) {
            $items = $sprint->backlogItems;
            $completedItems = $items->where('status', 'Done')->count();
            $totalItems = $items->count();

            return [
                'name' => $sprint->name,
                'status' => $sprint->status,
                'start_date' => $sprint->starts_at->format('M d, Y'),
                'end_date' => $sprint->ends_at->format('M d, Y'),
                'total_items' => $totalItems,
                'completed_items' => $completedItems,
                'completion_rate' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0,
                'goal' => $sprint->goal,
            ];
        });
    }

    private function calculateAverageResolutionTime()
    {
        $completedTickets = $this->project->tickets
            ->filter(function($ticket) {
                return in_array($ticket->status->name ?? '', ['Completed', 'Closed']);
            });

        if ($completedTickets->isEmpty()) {
            return 0;
        }

        $totalDays = $completedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInDays($ticket->updated_at);
        });

        return round($totalDays / $completedTickets->count());
    }

    private function calculateProductivityScore($user)
    {
        $timeLogs = TimeLog::forProject($this->projectId)
            ->where('user_id', $user->id)
            ->dateRange($this->startDate, $this->endDate)
            ->get();

        $tickets = $this->project->tickets->where('responsible_id', $user->id);
        $completedTickets = $tickets->where('status.name', 'Completed')->count();

        $totalHours = $timeLogs->sum('hours');
        $hoursScore = $totalHours > 0 ? min(($totalHours / 40) * 100, 100) : 0; // 40 hours = 100%
        $ticketScore = $tickets->count() > 0 ? ($completedTickets / $tickets->count()) * 100 : 0;

        $totalScore = $hoursScore + $ticketScore;
        return $totalScore > 0 ? round($totalScore / 2) : 0;
    }

    public function render()
    {
        return view('livewire.project.reports-view', [
            'reportTypes' => $this->reportTypes,
            'overviewStats' => $this->getOverviewStatsProperty(),
            'ticketStats' => $this->getTicketStatsProperty(),
            'hoursStats' => $this->getHoursStatsProperty(),
            'teamStats' => $this->getTeamStatsProperty(),
            'sprintStats' => $this->getSprintStatsProperty(),
        ]);
    }
}
