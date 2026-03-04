# Tutorial 02: Seeder con Datos JSON y Configuraciones Complejas

## Contexto Teórico

### JSON en la Base de Datos

Laravel permite almacenar datos en formato JSON en columnas de tipo `json`. Esto es útil cuando:

- La estructura de datos es variable o flexible
- Se necesita almacenar configuraciones específicas por registro
- Los datos tienen naturaleza jerárquica o anidada
- Se quiere evitar crear múltiples tablas para datos opcionales

**Ventajas:**
- Flexibilidad en la estructura de datos
- Menos tablas en la base de datos
- Facilita la extensibilidad sin modificar el schema

**Desventajas:**
- Búsquedas menos eficientes que columnas normales
- Validación de estructura más compleja
- Queries más complejas para datos anidados

### El método json_encode()

PHP proporciona `json_encode()` para convertir arrays o objetos PHP a strings JSON:

```php
$array = ['key' => 'value', 'number' => 42];
$json = json_encode($array);  // '{"key":"value","number":42}'
```

En la terminal el código anterior se puede ejecutar así:

```bash
php -r '$array = ["key" => "value", "number" => 42]; echo json_encode($array, JSON_PRETTY_PRINT); echo "\n";'
```

>> Nota: El flag `JSON_PRETTY_PRINT` es opcional y solo mejora la legibilidad del JSON generado.El parámetro '-r' permite ejecutar código PHP directamente desde la línea de comandos.

En el modelo de Laravel, cuando se define un cast a `array`, Laravel automáticamente convierte entre JSON y array PHP.

## Análisis del Modelo Media

El modelo Media representa medios de distribución de contenido. Analicemos su estructura:

### Campos de la tabla

- `id`: Identificador único
- `name`: Nombre del medio
- `type`: Tipo de medio (enum: physical_screen, social_media, editorial_platform)
- `configuration`: Configuración específica del medio (JSON, nullable)
- `semantic_context`: Contexto semántico (texto, nullable)
- `url_webhook`: URL para notificaciones webhook (string, nullable)
- `is_active`: Estado del medio (boolean, default: true)
- `timestamps`: created_at y updated_at

### El campo configuration

Este campo JSON almacena configuraciones específicas que varían según el tipo de medio:

**Para pantallas físicas:**
```json
{
    "location": "Hall Principal - Planta Baja",
    "resolution": "1920x1080",
    "orientation": "horizontal",
    "display_time": 15
}
```

**Para redes sociales:**
```json
{
    "platform": "facebook",
    "page_id": "institucional.oficial",
    "access_token": "fb_token_placeholder",
    "auto_publish": true
}
```

## Implementación del MediaSeeder

### Paso 1: Estructura de la clase

El comando Artisan para crear el seeder es:

```bash
php artisan make:seeder MediaSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Enums\MediaType;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        // Implementación
    }
}
```

### Paso 2: Definir el array de medios

A diferencia del seeder anterior, aquí cada registro contiene un campo JSON codificado:

```php
$medias = [
    [
        'name' => 'Pantalla Principal Hall de Entrada',
        'type' => MediaType::PHYSICAL_SCREEN->value,
        'configuration' => json_encode([
            'location' => 'Hall Principal - Planta Baja',
            'resolution' => '1920x1080',
            'orientation' => 'horizontal',
            'display_time' => 15,
        ]),
        'semantic_context' => 'Información institucional, anuncios generales, eventos principales',
        'url_webhook' => 'https://display-system.example.com/api/webhook/main-hall',
        'is_active' => true,
    ],
    // ... más medios
];
```

>> **Nota importante:** Se usa `json_encode()` para convertir el array de configuración a string JSON antes de insertarlo en la base de datos.

### Paso 3: Organización por tipo de medio

Los medios se organizan en tres categorías:

1. **Pantallas Físicas (4 medios)**
   - Pantalla Principal Hall de Entrada
   - Pantalla Cafetería
   - Pantalla Auditorio
   - Pantalla Biblioteca

2. **Redes Sociales (5 medios)**
   - Facebook Institucional
   - Instagram Oficial
   - Twitter/X Corporativo
   - LinkedIn Corporativo
   - YouTube Institucional

3. **Plataformas Editoriales (3 medios)**
   - Portal Web Institucional
   - Blog Institucional
   - Newsletter Email

### Paso 4: Procesamiento de los datos

```php
foreach ($medias as $mediaData) {
    Media::firstOrCreate(
        ['name' => $mediaData['name']],
        $mediaData
    );
}
```

## Código Completo del MediaSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Enums\MediaType;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medias = [
            // Pantallas Físicas
            [
                'name' => 'Pantalla Principal Hall de Entrada',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Hall Principal - Planta Baja',
                    'resolution' => '1920x1080',
                    'orientation' => 'horizontal',
                    'display_time' => 15,
                ]),
                'semantic_context' => 'Información institucional, anuncios generales, eventos principales',
                'url_webhook' => 'https://display-system.example.com/api/webhook/main-hall',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Cafetería',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Cafetería - Piso 2',
                    'resolution' => '1920x1080',
                    'orientation' => 'horizontal',
                    'display_time' => 20,
                ]),
                'semantic_context' => 'Menú, eventos sociales, actividades recreativas',
                'url_webhook' => 'https://display-system.example.com/api/webhook/cafeteria',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Auditorio',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Auditorio Principal',
                    'resolution' => '3840x2160',
                    'orientation' => 'horizontal',
                    'display_time' => 10,
                ]),
                'semantic_context' => 'Conferencias, eventos académicos, presentaciones',
                'url_webhook' => 'https://display-system.example.com/api/webhook/auditorium',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Biblioteca',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Biblioteca - Piso 3',
                    'resolution' => '1920x1080',
                    'orientation' => 'vertical',
                    'display_time' => 30,
                ]),
                'semantic_context' => 'Recursos bibliográficos, horarios, actividades culturales',
                'url_webhook' => 'https://display-system.example.com/api/webhook/library',
                'is_active' => true,
            ],

            // Redes Sociales
            [
                'name' => 'Facebook Institucional',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'facebook',
                    'page_id' => 'institucional.oficial',
                    'access_token' => 'fb_token_placeholder',
                    'auto_publish' => true,
                ]),
                'semantic_context' => 'Comunicación institucional, eventos, noticias, comunidad',
                'url_webhook' => 'https://api.facebook.com/v18.0/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Instagram Oficial',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'instagram',
                    'account_id' => '@institucion_oficial',
                    'access_token' => 'ig_token_placeholder',
                    'preferred_formats' => ['image', 'carousel', 'reels'],
                ]),
                'semantic_context' => 'Contenido visual, lifestyle institucional, estudiantes, cultura',
                'url_webhook' => 'https://graph.instagram.com/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Twitter/X Corporativo',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'twitter',
                    'handle' => '@institucion',
                    'api_key' => 'twitter_key_placeholder',
                    'max_characters' => 280,
                ]),
                'semantic_context' => 'Noticias rápidas, comunicados oficiales, trending topics',
                'url_webhook' => 'https://api.twitter.com/2/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'LinkedIn Corporativo',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'linkedin',
                    'company_id' => 'institution-inc',
                    'access_token' => 'li_token_placeholder',
                    'content_type' => 'professional',
                ]),
                'semantic_context' => 'Contenido profesional, logros institucionales, networking',
                'url_webhook' => 'https://api.linkedin.com/v2/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'YouTube Institucional',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'youtube',
                    'channel_id' => 'UCinstitucion123',
                    'api_key' => 'yt_key_placeholder',
                    'default_privacy' => 'public',
                ]),
                'semantic_context' => 'Videos educativos, conferencias, eventos grabados',
                'url_webhook' => 'https://www.googleapis.com/youtube/v3/webhook',
                'is_active' => true,
            ],

            // Plataformas Editoriales
            [
                'name' => 'Portal Web Institucional',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'wordpress',
                    'url' => 'https://www.institucion.edu',
                    'api_endpoint' => 'https://www.institucion.edu/wp-json/wp/v2',
                    'auth_type' => 'jwt',
                ]),
                'semantic_context' => 'Noticias institucionales, artículos, comunicados oficiales',
                'url_webhook' => 'https://www.institucion.edu/api/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Blog Institucional',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'medium',
                    'publication_id' => 'institucion-oficial',
                    'api_key' => 'medium_key_placeholder',
                    'auto_publish' => false,
                ]),
                'semantic_context' => 'Artículos de opinión, investigación, contenido académico',
                'url_webhook' => 'https://api.medium.com/v1/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Newsletter Email',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'mailchimp',
                    'list_id' => 'newsletter_main',
                    'api_key' => 'mailchimp_key_placeholder',
                    'sender_name' => 'Institución Oficial',
                    'sender_email' => 'newsletter@institucion.edu',
                ]),
                'semantic_context' => 'Newsletter semanal, actualizaciones, contenido exclusivo',
                'url_webhook' => 'https://us1.api.mailchimp.com/3.0/webhook',
                'is_active' => true,
            ],
        ];

        foreach ($medias as $mediaData) {
            Media::firstOrCreate(
                ['name' => $mediaData['name']],
                $mediaData
            );
        }

        $this->command->info('Medias seeded successfully!');
    }
}
```

## Análisis de las Configuraciones

### Configuraciones para Pantallas Físicas

Las pantallas físicas requieren:
- `location`: Ubicación física
- `resolution`: Resolución de pantalla
- `orientation`: Orientación (horizontal/vertical)
- `display_time`: Tiempo de visualización en segundos

### Configuraciones para Redes Sociales

Las redes sociales requieren:
- `platform`: Nombre de la plataforma
- Credenciales de acceso (tokens, API keys)
- Configuraciones específicas de la plataforma
- Opciones de publicación automática

### Configuraciones para Plataformas Editoriales

Las plataformas editoriales requieren:
- `platform`: Tecnología utilizada
- `url` o endpoints de API
- Métodos de autenticación
- Configuraciones de publicación

## Consideraciones de Seguridad

En este ejemplo, los tokens y API keys son placeholders. En un entorno de producción:

1. Nunca almacenar credenciales reales en el código
2. Usar variables de entorno para información sensible
3. Implementar encriptación para tokens en la base de datos
4. Considerar usar un sistema de gestión de secretos

## Acceso a Datos JSON desde el Modelo

Una vez almacenados, los datos JSON se pueden acceder fácilmente si el modelo tiene el cast correcto:

```php
// En el modelo Media
protected $casts = [
    'configuration' => 'array',
];

// Al consultar
$media = Media::find(1);
$location = $media->configuration['location'];  // Acceso directo como array
```
