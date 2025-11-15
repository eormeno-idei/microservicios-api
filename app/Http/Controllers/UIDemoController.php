<?php
namespace App\Http\Controllers;

use App\Services\UI\Support\UIDebug;
use App\Services\UI\UIChangesCollector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UIDemoController extends Controller
{

    public function __construct(protected UIChangesCollector $uiChanges)
    {}

    /**
     * Show UI for the specified demo service
     *
     * @param string $demo The demo name from the route (e.g., 'demo-ui', 'input-demo')
     * @param bool $reset Whether to reset the stored UI state
     * @return JsonResponse
     */
    public function show(string $demo): JsonResponse
    {
        $reset  = request()->query('reset', false);
        $parent = request()->query('parent', "main");

        $incomingStorage = request()->storage;

        // Convert kebab-case to PascalCase and append 'Service'
        // Example: 'demo-ui' -> 'DemoUi' -> 'DemoUiService'
        $serviceName = Str::studly($demo) . 'Service';

        // Build fully qualified class name
        $serviceClass = "App\\Services\\Screens\\{$serviceName}";

        // Check if service class exists
        if (! class_exists($serviceClass)) {
            return response()->json([
                'error'   => 'Demo service not found',
                'service' => $serviceName,
            ], 404);
        }

        $this->uiChanges->setStorage($incomingStorage);

        // Instantiate service using Laravel's service container
        // This allows dependency injection to work
        $service = app($serviceClass);

        // Inject incoming storage values into the service
        // $service->injectStorageValues($incomingStorage);

        // If the 'reset' url parameter is present, clear any cached data
        if ($reset) {
            // Log::info("Resetting stored UI for demo service: {$serviceName}");
            $service->clearStoredUI();
            $service->onResetService();
        }

        $service->initializeEventContext($incomingStorage);
        $service->finalizeEventContext(reload: true);

        $result = $this->uiChanges->all();
        // $ui     = $service->getUI($parent);
        UIDebug::info("UI Demo Service Response", $result);
        return response()->json($result);
    }
}
