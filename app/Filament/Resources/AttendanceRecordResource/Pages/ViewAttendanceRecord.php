<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceRecord extends ViewRecord
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
