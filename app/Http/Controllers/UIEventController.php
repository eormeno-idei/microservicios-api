<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UI\UIChangesCollector as AppChangesCollector;
use Idei\Usim\Services\UIChangesCollector as PackageChangesCollector;
use App\Services\UI\Support\UIIdGenerator as AppIdGenerator;
use Idei\Usim\Services\Support\UIIdGenerator as PackageIdGenerator;

/**
 * UI Event Controller
 *
 * Handles UI component events from the frontend.
 * Uses reflection to dynamically route events to service methods
 * based on component ID and action name.
 *
 * Flow:
 * 1. Receive event from frontend (component_id, event, action, parameters)
 * 2. Resolve service class from component ID using UIIdGenerator
 * 3. Convert action name to method name (snake_case → onPascalCase)
 * 4. Invoke method via reflection
 * 5. Return response (success/error + optional UI updates)
 */
class UIEventController extends Controller
{

    public function __construct(
        protected AppChangesCollector $appChanges,
        protected PackageChangesCollector $packageChanges
    )
    {
    }

    /**
     * Handle UI component event
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleEvent(Request $request): JsonResponse
    {
        $incomingStorage = $request->storage ?? [];
        // \Illuminate\Support\Facades\Log::info('UIEventController Incoming Storage:', $incomingStorage);

        // Validate request
        $validated = $request->validate([
            'component_id' => 'required|integer',
            'event' => 'required|string',
            'action' => 'required|string',
            'parameters' => 'array',
        ]);

        $componentId = $validated['component_id'];
        $action = $validated['action'];
        $parameters = $validated['parameters'] ?? [];

        try {
            // Check if there's a caller service ID (for modal callbacks)
            $callerServiceId = $parameters['_caller_service_id'] ?? null;
            if (isset($parameters['_caller_service_id'])) {
                unset($parameters['_caller_service_id']); // Remove internal parameter
            }

            // Resolve service class from component ID or caller service ID
            if ($callerServiceId) {
                // Try Package first, then App
                $serviceClass = PackageIdGenerator::getContextFromId((int)$callerServiceId)
                             ?? AppIdGenerator::getContextFromId((int)$callerServiceId);
            } else {
                $serviceClass = PackageIdGenerator::getContextFromId((int)$componentId)
                             ?? AppIdGenerator::getContextFromId((int)$componentId);
            }


            if (!$serviceClass) {
                return response()->json([
                    'error' => 'Service not found for this component',
                ], 404);
            }

            // Instantiate service
            $service = app($serviceClass);

            // Convert action to method name: test_action → onTestAction
            $method = $this->actionToMethodName($action);

            // Verify method exists
            if (!method_exists($service, $method)) {
                return response()->json([
                    'error' => "Action '{$action}' not implemented",
                ], 404);
            }

            // Init BOTH collectors
            $this->appChanges->setStorage($incomingStorage);
            $this->packageChanges->setStorage($incomingStorage);

            $service->initializeEventContext($incomingStorage);
            $service->$method($parameters);
            $service->finalizeEventContext();

            // Resolve and merge results from BOTH collectors
            $pkgResult = $this->packageChanges->all();
            $appResult = $this->appChanges->all();

            // Custom merge to preserve numeric keys (Component IDs)
            // Fix: Exclude 'storage' from generic recursive merge to avoid array conversion of encryption string
            // Logic: Determine authoritative storage source based on service type

            $authoritativeCollector = null;
            if ($service instanceof \Idei\Usim\Services\AbstractUIService) {
                $authoritativeCollector = $this->packageChanges;
            } elseif ($service instanceof \App\Services\UI\AbstractUIService) {
                $authoritativeCollector = $this->appChanges;
            }

            // Get the correct storage block
            $finalStorage = $authoritativeCollector ? $authoritativeCollector->all()['storage'] ?? null : null;

            // Remove storage from intermediate results to prevent dirty merge
            if (isset($pkgResult['storage'])) unset($pkgResult['storage']);
            if (isset($appResult['storage'])) unset($appResult['storage']);

            $result = $appResult;
            foreach ($pkgResult as $key => $value) {
                if (isset($result[$key]) && is_array($value) && is_array($result[$key])) {
                    $result[$key] = array_merge_recursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }

            // Re-attach the correct storage
            if ($finalStorage) {
                $result['storage'] = $finalStorage;
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Convert action name to method name
     *
     * Convention: snake_case → onPascalCase
     * Examples:
     * - test_action → onTestAction
     * - submit_form → onSubmitForm
     * - cancel_form → onCancelForm
     * - open_settings → onOpenSettings
     *
     * @param string $action Action name in snake_case
     * @return string Method name in onPascalCase format
     */
    private function actionToMethodName(string $action): string
    {
        // Replace underscores with spaces, capitalize words, remove spaces
        $pascalCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $action)));

        return 'on' . $pascalCase;
    }
}
