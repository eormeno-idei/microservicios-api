# Tutorial 03: Seeder con Relación Uno a Muchos (1:N)

## Objetivo

Aprender a crear un seeder que depende de registros existentes en otras tablas, específicamente manejando una relación uno a muchos (1:N).

## Contexto Teórico

### Relación Uno a Muchos (1:N)

Una relación 1:N ocurre cuando un registro de una tabla puede estar asociado con múltiples registros de otra tabla. En este caso:

- Un Post pertenece a un User (1:N inversa)
- Un User puede tener muchos Posts (1:N)

**En la base de datos:**
```
users                    posts
  id                       id
  name                     user_id (FK)
  ...                      name
                           content
                           ...
```

La clave foránea `user_id` en la tabla `posts` establece la relación.

### Validación de Dependencias

Antes de crear registros que dependen de otros, es fundamental:

1. Verificar que los registros necesarios existen
2. Proporcionar mensajes claros si faltan dependencias
3. Usar `return` para salir del seeder si no se cumplen las precondiciones

```php
if (!$dependencia) {
    $this->command->warn('Mensaje de advertencia');
    return;
}
```

### Uso de Carbon para Fechas

Carbon es la biblioteca de manipulación de fechas de Laravel. Permite trabajar con fechas de forma expresiva:

```php
Carbon::now()              // Fecha y hora actual
Carbon::now()->addDays(5)  // 5 días después de hoy
Carbon::now()->subMonth()  // 1 mes antes de hoy
```

## Análisis del Modelo Post

El modelo Post representa contenido para publicación. Analicemos su estructura:

### Campos principales

- `id`: Identificador único
- `user_id`: Clave foránea al usuario creador (relación 1:N)
- `name`: Título del post
- `content`: Contenido del post
- `type`: Tipo de contenido (enum: text, video, audio, image, multimedia)
- `status`: Estado del post (enum: draft, approved_by_moderator, scheduled, archived)
- `moderator_comments`: Comentarios del moderador (nullable)
- `scheduled_at`: Fecha de programación (timestamp, nullable)
- `published_at`: Fecha de publicación (timestamp, nullable)
- `deadline`: Fecha límite (timestamp, nullable)
- `timeout`: Tiempo límite (timestamp, nullable)

### Relaciones del Modelo Post

Post tiene tres tipos de relaciones:

1. **BelongsTo User:** Un post pertenece a un usuario
2. **BelongsToMany Channels:** Un post puede estar en múltiples canales (N:M)
3. **BelongsToMany Medias:** Un post puede distribuirse en múltiples medios (N:M)

Este tutorial se enfoca en la relación con User.

## Implementación del AttachmentSeeder

Aunque el PostSeeder es más complejo, comenzaremos con AttachmentSeeder porque ilustra claramente la relación 1:N.

### Análisis del Modelo Attachment

Un Attachment representa un archivo adjunto a un post:

- `id`: Identificador único
- `post_id`: Clave foránea al post (relación 1:N)
- `mime_type`: Tipo MIME del archivo
- `path`: Ruta del archivo

**Relación:** Un Post puede tener muchos Attachments.

### Paso 1: Estructura de la clase

Con el comando Artisan:

```bash
php artisan make:seeder AttachmentSeeder
```

Lo que genera:

```php
<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Attachment;
use App\Enums\PostType;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    public function run(): void
    {
        // Implementación
    }
}
```

### Paso 2: Obtener posts por tipo

Los attachments se crean según el tipo de post. Primero, se obtienen los posts agrupados por tipo:

```php
$imagePosts = Post::where('type', PostType::IMAGE->value)->get();
$videoPosts = Post::where('type', PostType::VIDEO->value)->get();
$audioPosts = Post::where('type', PostType::AUDIO->value)->get();
$multimediaPosts = Post::where('type', PostType::MULTIMEDIA->value)->get();
```

**Nota:** Posts de tipo TEXT no requieren attachments.

### Paso 3: Crear attachments según el tipo de post

Cada tipo de post requiere attachments específicos.

#### Para posts de imagen:

```php
foreach ($imagePosts as $post) {
    $attachments = [
        [
            'post_id' => $post->id,
            'mime_type' => 'image/jpeg',
            'path' => 'storage/posts/' . $post->id . '/images/main_image.jpg',
        ],
        [
            'post_id' => $post->id,
            'mime_type' => 'image/png',
            'path' => 'storage/posts/' . $post->id . '/images/thumbnail.png',
        ],
    ];

    foreach ($attachments as $attachmentData) {
        Attachment::firstOrCreate(
            [
                'post_id' => $attachmentData['post_id'],
                'path' => $attachmentData['path']
            ],
            $attachmentData
        );
    }
}
```

**Explicación:**
- Se itera sobre cada post de tipo imagen
- Se definen 2 attachments: imagen principal y thumbnail
- El `post_id` vincula cada attachment con su post
- La ruta incluye el ID del post para organización

#### Para posts de video:

Los posts de video requieren 3 attachments:

1. Video principal (MP4)
2. Thumbnail (imagen)
3. Subtítulos (SRT)

```php
$attachments = [
    [
        'post_id' => $post->id,
        'mime_type' => 'video/mp4',
        'path' => 'storage/posts/' . $post->id . '/videos/main_video.mp4',
    ],
    [
        'post_id' => $post->id,
        'mime_type' => 'image/jpeg',
        'path' => 'storage/posts/' . $post->id . '/videos/thumbnail.jpg',
    ],
    [
        'post_id' => $post->id,
        'mime_type' => 'text/srt',
        'path' => 'storage/posts/' . $post->id . '/videos/subtitles_es.srt',
    ],
];
```

#### Para posts de audio:

Los posts de audio requieren 2 attachments:

1. Archivo de audio (MP3)
2. Portada (imagen)

#### Para posts multimedia:

Los posts multimedia requieren 4 attachments variados:

1. Banner (imagen)
2. Video promocional
3. Programa (PDF)
4. Infografía (imagen)

### Paso 4: Criterio de búsqueda en firstOrCreate

Para Attachment, el criterio combina `post_id` y `path`:

```php
Attachment::firstOrCreate(
    [
        'post_id' => $attachmentData['post_id'],
        'path' => $attachmentData['path']
    ],
    $attachmentData
);
```

Esto asegura que no se duplique el mismo archivo para el mismo post.

## Código Completo del AttachmentSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Attachment;
use App\Enums\PostType;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imagePosts = Post::where('type', PostType::IMAGE->value)->get();
        $videoPosts = Post::where('type', PostType::VIDEO->value)->get();
        $audioPosts = Post::where('type', PostType::AUDIO->value)->get();
        $multimediaPosts = Post::where('type', PostType::MULTIMEDIA->value)->get();

        // Attachments para posts de imagen
        foreach ($imagePosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/images/main_image.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/png',
                    'path' => 'storage/posts/' . $post->id . '/images/thumbnail.png',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts de video
        foreach ($videoPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'video/mp4',
                    'path' => 'storage/posts/' . $post->id . '/videos/main_video.mp4',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/videos/thumbnail.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'text/vtt',
                    'path' => 'storage/posts/' . $post->id . '/videos/subtitles_es.vtt',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts de audio
        foreach ($audioPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'audio/mpeg',
                    'path' => 'storage/posts/' . $post->id . '/audio/podcast.mp3',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/audio/cover.jpg',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts multimedia
        foreach ($multimediaPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/banner.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'video/mp4',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/promo.mp4',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'application/pdf',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/program.pdf',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/png',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/infographic.png',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        $this->command->info('Attachments seeded successfully!');
    }
}
```

## Tipos MIME Comunes

El campo `mime_type` identifica el tipo de archivo:

- Imágenes: `image/jpeg`, `image/png`, `image/gif`
- Videos: `video/mp4`, `video/webm`
- Audio: `audio/mpeg`, `audio/wav`
- Documentos: `application/pdf`, `application/msword`
- Subtítulos: `text/vtt`, `text/srt`

## Orden de Ejecución

Este seeder depende de PostSeeder. El orden correcto es:

1. UserSeeder (o DatabaseSeeder que crea usuarios)
2. ChannelSeeder
3. MediaSeeder
4. PostSeeder
5. **AttachmentSeeder**

## Resumen

Este seeder demuestra:

1. Manejo de relaciones 1:N
2. Consultas filtradas por tipo de enum
3. Creación de registros dependientes con claves foráneas
4. Organización de archivos por tipo de contenido
5. Uso de tipos MIME estándar
6. Iteración sobre colecciones de Eloquent

El patrón de crear attachments según el tipo del padre es común en aplicaciones que manejan diferentes tipos de contenido multimedia.
