<?php

namespace Idei\Usim\Services\Support;

/**
 * Centralized ID generator for UI components
 * 
 * Ensures unique IDs across all UI elements (containers and components)
 * by maintaining a single auto-increment counter per context.
 */
class UIIdGenerator
{
    /** @var array<string, int> Auto-increment counter per context */
    private static array $autoIncPerContext = [];

    /** @var array<int, string> Mapping from offset to context class name */
    private static array $offsetToContext = [];

    /** @var bool Flag to ensure services are loaded only once */
    private static bool $servicesLoaded = false;

    /**
     * Generate a unique ID for a UI element
     * 
     * @param string $context The calling context (class name)
     * @return int Unique ID
     */
    public static function generate(string $context): int
    {
        if (!isset(self::$autoIncPerContext[$context])) {
            self::$autoIncPerContext[$context] = 0;
        }

        $localId = ++self::$autoIncPerContext[$context];
        $offset = self::getContextOffset($context);

        // Register offset → context mapping for reverse lookup
        self::$offsetToContext[$offset] = $context;

        return $offset + $localId;
    }

    /**
     * Generate a deterministic ID based on component name
     * 
     * This ensures the same component name always gets the same ID,
     * making IDs stable across requests for named components.
     * 
     * @param string $context The calling context (full class name with namespace)
     * @param string $name The component name
     * @return int Deterministic ID
     */
    public static function generateFromName(string $context, string $name): int
    {
        $offset = self::getContextOffset($context);
        
        // Generate deterministic local ID from component name
        // Use crc32 to get a hash, then limit to 9999 to avoid offset collision
        $hash = crc32($name);
        $localId = (abs($hash) % 9999) + 1; // +1 to avoid ID 0
        
        // Register offset → context mapping for reverse lookup
        self::$offsetToContext[$offset] = $context;
        
        return $offset + $localId;
    }

    /**
     * Get context information for debugging
     * 
     * @param string $context Context name
     * @return array Context information
     */
    public static function getContextInfo(string $context): array
    {
        return [
            'context' => $context,
            'offset' => self::getContextOffset($context),
            'current_count' => self::$autoIncPerContext[$context] ?? 0,
        ];
    }

    /**
     * Get context class name from component ID
     * 
     * Uses lazy loading to ensure all registered UI services are mapped.
     * Performance: ~0.001ms (in-memory array lookup)
     * 
     * @param int $id Component ID
     * @return string|null Context class name or null if not found
     */
    public static function getContextFromId(int $id): ?string
    {
        self::ensureServicesLoaded();

        $offset = (int)floor($id / 10000) * 10000;
        return self::$offsetToContext[$offset] ?? null;
    }

    /**
     * Lazy load registered UI services
     * 
     * Loads the service registry only once per PHP worker process.
     * This ensures deterministic offset → service mapping without
     * requiring database or cache lookups.
     * 
     * @return void
     */
    private static function ensureServicesLoaded(): void
    {
        if (self::$servicesLoaded) {
            return;
        }

        // Load all registered UI services from config
        $services = config('ui-services', []);
        
        foreach ($services as $serviceClass) {
            $offset = self::getContextOffset($serviceClass);
            self::$offsetToContext[$offset] = $serviceClass;
        }

        self::$servicesLoaded = true;
    }

    /**
     * Reset all counters (useful for testing)
     * 
     * @return void
     */
    public static function reset(): void
    {
        self::$autoIncPerContext = [];
    }

    /**
     * Convierte el nombre de clase en un número único usando hash CRC32
     * Genera offsets en múltiplos de 10000 para evitar colisiones
     * 
     * @param string $context Nombre del contexto (clase invocante)
     * @return int Offset único para el contexto
     */
    private static function getContextOffset(string $context): int
    {
        if ($context === 'default') {
            return 0;
        }

        // Generar un hash numérico único del nombre de la clase usando CRC32
        $hash = crc32($context);

        // Convertir a positivo si es negativo y escalar al rango deseado
        // Múltiplos de 10000, máximo 9999 contextos diferentes
        $offset = (abs($hash) % 9999) * 10000;

        return $offset;
    }
}
