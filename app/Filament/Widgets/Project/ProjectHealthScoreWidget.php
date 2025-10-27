<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProjectHealthScoreWidget extends BaseWidget
{
    public ?Project $project = null;

    protected function getStats(): array
    {
        if (!$this->project) {
            return [];
        }

        // Calculate health metrics
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

        // Calculate health score (0-100)
        $completionRate = $totalTickets > 0 ? ($completedTickets / $totalTickets) * 100 : 0;
        $progressRate = $totalTickets > 0 ? (($inProgressTickets + $completedTickets) / $totalTickets) * 100 : 0;
        $openRate = $totalTickets > 0 ? ($openTickets / $totalTickets) * 100 : 0;

        // Health score formula: 40% completion + 40% progress + 20% (100 - open rate)
        $healthScore = ($completionRate * 0.4) + ($progressRate * 0.35) + ((100 - $openRate) * 0.25);
        $healthScore = min(100, max(0, round($healthScore)));

        // Determine health status
        if ($healthScore >= 80) {
            $healthStatus = 'Excellent';
            $healthColor = 'success';
        } elseif ($healthScore >= 60) {
            $healthStatus = 'Good';
            $healthColor = 'info';
        } elseif ($healthScore >= 40) {
            $healthStatus = 'Fair';
            $healthColor = 'warning';
        } else {
            $healthStatus = 'Needs Attention';
            $healthColor = 'danger';
        }

        // Calculate velocity (tickets completed in last 7 days)
        $weeklyCompleted = $this->project->tickets()
            ->whereHas('status', function ($q) {
                $q->where('name', 'Completed');
            })
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        // Estimate remaining time (days)
        $remainingTickets = $totalTickets - $completedTickets;
        $estimatedDaysRemaining = $weeklyCompleted > 0 
            ? ceil(($remainingTickets / $weeklyCompleted) * 7) 
            : ($remainingTickets > 0 ? 999 : 0);

        return [
            Stat::make('Health Score', $healthScore . '%')
                ->description($healthStatus)
                ->descriptionIcon('heroicon-m-heart')
                ->color($healthColor),

            Stat::make('Completion Rate', round($completionRate) . '%')
                ->description($completedTickets . ' of ' . $totalTickets . ' tickets')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Progress Rate', round($progressRate) . '%')
                ->description('Completed + In Progress')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Est. Days Remaining', $estimatedDaysRemaining)
                ->description('Based on weekly velocity')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
