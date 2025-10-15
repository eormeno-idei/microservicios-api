<?php

namespace App\Enums;

enum MediaType: string
{
    case PHYSICAL_SCREEN = 'physical_screen';
    case SOCIAL_MEDIA = 'social_media';
    case EDITORIAL_PLATFORM = 'editorial_platform';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL_SCREEN => 'Pantalla Física',
            self::SOCIAL_MEDIA => 'Redes Sociales',
            self::EDITORIAL_PLATFORM => 'Plataforma Editorial',
        };
    }
}
