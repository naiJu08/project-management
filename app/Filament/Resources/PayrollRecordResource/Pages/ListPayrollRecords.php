<?php

namespace App\Filament\Resources\PayrollRecordResource\Pages;

use App\Filament\Resources\PayrollRecordResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollRecords extends ListRecords
{
    protected static string $resource = PayrollRecordResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
