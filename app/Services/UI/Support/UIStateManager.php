<?php
namespace App\Services\UI\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
     * Cookie name for client identification
     */
    public const CLIENT_ID_COOKIE = 'ui_client_id';

    /**
     * Cookie lifetime (1 year in minutes)
     */
    public const COOKIE_LIFETIME = 525600;

    /**
     * Get or create a unique client identifier
     *
     * This identifier persists across sessions and survives logout,
     * allowing UI preferences to be maintained per device/browser.
     *
     * @return string Client UUID
     */
    protected static function getOrCreateClientId(): string
    {
        $clientId = request()->cookie(self::CLIENT_ID_COOKIE);

        if ($clientId) {
            return $clientId;
        }

        // Generate new UUID for this client
        $clientId = (string) Str::uuid();

        // Queue functional cookie (no consent required - essential for UI preferences)
        cookie()->queue(
            name: self::CLIENT_ID_COOKIE,
            value: $clientId,
            minutes: self::COOKIE_LIFETIME,
            path: '/',
            domain: null,
            secure: request()->secure(), // HTTPS only in production
            httpOnly: true, // Not accessible from JavaScript
            sameSite: 'lax' // CSRF protection
        );

        return $clientId;
    }

    /**
     * Generate cache key for a service
     *
     * @param string $serviceClass Full service class name
     * @param string|null $userId Optional user ID (deprecated, not used)
     * @return string Cache key
     */
    public static function getCacheKey(?string $serviceClass = null, string $prefix = 'ui_state'): string
    {
        $serviceBaseName = $serviceClass ? class_basename($serviceClass) : '';
        $clientId = self::getOrCreateClientId();

        return "{$prefix}:{$serviceBaseName}:{$clientId}";
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
        $parents = session()->get('ui_parents', []);
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
        $ttl = env('UI_CACHE_TTL', self::DEFAULT_TTL);
        $encodedState = json_encode($uiState);

        // Store main UI state
        $cacheKey = self::getCacheKey($serviceClass);
        $result = Cache::put($cacheKey, $encodedState, $ttl);
        $logLevel = $result ? 'warning' : 'error';

        // UIDebug::$logLevel("Stored UI State of {$serviceClass}", [
        //     'result' => $result ? 'CACHED' : 'NOT CACHED',
        //     'cache_key' => $cacheKey,
        //     'ids' => implode(', ', array_keys($uiState)),
        //     'caller' => self::getCallerServiceInfo(),
        // ]);

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
        $content = Cache::get($cacheKey);
        $cache = ($content === null || $content === '') ? null : json_decode($content, true);

        $result = is_array($cache) ? $cache : null;
        $logLevel = $result !== null ? 'info' : 'error';

        // UIDebug::$logLevel("Retrieving UI State of {$serviceClass}", [
        //     'result' => $result !== null ? 'FOUND' : 'NOT FOUND',
        //     'cache_key' => $cacheKey,
        //     'service_class' => $serviceClass,
        //     'caller' => self::getCallerServiceInfo(),
        // ]);

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
        return Cache::forget($cacheKey);
    }

    public static function setAuthToken(string $token): bool
    {
        $cacheKey = self::getCacheKey(prefix: 'ui_auth_token');
        Cache::put($cacheKey, $token, self::DEFAULT_TTL);
        return true;
    }

    public static function getAuthToken(): ?string
    {
        $cacheKey = self::getCacheKey(prefix: 'ui_auth_token');
        $token = Cache::get($cacheKey);
        return $token;
    }

    public static function storeKeyValue(string $key, mixed $value): bool
    {
        $cacheKey = self::getCacheKey(prefix: "ui_key_{$key}");
        Cache::put($cacheKey, $value, self::DEFAULT_TTL);
        return true;
    }

    public static function getKeyValue(string $key): mixed
    {
        $cacheKey = self::getCacheKey(prefix: "ui_key_{$key}");
        return Cache::get($cacheKey);
    }

    public static function clearKeyValue(string $key): bool
    {
        $cacheKey = self::getCacheKey(prefix: "ui_key_{$key}");
        return Cache::forget($cacheKey);
    }

    private static function getCallerServiceInfo(): string
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($stack as $frame) {
            if (
                isset($frame['class']) &&
                str_starts_with($frame['class'], 'App\\Services\\UI\\') &&
                $frame['class'] !== self::class
            ) {
                $className = class_basename($frame['class']);
                $functionName = $frame['function'] ?? 'unknown';
                $lineNumber = $frame['line'] ?? null;
                return $className . '::' . $functionName . ($lineNumber ? " (line {$lineNumber})" : '');
            }
        }
        return 'unknown';
    }
}
