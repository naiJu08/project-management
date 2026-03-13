<?php

namespace App\Filament\Resources\PayslipTemplateResource\Pages;

use App\Filament\Resources\PayslipTemplateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayslipTemplates extends ListRecords
{
    protected static string $resource = PayslipTemplateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
