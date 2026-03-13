<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProjectTicketsTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tickets by Type';
    protected static ?int $sort = 6;
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
            ->select('ticket_types.name', DB::raw('count(*) as count'))
            ->join('ticket_types', 'tickets.type_id', '=', 'ticket_types.id')
            ->groupBy('ticket_types.name', 'ticket_types.id')
            ->orderBy('count', 'desc')
            ->get();

        $labels = $data->pluck('name')->toArray();
        $counts = $data->pluck('count')->toArray();
        $colors = [
            '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
            '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $counts,
                    'backgroundColor' => array_slice($colors, 0, count($counts)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
