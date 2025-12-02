<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UI\UIChangesCollector;
use App\Services\UI\Support\UIIdGenerator;

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

    public function __construct(protected UIChangesCollector $uiChanges)
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
        $incomingStorage = $request->storage;

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
            unset($parameters['_caller_service_id']); // Remove internal parameter

            // Resolve service class from component ID or caller service ID
            if ($callerServiceId) {
                // Use the caller service (the one that opened the modal)
                $serviceClass = UIIdGenerator::getContextFromId($callerServiceId);
            } else {
                // Use the component's service (normal flow)
                $serviceClass = UIIdGenerator::getContextFromId($componentId);
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

            $this->uiChanges->setStorage($incomingStorage);
            $service->initializeEventContext($incomingStorage);
            $service->$method($parameters);
            $service->finalizeEventContext();
            $result = $this->uiChanges->all();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => config('app.debug') ? $e->getMessage() : null,
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
