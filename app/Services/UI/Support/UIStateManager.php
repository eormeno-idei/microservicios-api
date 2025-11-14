<?php
namespace App\Services\UI\Support;

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
    public static function getCacheKey(?string $serviceClass = null, ?string $userId = null, string $prefix = 'ui_state'): string
    {
        $serviceBaseName = $serviceClass ? class_basename($serviceClass) : '';
        // $userId = $userId ?? (Auth::check() ? Auth::id() : session()->getId());

        // return "{$prefix}:{$serviceBaseName}:{$userId}";
        return "{$prefix}:{$serviceBaseName}";
    }

    /**
     * Store root component ID and its parent container in session
     *
     * @param string $parent Parent container name (e.g., 'main', 'modal')
     * @param string $rootComponentId Root component ID
     * @return void
     */
    private static function storeRootComponentId(string $parent, string $rootComponentId): void
    {
        $parents          = session()->get('ui_parents', []);
        $parents[$parent] = $rootComponentId;
        session()->put('ui_parents', $parents);
    }

    /**
     * Get root components from session
     *
     * @return array Root components array
     */
    public static function getRootComponents(): array
    {
        return session()->get('ui_parents', []);
    }

    /**
     * Store UI state in cache
     *
     * @param string $serviceClass Service class name
     * @param array $uiState UI state array (indexed by component ID)
     * @return bool Success
     */
    public static function store(string $serviceClass, array $uiState): bool
    {
        if (empty($uiState)) {
            return false;
        }

        // Get TTL from environment or use default
        $ttl          = env('UI_CACHE_TTL', self::DEFAULT_TTL);
        $encodedState = json_encode($uiState);

        // Store main UI state
        $cacheKey = self::getCacheKey($serviceClass);
        $result   = Cache::put($cacheKey, $encodedState, $ttl);
        $logLevel = $result ? 'warning' : 'error';

        UIDebug::$logLevel("Stored UI State of {$serviceClass}", [
            'result'    => $result ? 'CACHED' : 'NOT CACHED',
            'cache_key' => $cacheKey,
            'ids'       => implode(', ', array_keys($uiState)),
            'caller'    => self::getCallerServiceInfo(),
        ]);

        // Store root component ID and its parent container
        $firstKey = array_key_first($uiState);
        if (isset($uiState[$firstKey]['parent'])) {
            self::storeRootComponentId(
                $uiState[$firstKey]['parent'],
                (string) $firstKey
            );
        }

        return $result;
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
        $cache    = json_decode(Cache::get($cacheKey), true);

        $result   = is_array($cache) ? $cache : null;
        $logLevel = $result !== null ? 'info' : 'error';

        UIDebug::$logLevel("Retrieving UI State of {$serviceClass}", [
            'result'        => $result !== null ? 'FOUND' : 'NOT FOUND',
            'cache_key'     => $cacheKey,
            'service_class' => $serviceClass,
            'caller'        => self::getCallerServiceInfo(),
        ]);

        return $result;
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
        Cache::clear(); // TODO: Remove this line after testing
        return Cache::forget($cacheKey);
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

    private static function getCallerServiceInfo(): string
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($stack as $frame) {
            if (isset($frame['class']) &&
                str_starts_with($frame['class'], 'App\\Services\\UI\\') &&
                $frame['class'] !== self::class) {
                $className    = class_basename($frame['class']);
                $functionName = $frame['function'] ?? 'unknown';
                $lineNumber   = $frame['line'] ?? null;
                return $className . '::' . $functionName . ($lineNumber ? " (line {$lineNumber})" : '');
            }
        }
        return 'unknown';
    }
}
