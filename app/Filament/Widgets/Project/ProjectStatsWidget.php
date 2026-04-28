<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProjectStatsWidget extends BaseWidget
{
    public ?Project $project = null;

    protected function getStats(): array
    {
        if (!$this->project) {
            return [];
        }

        $completedStatusNames = ['done', 'completed', 'closed', 'archived'];
        $totalTickets = $this->project->tickets()->count();
        $completedTickets = $this->project->tickets()
            ->whereHas('status', function ($query) use ($completedStatusNames) {
                $query->whereIn(DB::raw('LOWER(ticket_statuses.name)'), $completedStatusNames);
            })
            ->count();
        $openTickets = $this->project->tickets()
            ->whereHas('status', function ($query) use ($completedStatusNames) {
                $query->whereNotIn(DB::raw('LOWER(ticket_statuses.name)'), $completedStatusNames);
            })
            ->count();
        $teamMembers = $this->project->users()->count() + 1; // +1 for owner

        $completionPercentage = $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100) : 0;

        return [
            Stat::make(__('Total Tickets'), $totalTickets)
                ->description(__('All project tickets'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make(__('Open Tickets'), $openTickets)
                ->description(__('Awaiting work'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('Completed'), $completedTickets)
                ->description(__('Finished tickets'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('Completion'), $completionPercentage . '%')
                ->description(__('Project progress'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make(__('Team Members'), $teamMembers)
                ->description(__('Active members'))
                ->descriptionIcon('heroicon-m-users')
                ->color('secondary'),

            Stat::make(__('Active Sprints'), $this->project->sprints()->where('status', 'active')->count())
                ->description(__('Running sprints'))
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->color('info'),
        ];
    }
}
