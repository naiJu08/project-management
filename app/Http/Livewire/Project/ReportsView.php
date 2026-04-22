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
    public $showHoursDetails = false;

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
            return in_array($ticket->status->name ?? '', ['Done', 'Archived']);
        })->count();

        $sprints = $this->project->sprints;
        $activeSprints = $sprints->where('status', 'active')->count();
        $completedSprints = $sprints->where('status', 'completed')->count();

        // Get time logs with fallback for date range issues
        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->with('user')
            ->get();
        
        // If no time logs in date range, try getting all time logs for this project
        if ($timeLogs->isEmpty()) {
            $allTimeLogs = TimeLog::forProject($this->projectId)->with('user')->get();
            if ($allTimeLogs->isNotEmpty()) {
                $timeLogs = $allTimeLogs;
            }
        }
        
        $totalHours = $timeLogs->sum('hours');
        $billableHours = $timeLogs->where('is_billable', true)->sum('hours');

        // Get team members count with fresh data
        $teamMembersCount = 0;
        try {
            // Always get fresh count to ensure we have the latest data
            $teamMembersCount = $this->project->users()->count();
        } catch (\Exception $e) {
            $teamMembersCount = 0;
        }

        return [
            'total_tickets' => $totalTickets,
            'completed_tickets' => $completedTickets,
            'completion_rate' => $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100) : 0,
            'active_sprints' => $activeSprints,
            'completed_sprints' => $completedSprints,
            'total_hours' => round($totalHours, 2),
            'billable_hours' => round($billableHours, 2),
            'team_members' => $teamMembersCount,
        ];
    }

    public function getHoursDistributionDetailsProperty()
    {
        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->with('user')
            ->get();

        // If no time logs in date range, try getting all time logs for this project
        if ($timeLogs->isEmpty()) {
            $allTimeLogs = TimeLog::forProject($this->projectId)->with('user')->get();
            if ($allTimeLogs->isNotEmpty()) {
                $timeLogs = $allTimeLogs;
            }
        }

        // Detailed breakdown by team member
        $byTeamMember = $timeLogs->groupBy('user_id')->map(function($group) {
            $user = $group->first()->user;
            return [
                'name' => $user ? $user->name : 'Unknown',
                'total_hours' => round($group->sum('hours'), 2),
                'billable_hours' => round($group->where('is_billable', true)->sum('hours'), 2),
                'non_billable_hours' => round($group->where('is_billable', false)->sum('hours'), 2),
                'billable_percentage' => $group->sum('hours') > 0 ? round(($group->where('is_billable', true)->sum('hours') / $group->sum('hours')) * 100) : 0,
            ];
        })->sortByDesc('total_hours');

        // Detailed breakdown by category
        $byCategory = $timeLogs->groupBy('category')->map(function($group) {
            return [
                'total_hours' => round($group->sum('hours'), 2),
                'billable_hours' => round($group->where('is_billable', true)->sum('hours'), 2),
                'non_billable_hours' => round($group->where('is_billable', false)->sum('hours'), 2),
                'billable_percentage' => $group->sum('hours') > 0 ? round(($group->where('is_billable', true)->sum('hours') / $group->sum('hours')) * 100) : 0,
            ];
        })->sortByDesc('total_hours');

        // Daily breakdown for trend analysis
        $byDate = $timeLogs->groupBy('logged_date')->map(function($group) {
            return [
                'total_hours' => round($group->sum('hours'), 2),
                'billable_hours' => round($group->where('is_billable', true)->sum('hours'), 2),
                'non_billable_hours' => round($group->where('is_billable', false)->sum('hours'), 2),
            ];
        })->sortKeys();

        return [
            'by_team_member' => $byTeamMember,
            'by_category' => $byCategory,
            'by_date' => $byDate,
            'total_hours' => round($timeLogs->sum('hours'), 2),
            'billable_hours' => round($timeLogs->where('is_billable', true)->sum('hours'), 2),
            'non_billable_hours' => round($timeLogs->where('is_billable', false)->sum('hours'), 2),
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
            'overdue_tickets' => $tickets->where('due_date', '<', now())->where('status.name', '!=', 'Done')->count(),
        ];
    }

    public function getHoursStatsProperty()
    {
        $timeLogs = TimeLog::forProject($this->projectId)
            ->dateRange($this->startDate, $this->endDate)
            ->with('user')
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
                'tickets_completed' => $memberTickets->where('status.name', 'Done')->count(),
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
                return in_array($ticket->status->name ?? '', ['Done', 'Archived']);
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
        $completedTickets = $tickets->where('status.name', 'Done')->count();

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
            'hoursDistributionDetails' => $this->getHoursDistributionDetailsProperty(),
        ]);
    }
}
