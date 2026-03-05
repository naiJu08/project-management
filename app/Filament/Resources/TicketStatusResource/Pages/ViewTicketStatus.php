<?php

namespace App\Filament\Resources\TicketStatusResource\Pages;

use App\Filament\Resources\TicketStatusResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTicketStatus extends ViewRecord
{
    protected static string $resource = TicketStatusResource::class;

     protected function getActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('← Back')
                ->url(TicketStatusResource::getUrl('index')),

            Actions\EditAction::make(),
        ];
    }
}
