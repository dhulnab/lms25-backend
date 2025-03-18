<?php

namespace App\Providers;

use App\Services\FirebaseNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseNotificationService::class, function ($app) {
            return new FirebaseNotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
