<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProjectTicketsByStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Tickets by Status';
    protected static ?int $sort = 2;
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
            ->select('ticket_statuses.name', DB::raw('count(*) as count'))
            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->groupBy('ticket_statuses.name')
            ->get();

        $labels = $data->pluck('name')->toArray();
        $counts = $data->pluck('count')->toArray();
        $colors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
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
