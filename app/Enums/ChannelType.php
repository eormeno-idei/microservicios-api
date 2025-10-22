<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'departamento';
    case INSTITUTE   = 'instituto';
    case SECRETARY = 'secretaría';
    case CENTER = 'centro';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'Departamento',
            self::INSTITUTE => 'Instituto',
            self::SECRETARY => 'Secretaría',
            self::CENTER => 'Centro',
        };
    }
}
