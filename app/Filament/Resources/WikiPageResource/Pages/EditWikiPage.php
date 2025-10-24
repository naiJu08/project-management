<?php

namespace App\Filament\Resources\WikiPageResource\Pages;

use App\Filament\Resources\WikiPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWikiPage extends EditRecord
{
    protected static string $resource = WikiPageResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        $data['version'] = $this->record->version + 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
