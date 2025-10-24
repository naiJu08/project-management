<?php

namespace App\Filament\Resources\PayslipTemplateResource\Pages;

use App\Filament\Resources\PayslipTemplateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayslipTemplate extends EditRecord
{
    protected static string $resource = PayslipTemplateResource::class;

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
