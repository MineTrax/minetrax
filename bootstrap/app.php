<?php

use App\Console\Commands\ResetUserPasswordCommand;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\EnsureEmailIsVerifiedWhenFeatureEnabled;
use App\Http\Middleware\ForbidBannedUser;
use App\Http\Middleware\ForbidMutedUser;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ImpersonateSanctum;
use App\Http\Middleware\RedirectUncompletedUser;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\StaffMember;
use App\Jobs\CalculatePlayersJob;
use App\Jobs\RunAwaitingCommandQueuesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ResetUserPasswordCommand::class,
    ])
    // Listeners are wired explicitly in App\Providers\EventServiceProvider::$listen. Laravel's
    // automatic discovery of app/Listeners defaults to on, which registered every one of them a
    // second time as "Listener@handle" and made each fire twice per event.
    ->withEvents(discover: false)
    ->withSchedule(function (Schedule $schedule) {
        $playerFetcherInterval = config('minetrax.players_fetcher_cron_interval') ?? 'hourly';
        $schedule->job(new CalculatePlayersJob)->{$playerFetcherInterval}();
        $schedule->job(new RunAwaitingCommandQueuesJob)->everyMinute();

        $schedule->command('telescope:prune')->daily();
        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
        $schedule->command('model:prune')->daily();
        $schedule->command('cache:prune-stale-tags')->hourly();

        $backupEnabled = config('backup.enabled');
        if ($backupEnabled) {
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
        }
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies
        $middleware->trustProxies(at: '*');

        // All cookies that is/shouldn't be encrypted, like the cookie consent cookie
        $middleware->encryptCookies(except: [
            'laravel_cookie_consent',
        ]);

        // Web middleware group
        $middleware->web(append: [
            HandleInertiaRequests::class,
            SetLocale::class,
        ]);

        // API middleware group
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
            ImpersonateSanctum::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'forbid-banned-user' => ForbidBannedUser::class,
            'forbid-muted-user' => ForbidMutedUser::class,
            'redirect-uncompleted-user' => RedirectUncompletedUser::class,
            'verified-if-enabled' => EnsureEmailIsVerifiedWhenFeatureEnabled::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'staff-member' => StaffMember::class,
            'auth.api-key' => AuthenticateApiKey::class,
        ]);

        // Throttle with Redis
        $middleware->throttleWithRedis();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'password',
            'password_confirmation',
        ]);
    })
    ->create();
