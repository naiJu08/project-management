<?php

namespace App\Filament\Resources\EmployeeProfileResource\Pages;

use App\Filament\Resources\EmployeeProfileResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeProfile extends EditRecord
{
    protected static string $resource = EmployeeProfileResource::class;

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
}
