<?php

namespace App\Listeners;

use App\Events\UsimEvent;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Support\UIIdGenerator;
use App\Services\UI\Support\UIStateManager;

class UsimEventDispatcher
{
    public function handle(UsimEvent $event): void
    {
        // Convertir el nombre del evento a nombre de método
        // "logged_user" -> "onLoggedUser"
        $methodName = 'on' . str_replace('_', '', ucwords($event->eventName, '_'));

        $rootComponents = UIStateManager::getRootComponents();
        $incomingStorage = request()->storage;

        foreach ($rootComponents as $parent => $rootComponentId) {
            $serviceClass = UIIdGenerator::getContextFromId($rootComponentId);

            // Instantiate service
            $service = app($serviceClass);
            if (method_exists($service, $methodName)) {
                $service->initializeEventContext($incomingStorage);

                $result = [];

                // Invoke handler method
                $methodResult = $service->$methodName($event->params);

                if (is_array($methodResult)) {
                    $result = $methodResult;
                }

                $finalizedResult = $service->finalizeEventContext();

                if (is_array($finalizedResult)) {
                    $result += $finalizedResult;
                }

                $storageVariables = $service->getStorageVariables();

                if (!empty($storageVariables)) {
                    $mergedStorage = array_merge($incomingStorage, $storageVariables);
                    $result['storage'] = ['usim' => encrypt(json_encode($mergedStorage))];
                }

                UIDebug::info('UI Event dispatched', $result);
            }
        }

        // // Leer servicios desde la configuración
        // $services = config('ui-services', []);

        // // Convertir el nombre del evento a nombre de método
        // // "logged_user" -> "onLoggedUser"
        // $methodName = 'on' . str_replace('_', '', ucwords($event->eventName, '_'));

        // foreach ($services as $serviceClass) {
        //     $service = app($serviceClass);

        //     if (method_exists($service, $methodName)) {
        //         $service->$methodName($event->params);
        //     }
        // }
    }
}
