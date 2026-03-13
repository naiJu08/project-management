<?php

namespace App\Filament\Resources\PayslipTemplateResource\Pages;

use App\Filament\Resources\PayslipTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayslipTemplate extends CreateRecord
{
    protected static string $resource = PayslipTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
