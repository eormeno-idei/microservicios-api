<?php

require_once 'vendor/autoload.php';

// Configurar la aplicaacion Laravel para usar en consola
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;

echo "=== PRACTICA DE MODELOS ELOQUENT ===\n\n";

/*

El equivalente en SQL sería:
INSERT INTO categories (name, slug, description, color, is_active, created_at, updated_at)
VALUES ('Electrónica', 'electronica', 'Dispositivos electrónicos y gadgets', '#FF5733', true, NOW(), NOW());

*/

// Usar firstOrCreate correctamente: buscar por campo único, crear con todos los datos
$categoria_electronica = Category::firstOrCreate(
    ['slug' => 'electronica'], // Criterio de búsqueda
    [
        'name' => 'Electrónica',
        'description' => 'Dispositivos electrónicos y gadgets',
        'color' => '#FF5733',
        'is_active' => true
    ]
);

$categoria_computacion = Category::firstOrCreate(
    ['slug' => 'computacion'], // Criterio de búsqueda
    [
        'name' => 'Computación',
        'description' => 'Todo sobre computadoras y accesorios',
        'color' => '#33FF57',
        'is_active' => true
    ]
);

Category::firstOrCreate([
    'name' => 'Hogar',
    'slug' => 'hogar',
    'description' => 'Artículos para el hogar y decoración',
    'color' => '#3357FF',
    'is_active' => true
]);

//desactivar la categoria 2
$cat1 = Category::where('slug', 'hogar')->first();
if ($cat1) {
    $cat1->is_active = false;
    $cat1->save();
}

/* el equivalente en SQL del segmento anterior seria:
UPDATE categories
SET is_active = false
WHERE id = 2;
*/

/*
El equivalente en SQL sería:
SELECT * FROM categories;
*/
$categorias = Category::all();


foreach ($categorias as $categoria) {
    echo " {$categoria->name}: {$categoria->description} " .
     ($categoria->is_active ? 'Activa' : 'No Activa') . " \n";
}

