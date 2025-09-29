<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel para usar en consola
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== PRÁCTICA DE MÉTODOS ELOQUENT CON CATEGORY ===\n\n";

// 1. MÉTODO CREATE - Crear nueva categoría
echo "1. MÉTODO CREATE:\n";

// $newCategory = Category::create([
//     'name' => 'Libros',
//     'slug' => 'libros',
//     'description' => 'Libros y literatura',
//     'color' => '#6f42c1',
//     'is_active' => true
// ]);

// Para segunda y sucesivas iteraciones utilizar:
$newCategory = Category::updateOrCreate([
    'name' => 'Libros',
    'slug' => 'libros',
    'description' => 'Libros y literatura',
    'color' => '#6f42c1',
    'is_active' => true
]);

if ($newCategory->wasRecentlyCreated) {
    echo "✓ Nueva categoría creada: {$newCategory->name}\n";
} else {
    echo "✓ La categoría ya existía: {$newCategory->name}\n";
}

// 2. MÉTODO FIND - Buscar por ID
echo "2. MÉTODO FIND:\n";

$foundCategory = Category::find($newCategory->id);
if ($foundCategory) {
    echo "✓ Categoría encontrada: {$foundCategory->name} - {$foundCategory->slug}\n";
}

// Buscar múltiples IDs
$multipleCategories = Category::find([1, 2, $newCategory->id]);
echo "✓ Encontradas " . $multipleCategories->count() . " categorías por ID\n\n";

// 3. MÉTODO WHERE - Filtrar con condiciones
echo "3. MÉTODO WHERE:\n";

// Buscar por slug
$categoryBySlug = Category::where('slug', 'libros')->first();
if ($categoryBySlug) {
    echo "✓ Categoría encontrada por slug: {$categoryBySlug->name}\n";
}

// Buscar categorías activas
$activeCategories = Category::where('is_active', true)->get();
echo "✓ Categorías activas encontradas: " . $activeCategories->count() . "\n";

// Buscar con múltiples condiciones
$specificCategories = Category::where('is_active', true)
    ->where('name', 'like', '%electrón%')
    ->get();
echo "✓ Categorías que contienen 'electrón': " . $specificCategories->count() . "\n\n";

// 4. MÉTODO FIRSTORCREATE - Buscar o crear si no existe
echo "4. MÉTODO FIRSTORCREATE:\n";

// Intentar buscar una categoría que ya existe
$existingCategory = Category::firstOrCreate(
    ['slug' => 'libros'], // Condición de búsqueda
    ['name' => 'Literatura', 'color' => '#ff0000'] // Datos adicionales (no se usarán)
);
echo "✓ firstOrCreate con slug existente: {$existingCategory->name} (no se creó nueva)\n";

// Buscar o crear una categoría nueva
$newOrExisting = Category::firstOrCreate(
    ['slug' => 'musica'], // Condición de búsqueda
    [
        'name' => 'Música',
        'description' => 'Instrumentos y equipos musicales',
        'color' => '#fd7e14',
        'is_active' => true
    ] // Datos para crear si no existe
);

if ($newOrExisting->wasRecentlyCreated) {
    echo "✓ Nueva categoría creada: {$newOrExisting->name}\n";
} else {
    echo "✓ Categoría existente encontrada: {$newOrExisting->name}\n";
}
echo "\n";

// 5. MÉTODO UPDATEORCREATE - Actualizar o crear
echo "5. MÉTODO UPDATEORCREATE:\n";

// Actualizar categoría existente
$updatedCategory = Category::updateOrCreate(
    ['slug' => 'musica'], // Condición de búsqueda
    [
        'name' => 'Música y Audio',
        'description' => 'Instrumentos musicales, equipos de audio y accesorios',
        'color' => '#e83e8c'
    ] // Datos a actualizar/crear
);

echo "✓ updateOrCreate en categoría existente: {$updatedCategory->name}\n";

// Crear nueva categoría con updateOrCreate
$newCategoryUpdate = Category::updateOrCreate(
    ['slug' => 'jardineria'], // No existe, se creará
    [
        'name' => 'Jardinería',
        'description' => 'Plantas, herramientas y accesorios de jardín',
        'color' => '#28a745',
        'is_active' => true
    ]
);

echo "✓ Nueva categoría con updateOrCreate: {$newCategoryUpdate->name}\n\n";

// 6. MÉTODO SAVE - Crear con instancia
echo "6. MÉTODO SAVE:\n";

$manualCategory = Category::where('slug', 'arte-manualidades')->first();

if (!$manualCategory) {
    // Crear nueva instancia y guardar
    $manualCategory = new Category();
    $manualCategory->name = 'Arte y Manualidades';
    $manualCategory->slug = 'arte-manualidades';
    $manualCategory->description = 'Materiales para arte y proyectos creativos';
    $manualCategory->color = '#17a2b8';
    $manualCategory->is_active = true;

    $manualCategory->save();
    echo "✓ Categoría creada con save(): {$manualCategory->name} (ID: {$manualCategory->id})\n";

    // Actualizar con save()
    $manualCategory->description = 'Materiales para arte, manualidades y proyectos DIY';
    $manualCategory->save();
} else {
    echo "✓ Ya existía: {$manualCategory->name}\n";
}

// 7. RESUMEN FINAL
echo "7. RESUMEN DE CATEGORÍAS CREADAS:\n";

$allCategories = Category::orderBy('name')->get();
foreach ($allCategories as $index => $category) {
    $status = $category->is_active ? '✅ Activa' : '❌ Inactiva';
    echo sprintf(
        "%d. (%d) %s (%s) - %s - Color: %s, creada %s\n",
        $index + 1,
        $category->id,
        $category->name,
        $category->slug,
        $category->color,
        $status,
        $category->created_at->diffForHumans()
    );
}

echo "\n=== TOTAL DE CATEGORÍAS: " . $allCategories->count() . " ===\n";
echo "=== FIN DE LA PRÁCTICA ===\n";
