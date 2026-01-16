<?php

namespace Idei\Usim;

use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Idei\Usim\Services\Support\UIIdGenerator;
use Illuminate\Contracts\Events\Dispatcher;

use Idei\Usim\Services\UIChangesCollector;

class UsimServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/usim.php', 'ui-services'
        );

        $this->app->scoped(UIChangesCollector::class, function ($app) {
            return new UIChangesCollector();
        });
    }

    public function boot(Dispatcher $events): void
    {
        // Listener para resetear estado en Octane/RoadRunner
        $events->listen(RequestReceived::class, function () {
            UIIdGenerator::reset();
        });

        $this->publishes([
            __DIR__.'/../config/usim.php' => config_path('ui-services.php'),
        ], 'usim-config');

        $this->publishes([
            __DIR__.'/../resources/js' => public_path('js'),
            __DIR__.'/../resources/css' => public_path('css'),
        ], 'usim-assets');
    }
}
