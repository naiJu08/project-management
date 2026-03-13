<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserMail;

class CreateUser extends CreateRecord
{
    protected function afterCreate(): void
{
    Mail::to($this->record->email)
        ->send(new NewUserMail($this->record, $this->plainPassword));
}
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $password = Str::random(10);

    $data['password'] = Hash::make($password);

    $this->plainPassword = $password;

    return $data;
}

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
