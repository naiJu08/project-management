<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddLoginFormOidcEnabledFlagsToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.enable_login_form', config('system.login_form.is_enabled'));
        } catch (\Exception $e) {
            // Setting already exists, skip
        }
        try {
            $this->migrator->add('general.enable_oidc_login', config('services.oidc.is_enabled'));
        } catch (\Exception $e) {
            // Setting already exists, skip
        }
    }
}
