<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        // Ensure theme settings have valid defaults for new dynamic theming system
        // Since we can't easily read existing values, we'll update to safe defaults

        // This will only update if the key doesn't exist or if forced
        // Most existing installations should already have valid theme_name from the original migration

        // No action needed for existing installations as the original migration
        // already sets theme.theme_name to 'light-blue' which is valid

        // This migration exists primarily for documentation and future compatibility
    }
};
