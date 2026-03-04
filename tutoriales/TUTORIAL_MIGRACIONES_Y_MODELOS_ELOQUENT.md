# Tutorial: Migraciones y Modelos Eloquent - Sistema Difexa

En este tutorial implementaremos las migraciones y modelos Eloquent del sistema **Difexa** con relaciones Many-to-Many entre posts, canales y medios.

## Prerrequisito Importante

**Antes de continuar, debes completar:** [Tutorial: Enums en Laravel - Sistema Difexa](./TUTORIAL_ENUMS_DIFEXA.md)

Este tutorial asume que tienes los enums (PostType, PostStatus, ChannelType, MediaType) configurados con el método `values()` estándar.

## Tabla de Contenidos

1. [Migraciones: Paso a Paso](#migraciones-paso-a-paso)
2. [Modelos Eloquent](#modelos-eloquent)
3. [Relaciones Avanzadas](#relaciones-avanzadas)
4. [Mejores Prácticas](#mejores-prácticas)

---

## Migraciones: Paso a Paso

### Conceptos Clave de Migraciones

**Columnas Importantes:**
- `id()` - Llave primaria autoincremental
- `foreignId()` - Llave foránea hacia otra tabla
- `timestamps()` - Campos `created_at` y `updated_at`
- `softDeletes()` - Eliminación lógica (opcional)

**Índices para Performance:**
- `->index()` - Índice simple para búsquedas rápidas
- `->unique()` - Garantiza valores únicos
- `->foreign()` - Relación con otra tabla

### Posts (Contenido Principal)

```bash
# Generar la migración
php artisan make:migration create_posts_table
```

**Archivo: `database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PostType;
use App\Enums\PostStatus;

return new class extends Migration
{
    /**
     * Ejecuta la migración - Crea la tabla
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            // Llave primaria
            $table->id();
            
            // Relación con usuarios (quien crea el post)
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade'); // Si se elimina el usuario, se eliminan sus posts
            
            // Contenido del post
            $table->string('name', 255);               // Nombre del post
            $table->text('content');                   // Contenido principal
            
            // Enums: tipo y estado
            $table->enum('type', PostType::values());
            $table->enum('status', PostStatus::values());
            
            // Comentarios del moderador
            $table->string('moderator_comments', 100)->nullable();
            
            // Fechas especiales
            $table->timestamp('scheduled_at')->nullable();  // Cuándo programar
            $table->timestamp('published_at')->nullable();  // Cuándo se publicó realmente
            $table->timestamp('deadline')->nullable();      // Fecha límite
            $table->timestamp('timeout')->nullable();       // Tiempo límite
            
            // Timestamps automáticos (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración - Elimina la tabla
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Channels (Canales de Distribución)

```bash
php artisan make:migration create_channels_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ChannelType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            
            // Información del canal
            $table->string('name');                        // Nombre del canal
            $table->text('description')->nullable();       // Descripción opcional
            $table->enum('type', ChannelType::values()); // Tipo de canal
            $table->boolean('is_active')->default(true);   // Canal activo/inactivo
            
            $table->timestamps();
            
            // Índice compuesto para filtrar por tipo y estado activo
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
```

### Media (Plataformas de Publicación)

```bash
php artisan make:migration create_media_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MediaType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');                        // Nombre del medio
            $table->enum('type', MediaType::values()); // Tipo de medio
            $table->json('configuration')->nullable();     // Configuración específica (JSON)
            $table->text('semantic_context')->nullable();  // Contexto semántico para IA
            $table->string('url_webhook')->nullable();     // URL para notificaciones
            $table->boolean('is_active')->default(true);   // Medio activo/inactivo
            
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
```

### Attachments (Archivos Adjuntos)

```bash
php artisan make:migration create_attachments_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            
            // Información del archivo
            $table->string('name');                        // Nombre para mostrar
            $table->string('file_name');                   // Nombre real del archivo
            $table->string('file_path');                   // Ruta donde se guardó
            $table->string('mime_type');                   // Tipo MIME (image/jpeg, etc.)
            $table->unsignedBigInteger('size');            // Tamaño en bytes
            $table->string('disk')->default('public');     // Disco de almacenamiento
            $table->text('description')->nullable();       // Descripción opcional
            
            // Estadísticas
            $table->integer('download_count')->default(0); // Contador de descargas
            
            // Relación con el usuario que subió el archivo
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
```

### Migraciones de Tablas Pivot (Relaciones Many-to-Many)

Las **tablas pivot** conectan dos tablas en relaciones de muchos a muchos.

#### Pivot 1: user_channels

```bash
php artisan make:migration create_user_channels_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channels', function (Blueprint $table) {
            $table->id();
            
            // Las dos llaves foráneas que conectamos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            
            // Campos adicionales del pivot (información extra de la relación)
            $table->boolean('is_approved')->default(false);    // ¿Está aprobado el usuario?
            $table->timestamp('approved_at')->nullable();      // ¿Cuándo fue aprobado?
            
            $table->timestamps();
            
            // Un usuario solo puede estar una vez en cada canal
            $table->unique(['user_id', 'channel_id']);
            
            // Índice para buscar usuarios aprobados de un canal
            $table->index(['channel_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channels');
    }
};
```

#### Pivot 2: post_channels

```bash
php artisan make:migration create_post_channels_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_channels', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            // Un post solo puede estar una vez en cada canal
            $table->unique(['post_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_channels');
    }
};
```

#### Pivot 3: post_medias

```bash
php artisan make:migration create_post_medias_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_medias', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['post_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_medias');
    }
};
```

#### Pivot 4: channel_medias

```bash
php artisan make:migration create_channel_medias_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_medias', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['channel_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_medias');
    }
};
```

### Ejecutar las Migraciones

```bash
# Ver el estado de las migraciones
php artisan migrate:status

# Ejecutar todas las migraciones pendientes
php artisan migrate

# Si algo sale mal, puedes revertir la última migración
php artisan migrate:rollback

# O revertir todo y volver a ejecutar (¡CUIDADO! Esto borra datos)
php artisan migrate:refresh
```

### Ventajas del Enfoque con Enums Estándar

Al usar `EnumName::values()` en lugar de `array_column()` obtenemos:

- **Código más limpio y legible**
- **Mejor mantenimiento** - Si cambia un enum, las migraciones se actualizan automáticamente
- **Consistencia** - Todos los enums siguen el mismo patrón
- **Menos propenso a errores** - No hay que recordar la sintaxis de `array_column()`
- **Mejor integración** - Los métodos como `label()` también están disponibles

---

## Modelos Eloquent

### Modelo 1: Post

```bash
php artisan make:model Post
```

**Archivo: `app/Models/Post.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\PostType;
use App\Enums\PostStatus;

class Post extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'user_id',
        'name',
        'content',
        'type',
        'status',
        'moderator_comments',
        'scheduled_at',
        'published_at',
        'deadline',
        'timeout',
    ];

    /**
     * Conversión automática de tipos de datos
     */
    protected $casts = [
        'type' => PostType::class,
        'status' => PostStatus::class,
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'deadline' => 'datetime',
        'timeout' => 'datetime',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Un post pertenece a un usuario (relación 1:N inversa)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un post puede estar en muchos canales (relación N:M)
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'post_channels');
    }

    /**
     * Un post puede usar muchos medios (relación N:M)
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_medias');
    }

    /**
     * Un post tiene muchos archivos adjuntos (relación 1:N)
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
```

### Modelo 2: Channel

```bash
php artisan make:model Channel
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ChannelType;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'semantic_context',
        'type',
        'is_active',
    ];

    protected $casts = [
        'type' => ChannelType::class,
        'is_active' => 'boolean',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Un canal puede tener muchos usuarios (relación N:M)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_channels')
                    ->withPivot(['is_approved', 'approved_at', 'approved_by']);
    }

    /**
     * Solo usuarios aprobados del canal
     */
    public function approvedUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_approved', true);
    }

    /**
     * Un canal puede tener muchos posts (relación N:M)
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_channels')
    }

    /**
     * Solo posts publicados del canal
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->where('status', PostStatus::PUBLISHED);
    }

    /**
     * Un canal puede usar muchos medios (relación N:M)
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'channel_medias')
    }
}
```

### Modelo 3: Attachment

```bash
php artisan make:model Attachment
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'name',
        'mime_type',
        'size',
        'path',
        'url',
        'protected',
        'metadata',
    ];

    protected $casts = [
        'protected' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Un attachment pertenece a un post (relación 1:N inversa)
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Modelo 4: Media

```bash
php artisan make:model Media
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\MediaType;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'configuration',
        'semantic_context',
        'is_active',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'configuration' => 'array',        // JSON se convierte a array automáticamente
        'is_active' => 'boolean',
    ];

    // ================================
    // RELACIONES
    // ================================

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_medias')
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_medias')
    }

    // ================================
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
**1:N (Uno a Muchos)**
- Un usuario tiene muchos posts
- Un post pertenece a un usuario

**N:M (Muchos a Muchos)**
- Un post puede estar en muchos canales
- Un canal puede tener muchos posts
- Se necesita una tabla pivot

### Ejemplos Prácticos de Uso

#### 1. Crear y Relacionar Datos

```php
// Crear un usuario
$user = User::create([
    'name' => 'Juan Pérez',
    'email' => 'juan@universidad.edu',
    'password' => bcrypt('secreto123'),
]);

// Crear un post para ese usuario
$post = $user->posts()->create([
    'title' => 'Mi Primer Artículo',
    'content' => 'Contenido del artículo...',
    'type' => PostType::ARTICLE,
    'status' => PostStatus::DRAFT,
]);

// Crear canales
$channelDepartamento = Channel::create([
    'name' => 'Departamento de Sistemas',
    'type' => ChannelType::DEPARTMENT,
    'is_active' => true,
]);

$channelInstituto = Channel::create([
    'name' => 'Instituto de Investigación',
    'type' => ChannelType::INSTITUTE,
    'is_active' => true,
]);

// Asociar el usuario a canales (solicitud pendiente)
$user->requestChannelAccess($channelDepartamento);
$user->requestChannelAccess($channelInstituto);

// Aprobar usuario en departamento
$channelDepartamento->approveUser($user);

// Asociar el post a canales
$post->channels()->attach([$channelDepartamento->id, $channelInstituto->id]);
```

#### 2. Consultas Avanzadas

```php
// Obtener posts publicados de un usuario específico
$postsPublicados = User::find(1)
    ->posts()
    ->published()
    ->orderBy('published_at', 'desc')
    ->get();

// Obtener usuarios aprobados de un canal
$usuariosAprobados = Channel::find(1)
    ->approvedUsers()
    ->get();

// Posts por tipo con sus canales
$videosConCanales = Post::with('channels')
    ->byType(PostType::VIDEO)
    ->published()
    ->get();

// Canales activos de tipo departamento con sus posts
$departamentosConPosts = Channel::with(['posts' => function ($query) {
        $query->published()->orderBy('published_at', 'desc');
    }])
    ->active()
    ->byType(ChannelType::DEPARTMENT)
    ->get();

// Posts listos para publicar (programados)
$postsListos = Post::readyToPublish()->get();

foreach ($postsListos as $post) {
    $post->publish();
    echo "Post '{$post->title}' publicado!\n";
}
```

#### 3. Trabajar con Tablas Pivot

```php
// Obtener información del pivot
$user = User::with('channels')->find(1);

foreach ($user->channels as $channel) {
    echo "Canal: {$channel->name}\n";
    echo "Aprobado: " . ($channel->pivot->is_approved ? 'Sí' : 'No') . "\n";
    if ($channel->pivot->approved_at) {
        echo "Fecha aprobación: {$channel->pivot->approved_at}\n";
    }
    echo "---\n";
}

// Actualizar datos del pivot
$user->channels()->updateExistingPivot($channelId, [
    'is_approved' => true,
    'approved_at' => now(),
]);

// Desasociar (eliminar relación)
$post->channels()->detach($channelId);

// Sincronizar (reemplazar todas las relaciones)
$post->channels()->sync([1, 2, 3]); // Solo estos IDs quedarán asociados
```

---

## Mejores Prácticas

### 1. Nomenclatura Clara

```php
// BIEN: Nombres descriptivos
class PostChannel extends Model // Tabla pivot
{
    protected $table = 'post_channels';
}

// MAL: Nombres confusos
class PostCh extends Model
{
    // No está claro qué representa
}
```

### 2. Usar Enums para Valores Fijos

```php
// BIEN: Enum con métodos auxiliares
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    

```php
// BIEN: Índices en campos consultados frecuentemente
$table->index(['status', 'published_at']); // Para buscar por estado y fecha
$table->index(['user_id', 'status']);      // Para posts de usuario por estado

// BIEN: Índices únicos en tablas pivot
$table->unique(['post_id', 'channel_id']); // Un post solo una vez por canal
```

### 4. Validación en el Modelo

```php
// BIEN: Métodos de validación en el modelo
class Post extends Model
{
        }
        
        return $this->update([
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
```

### 5. Usar Scopes para Consultas Comunes

```php
// BIEN: Scopes reutilizables
class Post extends Model
{

// Uso limpio
$postsRecientes = Post::publishedInLast(30)->get();
```

### 6. Eager Loading para Evitar N+1

```php
// BIEN: Cargar relaciones de una vez
$posts = Post::with(['user', 'channels', 'medias'])->published()->get();

foreach ($posts as $post) {
    echo $post->user->name; // No ejecuta consulta adicional
}

// MAL: Problema N+1
$posts = Post::published()->get();

foreach ($posts as $post) {
    echo $post->user->name; // Ejecuta una consulta por cada post
}
```

---

## Resumen y Comandos de Referencia

### Comandos Artisan Importantes

```bash
# Crear migración
php artisan make:migration create_posts_table

# Crear modelo
php artisan make:model Post

# Crear modelo con migración
php artisan make:model Post -m

# Ver estado de migraciones
php artisan migrate:status

# Ejecutar migraciones
php artisan migrate

# Revertir última migración
php artisan migrate:rollback

# Revertir y volver a ejecutar (¡CUIDADO!)
php artisan migrate:refresh

# Crear seeder
php artisan make:seeder PostSeeder

# Ejecutar seeders
php artisan db:seed

# Crear factory
php artisan make:factory PostFactory
```

### Relaciones del Sistema

```
User (usuarios)
├── 1:N → Post (posts)
├── 1:N → Attachment (attachments)  
└── N:M → Channel (channels) via user_channels

Post (posts)
├── N:1 → User (user)
├── N:M → Channel (channels) via post_channels
└── N:M → Media (medias) via post_medias

Channel (channels)
├── N:M → User (users) via user_channels
├── N:M → Post (posts) via post_channels
└── N:M → Media (medias) via channel_medias

Media (medias)
├── N:M → Post (posts) via post_medias
└── N:M → Channel (channels) via channel_medias

Attachment (attachments)
└── N:1 → User (user)
```
