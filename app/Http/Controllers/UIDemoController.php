<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\UI\Support\UIDebug;

class UIDemoController extends Controller
{
    /**
     * Show UI for the specified demo service
     *
     * @param string $demo The demo name from the route (e.g., 'demo-ui', 'input-demo')
     * @param bool $reset Whether to reset the stored UI state
     * @return JsonResponse
     */
    public function show(string $demo): JsonResponse
    {
        $reset = request()->query('reset', false);

        // Get and decrypt storage from header
        $incomingStorage = $this->getStorageFromRequest(request());

        UIDebug::info("Incoming storage for demo '{$demo}':", $incomingStorage);

        // Convert kebab-case to PascalCase and append 'Service'
        // Example: 'demo-ui' -> 'DemoUi' -> 'DemoUiService'
        $serviceName = Str::studly($demo) . 'Service';

        // Build fully qualified class name
        $serviceClass = "App\\Services\\Screens\\{$serviceName}";

        // Check if service class exists
        if (!class_exists($serviceClass)) {
            return response()->json([
                'error' => 'Demo service not found',
                'service' => $serviceName
            ], 404);
        }

        // Instantiate service using Laravel's service container
        // This allows dependency injection to work
        $service = app($serviceClass);
        // Inject incoming storage values into the service
        $service->injectStorageValues($incomingStorage);

        // If the 'reset' url parameter is present, clear any cached data
        if ($reset) {
            // Log::info("Resetting stored UI for demo service: {$serviceName}");
            $service->clearStoredUI();
            $service->onResetService();
        }

        $ui = $service->getUI();

        $firstElementType = $ui[array_keys($ui)[0]]['type'] ?? null;
        if ($firstElementType !== 'menu_dropdown') {
            //Log::info("\n" . json_encode($ui, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }

        // Return UI JSON
        return response()->json($ui);
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
