<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ProjectAIInsightsWidget extends Widget
{
    protected static string $view = 'filament.widgets.project.ai-insights';
    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 6
    ];

    public ?Project $project = null;
    public array $insights = [];

    public function mount(): void
    {
        if ($this->project) {
            $this->insights = $this->generateInsights();
        }
    }

    private function generateInsights(): array
    {
        $totalTickets = $this->project->tickets()->count();
        $completedTickets = $this->project->tickets()->whereHas('status', function ($q) {
            $q->where('name', 'Completed');
        })->count();
        $openTickets = $this->project->tickets()->whereHas('status', function ($q) {
            $q->where('name', 'Open');
        })->count();
        $inProgressTickets = $this->project->tickets()->whereHas('status', function ($q) {
            $q->where('name', 'In Progress');
        })->count();

        $criticalTickets = $this->project->tickets()
            ->whereHas('priority', function ($q) {
                $q->where('name', 'Critical');
            })->count();

        $highPriorityTickets = $this->project->tickets()
            ->whereHas('priority', function ($q) {
                $q->where('name', 'High');
            })->count();

        $overdueTickets = $this->project->tickets()
            ->where('due_date', '<', now()->toDateString())
            ->whereHas('status', function ($q) {
                $q->whereNotIn('name', ['Completed', 'Closed']);
            })->count();

        $teamMembers = $this->project->users()->count() + 1;
        $avgTicketsPerMember = $teamMembers > 0 ? round($totalTickets / $teamMembers, 1) : 0;

        $insights = [];

        // Insight 1: Project Status
        if ($totalTickets === 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-m-information-circle',
                'title' => 'Project Started',
                'description' => 'No tickets created yet. Start by creating your first ticket to begin tracking work.',
            ];
        } elseif ($completedTickets === $totalTickets) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-m-check-circle',
                'title' => 'Project Complete! 🎉',
                'description' => 'All tickets have been completed. Great work!',
            ];
        } else {
            $completionRate = round(($completedTickets / $totalTickets) * 100);
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-m-chart-pie',
                'title' => 'Project Progress',
                'description' => $completionRate . '% complete with ' . $remainingTickets = ($totalTickets - $completedTickets) . ' tickets remaining.',
            ];
        }

        // Insight 2: Critical Issues
        if ($criticalTickets > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'heroicon-m-exclamation-circle',
                'title' => 'Critical Tickets Pending',
                'description' => 'You have ' . $criticalTickets . ' critical ticket(s) that need immediate attention.',
            ];
        }

        if ($overdueTickets > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-m-clock',
                'title' => 'Overdue Tickets',
                'description' => $overdueTickets . ' ticket(s) have passed their due date. Consider prioritizing these.',
            ];
        }

        // Insight 3: Team Workload
        if ($avgTicketsPerMember > 10) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-m-users',
                'title' => 'High Team Workload',
                'description' => 'Average of ' . $avgTicketsPerMember . ' tickets per team member. Consider adding more resources or adjusting scope.',
            ];
        } elseif ($avgTicketsPerMember > 0 && $avgTicketsPerMember < 3) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-m-users',
                'title' => 'Balanced Workload',
                'description' => 'Team workload is well-distributed with ' . $avgTicketsPerMember . ' tickets per member on average.',
            ];
        }

        // Insight 4: Velocity
        $weeklyCompleted = $this->project->tickets()
            ->whereHas('status', function ($q) {
                $q->where('name', 'Completed');
            })
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        if ($weeklyCompleted > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-m-rocket-launch',
                'title' => 'Weekly Velocity',
                'description' => $weeklyCompleted . ' ticket(s) completed this week. Keep up the momentum!',
            ];
        }

        // Insight 5: Priority Distribution
        if ($highPriorityTickets > ($totalTickets * 0.3)) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-m-flag',
                'title' => 'High Priority Items',
                'description' => 'Over 30% of tickets are marked as high priority. Review and prioritize accordingly.',
            ];
        }

        // Insight 6: In Progress Status
        if ($inProgressTickets === 0 && $openTickets > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-m-arrow-path',
                'title' => 'No Work in Progress',
                'description' => 'No tickets are currently in progress. Consider starting work on open tickets.',
            ];
        }

        return array_slice($insights, 0, 4); // Return top 4 insights
    }
}
