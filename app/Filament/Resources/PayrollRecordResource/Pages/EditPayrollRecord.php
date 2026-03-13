<?php

namespace App\Filament\Resources\PayrollRecordResource\Pages;

use App\Filament\Resources\PayrollRecordResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollRecord extends EditRecord
{
    protected static string $resource = PayrollRecordResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
