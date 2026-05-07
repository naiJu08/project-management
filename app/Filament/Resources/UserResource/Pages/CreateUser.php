<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\NewUserMail;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class CreateUser extends CreateRecord
{
    protected function afterCreate(): void
    {
        try {
            Mail::to(new Address($this->record->email, $this->record->name ?? ''))
                ->send(new NewUserMail($this->record, $this->plainPassword));
        } catch (Throwable $exception) {
            Log::warning('New user created, but welcome email could not be sent.', [
                'user_id' => $this->record->id,
                'email' => $this->record->email,
                'error' => $exception->getMessage(),
            ]);

            Notification::make()
                ->warning()
                ->title('User created, but email was not sent')
                ->body('Please verify the SMTP settings and resend the welcome email if needed.')
                ->send();
        }
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
