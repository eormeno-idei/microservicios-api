<?php

namespace App\Listeners;

use App\Events\UsimEvent;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Support\UIStateManager;

class UsimEventDispatcher
{
    public function handle(UsimEvent $event): void
    {
        $rootComponents = UIStateManager::getRootComponents();

        UIDebug::info('Root Components:', $rootComponents);
        
        // Leer servicios desde la configuración
        $services = config('ui-services', []);
        
        // Convertir el nombre del evento a nombre de método
        // "logged_user" -> "onLoggedUser"
        $methodName = 'on' . str_replace('_', '', ucwords($event->eventName, '_'));

        foreach ($services as $serviceClass) {
            $service = app($serviceClass);
            
            if (method_exists($service, $methodName)) {
                $service->$methodName($event->params);
            }
        }
    }
}