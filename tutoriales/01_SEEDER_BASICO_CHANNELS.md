# Tutorial 01: Seeder Básico con Modelo Simple

## Objetivo

Aprender a crear un seeder básico que puebla una tabla sin relaciones externas, utilizando el método `firstOrCreate()` para prevenir duplicados.

## Contexto Teórico

Un seeder es una clase de Laravel que permite insertar datos iniciales en la base de datos de forma programática. Los seeders son fundamentales para:

- Inicializar datos de configuración del sistema
- Crear datos de prueba para desarrollo
- Establecer valores predeterminados necesarios para el funcionamiento de la aplicación

### El método firstOrCreate()

Este método de Eloquent busca un registro en la base de datos usando los atributos especificados en el primer parámetro. Si no encuentra ninguna coincidencia, crea un nuevo registro con todos los atributos especificados en ambos parámetros.

**Sintaxis:**

```php
Model::firstOrCreate(
    ['campo_busqueda' => 'valor'],  // Criterio de búsqueda
    ['campo1' => 'valor1', ...]      // Datos completos del registro
);
```

**Ventaja:** El seeder es idempotente, es decir, puede ejecutarse múltiples veces sin generar duplicados.

## Análisis del Modelo Channel

El modelo Channel representa canales organizacionales en el sistema. Analicemos su estructura:

### Campos de la tabla

- `id`: Identificador único autoincrementable
- `name`: Nombre del canal (string, 255 caracteres)
- `description`: Descripción del canal (texto, nullable)
- `type`: Tipo de canal (enum: department, institute, secretary, center)
- `semantic_context`: Contexto semántico para búsquedas con IA (texto, nullable)
- `timestamps`: created_at y updated_at (gestionados automáticamente por Laravel)

### El uso de Enums

Los Enums (enumeraciones) en PHP 8.1+ permiten definir un conjunto de valores posibles de forma type-safe. En este caso, `ChannelType` es un enum que define cuatro tipos posibles:

- DEPARTMENT (departamento)
- INSTITUTE (instituto)
- SECRETARY (secretaría)
- CENTER (centro)

Para usar el valor del enum en la base de datos, se accede mediante la propiedad `value`:

```php
ChannelType::DEPARTMENT->value  // Devuelve 'department'
```

## Implementación del ChannelSeeder

### Paso 1: Estructura de la clase

```php
<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Enums\ChannelType;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        // Implementación aquí
    }
}
```

### Paso 2: Definir el array de datos

Se define un array asociativo con todos los canales a crear. Cada elemento del array contiene los campos necesarios para crear un canal:

```php
$channels = [
    [
        'name' => 'Departamento de Comunicación',
        'description' => 'Responsable de la comunicación institucional y relaciones públicas',
        'type' => ChannelType::DEPARTMENT->value,
        'semantic_context' => 'Comunicación corporativa, prensa, relaciones públicas, eventos institucionales',
    ],
    // ... más canales
];
```

**Nota sobre la organización:** Los canales se agrupan por tipo (departamentos, institutos, secretarías, centros) para facilitar la lectura y mantenimiento del código.

### Paso 3: Iterar y crear los registros

Se utiliza un bucle foreach para procesar cada elemento del array:

```php
foreach ($channels as $channelData) {
    Channel::firstOrCreate(
        ['name' => $channelData['name']],
        $channelData
    );
}
```

**Explicación del firstOrCreate:**

1. Busca un registro donde `name` coincida con `$channelData['name']`
2. Si lo encuentra, no hace nada (previene duplicado)
3. Si no lo encuentra, crea un nuevo registro con todos los datos de `$channelData`

### Paso 4: Mensaje de confirmación

Al finalizar, se envía un mensaje informativo a la consola:

```php
$this->command->info('Channels seeded successfully!');
```

## Código Completo del ChannelSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Enums\ChannelType;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            // Departamentos
            [
                'name' => 'Departamento de Comunicación',
                'description' => 'Responsable de la comunicación institucional y relaciones públicas',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Comunicación corporativa, prensa, relaciones públicas, eventos institucionales',
            ],
            [
                'name' => 'Departamento de Recursos Humanos',
                'description' => 'Gestión del talento humano y desarrollo organizacional',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Personal, capacitación, cultura organizacional, bienestar laboral',
            ],
            [
                'name' => 'Departamento de Sistemas',
                'description' => 'Tecnologías de la información y soporte técnico',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'IT, infraestructura, desarrollo software, ciberseguridad',
            ],
            [
                'name' => 'Departamento de Marketing',
                'description' => 'Estrategias de marketing digital y tradicional',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Marketing digital, campañas, branding, publicidad',
            ],

            // Institutos
            [
                'name' => 'Instituto de Investigación Científica',
                'description' => 'Centro de investigación y desarrollo científico',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Investigación, ciencia, desarrollo, innovación, papers académicos',
            ],
            [
                'name' => 'Instituto de Capacitación Profesional',
                'description' => 'Formación continua y desarrollo profesional',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Educación, cursos, certificaciones, capacitación',
            ],
            [
                'name' => 'Instituto Tecnológico',
                'description' => 'Desarrollo e innovación tecnológica',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Tecnología, innovación, transformación digital, I+D',
            ],

            // Secretarías
            [
                'name' => 'Secretaría Académica',
                'description' => 'Gestión y coordinación académica institucional',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Educación, programas académicos, estudiantes, docentes',
            ],
            [
                'name' => 'Secretaría de Extensión',
                'description' => 'Proyectos de extensión y vinculación con la comunidad',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Comunidad, proyectos sociales, vinculación, responsabilidad social',
            ],
            [
                'name' => 'Secretaría de Cultura',
                'description' => 'Promoción de actividades culturales y artísticas',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Cultura, arte, eventos culturales, patrimonio',
            ],

            // Centros
            [
                'name' => 'Centro de Innovación Digital',
                'description' => 'Hub de innovación y transformación digital',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Innovación, startups, emprendimiento, tecnología disruptiva',
            ],
            [
                'name' => 'Centro de Atención al Cliente',
                'description' => 'Soporte y atención a usuarios',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Servicio al cliente, soporte, consultas, atención',
            ],
            [
                'name' => 'Centro de Documentación',
                'description' => 'Gestión documental y biblioteca institucional',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Documentación, biblioteca, archivo, recursos bibliográficos',
            ],
        ];

        foreach ($channels as $channelData) {
            Channel::firstOrCreate(
                ['name' => $channelData['name']],
                $channelData
            );
        }

        $this->command->info('Channels seeded successfully!');
    }
}
```

## Análisis de los Datos

### Distribución por tipo

- Departamentos: 4
- Institutos: 3
- Secretarías: 3
- Centros: 3
- Total: 13 canales

### Consideraciones al definir datos

1. **Nombres descriptivos:** Cada canal tiene un nombre claro que indica su función
2. **Descripciones útiles:** Proporcionan contexto sobre las responsabilidades del canal
3. **Contexto semántico:** Palabras clave que facilitan búsquedas y clasificaciones automáticas
4. **Organización por tipo:** Los datos están agrupados lógicamente en el código

## Ejecución del Seeder

Para ejecutar este seeder específico:

```bash
php artisan db:seed --class=ChannelSeeder
```

Para ejecutar todos los seeders (incluyendo este):

```bash
php artisan db:seed
```

## Verificación de Resultados

Después de ejecutar el seeder, puedes verificar los datos en Laravel Tinker:

```bash
php artisan tinker
```

```php
// Contar canales
Channel::count()

// Ver todos los canales
Channel::all()

// Ver canales por tipo
Channel::where('type', 'department')->get()
```

## Resumen

Este seeder básico demuestra:

1. Cómo estructurar datos estáticos en un array
2. El uso de `firstOrCreate()` para prevenir duplicados
3. Trabajo con Enums en Laravel
4. Organización de datos por categorías
5. Mensajes informativos al usuario

Es el patrón fundamental para seeders de tablas independientes sin relaciones externas.
