# Tutorial: Enums en Laravel - Sistema Difexa

Los enums (enumeraciones) son tipos de datos que definen un conjunto fijo de valores posibles. En Laravel, los enums de PHP 8.1+ nos permiten crear código más robusto y mantenible.

## Enums del Sistema Difexa

### PostType - Tipos de Contenido

Define los diferentes tipos de contenido que puede tener un post:

```php
<?php

namespace App\Enums;

enum PostType: string
{
    case TEXT = 'text';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case IMAGE = 'image';
    case MULTIMEDIA = 'multimedia';

    /**
     * Obtiene todos los valores como array para migraciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Etiquetas legibles para humanos
     */
    public function label(): string
    {
        return match($this) {
            self::TEXT => 'Texto',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::IMAGE => 'Imagen',
            self::MULTIMEDIA => 'Multimedia',
        };
    }

}
```

### PostStatus - Estados del Post

Define los estados del ciclo de vida de un post:

```php
<?php

namespace App\Enums;

enum PostStatus : string
{
    case DRAFT = 'draft';
    case APPROVED_BY_MODERATOR = 'approved_by_moderator';
    case SCHEDULED = 'scheduled';
    case ARCHIVED = 'archived';

    /**
     * Obtiene todos los valores como array para migraciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Etiquetas legibles para humanos
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::APPROVED_BY_MODERATOR => 'Aprobado',
            self::SCHEDULED => 'Programado',
            self::ARCHIVED => 'Archivado',
        };
    }
}
```

### ChannelType - Tipos de Canal

Define los tipos de canales organizacionales:

```php
<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'department';
    case INSTITUTE = 'institute';
    case SECRETARY = 'secretary';
    case CENTER = 'center';

    /**
     * Obtiene todos los valores como array para migraciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Etiquetas legibles para humanos
     */
    public function label(): string
    {
        return match($this) {
            self::DEPARTMENT => 'Departamento',
            self::INSTITUTE => 'Instituto',
            self::SECRETARY => 'Secretaría',
            self::CENTER => 'Centro',
        };
    }

}
```

### MediaType - Tipos de Media

Define los tipos de plataformas de publicación:

```php
<?php

namespace App\Enums;

enum MediaType : string
{
    case PHYSICAL_SCREEN = 'physical_screen';
    case SOCIAL_MEDIA = 'social_media';
    case EDITORIAL_PLATFORM = 'editorial_platform';

    /**
     * Obtiene todos los valores como array para migraciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Etiquetas legibles para humanos
     */
    public function label(): string
    {
        return match($this) {
            self::PHYSICAL_SCREEN => 'Pantalla Física',
            self::SOCIAL_MEDIA => 'Redes Sociales',
            self::EDITORIAL_PLATFORM => 'Plataforma Editorial',
        };
    }

}
```

## Patrón de Implementación Estándar

### 1. Método values() para Migraciones

Todos los enums incluyen el método estático `values()` que devuelve un array con todos los valores posibles. Esto es esencial para usar en las migraciones de Laravel:

```php
// En migraciones
$table->enum('status', PostStatus::values());
$table->enum('type', PostType::values());
```

### 2. Método label() para Interfaz de Usuario

Proporciona etiquetas legibles para mostrar en la interfaz:

```php
// En Blade templates
{{ $post->status->label() }}
{{ $channel->type->label() }}
```



### 3. Conversión Automática en Modelos

Los modelos Eloquent pueden convertir automáticamente strings a enums:

```php
class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
        'type' => PostType::class,
    ];
}

// Uso automático
$post = Post::find(1);
echo $post->status->label(); // "Borrador", "Aprobado", etc.
```

## Comandos Artisan para Actualizar Enums

Si necesitas actualizar los enums existentes, usa estos comandos:

```bash
# Verificar enums actuales
php artisan tinker
PostType::cases()
PostStatus::cases()

# Verificar que los métodos funcionen
PostType::values()
PostStatus::DRAFT->label()
```

## Mejores Prácticas

1. **Usa el método values()** en migraciones en lugar de hardcodear arrays
2. **Implementa label()** para todas las interfaces de usuario  
3. **Usa match() expressions** para mayor legibilidad
4. **Incluye documentación** de cada caso del enum
5. **Mantén consistencia** en la estructura de todos los enums

---

*Este tutorial debe completarse antes de trabajar con migraciones que usen enums*
