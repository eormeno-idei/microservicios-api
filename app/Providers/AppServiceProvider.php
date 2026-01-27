<?php

namespace App\Providers;

use Idei\Usim\Services\UIChangesCollector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar como singleton para que sea la misma instancia en toda la request
        // TODO: Analizar si es mejor singleton o scoped
        $this->app->scoped(UIChangesCollector::class, function ($app) {
            return new UIChangesCollector();
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
