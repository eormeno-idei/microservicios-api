<?php

namespace App\Http\Controllers;

use App\Services\UI\Support\UIDebug;
use App\Services\UI\Support\UIIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    /**
     * Handle UI component event
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleEvent(Request $request): JsonResponse
    {
        // Get and decrypt storage from header
        $incomingStorage = $this->getStorageFromRequest($request);

        // Validate request
        $validated = $request->validate([
            'component_id' => 'required|integer',
            'event' => 'required|string',
            'action' => 'required|string',
            'parameters' => 'array',
        ]);

        $componentId = $validated['component_id'];
        $event = $validated['event'];
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
                Log::warning('UI Event: Service not found for component', [
                    'component_id' => $componentId,
                    'caller_service_id' => $callerServiceId,
                    'action' => $action,
                ]);

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
                Log::warning('UI Event: Action method not found', [
                    'service' => $serviceClass,
                    'action' => $action,
                    'method' => $method,
                ]);

                return response()->json([
                    'error' => "Action '{$action}' not implemented",
                ], 404);
            }

            $service->initializeEventContext($incomingStorage);

            // Invoke handler method
            $result = $service->$method($parameters);
            if (!is_array($result)) {
                $result = $service->finalizeEventContext();
            }

            $storageVariables = $service->getStorageVariables();

            UIDebug::debug("Incoming front variables", $incomingStorage);

            if (!empty($storageVariables)) {
                UIDebug::debug("New storage variables", $storageVariables);
                $mergedStorage = array_merge($incomingStorage, $storageVariables);
                UIDebug::debug("Merged storage variables", $mergedStorage);

                $result['storage'] = ['usim' => encrypt(json_encode($mergedStorage))];
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('UI Event: Exception during action execution', [
                'component_id' => $componentId,
                'action' => $action,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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

    /**
     * Get storage from request header and decrypt it
     *
     * Reads the X-USIM-Storage header, decrypts it, and converts from JSON to array.
     * If header is missing, empty, or decryption fails, returns empty array.
     *
     * @param Request $request
     * @return array Decrypted storage data or empty array
     */
    private function getStorageFromRequest(Request $request): array
    {
        try {
            // Get storage from header
            $encryptedStorage = $request->header('X-USIM-Storage');

            // Return empty array if header is missing or empty
            if (empty($encryptedStorage)) {
                return [];
            }

            // Decrypt the storage
            $decryptedJson = decrypt($encryptedStorage);

            // Convert JSON to array
            $storage = json_decode($decryptedJson, true);

            // Return empty array if JSON decode failed
            if (!is_array($storage)) {
                return [];
            }

            return $storage;
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::debug('Failed to decrypt storage from header', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
