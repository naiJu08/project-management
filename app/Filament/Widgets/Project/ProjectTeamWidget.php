<?php

namespace App\Filament\Widgets\Project;

use App\Models\Project;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ProjectTeamWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public ?Project $project = null;

    public function mount(): void
    {
        $this->heading = __('Team Members');
    }

    protected function getTableQuery(): Builder
    {
        if (!$this->project) {
            return \App\Models\User::query()->limit(0);
        }

        return $this->project->users()
            ->with('roles')
            ->latest('users.created_at')
            ->limit(10);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Name'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex items-center gap-3">
                        <img src="' . $record->avatar_url . '" alt="' . $record->name . '" 
                             class="w-8 h-8 rounded-full object-cover">
                        <div>
                            <p class="font-medium">' . $record->name . '</p>
                            <p class="text-xs text-gray-500">' . $record->email . '</p>
                        </div>
                    </div>
                ')),

            Tables\Columns\TextColumn::make('roles.name')
                ->label(__('Role'))
                ->formatStateUsing(fn($record) => new HtmlString(
                    $record->roles->map(fn($role) => 
                        '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' 
                        . $role->name . '</span>'
                    )->join(' ')
                )),
        ];
    }
}
