<?php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
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
        // Log cuando se procesa un job exitosamente
        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            if (str_contains($event->job->resolveName(), 'Notification') ||
                str_contains($event->job->resolveName(), 'SendEmailVerification')) {
                Log::info('✅ Email sent successfully', [
                    'job' => $event->job->resolveName(),
                    'queue' => $event->job->getQueue(),
                ]);
            }
        });

        // Log cuando falla un job
        Event::listen(JobFailed::class, function (JobFailed $event) {
            if (str_contains($event->job->resolveName(), 'Notification') ||
                str_contains($event->job->resolveName(), 'SendEmailVerification')) {
                Log::error('❌ Email failed to send', [
                    'job' => $event->job->resolveName(),
                    'exception' => $event->exception->getMessage(),
                ]);
            }
        });
    }
}
