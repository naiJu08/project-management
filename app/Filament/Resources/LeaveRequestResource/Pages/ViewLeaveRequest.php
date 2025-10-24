<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaveRequest extends ViewRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }
}
