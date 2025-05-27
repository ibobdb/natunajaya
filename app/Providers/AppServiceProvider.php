<?php

namespace App\Providers;

use App\Helpers\MidtransHelper;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Midtrans Helper
        $this->app->singleton(MidtransHelper::class, function ($app) {
            return new MidtransHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
