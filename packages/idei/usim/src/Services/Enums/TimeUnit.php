<?php

namespace Idei\Usim\Services\Enums;

/**
 * Time Unit Enum
 * 
 * Defines time units for timeout dialogs
 */
enum TimeUnit: string
{
    case SECONDS = 'seconds';
    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';

    /**
     * Get singular label for this time unit
     */
    public function getSingularLabel(): string
    {
        return match($this) {
            self::SECONDS => 'segundo',
            self::MINUTES => 'minuto',
            self::HOURS => 'hora',
            self::DAYS => 'día',
        };
    }

    /**
     * Get plural label for this time unit
     */
    public function getPluralLabel(): string
    {
        return match($this) {
            self::SECONDS => 'segundos',
            self::MINUTES => 'minutos',
            self::HOURS => 'horas',
            self::DAYS => 'días',
        };
    }

    /**
     * Get label based on quantity (singular/plural)
     */
    public function getLabel(int $quantity): string
    {
        return $quantity === 1 ? $this->getSingularLabel() : $this->getPluralLabel();
    }

    /**
     * Convert value to milliseconds for JavaScript timer
     */
    public function toMilliseconds(int $value): int
    {
        return match($this) {
            self::SECONDS => $value * 1000,
            self::MINUTES => $value * 60 * 1000,
            self::HOURS => $value * 60 * 60 * 1000,
            self::DAYS => $value * 24 * 60 * 60 * 1000,
        };
    }
}
