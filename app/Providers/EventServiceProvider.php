<?php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\UsimEvent;
use App\Listeners\UsimEventDispatcher;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UsimEvent::class => [
            UsimEventDispatcher::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
