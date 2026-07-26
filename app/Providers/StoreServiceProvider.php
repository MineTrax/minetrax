<?php

namespace App\Providers;

use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StorePaymentGatewayManager::class, function ($app) {
            return new StorePaymentGatewayManager($app);
        });
    }
}
