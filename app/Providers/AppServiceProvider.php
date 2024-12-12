<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Support\Facades\Vite;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureCommands();

        // Add User Info to Pulse
        Pulse::users(function ($ids) {
            return User::findMany($ids)->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'extra' => $user->email,
                'avatar' => $user->profile_photo_url,
            ]);
        });

        // Add ->recursive() method to Collection. Eg: collect([something])->recursive()
        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });

        // Queue Status
        Queue::after(function (JobProcessed $event) {
            Cache::set('queue_last_processed', now());
        });

        // Vite Prefetch
        if (config('inertia.vite_prefetch')) {
            Vite::prefetch(concurrency: 3);
        }
    }

    private function configureCommands()
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
