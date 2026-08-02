<?php

use App\Contracts\StorePaymentGatewayContract;
use App\Enums\Concerns\HasKeyValueSerialization;
use App\Models\BaseModel;

/**
 * @return array<int, string>
 */
function storeMigrations(): array
{
    return glob(database_path('migrations/*create_store_*.php')) ?: [];
}

/**
 * @return array<int, string>
 */
function storeSourceFiles(): array
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

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
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
function storeEnums(): array
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
function storeModels(): array
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
function storeNotifications(): array
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

test('the module actually has the files these rules police', function () {
    // Guards against every assertion below passing vacuously because a glob stopped matching.
    expect(count(storeMigrations()))->toBeGreaterThanOrEqual(7);
    expect(count(storeModels()))->toBeGreaterThanOrEqual(15);
    expect(count(storeEnums()))->toBeGreaterThanOrEqual(8);
    expect(count(storeSourceFiles()))->toBeGreaterThanOrEqual(10);
    expect(count(storeNotifications()))->toBeGreaterThanOrEqual(5);
});

test('every store notification can render for the channels it offers', function () {
    $offenders = [];

    foreach (storeNotifications() as $class) {
        $source = file_get_contents((new ReflectionClass($class))->getFileName());

        // Only the ones that read a user's preferences can end up on the discord channel; a
        // notification with a hardcoded via() offers exactly what it implements.
        if (! str_contains($source, 'notificationPreferencesFor')) {
            continue;
        }

        if (! method_exists($class, 'toDiscord')) {
            $offenders[] = class_basename($class);
        }
    }

    expect($offenders)->toBeEmpty('These notifications offer the discord channel but cannot render for it: '.implode(', ', $offenders));
});

test('no store migration declares a database enum column', function () {
    foreach (storeMigrations() as $path) {
        $this->assertStringNotContainsString(
            '->enum(',
            file_get_contents($path),
            basename($path).' declares a DB enum column. Use a string plus a PHP backed enum cast.'
        );
    }
});

test('no store source file scales money by a hardcoded hundred', function () {
    $offenders = [];

    foreach (storeSourceFiles() as $path) {
        if (basename($path) === 'StoreCurrencyService.php') {
            continue; // the one place allowed to know about exponents at all
        }

        $source = file_get_contents($path);

        if (preg_match('/[*\/]\s*100\b/', $source)) {
            $offenders[] = basename($path);
        }
    }

    expect($offenders)->toBeEmpty('Money must never be scaled by a literal 100. Use StoreCurrencyService. Offenders: '.implode(', ', $offenders));
});

test('every store enum serialises as key and value', function () {
    foreach (storeEnums() as $enum) {
        expect(is_subclass_of($enum, HasKeyValueSerialization::class))->toBeTrue($enum.' must implement HasKeyValueSerialization so the frontend reads it as {key, value}.');
        expect(is_subclass_of($enum, BackedEnum::class))->toBeTrue($enum.' must be a backed enum; the DB column stores its value.');
    }
});

test('every store model extends base model', function () {
    foreach (storeModels() as $model) {
        expect(is_subclass_of($model, BaseModel::class))->toBeTrue($model.' must extend BaseModel, which is what serialises enums as {key, value}.');
    }
});

test('no store migration stores money in a float column', function () {
    foreach (storeMigrations() as $path) {
        $source = file_get_contents($path);

        foreach (['->float(', '->double('] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $source,
                basename($path).' uses '.$forbidden.'. Money is integer minor units; rates are decimal.'
            );
        }
    }
});

test('every registered gateway class exists and implements the contract', function () {
    $registry = config('store.gateways', []);

    expect($registry)->not->toBeEmpty();

    foreach ($registry as $key => $class) {
        expect(class_exists($class))->toBeTrue("Gateway [{$key}] points at a missing class [{$class}].");
        expect(is_subclass_of($class, StorePaymentGatewayContract::class))->toBeTrue("Gateway [{$key}] must implement StorePaymentGatewayContract.");
    }
});

test('the webhook route is registered without the api key middleware', function () {
    $route = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->getName() === 'api.store.webhook');

    expect($route)->not->toBeNull('The store webhook route is missing.');
    expect($route->gatherMiddleware())->not->toContain('auth.api-key');
    expect($route->gatherMiddleware())->toContain('throttle:store-webhook');
});

test('no store test drives checkout through the live mojang lookup', function () {
    // StorePlayerResolver falls through to api.minecraftservices.com for a username it does not
    // already know, and Mojang rate-limits that endpoint — so a test that checks out without
    // either seeding the player, faking the HTTP call, or turning verification off passes until
    // the suite has been run a few times in a row, then fails at random on an unrelated assertion.
    $offenders = [];

    foreach (glob(__DIR__.'/*.php') as $file) {
        $source = file_get_contents($file);

        if (! str_contains($source, "route('store.checkout.store')")) {
            continue;
        }

        $isInsulated = str_contains($source, "Player::factory()->create(['username'")
            || str_contains($source, 'Http::fake')
            || str_contains($source, 'mojang_username_verification = false')
            || str_contains($source, "'mojang_username_verification' => false");

        if (! $isInsulated) {
            $offenders[] = basename($file);
        }
    }

    expect($offenders)->toBe([], 'These files check out without insulating the Mojang lookup: '.implode(', ', $offenders));
});

test('every listing layout shows a reduced price and says what reduced it', function () {
    // The props were always right; two of the four layouts simply never rendered them. The stacked
    // rows showed the sale price with nothing to compare it against, and the comparison table
    // struck the old price through without naming the sale — so a store-wide sale ran invisibly on
    // both. Asserting Inertia props cannot catch that, because the props were never the problem.
    $directory = base_path('resources/default/js/Components/Store');
    $layouts = [
        'StorePackageCard',
        'StorePackageListing',
        'StorePackageStacked',
        'StorePackageComparison',
    ];

    foreach ($layouts as $layout) {
        $source = file_get_contents("{$directory}/{$layout}.vue");

        // Follow one level of imports: most layouts delegate the saving to a shared child, and the
        // shared children are leaves.
        preg_match_all('#@/Components/Store/(\w+)\.vue#', $source, $matches);

        foreach (array_unique($matches[1]) as $child) {
            $source .= file_get_contents("{$directory}/{$child}.vue");
        }

        // str_contains rather than toContain: Pest reads every extra argument to toContain as
        // another needle, so a failure message passed there is silently asserted as one.
        expect(str_contains($source, 'price_original_formatted'))->toBeTrue(
            "[{$layout}] never shows the price a discount came down from."
        );

        expect(str_contains($source, 'sale_name') || str_contains($source, 'discountLabel'))->toBeTrue(
            "[{$layout}] shows a reduced price without naming the sale or discount behind it."
        );
    }
});
