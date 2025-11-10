<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

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
        $parent = request()->query('parent', "main");

        $incomingStorage = request()->storage;

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

        $ui = $service->getUI($parent);

        $firstElementType = $ui[array_keys($ui)[0]]['type'] ?? null;
        if ($firstElementType !== 'menu_dropdown') {
            //Log::info("\n" . json_encode($ui, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }

        // Return UI JSON
        return response()->json($ui);
    }
}
