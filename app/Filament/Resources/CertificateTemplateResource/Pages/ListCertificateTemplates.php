<?php

namespace App\Filament\Resources\CertificateTemplateResource\Pages;

use App\Filament\Resources\CertificateTemplateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertificateTemplates extends ListRecords
{
    protected static string $resource = CertificateTemplateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
