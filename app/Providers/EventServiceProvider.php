<?php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use Idei\Usim\Events\UsimEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Idei\Usim\Listeners\UsimEventDispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // Removed $listen array - Laravel 11 autodiscovery handles this automatically
    // based on type-hints in Listener::handle() methods

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
