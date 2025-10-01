<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== INSERTANDO PRODUCTOS DE PRUEBA ===\n\n";

$products = [
    [
        'name' => 'iPhone 15',
        'description' => 'El último modelo de iPhone con características avanzadas.',
        'image_url' => 'https://example.com/images/iphone15.jpg',
        'price' => 999.99,
        'weight' => 0.174,
        'stock' => 5,
        'is_active' => true,
        'category_id' => 1
    ],
    [
        'name' => 'Samsung Galaxy S23',
        'description' => 'Smartphone de alta gama con pantalla AMOLED.',
        'image_url' => 'https://example.com/images/galaxy_s23.jpg',
        'price' => 899.99,
        'weight' => 0.168,
        'stock' => 30,
        'is_active' => true,
        'category_id' => 1
    ],
    [
        'name' => 'Dell XPS 13',
        'description' => 'Portátil ultraligero con rendimiento excepcional.',
        'image_url' => 'https://example.com/images/dell_xps13.jpg',
        'price' => 1199.99,
        'weight' => 1.2,
        'stock' => 20,
        'is_active' => true,
        'category_id' => 2
    ],
    [
        'name' => 'Sony WH-1000XM4',
        'description' => 'Auriculares inalámbricos con cancelación de ruido líder en la industria.',
        'image_url' => 'https://example.com/images/sony_wh1000xm4.jpg',
        'price' => 349.99,
        'weight' => 0.254,
        'stock' => 100,
        'is_active' => true,
        'category_id' => 3
    ]
];

foreach ($products as $productData) {
    $product = Product::updateOrCreate(
        ['name' => $productData['name']], // Condición de búsqueda
        $productData // Datos a actualizar o crear
    );
    if ($product->wasRecentlyCreated) {
        echo "- Creado $product\n";
    } else {
        echo "- Actualizado: $product\n";
    }
}
echo "\n=== TOTAL DE PRODUCTOS CREADOS: " . count($products) . " ===\n";
