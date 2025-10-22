# Tutorial 06: Seeder Orquestador (DatabaseSeeder)

## Objetivo

Aprender a crear un seeder principal que orquesta la ejecución de todos los seeders específicos, gestionando el orden de ejecución, las dependencias y proporcionando retroalimentación al usuario.

## Contexto Teórico

### El DatabaseSeeder como Punto de Entrada

Laravel designa `DatabaseSeeder` como el seeder principal que se ejecuta cuando se invoca:

```bash
php artisan db:seed
```

Este seeder debe:

1. Coordinar el orden de ejecución de otros seeders
2. Respetar las dependencias entre tablas
3. Crear datos base necesarios (roles, usuario admin)
4. Proporcionar retroalimentación clara al usuario
5. Ser idempotente (ejecutable múltiples veces)

### El método call()

Laravel proporciona el método `call()` para invocar otros seeders:

```php
$this->call(ChannelSeeder::class);
```

**Características:**

- Ejecuta el seeder especificado
- Mantiene el contexto de la consola
- Proporciona salida visible al usuario
- Respeta transacciones de base de datos

**Puede invocar múltiples seeders:**

```php
$this->call([
    ChannelSeeder::class,
    MediaSeeder::class,
    PostSeeder::class,
]);
```

### Grafo de Dependencias

Un aspecto crítico en el diseño de seeders es entender y respetar las dependencias:

```
Roles (independiente)
  └─> Users (depende de Roles)
        ├─> Channels (independiente de Users, pero se relaciona después)
        ├─> Medias (independiente)
        └─> Posts (depende de Users, Channels, Medias)
              ├─> Attachments (depende de Posts)
              └─> User-Channel relations (depende de Users y Channels)
```

**Orden de ejecución correcto:**

1. Roles
2. Users
3. Channels (independiente)
4. Medias (independiente)
5. Posts (necesita Users, Channels, Medias)
6. Attachments (necesita Posts)
7. UserChannels (necesita Users y Channels)

## Análisis del DatabaseSeeder

### Paso 1: Estructura de la clase

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Implementación
    }
}
```

### Paso 2: Crear roles (base del sistema de permisos)

```php
$this->command->info('Creating roles...');
Role::firstOrCreate(['name' => 'admin']);
Role::firstOrCreate(['name' => 'user']);
$this->command->info('Roles created successfully!');
```

**Por qué primero:**

Los roles son fundamentales porque:
- Los usuarios necesitan roles al ser creados
- Spatie Permission requiere que existan antes de asignarlos
- Son independientes de cualquier otra tabla

**Uso de firstOrCreate:**

Asegura que los roles existan sin duplicarlos si el seeder se ejecuta múltiples veces.

### Paso 3: Crear usuario administrador

```php
$admin = User::firstOrCreate(
    ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
    [
        'name' => env('ADMIN_NAME', 'Admin User'),
        'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
        'last_name' => env('ADMIN_LAST_NAME', 'User'),
        'mobile' => env('ADMIN_MOBILE', '+1234567890'),
        'semantic_context' => 'Administrador del sistema con acceso completo',
        'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
        'email_verified_at' => now(),
    ]
);
$admin->assignRole('admin');
```

**Análisis de la configuración:**

1. **Función env():**
   ```php
   env('ADMIN_EMAIL', 'admin@example.com')
   ```
   Lee del archivo `.env`, si no existe usa el valor por defecto.

2. **Criterio de búsqueda:**
   ```php
   ['email' => env('ADMIN_EMAIL', 'admin@example.com')]
   ```
   Busca por email porque debe ser único.

3. **Campo password:**
   ```php
   'password' => bcrypt(env('ADMIN_PASSWORD', 'password'))
   ```
   `bcrypt()` encripta la contraseña antes de almacenarla.

4. **Campo email_verified_at:**
   ```php
   'email_verified_at' => now()
   ```
   Marca el email como verificado inmediatamente.

5. **Asignación de rol:**
   ```php
   $admin->assignRole('admin')
   ```
   Usa Spatie Permission para asignar el rol de administrador.

### Paso 4: Crear usuarios regulares con Factories

```php
$existingUsersCount = User::role('user')->count();
$usersToCreate = max(0, 10 - $existingUsersCount);

if ($usersToCreate > 0) {
    User::factory($usersToCreate)->create()->each(function ($user) {
        $user->assignRole('user');
    });
    $this->command->info("{$usersToCreate} regular users created successfully!");
} else {
    $this->command->info('Regular users already exist, skipping creation.');
}
```

**Análisis de la lógica:**

1. **Contar usuarios existentes:**
   ```php
   User::role('user')->count()
   ```
   Cuenta solo usuarios con rol 'user', no incluye al admin.

2. **Calcular cantidad a crear:**
   ```php
   $usersToCreate = max(0, 10 - $existingUsersCount)
   ```
   - Queremos 10 usuarios regulares en total
   - Si ya existen 3, crea 7
   - Si ya existen 10 o más, crea 0
   - `max(0, ...)` asegura que nunca sea negativo

3. **Crear con factory:**
   ```php
   User::factory($usersToCreate)->create()
   ```
   Los factories generan datos aleatorios pero realistas.

4. **Asignar rol a cada uno:**
   ```php
   ->each(function ($user) {
       $user->assignRole('user');
   })
   ```
   El método `each()` itera sobre cada usuario creado y le asigna el rol.

5. **Mensajes condicionales:**
   Informa si creó usuarios o si ya existían.

### Paso 5: Invocar seeders específicos

```php
$this->command->info('Seeding channels...');
$this->call(ChannelSeeder::class);

$this->command->info('Seeding medias...');
$this->call(MediaSeeder::class);

$this->command->info('Seeding posts...');
$this->call(PostSeeder::class);

$this->command->info('Seeding attachments...');
$this->call(AttachmentSeeder::class);

$this->command->info('Seeding user-channel relationships...');
$this->call(UserChannelSeeder::class);
```

**Orden crítico:**

1. **Channels y Medias:** Independientes, pueden ser cualquier orden entre sí
2. **Posts:** Después de Channels y Medias porque crea relaciones con ambos
3. **Attachments:** Después de Posts porque depende de ellos
4. **UserChannels:** Después de Users y Channels

### Paso 6: Resumen final con tabla de estadísticas

```php
$this->command->info('Database seeding completed successfully!');

$this->command->table(
    ['Model', 'Count'],
    [
        ['Users', User::count()],
        ['Roles', Role::count()],
        ['Channels', \App\Models\Channel::count()],
        ['Medias', \App\Models\Media::count()],
        ['Posts', \App\Models\Post::count()],
        ['Attachments', \App\Models\Attachment::count()],
    ]
);
```

**El método table():**

Laravel proporciona `table()` para mostrar datos tabulares en consola:

```php
$this->command->table($headers, $rows);
```

- `$headers`: Array con los encabezados de columna
- `$rows`: Array de arrays, cada uno representa una fila

**Namespace completo:**

```php
\App\Models\Channel::count()
```

Se usa el namespace completo porque Channel no fue importado al inicio con `use`.

### Paso 7: Uso de newLine() para espaciado

```php
$this->command->newLine();
```

Inserta una línea en blanco para mejorar la legibilidad de la salida.

## Código Completo del DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Este seeder principal orquesta la ejecución de todos los seeders
     * en el orden correcto para mantener la integridad referencial.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // 1. Crear roles (necesarios para usuarios)
        $this->command->info('Creating roles...');
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);
        $this->command->info('Roles created successfully!');
        $this->command->newLine();

        // 2. Crear usuario administrador
        $this->command->info('Creating admin user...');
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Admin User'),
                'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
                'last_name' => env('ADMIN_LAST_NAME', 'User'),
                'mobile' => env('ADMIN_MOBILE', '+1234567890'),
                'semantic_context' => 'Administrador del sistema con acceso completo',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
        $this->command->info('Admin user created successfully!');
        $this->command->newLine();

        // 3. Crear usuarios regulares
        $this->command->info('Creating regular users...');
        $existingUsersCount = User::role('user')->count();
        $usersToCreate = max(0, 10 - $existingUsersCount);

        if ($usersToCreate > 0) {
            User::factory($usersToCreate)->create()->each(function ($user) {
                $user->assignRole('user');
            });
            $this->command->info("{$usersToCreate} regular users created successfully!");
        } else {
            $this->command->info('Regular users already exist, skipping creation.');
        }
        $this->command->newLine();

        // 4. Ejecutar seeders específicos en orden
        $this->command->info('Running specific seeders...');
        $this->command->newLine();

        // Channels (independiente)
        $this->command->info('Seeding channels...');
        $this->call(ChannelSeeder::class);
        $this->command->newLine();

        // Medias (independiente)
        $this->command->info('Seeding medias...');
        $this->call(MediaSeeder::class);
        $this->command->newLine();

        // Posts (depende de Users, Channels, Medias)
        $this->command->info('Seeding posts...');
        $this->call(PostSeeder::class);
        $this->command->newLine();

        // Attachments (depende de Posts)
        $this->command->info('Seeding attachments...');
        $this->call(AttachmentSeeder::class);
        $this->command->newLine();

        // User-Channel relationships (depende de Users y Channels)
        $this->command->info('Seeding user-channel relationships...');
        $this->call(UserChannelSeeder::class);
        $this->command->newLine();

        // Resumen final
        $this->command->info('Database seeding completed successfully!');
        $this->command->newLine();

        // Estadísticas
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Users', User::count()],
                ['Roles', Role::count()],
                ['Channels', \App\Models\Channel::count()],
                ['Medias', \App\Models\Media::count()],
                ['Posts', \App\Models\Post::count()],
                ['Attachments', \App\Models\Attachment::count()],
            ]
        );
    }
}
```

## Variables de Entorno Requeridas

Para que el seeder funcione correctamente, el archivo `.env` debe contener:

```env
ADMIN_EMAIL=admin@example.com
ADMIN_NAME=Admin User
ADMIN_FIRST_NAME=Admin
ADMIN_LAST_NAME=User
ADMIN_MOBILE=+1234567890
ADMIN_PASSWORD=password
```

Si no existen, se usarán los valores por defecto definidos en el código.

## Salida Esperada al Ejecutar

```
Starting database seeding...

Creating roles...
Roles created successfully!

Creating admin user...
Admin user created successfully!

Creating regular users...
10 regular users created successfully!

Running specific seeders...

Seeding channels...
  Database\Seeders\ChannelSeeder .... DONE
Channels seeded successfully!

Seeding medias...
  Database\Seeders\MediaSeeder ....... DONE
Medias seeded successfully!

Seeding posts...
  Database\Seeders\PostSeeder ........ DONE
Posts seeded successfully with relationships!

Seeding attachments...
  Database\Seeders\AttachmentSeeder .. DONE
Attachments seeded successfully!

Seeding user-channel relationships...
  Database\Seeders\UserChannelSeeder . DONE
Admin user assigned to all channels.
User-Channel relationships seeded successfully!

Database seeding completed successfully!

+-------------+-------+
| Model       | Count |
+-------------+-------+
| Users       | 11    |
| Roles       | 2     |
| Channels    | 13    |
| Medias      | 12    |
| Posts       | 11    |
| Attachments | 20    |
+-------------+-------+
```

## Mejores Prácticas Implementadas

1. **Mensajes informativos:** Cada etapa tiene mensajes claros
2. **Separación visual:** Uso de `newLine()` para legibilidad
3. **Orden de dependencias:** Respeta el grafo de dependencias
4. **Idempotencia:** Puede ejecutarse múltiples veces sin errores
5. **Configuración externa:** Usa variables de entorno para valores sensibles
6. **Verificación de existencia:** No crea duplicados innecesarios
7. **Resumen final:** Tabla de estadísticas para verificación rápida
8. **Comentarios descriptivos:** Documenta el propósito de cada sección

## Comandos de Ejecución

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Refrescar base de datos y ejecutar seeders
php artisan migrate:fresh --seed

# Ejecutar solo el DatabaseSeeder (es lo mismo que db:seed)
php artisan db:seed --class=DatabaseSeeder
```

## Resumen

El DatabaseSeeder demuestra:

1. Orquestación de múltiples seeders con el método `call()`
2. Gestión correcta de dependencias y orden de ejecución
3. Creación de datos base del sistema (roles, admin)
4. Uso de factories para datos aleatorios
5. Validación de existencia antes de crear
6. Configuración desde variables de entorno
7. Retroalimentación rica al usuario con mensajes y tablas
8. Separación de responsabilidades entre seeders
9. Implementación de idempotencia
10. Documentación clara del proceso

Este patrón de orquestación es fundamental para sistemas complejos donde múltiples tablas tienen interdependencias y el orden de creación es crítico.
