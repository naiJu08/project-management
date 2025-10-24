<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceRecord extends EditRecord
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate total hours if both check_in and check_out are provided
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $checkIn = \Carbon\Carbon::parse($data['date'] . ' ' . $data['check_in']);
            $checkOut = \Carbon\Carbon::parse($data['date'] . ' ' . $data['check_out']);
            $data['total_hours'] = $checkIn->diffInHours($checkOut, true);
        }
        
        return $data;
    }
}
