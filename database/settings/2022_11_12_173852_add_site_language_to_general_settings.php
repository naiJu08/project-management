<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddSiteLanguageToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.site_language', config('app.fallback_locale'));
        } catch (\Exception $e) {
            // Setting already exists, skip
        }
    }
}
