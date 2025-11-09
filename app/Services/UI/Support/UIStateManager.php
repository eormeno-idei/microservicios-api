<?php

namespace App\Services\UI\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * UI State Manager
 *
 * Centralized management of UI state caching.
 * Provides methods to store, retrieve, and update UI component state.
 *
 * Usage:
 * - Store entire UI: UIStateManager::store($serviceClass, $uiArray)
 * - Get entire UI: UIStateManager::get($serviceClass)
 * - Update component: UIStateManager::updateComponent($serviceClass, $componentId, $updates)
 * - Get component property: UIStateManager::getComponentProperty($serviceClass, $componentName, $property)
 */
class UIStateManager
{
    /**
     * Default cache TTL (30 minutes)
     */
    public const DEFAULT_TTL = 1800;

    /**
     * Generate cache key for a service
     *
     * @param string $serviceClass Full service class name
     * @param string|null $userId Optional user ID (defaults to current user)
     * @return string Cache key
     */
    public static function getCacheKey(string $serviceClass, ?string $userId = null): string
    {
        $serviceBaseName = class_basename($serviceClass);
        $userId = $userId ?? (Auth::check() ? Auth::id() : session()->getId());

        return "ui_state:{$serviceBaseName}:{$userId}";
    }

    /**
     * Store UI state in cache
     *
     * @param string $serviceClass Service class name
     * @param array $uiState UI state array
     * @param int $ttl Time to live in seconds
     * @return bool Success
     */
    public static function store(string $serviceClass, array $uiState): bool
    {
        $ttl = env('UI_CACHE_TTL', UIStateManager::DEFAULT_TTL);
        $cacheKey = self::getCacheKey($serviceClass);
        return Cache::put($cacheKey, $uiState, $ttl);
    }

    /**
     * Get UI state from cache
     *
     * @param string $serviceClass Service class name
     * @return array|null UI state array or null if not found
     */
    public static function get(string $serviceClass): ?array
    {
        $cacheKey = self::getCacheKey($serviceClass);
        $cache = Cache::get($cacheKey);

        return is_array($cache) ? $cache : null;
    }

    /**
     * Clear UI state from cache
     *
     * @param string $serviceClass Service class name
     * @return bool Success
     */
    public static function clear(string $serviceClass): bool
    {
        $cacheKey = self::getCacheKey($serviceClass);
        return Cache::forget($cacheKey);
    }

    /**
     * Update a specific component in the cached UI
     *
     * @param string $serviceClass Service class name
     * @param int|string $componentId Component ID
     * @param array $updates Properties to update
     * @param int $ttl Time to live in seconds
     * @return bool Success (false if cache not found or component not found)
     */
    public static function updateComponent(
        string $serviceClass,
        int|string $componentId,
        array $updates
    ): bool {
        $uiState = self::get($serviceClass);

        if (!$uiState || !isset($uiState[$componentId])) {
            return false;
        }

        // Merge updates into component
        $uiState[$componentId] = array_merge($uiState[$componentId], $updates);

        return self::store($serviceClass, $uiState);
    }

    /**
     * Find component by name
     *
     * @param string $serviceClass Service class name
     * @param string $componentName Component name
     * @return array|null Component data with '_id' key, or null if not found
     */
    public static function findComponentByName(string $serviceClass, string $componentName): ?array
    {
        $uiState = self::get($serviceClass);

        if (!$uiState) {
            return null;
        }

        foreach ($uiState as $componentId => $component) {
            if (isset($component['name']) && $component['name'] === $componentName) {
                $component['_id'] = $componentId;
                return $component;
            }
        }

        return null;
    }

    /**
     * Find component by type and name
     *
     * @param string $serviceClass Service class name
     * @param string $componentType Component type (e.g., 'table', 'button')
     * @param string $componentName Component name
     * @return array|null Component data with '_id' key, or null if not found
     */
    public static function findComponent(
        string $serviceClass,
        string $componentType,
        string $componentName
    ): ?array {
        $uiState = self::get($serviceClass);

        if (!$uiState) {
            return null;
        }

        foreach ($uiState as $componentId => $component) {
            if (isset($component['type']) && $component['type'] === $componentType &&
                isset($component['name']) && $component['name'] === $componentName) {
                $component['_id'] = $componentId;
                return $component;
            }
        }

        return null;
    }

    /**
     * Get a specific property from a component
     *
     * @param string $serviceClass Service class name
     * @param string $componentType Component type
     * @param string $componentName Component name
     * @param string $property Property name
     * @param mixed $default Default value if not found
     * @return mixed Property value or default
     */
    public static function getComponentProperty(
        string $serviceClass,
        string $componentType,
        string $componentName,
        string $property,
        mixed $default = null
    ): mixed {
        $component = self::findComponent($serviceClass, $componentType, $componentName);

        return $component[$property] ?? $default;
    }

    /**
     * Update a specific property of a component
     *
     * Convenience method for updating a single property.
     *
     * @param string $serviceClass Service class name
     * @param string $componentType Component type
     * @param string $componentName Component name
     * @param string $property Property name
     * @param mixed $value New property value
     * @param int $ttl Time to live in seconds
     * @return bool Success
     */
    public static function setComponentProperty(
        string $serviceClass,
        string $componentType,
        string $componentName,
        string $property,
        mixed $value
    ): bool {
        $component = self::findComponent($serviceClass, $componentType, $componentName);

        if (!$component) {
            return false;
        }

        return self::updateComponent($serviceClass, $component['_id'], [
            $property => $value
        ]);
    }

    /**
     * Check if UI state exists in cache
     *
     * @param string $serviceClass Service class name
     * @return bool True if cache exists
     */
    public static function exists(string $serviceClass): bool
    {
        return self::get($serviceClass) !== null;
    }

    /**
     * Get all components of a specific type
     *
     * @param string $serviceClass Service class name
     * @param string $componentType Component type
     * @return array Array of components with '_id' keys
     */
    public static function getComponentsByType(string $serviceClass, string $componentType): array
    {
        $uiState = self::get($serviceClass);

        if (!$uiState) {
            return [];
        }

        $components = [];
        foreach ($uiState as $componentId => $component) {
            if (isset($component['type']) && $component['type'] === $componentType) {
                $component['_id'] = $componentId;
                $components[] = $component;
            }
        }

        return $components;
    }
}
