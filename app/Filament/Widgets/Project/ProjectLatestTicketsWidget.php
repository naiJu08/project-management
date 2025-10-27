<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ProjectLatestTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 6
    ];

    public ?Project $project = null;

    public function mount(): void
    {
        $this->heading = __('Latest Tickets');
    }

    protected function getTableQuery(): Builder
    {
        if (!$this->project) {
            return \App\Models\Ticket::query()->limit(0);
        }

        return $this->project->tickets()
            ->with(['status', 'priority', 'responsible'])
            ->latest()
            ->limit(10);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('code')
                ->label(__('Ticket'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex flex-col gap-1">
                        <a href="' . route('filament.resources.tickets.share', $record->code) . '" target="_blank" 
                           class="text-primary-500 font-medium hover:underline">'
                           . $record->code . '</a>
                        <span class="text-sm text-gray-500">' . $record->name . '</span>
                    </div>
                ')),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                          style="background-color: ' . $record->status->color . '20; color: ' . $record->status->color . '">
                        ' . $record->status->name . '
                    </span>
                ')),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                          style="background-color: ' . $record->priority->color . '20; color: ' . $record->priority->color . '">
                        ' . $record->priority->name . '
                    </span>
                ')),

            Tables\Columns\TextColumn::make('responsible.name')
                ->label(__('Assigned To'))
                ->formatStateUsing(fn($record) => $record->responsible?->name ?? '-'),
        ];
    }
}
