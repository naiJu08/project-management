<?php

use App\Models\Role;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddDefaultRoleToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.default_role');
        } catch (\Exception $e) {
            // Setting already exists, skip
        }
    }
}
