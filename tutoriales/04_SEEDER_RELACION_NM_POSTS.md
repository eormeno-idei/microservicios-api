# Tutorial 04: Seeder con Relaciones Muchos a Muchos (N:M)

## Objetivo

Aprender a crear un seeder complejo que maneja múltiples dependencias y establece relaciones muchos a muchos (N:M) entre modelos.

## Contexto Teórico

### Relación Muchos a Muchos (N:M)

Una relación N:M ocurre cuando múltiples registros de una tabla pueden estar asociados con múltiples registros de otra tabla. Requiere una tabla intermedia (pivote).

**Ejemplo en este sistema:**

```
posts ←→ post_channels ←→ channels
posts ←→ post_medias ←→ medias
```

**Estructura de tablas:**

```
posts               post_channels        channels
  id                  post_id (FK)         id
  name                channel_id (FK)      name
  ...                                      ...

posts               post_medias          medias
  id                  post_id (FK)         id
  name                media_id (FK)        name
  ...                                      ...
```

### El método sync() en Laravel

Eloquent proporciona métodos para gestionar relaciones N:M:

```php
// Reemplaza todas las relaciones
$post->channels()->sync([1, 2, 3]);

// Agrega sin eliminar existentes
$post->channels()->syncWithoutDetaching([1, 2, 3]);

// Agrega (puede crear duplicados sin constraints)
$post->channels()->attach([1, 2, 3]);

// Elimina relaciones específicas
$post->channels()->detach([1, 2]);
```

**En este seeder usamos sync()** porque queremos establecer exactamente las relaciones especificadas.

### La propiedad wasRecentlyCreated

Cuando se usa `firstOrCreate()`, Laravel establece la propiedad `wasRecentlyCreated`:

```php
$post = Post::firstOrCreate(['name' => 'Post'], $data);

if ($post->wasRecentlyCreated) {
    // Este código solo se ejecuta si el post fue creado ahora
    // No se ejecuta si el post ya existía
}
```

Esto previene duplicar relaciones cuando el seeder se ejecuta múltiples veces.

## Análisis del PostSeeder

Este es el seeder más complejo del sistema porque:

1. Depende de usuarios, canales y medios
2. Crea posts con múltiples estados y tipos
3. Establece relaciones N:M con canales y medios
4. Usa fechas dinámicas con Carbon
5. Valida múltiples precondiciones

### Paso 1: Estructura y validación de dependencias

```php
<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Channel;
use App\Models\Media;
use App\Enums\PostType;
use App\Enums\PostStatus;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Validación de dependencias
    }
}
```

### Paso 2: Obtener registros necesarios

```php
$adminUser = User::role('admin')->first();
$regularUsers = User::role('user')->limit(5)->get();

if (!$adminUser || $regularUsers->isEmpty()) {
    $this->command->warn('No users found. Please run UserSeeder first.');
    return;
}

$channels = Channel::all();
$medias = Media::where('is_active', true)->get();

if ($channels->isEmpty()) {
    $this->command->warn('No channels found. Please run ChannelSeeder first.');
    return;
}
```

**Explicación de las validaciones:**

1. Se obtiene un usuario admin (solo uno)
2. Se obtienen 5 usuarios regulares (limitados para variedad)
3. Se verifica que existan usuarios necesarios
4. Se obtienen todos los canales
5. Se obtienen solo medios activos
6. Se verifica que existan canales

### Paso 3: Definir array de posts

Los posts se organizan por tipo y contienen datos realistas:

```php
$posts = [
    [
        'user_id' => $adminUser->id,
        'name' => 'Convocatoria: Conferencia Internacional 2025',
        'content' => 'Nos complace invitarlos a la Conferencia Internacional...',
        'type' => PostType::TEXT->value,
        'status' => PostStatus::APPROVED_BY_MODERATOR->value,
        'moderator_comments' => 'Aprobado para publicación inmediata',
        'scheduled_at' => Carbon::now()->addDays(2),
        'published_at' => null,
        'deadline' => Carbon::now()->addMonths(1),
        'timeout' => Carbon::now()->addMonths(2),
    ],
    // ... más posts
];
```

### Paso 4: Asignación dinámica de usuarios

Algunos posts se asignan al admin, otros a usuarios aleatorios:

```php
'user_id' => $adminUser->id,  // Asignado al admin

'user_id' => $regularUsers->random()->id,  // Usuario aleatorio
```

El método `random()` de las colecciones de Laravel selecciona un elemento aleatorio.

### Paso 5: Uso de fechas con Carbon

Carbon permite fechas relativas al momento de ejecución:

```php
Carbon::now()              // Ahora
Carbon::now()->addDays(2)  // Dentro de 2 días
Carbon::now()->addWeeks(1) // Dentro de 1 semana
Carbon::now()->addMonths(1) // Dentro de 1 mes
Carbon::now()->subMonths(3) // Hace 3 meses (para archivados)
```

Esto hace que las fechas sean siempre relevantes sin importar cuándo se ejecute el seeder.

### Paso 6: Crear posts y establecer relaciones

```php
foreach ($posts as $postData) {
    $post = Post::firstOrCreate(
        [
            'name' => $postData['name'],
            'user_id' => $postData['user_id']
        ],
        $postData
    );

    // Asociar canales aleatorios (entre 1 y 3)
    if ($post->wasRecentlyCreated && $channels->isNotEmpty()) {
        $randomChannels = $channels->random(rand(1, min(3, $channels->count())));
        $post->channels()->sync($randomChannels->pluck('id')->toArray());
    }

    // Asociar medios aleatorios (entre 1 y 4)
    if ($post->wasRecentlyCreated && $medias->isNotEmpty()) {
        $randomMedias = $medias->random(rand(1, min(4, $medias->count())));
        $post->medias()->sync($randomMedias->pluck('id')->toArray());
    }
}
```

**Análisis detallado:**

1. **Criterio de búsqueda:** Se busca por `name` y `user_id` combinados
2. **Verificación wasRecentlyCreated:** Solo establece relaciones si el post es nuevo
3. **Selección aleatoria de canales:**
   - `rand(1, min(3, $channels->count()))`: Entre 1 y 3 canales, o menos si no hay suficientes
   - `random()`: Selecciona elementos aleatorios
   - `pluck('id')`: Extrae solo los IDs
   - `toArray()`: Convierte a array para sync()
4. **Selección aleatoria de medios:** Similar, pero entre 1 y 4

### Paso 7: El método random() con cantidad

```php
$collection->random(3);  // Selecciona 3 elementos aleatorios únicos
```

Si la colección tiene menos elementos que los solicitados, lanzaría un error. Por eso se usa `min()`:

```php
$cantidad = min(3, $channels->count());
// Si hay 2 canales, $cantidad = 2
// Si hay 5 canales, $cantidad = 3
```

## Código Completo del PostSeeder

El código completo está en el archivo `PostSeeder.php`. Aquí destacamos la estructura:

```php
class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener usuarios
        $adminUser = User::role('admin')->first();
        $regularUsers = User::role('user')->limit(5)->get();
        
        // 2. Validar usuarios
        if (!$adminUser || $regularUsers->isEmpty()) {
            $this->command->warn('No users found...');
            return;
        }
        
        // 3. Obtener canales y medios
        $channels = Channel::all();
        $medias = Media::where('is_active', true)->get();
        
        // 4. Validar canales
        if ($channels->isEmpty()) {
            $this->command->warn('No channels found...');
            return;
        }
        
        // 5. Definir array de 11 posts
        $posts = [
            // 3 posts TEXT
            // 2 posts IMAGE
            // 2 posts VIDEO
            // 1 post AUDIO
            // 2 posts MULTIMEDIA
            // 1 post ARCHIVED
        ];
        
        // 6. Procesar cada post
        foreach ($posts as $postData) {
            $post = Post::firstOrCreate(
                ['name' => $postData['name'], 'user_id' => $postData['user_id']],
                $postData
            );
            
            // 7. Establecer relaciones N:M
            if ($post->wasRecentlyCreated) {
                // Canales (1-3)
                // Medios (1-4)
            }
        }
        
        $this->command->info('Posts seeded successfully with relationships!');
    }
}
```

## Distribución de Posts

### Por tipo:
- TEXT: 4 posts (36.4%)
- IMAGE: 2 posts (18.2%)
- VIDEO: 2 posts (18.2%)
- AUDIO: 1 post (9.1%)
- MULTIMEDIA: 2 posts (18.2%)

### Por estado:
- DRAFT: 1 post
- APPROVED_BY_MODERATOR: 6 posts
- SCHEDULED: 3 posts
- ARCHIVED: 1 post

## Lógica de las Fechas por Estado

### Estado DRAFT:
```php
'scheduled_at' => null,
'published_at' => null,
'deadline' => Carbon::now()->addDays(7),
```
Borradores tienen deadline pero no fecha de publicación.

### Estado SCHEDULED:
```php
'scheduled_at' => Carbon::now()->addDays(5),
'published_at' => null,
'deadline' => Carbon::now()->addDays(4),
```
Programados tienen fecha futura de publicación.

### Estado APPROVED_BY_MODERATOR:
```php
'scheduled_at' => Carbon::now()->addDays(2),
'published_at' => null,  // o Carbon::now() si ya está publicado
```
Aprobados pueden tener o no fecha de publicación.

### Estado ARCHIVED:
```php
'scheduled_at' => Carbon::now()->subMonths(3),
'published_at' => Carbon::now()->subMonths(3),
'deadline' => Carbon::now()->subMonth(),
'timeout' => Carbon::now()->subWeek(),
```
Archivados tienen fechas en el pasado.

## Relaciones Establecidas

Después de ejecutar el seeder:

- Cada post tendrá entre 1 y 3 canales asociados
- Cada post tendrá entre 1 y 4 medios asociados
- Las relaciones se almacenan en las tablas pivote `post_channels` y `post_medias`

## Verificación de Relaciones

Para verificar las relaciones en Tinker:

```php
$post = Post::first();

// Ver canales del post
$post->channels;

// Ver medios del post
$post->medias;

// Contar relaciones
$post->channels()->count();
$post->medias()->count();
```

## Resumen

Este seeder demuestra:

1. Validación compleja de múltiples dependencias
2. Uso de Spatie Laravel Permission (role())
3. Selección aleatoria de registros relacionados
4. Establecimiento de relaciones N:M con sync()
5. Uso de wasRecentlyCreated para evitar duplicados
6. Fechas dinámicas con Carbon
7. Manejo de diferentes tipos y estados de posts
8. Organización de datos complejos
9. Mensajes informativos al usuario

Es el patrón más completo para seeders con relaciones múltiples en Laravel.
