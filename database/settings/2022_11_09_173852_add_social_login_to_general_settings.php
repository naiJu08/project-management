<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddSocialLoginToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.enable_social_login', true);
        } catch (\Exception $e) {
            // Setting already exists, skip
        }
    }
}
