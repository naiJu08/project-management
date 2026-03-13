<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProjectPriorityDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Priority Distribution';
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public ?Project $project = null;

    protected function getData(): array
    {
        if (!$this->project) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $data = $this->project->tickets()
            ->select('ticket_priorities.name', DB::raw('count(*) as count'))
            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.id')
            ->orderBy('count', 'desc')
            ->get();

        $labels = $data->pluck('name')->toArray();
        $counts = $data->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Count',
                    'data' => $counts,
                    'backgroundColor' => ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
