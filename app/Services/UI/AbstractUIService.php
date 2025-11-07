<?php

namespace App\Services\UI;

use ReflectionClass;
use RuntimeException;
use ReflectionProperty;
use App\Services\UI\Support\UIDiffer;
use App\Services\UI\Support\UIIdGenerator;
use App\Services\UI\Components\CardBuilder;
use App\Services\UI\Components\FormBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Support\UIStateManager;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\SelectBuilder;
use App\Services\UI\Components\CheckboxBuilder;
use App\Services\UI\Components\TableRowBuilder;
use App\Services\UI\Components\TableCellBuilder;
use App\Services\UI\Components\MenuDropdownBuilder;
use App\Services\UI\Components\TableHeaderRowBuilder;
use App\Services\UI\Components\TableHeaderCellBuilder;

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
     * Whether the UI has been modified during event handling
     */
    protected bool $modified = false;

    /**
     * Build base UI structure
     *
     * Override this method in your service to define the base UI.
     * This will be called automatically if the cache expires.
     *
     * @param mixed ...$params Optional parameters for UI construction
     * @return UIContainer Base UI structure
     */
    abstract protected function buildBaseUI(...$params): UIContainer;

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
    public function initializeEventContext(array $incomingStorage = []): void
    {
        $this->container = $this->getUIContainer();
        $this->oldUI = $this->container->toJson();
        $this->modified = false;

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
                continue;
            }

            $value = $incomingStorage[$propertyName];

            // Set the value
            $property->setValue($this, $value);
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
     * @return array Indexed diff response
     */
    public function finalizeEventContext(): array
    {
        // Get current UI state
        $this->newUI = $this->container->toJson();

        // Auto-detect if UI was modified by comparing states
        if ($this->oldUI === $this->newUI) {
            // No changes detected, return empty response
            return [];
        }

        // Store updated UI
        $this->storeUI($this->container);

        $diff = $this->buildDiffResponse();

        return $diff;
    }

    /**
     * Build diff response in indexed format
     *
     * @return array Indexed diff response
     */
    protected function buildDiffResponse(): array
    {
        if (!$this->oldUI || !$this->newUI) {
            return [];
        }

        $diff = UIDiffer::compare($this->oldUI, $this->newUI);

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

    /**
     * Get the UI structure
     *
     * Returns the UI from cache or regenerates if not exists.
     * This is the standard public method to retrieve UI for all services.
     *
     * @param mixed ...$params Optional parameters that can be used by child classes
     * @return array UI structure in JSON format
     */
    public function getUI(...$params): array
    {
        return $this->getStoredUI(...$params);
    }

    /**
     * Get stored UI state, regenerate if missing
     *
     * @param mixed ...$params Optional parameters passed to buildBaseUI
     * @return array UI structure in JSON format
     */
    protected function getStoredUI(...$params): array
    {
        // Check if UI exists in cache
        $cachedUI = UIStateManager::get(static::class);

        if ($cachedUI !== null) {
            return $cachedUI;
        }

        // Generate and cache new UI
        $ui = $this->buildBaseUI(...$params)->toJson();
        // $formatted = json_encode(
        //     $ui,
        //     JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        // );
        // Log::debug("Generated new UI for " . static::class . ":\n" . $formatted);
        $ttl = env('UI_CACHE_TTL', UIStateManager::DEFAULT_TTL);
        UIStateManager::store(static::class, $ui, $ttl);

        return $ui;
    }

    /**
     * Get UI container instance from cache, regenerate if missing
     *
     * @return UIContainer UI container instance
     */
    protected function getUIContainer(): UIContainer
    {
        // Always get JSON from cache and reconstruct container
        // This ensures we get the latest state after events modify it
        $jsonUI = $this->getStoredUI();
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

        // $formatted = json_encode(
        //     $jsonUI,
        //     JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        // );
        // Log::debug("Reconstructing UI Container from JSON:\n" . $formatted);

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
            if ($parentId === 'main' || $parentId === 'menu') {
                // TODO: Esto está mal. Hay que buscar otra forma de identificar el root container
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

        // $formatted = json_encode(
        //     $rootContainer->toJson(),
        //     JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        // );
        // Log::debug("Reconstructed UI Container:\n" . $formatted);

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
            'menu_dropdown' => MenuDropdownBuilder::class,
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
    public function onResetService(): void {}

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
                return (int)$id;
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

        // $storage = encrypt(json_encode($storage));
        // return ['storage' => ['usim' => $storage]];
    }
}
