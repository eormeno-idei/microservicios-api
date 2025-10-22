# 🎓 Tutorial: Creación de Seeders en Laravel

## 📚 Guía Paso a Paso para Estudiantes

Este tutorial te enseñará cómo crear seeders profesionales en Laravel desde cero.

---

## 🎯 ¿Qué son los Seeders?

Los **seeders** son clases especiales de Laravel que permiten poblar (llenar) la base de datos con datos de prueba de forma **automatizada** y **reproducible**.

### ¿Por qué usar Seeders?

✅ **Desarrollo rápido:** No tienes que insertar datos manualmente  
✅ **Testing:** Datos consistentes para pruebas  
✅ **Colaboración:** Todo el equipo tiene los mismos datos  
✅ **Demos:** Datos realistas para presentaciones  

---

## 📖 Conceptos Fundamentales

### 1. Crear un Seeder

```bash
php artisan make:seeder NombreDelSeeder
```

Ejemplo:
```bash
php artisan make:seeder ProductSeeder
```

Esto crea el archivo: `database/seeders/ProductSeeder.php`

### 2. Estructura Básica de un Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Aquí va tu lógica para crear datos
        Product::create([
            'name' => 'Laptop',
            'price' => 999.99,
        ]);
    }
}
```

### 3. Llamar al Seeder desde DatabaseSeeder

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call(ProductSeeder::class);
}
```

### 4. Ejecutar el Seeder

```bash
php artisan db:seed
```

---

## 🔑 Método 1: create() - Básico

**Uso:** Crear un registro siempre (puede fallar si ya existe)

```php
public function run(): void
{
    Product::create([
        'name' => 'Laptop',
        'price' => 999.99,
    ]);
}
```

**Problema:** Si ejecutas dos veces, intenta crear duplicados ❌

---

## ✅ Método 2: firstOrCreate() - Recomendado

**Uso:** Busca primero, crea solo si no existe

```php
public function run(): void
{
    Product::firstOrCreate(
        ['name' => 'Laptop'],  // Busca por este campo
        ['price' => 999.99]     // Campos adicionales si crea
    );
}
```

**Ventaja:** Puedes ejecutar múltiples veces sin errores ✅

---

## 🔄 Método 3: updateOrCreate() - Actualiza si existe

**Uso:** Actualiza si existe, crea si no existe

```php
public function run(): void
{
    Product::updateOrCreate(
        ['name' => 'Laptop'],           // Busca por este campo
        ['price' => 899.99, 'stock' => 10]  // Actualiza/Crea con estos datos
    );
}
```

---

## 📊 Ejemplo Completo: Blog System

### Paso 1: Crear los Modelos y Migraciones

```bash
php artisan make:model Category -m
php artisan make:model Article -m
```

### Paso 2: Definir las Migraciones

```php
// database/migrations/xxxx_create_categories_table.php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });
}
```

```php
// database/migrations/xxxx_create_articles_table.php
public function up(): void
{
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
}
```

### Paso 3: Ejecutar las Migraciones

```bash
php artisan migrate
```

### Paso 4: Crear los Seeders

```bash
php artisan make:seeder CategorySeeder
php artisan make:seeder ArticleSeeder
```

### Paso 5: Implementar CategorySeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tecnología', 'slug' => 'tecnologia'],
            ['name' => 'Deportes', 'slug' => 'deportes'],
            ['name' => 'Ciencia', 'slug' => 'ciencia'],
            ['name' => 'Arte', 'slug' => 'arte'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Categorías creadas exitosamente!');
    }
}
```

### Paso 6: Implementar ArticleSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan categorías
        if (Category::count() === 0) {
            $this->command->warn('No hay categorías. Ejecuta CategorySeeder primero.');
            return;
        }

        $articles = [
            [
                'category_id' => Category::where('slug', 'tecnologia')->first()->id,
                'title' => 'Inteligencia Artificial en 2025',
                'content' => 'La IA está transformando el mundo...',
            ],
            [
                'category_id' => Category::where('slug', 'deportes')->first()->id,
                'title' => 'Mundial de Fútbol 2026',
                'content' => 'Los preparativos están en marcha...',
            ],
            [
                'category_id' => Category::where('slug', 'ciencia')->first()->id,
                'title' => 'Descubrimiento en Marte',
                'content' => 'Científicos encuentran evidencia de...',
            ],
        ];

        foreach ($articles as $articleData) {
            Article::firstOrCreate(
                ['title' => $articleData['title']],
                $articleData
            );
        }

        $this->command->info('Artículos creados exitosamente!');
    }
}
```

### Paso 7: Actualizar DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders...');
        
        // IMPORTANTE: Orden correcto (dependencias primero)
        $this->call(CategorySeeder::class);
        $this->call(ArticleSeeder::class);
        
        $this->command->info('✅ Seeders completados!');
    }
}
```

### Paso 8: Ejecutar los Seeders

```bash
php artisan db:seed
```

---

## 🔗 Trabajando con Relaciones N:M

### Ejemplo: Sistema de Etiquetas (Tags)

```php
// Migration: create_article_tag_table.php
Schema::create('article_tag', function (Blueprint $table) {
    $table->foreignId('article_id')->constrained();
    $table->foreignId('tag_id')->constrained();
    $table->primary(['article_id', 'tag_id']);
});
```

### Seeder con Relaciones

```php
public function run(): void
{
    $article = Article::firstOrCreate([
        'title' => 'Mi Artículo'
    ], [
        'category_id' => 1,
        'content' => 'Contenido del artículo'
    ]);

    // Crear tags
    $tag1 = Tag::firstOrCreate(['name' => 'Laravel']);
    $tag2 = Tag::firstOrCreate(['name' => 'PHP']);
    $tag3 = Tag::firstOrCreate(['name' => 'Backend']);

    // Asociar tags al artículo (relación N:M)
    $article->tags()->syncWithoutDetaching([
        $tag1->id,
        $tag2->id,
        $tag3->id,
    ]);
}
```

**Métodos de Sincronización:**

- `sync([1,2,3])` - Reemplaza todas las relaciones
- `syncWithoutDetaching([1,2,3])` - Agrega sin eliminar existentes
- `attach([1,2,3])` - Agrega (puede crear duplicados)
- `detach([1,2])` - Elimina relaciones específicas

---

## 📝 Buenas Prácticas

### ✅ DO (Hacer)

```php
// 1. Usar firstOrCreate para prevenir duplicados
Category::firstOrCreate(['slug' => 'tech'], $data);

// 2. Verificar dependencias
if (Category::count() === 0) {
    $this->command->warn('Primero ejecuta CategorySeeder');
    return;
}

// 3. Mensajes informativos
$this->command->info('✅ Categorías creadas!');

// 4. Datos realistas y útiles
[
    'name' => 'Departamento de Comunicación',
    'description' => 'Responsable de la comunicación institucional',
]
```

### ❌ DON'T (Evitar)

```php
// 1. No usar create() sin verificar
Category::create($data); // ❌ Puede fallar con duplicados

// 2. No hardcodear IDs
'category_id' => 1, // ❌ ¿Y si ese ID no existe?

// 3. Datos irrealistas
'name' => 'Test 123', // ❌ Poco profesional
'content' => 'Lorem ipsum...', // ❌ Poco útil

// 4. Seeders sin mensajes
// ❌ El usuario no sabe qué está pasando
```

---

## 🎯 Ejercicio Práctico

**Crea un sistema de biblioteca con:**

1. **Autores** (name, nationality, birth_year)
2. **Libros** (title, author_id, pages, published_year)
3. **Géneros** (name)
4. **Relación N:M** entre Libros y Géneros

### Requisitos:

- Crear 5 autores
- Crear 10 libros (2 por autor)
- Crear 5 géneros (Ficción, Terror, Romance, Ciencia Ficción, Historia)
- Asignar 1-3 géneros a cada libro
- Usar `firstOrCreate()` en todos los seeders
- Mensajes informativos
- Verificar dependencias

### Solución:

```php
// AuthorSeeder.php
public function run(): void
{
    $authors = [
        ['name' => 'Gabriel García Márquez', 'nationality' => 'Colombiana', 'birth_year' => 1927],
        ['name' => 'Isabel Allende', 'nationality' => 'Chilena', 'birth_year' => 1942],
        ['name' => 'Stephen King', 'nationality' => 'Estadounidense', 'birth_year' => 1947],
        ['name' => 'Haruki Murakami', 'nationality' => 'Japonesa', 'birth_year' => 1949],
        ['name' => 'Chimamanda Ngozi Adichie', 'nationality' => 'Nigeriana', 'birth_year' => 1977],
    ];

    foreach ($authors as $author) {
        Author::firstOrCreate(['name' => $author['name']], $author);
    }

    $this->command->info('✅ Autores creados!');
}

// GenreSeeder.php
public function run(): void
{
    $genres = ['Ficción', 'Terror', 'Romance', 'Ciencia Ficción', 'Historia'];

    foreach ($genres as $genreName) {
        Genre::firstOrCreate(['name' => $genreName]);
    }

    $this->command->info('✅ Géneros creados!');
}

// BookSeeder.php
public function run(): void
{
    if (Author::count() === 0 || Genre::count() === 0) {
        $this->command->warn('Ejecuta AuthorSeeder y GenreSeeder primero!');
        return;
    }

    $books = [
        [
            'title' => 'Cien Años de Soledad',
            'author' => 'Gabriel García Márquez',
            'pages' => 417,
            'published_year' => 1967,
            'genres' => ['Ficción', 'Historia'],
        ],
        [
            'title' => 'El Resplandor',
            'author' => 'Stephen King',
            'pages' => 447,
            'published_year' => 1977,
            'genres' => ['Terror', 'Ficción'],
        ],
        // ... más libros
    ];

    foreach ($books as $bookData) {
        $author = Author::where('name', $bookData['author'])->first();
        
        $book = Book::firstOrCreate(
            ['title' => $bookData['title']],
            [
                'author_id' => $author->id,
                'pages' => $bookData['pages'],
                'published_year' => $bookData['published_year'],
            ]
        );

        // Asignar géneros
        $genreIds = Genre::whereIn('name', $bookData['genres'])->pluck('id');
        $book->genres()->syncWithoutDetaching($genreIds);
    }

    $this->command->info('✅ Libros creados con géneros asignados!');
}

// DatabaseSeeder.php
public function run(): void
{
    $this->command->info('🌱 Iniciando seeders de biblioteca...');
    
    $this->call([
        AuthorSeeder::class,
        GenreSeeder::class,
        BookSeeder::class,
    ]);
    
    $this->command->newLine();
    $this->command->table(
        ['Entidad', 'Cantidad'],
        [
            ['Autores', Author::count()],
            ['Géneros', Genre::count()],
            ['Libros', Book::count()],
        ]
    );
    
    $this->command->info('✅ Sistema de biblioteca poblado exitosamente!');
}
```

---

## 🚀 Comandos Útiles

```bash
# Crear un seeder
php artisan make:seeder NombreSeeder

# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=CategorySeeder

# Refrescar BD y ejecutar seeders (⚠️ BORRA TODO)
php artisan migrate:fresh --seed

# Ver lista de seeders disponibles
php artisan db:seed --help
```

---

## 📚 Recursos Adicionales

- [Documentación Oficial de Laravel - Seeding](https://laravel.com/docs/seeding)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Factories en Laravel](https://laravel.com/docs/database-testing#defining-model-factories)

---

## 🎓 Conclusión

Has aprendido:

✅ Qué son los seeders y por qué usarlos  
✅ Cómo crear seeders con `php artisan make:seeder`  
✅ Diferencia entre `create()`, `firstOrCreate()` y `updateOrCreate()`  
✅ Cómo manejar relaciones 1:N y N:M  
✅ Buenas prácticas y código profesional  
✅ Ejercicio práctico completo  

**¡Ahora estás listo para crear seeders profesionales en tus proyectos Laravel!** 🚀

---

**Profesor:** Sistema de Gestión de Contenidos  
**Nivel:** Intermedio  
**Última actualización:** Octubre 2025
