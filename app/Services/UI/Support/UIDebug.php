<?php

namespace App\Services\UI\Support;

use Illuminate\Support\Facades\Log;

/**
 * UI Debugging Utility
 *
 * Provides methods for logging and debugging UI state and events.
 */
class UIDebug
{
    public const LEVEL_INFO = 'info';
    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_WARNING = 'warning';

    public static function info(string $message, array $context = []): void
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log a debug message
     * @param string $message Debug message
     * @param array $context Additional context data
     * @return void
     */
    private static function log(string $level,string $message, array $context = []): void
    {
        $formatted = json_encode(
            $context,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        Log::$level("$message:\n" . $formatted);
    }
}