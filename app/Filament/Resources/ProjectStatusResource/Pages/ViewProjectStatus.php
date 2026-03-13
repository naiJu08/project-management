<?php

namespace App\Filament\Resources\ProjectStatusResource\Pages;

use App\Filament\Resources\ProjectStatusResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;

class ViewProjectStatus extends ViewRecord
{
    protected static string $resource = ProjectStatusResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('← Back')
                ->url(ProjectStatusResource::getUrl()),

            Actions\EditAction::make(),
        ];
    }
}