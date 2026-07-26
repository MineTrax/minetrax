<?php

use App\Settings\GeneralSettings;
use App\Settings\NavigationSettings;
use App\Settings\PlayerSettings;
use App\Settings\PluginSettings;
use App\Settings\SeoSettings;
use App\Settings\StoreSettings;
use App\Settings\ThemeSettings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;
use Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository;

return [

    /*
     * Each settings class used in your application must be registered, you can
     * put them (manually) here.
     */
    'settings' => [
        GeneralSettings::class,
        PluginSettings::class,
        ThemeSettings::class,
        PlayerSettings::class,
        NavigationSettings::class,
        SeoSettings::class,
        StoreSettings::class,
    ],

    /*
     * When you create a new settings migration via the `make:settings-migration`
     * command the package will store these migrations in this directory.
     */
    'migrations_path' => database_path('settings'),

    /*
     * When no repository was set for a settings class the following repository
     * will be used for loading and saving settings.
     */
    'default_repository' => 'database',

    /*
     * Settings will be stored and loaded from these repositories.
     */
    'repositories' => [
        'database' => [
            'type' => DatabaseSettingsRepository::class,
            'model' => null,
            'connection' => null,
        ],
        'redis' => [
            'type' => RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
     * The contents of settings classes can be cached through your application,
     * settings will be stored within a provided Laravel store and can have an
     * additional prefix.
     */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', false),
        'store' => null,
        'prefix' => null,
    ],

    /*
     * These global casts will be automatically used whenever a property within
     * your settings class isn't a default PHP type.
     */
    'global_casts' => [
        DateTimeInterface::class => DateTimeInterfaceCast::class,
        DateTimeZone::class => DateTimeZoneCast::class,
        //        Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
    ],

    /*
     * The package will look for settings in these paths and automatically
     * register them.
     */
    'auto_discover_settings' => [
        app()->path(),
    ],

    /*
     * Automatically discovered settings classes can be cached so they don't
     * need to be searched each time the application boors up.
     */
    'discovered_settings_cache_path' => storage_path('app/laravel-settings'),
];
