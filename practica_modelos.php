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

Category::firstOrCreate([
    'name' => 'Electrónica',
    'slug' => 'electronica',
    'description' => 'Dispositivos electrónicos y gadgets',
    'color' => '#FF5733',
    'is_active' => true
]);

Category::firstOrCreate([

    'name' => 'Computación',
    'slug' => 'computacion',
    'description' => 'Todo sobre computadoras y accesorios',
    'color' => '#33FF57',
    'is_active' => true
]);

Category::firstOrCreate([
    'name' => 'Hogar',
    'slug' => 'hogar',
    'description' => 'Artículos para el hogar y decoración',
    'color' => '#3357FF',
    'is_active' => true
]);

//desactivar la categoria 2
$category = Category::find(2);
if ($category) {
    $category->is_active = false;
    $category->save();
}

/*
El equivalente en SQL sería:
SELECT * FROM categories;
*/
$categorias = Category::all();


foreach ($categorias as $categoria) {
    echo " {$categoria->name}: {$categoria->description} " .
     ($categoria->is_active ? 'Activa' : 'No Activa') . " \n";
}

