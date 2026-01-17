<?php
namespace App\Services\UI;

use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\CardBuilder;
use App\Services\UI\Components\CheckboxBuilder;
use App\Services\UI\Components\FormBuilder;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\MenuDropdownBuilder;
use App\Services\UI\Components\SelectBuilder;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\TableCellBuilder;
use App\Services\UI\Components\TableHeaderCellBuilder;
use App\Services\UI\Components\TableHeaderRowBuilder;
use App\Services\UI\Components\TableRowBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\UploaderBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Support\UIDiffer;
use App\Services\UI\Support\UIIdGenerator;
use App\Services\UI\Support\UIStateManager;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

/**
 * Abstract UI Service
 *
 * Base class for all UI services that handles:
 * - UI state storage and retrieval
 * - Automatic diff calculation
 * - Event lifecycle management
 * - Response formatting
 *
 * Child classes only need to:
 * 1. Implement buildBaseUI() to define UI structure
 * 2. Implement event handlers that modify components (no return needed)
 *
 * The lifecycle is managed by UIEventController:
 * - initializeEventContext() - Called before event handler
 * - onEventHandler($params) - Your event handler
 * - finalizeEventContext() - Called after event handler, returns formatted response
 */
abstract class AbstractUIService
{
    /**
     * Current UI container instance
     */
    protected UIContainer $container;

    /**
     * UI state before modifications (for diff calculation)
     */
    protected ?array $oldUI = null;

    /**
     * UI state after modifications (for diff calculation)
     */
    protected ?array $newUI = null;

    /**
     * Query parameters from the request
     */
    protected array $queryParams = [];

    protected function uiChanges(): UIChangesCollector
    {
        return app(UIChangesCollector::class);
    }

    /**
     * Build base UI structure
     *
     * Override this method in your service to define the base UI.
     * This will be called automatically if the cache expires.
     *
     * @param mixed ...$params Optional parameters for UI construction
     * @return UIContainer Base UI structure
     */
    abstract protected function buildBaseUI(UIContainer $container, ...$params): void;

    protected function postLoadUI(): void
    {
    }

    /**
     * Initialize event context
     *
     * Called by UIEventController before invoking event handler.
     * Loads UI container and captures state for diff calculation.
     * Also injects storage values and component references into protected properties.
     *
     * @param array $incomingStorage Storage data from frontend (decrypted)
     * @return void
     */
    public function initializeEventContext(array $incomingStorage = [], array $queryParams = [], bool $debug = false): void
    {
        $this->container = $this->getUIContainer($debug);
        $this->oldUI = $this->container->toJson();

        $this->queryParams = $queryParams;

        // Inject storage values into protected properties (store_* variables)
        $this->injectStorageValues($incomingStorage);

        // Inject component references into protected properties
        $this->injectComponentReferences();
    }

    /**
     * Inject storage values into protected properties
     *
     * Uses reflection to find protected properties whose names start with 'store_'.
     * If a matching key exists in the incoming storage array, the value is injected.
     *
     * Convention: Property name must match storage key
     * Example: protected int $store_user_id; matches storage['store_user_id']
     *
     * @param array $incomingStorage Storage data from frontend
     * @return void
     */
    public function injectStorageValues(array $incomingStorage): void
    {
        if (empty($incomingStorage)) {
            return;
        }

        $reflection = new ReflectionClass($this);

        $injected = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PROTECTED) as $property) {
            // Skip properties declared in AbstractUIService itself
            if ($property->getDeclaringClass()->getName() === self::class) {
                continue;
            }

            $propertyName = $property->getName();

            // Only process properties that start with 'store_'
            if (!str_starts_with($propertyName, 'store_')) {
                continue;
            }

            // Check if this key exists in incoming storage
            if (!array_key_exists($propertyName, $incomingStorage)) {
                 // \Illuminate\Support\Facades\Log::info("Skipping inject $propertyName - Not in storage");
                continue;
            }

            $value = $incomingStorage[$propertyName];
            // \Illuminate\Support\Facades\Log::info("Injecting $propertyName = $value");

            // Set the value
            $property->setValue($this, $value);

            $injected[$propertyName] = $value;
        }
    }

    /**
     * Inject component references into protected properties
     *
     * Uses reflection to find protected properties with UI component type hints.
     * If a property name matches a component name in the container,
     * the component is injected into that property.
     *
     * Convention: Property name must match component name
     * Example: protected LabelBuilder $lbl_result; matches component 'lbl_result'
     *
     * @return void
     */
    private function injectComponentReferences(): void
    {
        $reflection = new ReflectionClass($this);
        $injected = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PROTECTED) as $property) {
            // Skip properties declared in AbstractUIService itself
            if ($property->getDeclaringClass()->getName() === self::class) {
                continue;
            }

            $propertyType = $property->getType();

            // Skip if no type hint or is a built-in type
            if (!$propertyType || $propertyType->isBuiltin()) {
                continue;
            }

            $typeName = $propertyType->getName();

            // Only process UI component types
            if (str_starts_with($typeName, 'App\\Services\\UI\\Components\\')) {
                $componentName = $property->getName();
                $component = $this->container->findByName($componentName);

                if ($component) {
                    $property->setValue($this, $component);
                    $injected[$componentName] = $typeName;
                } elseif (!$propertyType->allowsNull()) {
                    // Component not found and property is not nullable
                    throw new RuntimeException(
                        "Component '{$componentName}' not found in UI container. " .
                        "Make sure the component exists or make the property nullable: protected ?{$typeName} \${$componentName};"
                    );
                }
            }
        }
    }

    /**
     * Finalize event context
     *
     * Called by UIEventController after event handler completes.
     * Automatically detects changes by comparing UI state, stores updated UI,
     * and returns formatted response.
     *
     * @return void
     */
    public function finalizeEventContext(bool $reload = false, bool $debug = false): void
    {

        if ($reload) {
            $this->postLoadUI();
        }

        // Get current UI state
        $this->newUI = $this->container->toJson();

        if (!$reload) {
            // Store updated UI
            $this->storeUI($this->container);
        }

        $diff = $this->buildDiffResponse($reload);
        $storageVariables = $this->getStorageVariables();
        $this->uiChanges()->add($diff);
        $this->uiChanges()->setStorage($storageVariables);
    }

    /**
     * Build diff response in indexed format
     *
     * @return array Indexed diff response
     */
    protected function buildDiffResponse(bool $reload = false): array
    {
        $diff = $reload ?
            UIDiffer::compare([], $this->newUI) :
            UIDiffer::compare($this->oldUI, $this->newUI);

        $result = [];
        foreach ($diff as $componentId => $changes) {
            $changes['_id'] = $componentId;

            // Always include 'type' from newUI so frontend knows how to handle the change
            if (isset($this->newUI[$componentId]['type'])) {
                $changes['type'] = $this->newUI[$componentId]['type'];
            }

            $result[$componentId] = $changes;
        }

        return $result;
    }

    // /**
    //  * Get the UI structure
    //  *
    //  * Returns the UI from cache or regenerates if not exists.
    //  * This is the standard public method to retrieve UI for all services.
    //  *
    //  * @param mixed ...$params Optional parameters that can be used by child classes
    //  * @return array UI structure in JSON format
    //  */
    // public function getUI(string $parent = 'main', ...$params): array
    // {
    //     $ui = $this->getStoredUI($parent, ...$params);
    //     return $ui;
    // }

    /**
     * Get stored UI state, regenerate if missing
     *
     * @param mixed ...$params Optional parameters passed to buildBaseUI
     * @return array UI structure in JSON format
     */
    protected function getStoredUI(string $parent = 'main', bool $debug = false, ...$params): array
    {
        // Check if UI exists in cache
        $cachedUI = UIStateManager::get(static::class);

        if ($cachedUI !== null) {
            return $cachedUI;
        }

        $current_class = static::class;
        $current_class_slug = strtolower(str_replace('\\', '_', $current_class));
        $container = UIBuilder::container($current_class_slug, $current_class)
            ->parent($parent)
            ->padding(30)
            ->layout(LayoutType::VERTICAL)
            ->justifyContent('center')
            ->alignItems('center');

        // Generate and cache new UI
        $this->buildBaseUI($container, ...$params);

        $ui = $container
            ->root(true)
            // ->parent($parent)   // TODO: Acá está el problema.
            ->toJson();

        UIStateManager::store(static::class, $ui);

        return $ui;
    }

    /**
     * Get UI container instance from cache, regenerate if missing
     *
     * @return UIContainer UI container instance
     */
    protected function getUIContainer(bool $debug = false): UIContainer
    {
        // Always get JSON from cache and reconstruct container
        // This ensures we get the latest state after events modify it
        $jsonUI = $this->getStoredUI(debug: $debug);
        // Log::info(json_encode($jsonUI));

        // Reconstruct container from JSON
        return $this->reconstructContainerFromJson($jsonUI);
    }

    /**
     * Reconstruct UI container from JSON array
     *
     * @param array $jsonUI JSON representation of UI
     * @return UIContainer Reconstructed container
     */
    private function reconstructContainerFromJson(array $jsonUI): UIContainer
    {
        $components = [];
        $rootContainer = null;

        // UIDebug::debug("Reconstructing UI Container from JSON", $jsonUI);

        // First pass: instantiate all components
        foreach ($jsonUI as $id => $component) {
            $type = $component['type'];
            $className = $this->mapTypeToClass($type);
            if (!$className) {
                throw new RuntimeException("Unknown component type '{$type}'.");
            }
            $components[$id] = $className::deserialize($id, $component);
        }

        // Second pass: set up parent-child relationships
        foreach ($components as $id => $component) {
            $parentId = $jsonUI[$id]['parent'] ?? null;

            if ($component->isContainer() && $component->isRoot()) {
                $rootContainer = $component;
            }

            if (!$parentId) {
                throw new RuntimeException("Component '{$id}' has no parent defined.");
            }

            if (!$parentId || !isset($components[$parentId])) {
                continue;
            }

            $components[$parentId]->connectChild($component);
        }

        // Third pass: post-connection initialization
        foreach ($components as $component) {
            $component->postConnect();
        }

        if (!$rootContainer) {
            throw new RuntimeException("No root container found in UI JSON.");
        }

        // UIDebug::debug("Reconstructed UI Container:\n", $rootContainer);

        return $rootContainer;
    }

    private function mapTypeToClass(string $type): ?string
    {
        return match ($type) {
            'label' => LabelBuilder::class,
            'button' => ButtonBuilder::class,
            'input' => InputBuilder::class,
            'select' => SelectBuilder::class,
            'checkbox' => CheckboxBuilder::class,
            'card' => CardBuilder::class,
            'table' => TableBuilder::class,
            'container' => UIContainer::class,
            'tablerow' => TableRowBuilder::class,
            'tablecell' => TableCellBuilder::class,
            'tableheadercell' => TableHeaderCellBuilder::class,
            'form' => FormBuilder::class,
            'tableheaderrow' => TableHeaderRowBuilder::class,
            'menudropdown' => MenuDropdownBuilder::class,
            'uploader' => UploaderBuilder::class,
            'calendar' => \App\Services\UI\Components\CalendarBuilder::class,
            'default' => null,
        };
    }

    /**
     * Store UI state in cache
     *
     * @param UIContainer $ui UI container to store
     * @return void
     */
    protected function storeUI(UIContainer $ui): void
    {
        UIStateManager::store(static::class, $ui->toJson());
    }

    /**
     * Clear stored UI state
     *
     * @return void
     */
    public function clearStoredUI(): void
    {
        UIStateManager::clear(static::class);
    }

    /**
     * Allow child classes to react when the service is reset.
     *
     * @return void
     */
    public function onResetService(): void
    {
    }

    /**
     * Get the service component ID
     * Returns the ID of the main container, which represents this service
     * Used for modal callbacks to route events back to this service
     *
     * @return int Service component ID
     */
    protected function getServiceComponentId(): int
    {
        $ui = $this->getStoredUI();

        // Find the first container (main container that represents the service)
        foreach ($ui as $id => $component) {
            if ($component['type'] === 'container') {
                return (int) $id;
            }
        }

        // Fallback: generate deterministic ID from service class name
        return UIIdGenerator::generateFromName(
            static::class,
            'service_root'
        );
    }

    /**
     * Uses reflection to scan private and protected properties whose names start with the "store_"
     * prefix and whose type hints are non-nullable primitive types (int, float, string, bool) or array.
     * It then builds an associative array with the following structure:
     *
     * [
     *   'storage' => [
     *      'usim' => 'encrypted_json_string',
     *   ]
     * ]
     *
     * @return array Associative array with the variables to be stored on the frontend
     */
    public function getStorageVariables(): array
    {
        $storage = [];
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (str_starts_with($propertyName, 'store_')) {
                $propertyType = $property->getType();
                if ($propertyType && !$propertyType->allowsNull()) {
                    $typeName = $propertyType->getName();
                    $isPrimitive = in_array($typeName, ['int', 'float', 'string', 'bool', 'array']);
                    if ($isPrimitive) {
                        $value = $property->getValue($this);
                        $storage[$propertyName] = $value;
                    }
                }
            }
        }

        return $storage;
    }

    /**
     * Generic handler for 'close_modal' action
     */
    public function onCloseModal(array $params): void
    {
        $this->closeModal();
    }

    /**
     * Sends 'close_modal' action to front.
     *
     * @return void
     */
    protected function closeModal(): void
    {
        $this->uiChanges()->add([
            'action' => 'close_modal',
        ]);
    }

    /**
     * Requests to front to renderize a toast type message.
     *
     * @param string $message
     * @param string $type
     * @param int $duration
     * @param string $openEffect
     * @param string $showEffect
     * @param string $closeEffect
     * @param string $position
     * @return void
     */
    protected function toast(
        string $message,
        string $type = 'info',
        int $duration = 5000,
        string $openEffect = 'fade',
        string $showEffect = 'bounce',
        string $closeEffect = 'fade',
        string $position = 'top-right'
    ): void {
        $this->uiChanges()->add([
            'toast' => [
                'message' => $message,
                'type' => $type,
                'duration' => $duration,
                'open_effect' => $openEffect,
                'show_effect' => $showEffect,
                'close_effect' => $closeEffect,
                'position' => $position,
            ],
        ]);
    }

    /**
     * Requests to front to perform a redirect to the given URL.
     *
     * If no URL is provided, it will use Laravel's intended redirect
     * (the previous URL or the default URL if none).
     *
     * @param string|null $url The URL to redirect to, or null to use intended redirect
     * @return void
     */
    protected function redirect(?string $url = null): void
    {
        // If no URL provided, use Laravel's intended redirect (previous URL or default)
        if ($url === null) {
            $url = redirect()->intended('/')->getTargetUrl();
        }

        $this->uiChanges()->add([
            'redirect' => $url,
        ]);
    }

    protected function updateModal(array $content): void
    {
        $this->uiChanges()->add([
            'update_modal' => $content,
        ]);
    }
}
