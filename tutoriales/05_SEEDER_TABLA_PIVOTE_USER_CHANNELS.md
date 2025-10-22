# Tutorial 05: Seeder de Tabla Pivote con Query Builder

## Objetivo

Aprender a poblar directamente una tabla pivote (intermedia) en una relación N:M utilizando Query Builder en lugar de los métodos de relación de Eloquent.

## Contexto Teórico

### Tablas Pivote en Relaciones N:M

En una relación muchos a muchos, la tabla pivote almacena las asociaciones entre dos entidades. Esta tabla típicamente:

- Contiene las claves foráneas de ambas tablas
- Puede tener campos adicionales como timestamps
- No suele tener un modelo Eloquent propio

**Estructura de user_channels:**

```
user_channels
  user_id (FK → users.id)
  channel_id (FK → channels.id)
  created_at
  updated_at
```

### Query Builder vs Eloquent

Hay dos formas de insertar en una tabla pivote:

**Método 1: A través de la relación Eloquent**
```php
$user->channels()->attach([1, 2, 3]);
```

**Método 2: Query Builder directo**
```php
DB::table('user_channels')->insert([
    'user_id' => 1,
    'channel_id' => 2,
]);
```

**¿Cuándo usar Query Builder directo?**

- Cuando se necesita control preciso sobre los datos insertados
- Para operaciones masivas más eficientes
- Cuando no se tiene cargado el modelo padre
- Para seeders de tablas pivote con lógica específica

### El método insertOrIgnore()

Laravel proporciona `insertOrIgnore()` que:

- Intenta insertar el registro
- Si existe (por unique constraint), lo ignora sin lanzar error
- Es equivalente a INSERT IGNORE en MySQL

```php
DB::table('user_channels')->insertOrIgnore([
    'user_id' => 1,
    'channel_id' => 2,
]);
```

**Ventaja:** Idempotencia sin necesidad de buscar antes de insertar.

**Requisito:** La tabla debe tener restricciones unique o primary key compuestas.

## Análisis del UserChannelSeeder

Este seeder establece qué usuarios tienen acceso o responsabilidad sobre qué canales.

### Lógica de negocio implementada:

1. El usuario administrador debe tener acceso a todos los canales
2. Los usuarios regulares deben ser asignados a 2-4 canales aleatorios
3. No debe haber duplicados en las asignaciones

### Paso 1: Estructura de la clase

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserChannelSeeder extends Seeder
{
    public function run(): void
    {
        // Implementación
    }
}
```

**Nota:** Se importa `DB` de `Illuminate\Support\Facades\DB` para usar Query Builder.

### Paso 2: Obtener y validar datos

```php
$users = User::all();
$channels = Channel::all();

if ($users->isEmpty() || $channels->isEmpty()) {
    $this->command->warn('No users or channels found. Please run previous seeders first.');
    return;
}
```

Esta validación asegura que existen los registros necesarios antes de intentar crear relaciones.

### Paso 3: Asignar admin a todos los canales

```php
$adminUser = User::role('admin')->first();

if ($adminUser) {
    foreach ($channels as $channel) {
        DB::table('user_channels')->insertOrIgnore([
            'user_id' => $adminUser->id,
            'channel_id' => $channel->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    $this->command->info("Admin user assigned to all channels.");
}
```

**Análisis:**

1. Se obtiene el primer usuario con rol admin usando Spatie Permission
2. Se itera sobre todos los canales
3. Para cada canal, se inserta una relación con el admin
4. Se usa `insertOrIgnore()` para evitar errores si ya existe
5. Se establecen manualmente `created_at` y `updated_at` (Query Builder no lo hace automáticamente)
6. Se informa al usuario cuando termina

**Función now():**

La función helper `now()` de Laravel devuelve una instancia de Carbon con la fecha y hora actual. Es equivalente a `Carbon::now()`.

### Paso 4: Asignar usuarios regulares a canales aleatorios

```php
$regularUsers = User::role('user')->get();

foreach ($regularUsers as $user) {
    $randomChannels = $channels->random(rand(2, min(4, $channels->count())));
    
    foreach ($randomChannels as $channel) {
        DB::table('user_channels')->insertOrIgnore([
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
```

**Análisis detallado:**

1. **Obtener usuarios regulares:**
   ```php
   User::role('user')->get()
   ```
   Usa Spatie Permission para filtrar solo usuarios con rol 'user'.

2. **Calcular cantidad de canales:**
   ```php
   rand(2, min(4, $channels->count()))
   ```
   - `rand(2, 4)`: Entre 2 y 4 canales
   - `min(4, $channels->count())`: Asegura no pedir más canales de los que existen
   - Si hay solo 3 canales, cada usuario tendrá entre 2 y 3

3. **Seleccionar canales aleatorios:**
   ```php
   $channels->random($cantidad)
   ```
   Devuelve una colección con elementos únicos aleatorios.

4. **Insertar cada relación:**
   - Se itera sobre los canales seleccionados
   - Se inserta cada combinación user-channel
   - `insertOrIgnore()` previene duplicados

## Código Completo del UserChannelSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder asigna usuarios a canales para establecer
     * las relaciones de pertenencia/responsabilidad.
     */
    public function run(): void
    {
        $users = User::all();
        $channels = Channel::all();

        if ($users->isEmpty() || $channels->isEmpty()) {
            $this->command->warn('No users or channels found. Please run previous seeders first.');
            return;
        }

        // Asignar el admin a todos los canales
        $adminUser = User::role('admin')->first();
        if ($adminUser) {
            foreach ($channels as $channel) {
                DB::table('user_channels')->insertOrIgnore([
                    'user_id' => $adminUser->id,
                    'channel_id' => $channel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("Admin user assigned to all channels.");
        }

        // Asignar usuarios regulares a canales aleatorios (2-4 canales por usuario)
        $regularUsers = User::role('user')->get();
        foreach ($regularUsers as $user) {
            $randomChannels = $channels->random(rand(2, min(4, $channels->count())));

            foreach ($randomChannels as $channel) {
                DB::table('user_channels')->insertOrIgnore([
                    'user_id' => $user->id,
                    'channel_id' => $channel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('User-Channel relationships seeded successfully!');
    }
}
```

## Alternativa con Eloquent

Este mismo seeder podría implementarse usando solo Eloquent:

```php
$adminUser = User::role('admin')->first();
if ($adminUser) {
    $channelIds = $channels->pluck('id')->toArray();
    $adminUser->channels()->syncWithoutDetaching($channelIds);
}

$regularUsers = User::role('user')->get();
foreach ($regularUsers as $user) {
    $randomChannelIds = $channels->random(rand(2, min(4, $channels->count())))
                                 ->pluck('id')
                                 ->toArray();
    $user->channels()->syncWithoutDetaching($randomChannelIds);
}
```

**Comparación:**

| Aspecto | Query Builder | Eloquent |
|---------|---------------|----------|
| Código | Más verboso | Más conciso |
| Control | Mayor control | Abstracción mayor |
| Performance | Ligeramente más rápido | Ligeramente más lento |
| Timestamps | Manual | Automático con withTimestamps() |
| Legibilidad | Menos expresivo | Más expresivo |

**Conclusión:** En este seeder se usa Query Builder para demostrar el control directo sobre la tabla pivote, pero Eloquent sería igualmente válido.

## Configuración de la Tabla Pivote

Para que `insertOrIgnore()` funcione correctamente, la tabla debe tener la restricción adecuada:

```php
// En la migración create_user_channels_table.php
Schema::create('user_channels', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('channel_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    
    // Clave primaria compuesta previene duplicados
    $table->primary(['user_id', 'channel_id']);
});
```

La primary key compuesta asegura que no puede haber duplicados de la combinación user-channel.

## Orden de Ejecución

Este seeder debe ejecutarse después de:

1. UserSeeder (crea usuarios y roles)
2. ChannelSeeder (crea canales)

El DatabaseSeeder lo ejecuta en la posición correcta.

## Resultados Esperados

Con 11 usuarios (1 admin + 10 regulares) y 13 canales:

- Admin: 13 relaciones (una con cada canal)
- Cada usuario regular: 2-4 relaciones
- Total aproximado: 13 + (10 × 3) = 43 relaciones

## Verificación de Resultados

En Tinker:

```php
// Ver canales del admin
$admin = User::role('admin')->first();
$admin->channels()->count();  // Debe ser 13

// Ver canales de un usuario regular
$user = User::role('user')->first();
$user->channels;

// Ver usuarios de un canal
$channel = Channel::first();
$channel->users;

// Total de relaciones
DB::table('user_channels')->count();
```

## Consideración: Limpieza Opcional

El seeder incluye una línea comentada para limpiar datos:

```php
// DB::table('user_channels')->truncate();
```

**Cuándo descomentar:**
- Si deseas eliminar todas las relaciones antes de recrearlas
- En entornos de desarrollo cuando cambias la lógica

**Cuándo NO usar:**
- En producción (eliminaría datos reales)
- Si el seeder debe ser idempotente (ejecutable múltiples veces)

Con `insertOrIgnore()`, no es necesario truncar porque los duplicados se ignoran automáticamente.

## Resumen

Este seeder demuestra:

1. Uso de Query Builder directo para tablas pivote
2. El método `insertOrIgnore()` para idempotencia
3. Asignación manual de timestamps
4. Lógica de negocio específica (admin a todos, regulares aleatorio)
5. Uso de Spatie Permission para filtrar por roles
6. Selección aleatoria controlada con validación de cantidad
7. Mensajes informativos por etapa

Este patrón es útil cuando necesitas control preciso sobre cómo se pueblan las relaciones N:M, especialmente con lógica de negocio específica.
