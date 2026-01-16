<?php

namespace Idei\Usim\Services\Support;

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

    public static function log(string $message, array | string $context = []): void
    {
        self::_log(self::LEVEL_DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::_log(self::LEVEL_INFO, $message, $context);
    }

    public static function debug(string $message, array | string $context = []): void
    {
        self::_log(self::LEVEL_DEBUG, $message, $context);
    }

    public static function error(string $message, array | string $context = []): void
    {
        self::_log(self::LEVEL_ERROR, $message, $context);
    }

    public static function warning(string $message, array | string $context = []): void
    {
        self::_log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log a debug message
     * @param string $message Debug message
     * @param array $context Additional context data
     * @return void
     */
    private static function _log(string $level,string $message, array | string $context = []): void
    {
        if (\is_string($context)) {
            Log::$level("$message: $context");
            return;
        }

        $formatted = json_encode(
            $context,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        Log::$level("$message:\n" . $formatted);
    }
}
