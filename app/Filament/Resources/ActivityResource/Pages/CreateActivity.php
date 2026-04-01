<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

     protected function getRedirectUrl(): string
    {
        return ActivityResource::getUrl('index');
    }

    protected function getFormValidationMessages(): array
{
    return [
        'name.regex' => 'Activity name should contain only letters, numbers, and spaces.',
    ];
}
}
