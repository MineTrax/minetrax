<?php

namespace Tests\Feature\Store;

use App\Contracts\StorePaymentGatewayContract;
use App\Enums\Concerns\HasKeyValueSerialization;
use App\Models\BaseModel;
use Tests\TestCase;

/**
 * Conventions the Store module has to keep, enforced by reading the source rather than by review.
 *
 * These are the rules that are easy to break by accident months from now and expensive to notice:
 * a DB enum column that cannot be extended without an ALTER, a hardcoded x100 that overcharges a
 * Japanese buyer a hundredfold, an enum that serialises differently from every other one.
 */
class StoreArchTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function storeMigrations(): array
    {
        return glob(database_path('migrations/*create_store_*.php')) ?: [];
    }

    /**
     * @return array<int, string>
     */
    private function storeSourceFiles(): array
    {
        $paths = [];

        foreach ([
            app_path('Http/Controllers/Store'),
            app_path('Http/Controllers/Admin/Store'),
            app_path('Services'),
            app_path('Jobs/Store'),
            app_path('Utils/Payments'),
            app_path('Models'),
        ] as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php' && str_contains($file->getFilename(), 'Store')) {
                    $paths[] = $file->getPathname();
                }
            }
        }

        return $paths;
    }

    /**
     * @return array<int, class-string>
     */
    private function storeEnums(): array
    {
        $enums = [];

        foreach (glob(app_path('Enums/Store*.php')) ?: [] as $path) {
            $class = 'App\\Enums\\'.basename($path, '.php');

            if (enum_exists($class)) {
                $enums[] = $class;
            }
        }

        return $enums;
    }

    /**
     * @return array<int, class-string>
     */
    private function storeModels(): array
    {
        $models = [];

        foreach (glob(app_path('Models/Store*.php')) ?: [] as $path) {
            $class = 'App\\Models\\'.basename($path, '.php');

            if (class_exists($class)) {
                $models[] = $class;
            }
        }

        return $models;
    }

    /**
     * @return array<int, class-string>
     */
    private function storeNotifications(): array
    {
        $notifications = [];

        foreach (glob(app_path('Notifications/Store*.php')) ?: [] as $path) {
            $class = 'App\\Notifications\\'.basename($path, '.php');

            if (class_exists($class)) {
                $notifications[] = $class;
            }
        }

        return $notifications;
    }

    public function test_the_module_actually_has_the_files_these_rules_police()
    {
        // Guards against every assertion below passing vacuously because a glob stopped matching.
        $this->assertGreaterThanOrEqual(7, count($this->storeMigrations()));
        $this->assertGreaterThanOrEqual(15, count($this->storeModels()));
        $this->assertGreaterThanOrEqual(8, count($this->storeEnums()));
        $this->assertGreaterThanOrEqual(10, count($this->storeSourceFiles()));
        $this->assertGreaterThanOrEqual(5, count($this->storeNotifications()));
    }

    /**
     * Every notification that offers a channel must be able to render for it.
     *
     * `notificationPreferencesFor()` returns database, mail *and* discord for anyone who has not
     * narrowed their preferences, so that is the default for most users. The Discord channel then
     * calls toDiscord() without checking it exists, and the job dies in the queue — silently, from
     * the buyer's point of view, because the receipt simply never arrives.
     */
    public function test_every_store_notification_can_render_for_the_channels_it_offers()
    {
        $offenders = [];

        foreach ($this->storeNotifications() as $class) {
            $source = file_get_contents((new \ReflectionClass($class))->getFileName());

            // Only the ones that read a user's preferences can end up on the discord channel; a
            // notification with a hardcoded via() offers exactly what it implements.
            if (! str_contains($source, 'notificationPreferencesFor')) {
                continue;
            }

            if (! method_exists($class, 'toDiscord')) {
                $offenders[] = class_basename($class);
            }
        }

        $this->assertEmpty(
            $offenders,
            'These notifications offer the discord channel but cannot render for it: '.implode(', ', $offenders)
        );
    }

    /**
     * MySQL cannot add a value to an ENUM column without an ALTER on the whole table, so every
     * enumerated column here is a plain string cast to a PHP backed enum instead.
     */
    public function test_no_store_migration_declares_a_database_enum_column()
    {
        foreach ($this->storeMigrations() as $path) {
            $this->assertStringNotContainsString(
                '->enum(',
                file_get_contents($path),
                basename($path).' declares a DB enum column. Use a string plus a PHP backed enum cast.'
            );
        }
    }

    /**
     * The single most expensive mistake this module could make. JPY has no minor unit and KWD has
     * three, so a literal 100 is wrong for both directions. Amounts only ever come from
     * StoreCurrencyService, which asks brick/money for the ISO exponent.
     */
    public function test_no_store_source_file_scales_money_by_a_hardcoded_hundred()
    {
        $offenders = [];

        foreach ($this->storeSourceFiles() as $path) {
            if (basename($path) === 'StoreCurrencyService.php') {
                continue; // the one place allowed to know about exponents at all
            }

            $source = file_get_contents($path);

            if (preg_match('/[*\/]\s*100\b/', $source)) {
                $offenders[] = basename($path);
            }
        }

        $this->assertEmpty(
            $offenders,
            'Money must never be scaled by a literal 100. Use StoreCurrencyService. Offenders: '.implode(', ', $offenders)
        );
    }

    public function test_every_store_enum_serialises_as_key_and_value()
    {
        foreach ($this->storeEnums() as $enum) {
            $this->assertTrue(
                is_subclass_of($enum, HasKeyValueSerialization::class),
                $enum.' must implement HasKeyValueSerialization so the frontend reads it as {key, value}.'
            );
            $this->assertTrue(
                is_subclass_of($enum, \BackedEnum::class),
                $enum.' must be a backed enum; the DB column stores its value.'
            );
        }
    }

    public function test_every_store_model_extends_base_model()
    {
        foreach ($this->storeModels() as $model) {
            $this->assertTrue(
                is_subclass_of($model, BaseModel::class),
                $model.' must extend BaseModel, which is what serialises enums as {key, value}.'
            );
        }
    }

    /**
     * Money columns are integers in the currency's minor unit. A float or decimal column would
     * reintroduce the rounding drift the whole design exists to avoid.
     */
    public function test_no_store_migration_stores_money_in_a_float_column()
    {
        foreach ($this->storeMigrations() as $path) {
            $source = file_get_contents($path);

            foreach (['->float(', '->double('] as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $source,
                    basename($path).' uses '.$forbidden.'. Money is integer minor units; rates are decimal.'
                );
            }
        }
    }

    /**
     * Every driver in the registry has to satisfy the contract, so registering a new one is
     * covered by the existing suite the moment the config line lands.
     */
    public function test_every_registered_gateway_class_exists_and_implements_the_contract()
    {
        $registry = config('store.gateways', []);

        $this->assertNotEmpty($registry);

        foreach ($registry as $key => $class) {
            $this->assertTrue(class_exists($class), "Gateway [{$key}] points at a missing class [{$class}].");
            $this->assertTrue(
                is_subclass_of($class, StorePaymentGatewayContract::class),
                "Gateway [{$key}] must implement StorePaymentGatewayContract."
            );
        }
    }

    /**
     * The webhook route has to stay outside the api-key group: a gateway cannot compute
     * MineTrax's own HMAC, so moving it inside would silently reject every real callback.
     */
    public function test_the_webhook_route_is_registered_without_the_api_key_middleware()
    {
        $route = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'api.store.webhook');

        $this->assertNotNull($route, 'The store webhook route is missing.');
        $this->assertNotContains('auth.api-key', $route->gatherMiddleware());
        $this->assertContains('throttle:store-webhook', $route->gatherMiddleware());
    }
}
