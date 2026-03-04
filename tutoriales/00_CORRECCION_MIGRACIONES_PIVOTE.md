# Tutorial 00: Corrección de Migraciones de Tablas Pivote

## El Problema

Laravel tiene una convención para los nombres de tablas:

- **Nombre del modelo:** Singular (Media)
- **Nombre de tabla predeterminado:** Plural (medias)

Sin embargo, cuando se usan claves foráneas con el método `constrained()` sin parámetros, Laravel intenta inferir el nombre de la tabla desde el nombre de la columna:

```php
$table->foreignId('media_id')->constrained()->onDelete('cascade');
//                                         ↑
//                            Sin parámetro: asume tabla "media" (incorrecto)
```

Laravel toma el nombre de la columna `media_id`, le quita el sufijo `_id`, y busca una tabla llamada `media` (singular), pero la tabla real se llama `medias` (plural).

## Solución Implementada

Se corrigieron dos migraciones de tablas pivote especificando explícitamente el nombre correcto de la tabla en el método `constrained()`.

### Archivo 1: create_post_medias_table.php

**Antes (incorrecto):**

```php
Schema::create('post_medias', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('media_id')->constrained()->onDelete('cascade');
    //                                         ↑
    //                            Busca tabla "media" (no existe)
    
    $table->primary(['post_id', 'media_id']);
});
```

**Después (correcto):**

```php
Schema::create('post_medias', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('media_id')->constrained('medias')->onDelete('cascade');
    //                                         ↑
    //                            Especifica tabla "medias" explícitamente
    
    $table->primary(['post_id', 'media_id']);
});
```

### Archivo 2: create_channel_medias_table.php

**Antes (incorrecto):**

```php
Schema::create('channel_medias', function (Blueprint $table) {
    $table->foreignId('channel_id')->constrained()->onDelete('cascade');
    $table->foreignId('media_id')->constrained()->onDelete('cascade');
    //                                         ↑
    //                            Busca tabla "media" (no existe)
    
    $table->primary(['channel_id', 'media_id']);
});
```

**Después (correcto):**

```php
Schema::create('channel_medias', function (Blueprint $table) {
    $table->foreignId('channel_id')->constrained()->onDelete('cascade');
    $table->foreignId('media_id')->constrained('medias')->onDelete('cascade');
    //                                         ↑
    //                            Especifica tabla "medias" explícitamente
    
    $table->primary(['channel_id', 'media_id']);
});
```

## Explicación del Método constrained()

El método `constrained()` de Laravel acepta parámetros opcionales:

```php
constrained(string $table = null, string $column = 'id')
```

**Parámetros:**

1. **$table (opcional):** Nombre de la tabla referenciada
   - Si se omite, Laravel infiere el nombre desde la columna (media_id → media)
   - Si se especifica, usa exactamente ese nombre

2. **$column (opcional):** Nombre de la columna en la tabla referenciada
   - Por defecto es 'id'
   - Raramente necesita cambiarse

**Ejemplos de uso:**

```php
// Inferencia automática (funciona si el nombre coincide)
$table->foreignId('user_id')->constrained();
// Busca tabla "users", columna "id" ✅

// Especificación explícita (recomendado para evitar ambigüedad)
$table->foreignId('media_id')->constrained('medias');
// Busca tabla "medias", columna "id" ✅

// Especificación completa (casos especiales)
$table->foreignId('author_id')->constrained('users', 'id');
// Busca tabla "users", columna "id" ✅
```

## Cuándo Usar Especificación Explícita

**Usar constrained('nombre_tabla') cuando:**

1. El nombre de la tabla no sigue la convención estándar de pluralización
2. El nombre de la columna no coincide exactamente con el nombre de la tabla
3. Hay ambigüedad en los nombres
4. Se quiere mayor claridad y mantenibilidad del código

**Ejemplo comparativo:**

```php
// ❌ Puede fallar si "media" no existe
$table->foreignId('media_id')->constrained();

// ✅ Siempre funciona, explícito y claro
$table->foreignId('media_id')->constrained('medias');
```

## Componentes de una Tabla Pivote Completa

Una tabla pivote Many-to-Many típica incluye:

```php
Schema::create('post_medias', function (Blueprint $table) {
    // 1. Clave foránea a la primera tabla
    $table->foreignId('post_id')
        ->constrained()              // Referencia tabla "posts"
        ->onDelete('cascade');       // Si se elimina el post, elimina la relación
    
    // 2. Clave foránea a la segunda tabla
    $table->foreignId('media_id')
        ->constrained('medias')      // Referencia tabla "medias" (explícito)
        ->onDelete('cascade');       // Si se elimina el media, elimina la relación
    
    // 3. Clave primaria compuesta
    $table->primary(['post_id', 'media_id']);
    // Evita duplicados: un post no puede relacionarse dos veces con el mismo media
});
```

**Explicación de onDelete('cascade'):**

- Si se elimina un registro en la tabla padre (posts o medias)
- Automáticamente se eliminan todas las relaciones en la tabla pivote
- Mantiene la integridad referencial sin registros huérfanos

## Alternativa: Especificar Tabla en el Modelo

Otra solución (aunque menos recomendada para este caso) sería especificar el nombre de la tabla en el modelo:

```php
// app/Models/Media.php
class Media extends Model
{
    protected $table = 'medias'; // ← Especifica el nombre de tabla
}
```

Sin embargo, esta solución no resuelve el problema en las migraciones porque las migraciones se ejecutan antes de que los modelos estén disponibles.

## Buenas Prácticas

Siempre especificar explícitamente el nombre de la tabla en `constrained()` cuando:

1. El nombre de la tabla usa plurales irregulares (media/medias, person/people)
2. Se trabaja en equipos donde puede haber confusión de nombres
3. Se quiere código autodocumentado y sin ambigüedades

**Patrón recomendado:**

```php
// ✅ Explícito, claro, sin errores
$table->foreignId('media_id')->constrained('medias')->onDelete('cascade');

// ⚠️ Implícito, puede fallar, menos claro
$table->foreignId('media_id')->constrained()->onDelete('cascade');
```
