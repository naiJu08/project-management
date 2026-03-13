<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.resources.projects.pages.view-project';

    protected function getActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label(
                    fn ()
                    => ($this->record->type === 'scrum' ? __('Scrum board') : __('Kanban board'))
                )
                ->icon('heroicon-o-view-boards')
                ->color('secondary')
                ->url(function () {
                   return $this->record->type === 'scrum'
                        ? route('filament.pages.scrum/{project}', ['project' => $this->record->id])
                        : route('filament.pages.kanban/{project}', ['project' => $this->record->id]);
                }),


            Actions\EditAction::make(),

             Actions\Action::make('createTicket')
                ->label('Create Ticket')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => '/tickets/create?project_id=' . $this->record->id)
        ];
    }

    protected function getRelationManagers(): array
    {
        return [];
    }
}
