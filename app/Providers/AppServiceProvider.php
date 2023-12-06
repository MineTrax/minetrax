<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

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
        //
        Pulse::users(function ($ids) {
            return User::findMany($ids)->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'extra' => $user->email,
                'avatar' => $user->profile_photo_url,
            ]);
        });
    }
}
