<?php
namespace App\Listeners;

use App\Events\UsimEvent;
use Idei\Usim\Services\Support\UIIdGenerator;
use Idei\Usim\Services\Support\UIStateManager;

class UsimEventDispatcher
{
    public function handle(UsimEvent $event): void
    {
        // Convertir el nombre del evento a nombre de método
        // "logged_user" -> "onLoggedUser"
        $methodName = 'on' . str_replace('_', '', ucwords($event->eventName, '_'));

        $rootComponents  = UIStateManager::getRootComponents();
        $incomingStorage = request()->storage;

        foreach ($rootComponents as $parent => $rootComponentId) {
            $serviceClass = UIIdGenerator::getContextFromId($rootComponentId);

            // Instantiate service
            $service = app($serviceClass);
            if (method_exists($service, $methodName)) {
                $service->initializeEventContext($incomingStorage, debug: true);

                $result = [];

                // Invoke handler method
                $methodResult = $service->$methodName($event->params);

                // if (is_array($methodResult)) {
                //     $result = $methodResult;
                // }

                $finalizedResult = $service->finalizeEventContext(debug: true);

                // if (is_array($finalizedResult)) {
                //     $result += $finalizedResult;
                // }

                // $storageVariables = $service->getStorageVariables();

                // if (!empty($storageVariables)) {
                //     $mergedStorage = array_merge($incomingStorage, $storageVariables);
                //     $result['storage'] = ['usim' => encrypt(json_encode($mergedStorage))];
                // }
            }
        }
    }
}
