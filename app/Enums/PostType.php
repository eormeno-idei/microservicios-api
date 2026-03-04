<?php

namespace App\Enums;

enum PostType: string
{
    case TEXT = 'text';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case IMAGE = 'image';
    case MULTIMEDIA = 'multimedia';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Texto',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::IMAGE => 'Imagen',
            self::MULTIMEDIA => 'Multimedia',
        };
    }
}
