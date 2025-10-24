<?php

namespace App\Filament\Resources\WikiPageResource\Pages;

use App\Filament\Resources\WikiPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWikiPage extends CreateRecord
{
    protected static string $resource = WikiPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['version'] = 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
