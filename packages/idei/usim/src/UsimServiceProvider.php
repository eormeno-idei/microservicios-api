<?php

namespace Idei\Usim;

use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Idei\Usim\Services\Support\UIIdGenerator;
use Illuminate\Contracts\Events\Dispatcher;

use Idei\Usim\Services\UIChangesCollector;
use Idei\Usim\Events\UsimEvent;
use Idei\Usim\Listeners\UsimEventDispatcher;
use Idei\Usim\Console\Commands\DiscoverScreensCommand;

use Illuminate\Console\Scheduling\Schedule;

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

        $this->commands([
            DiscoverScreensCommand::class,
        ]);
    }

    public function boot(Dispatcher $events): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'usim');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/idei/usim'),
            ], 'usim-assets');
        }

        // Programar limpieza de archivos temporales (Self-healing maintenance)
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->job(new \Idei\Usim\Jobs\CleanTemporaryUploadsJob)->hourly();
        });

        // Registrar Evento del Sistema
        $events->listen(UsimEvent::class, UsimEventDispatcher::class);

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
