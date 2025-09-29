<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Product;
use App\Models\Review;

$iPhone = Product::where('name', 'like', 'iPhone 15')->first();
$samsung = Product::where('name', 'like', '%Samsung%')->first();
$maru = Customer::where('email', 'like', 'mscheffer@hotmail.com')->first();
$john = Customer::where('email', 'like', 'jdoe@hotmail.com')->first();

echo json_encode($maru, JSON_PRETTY_PRINT) . "\n";
echo json_encode($john, JSON_PRETTY_PRINT) . "\n";

echo "Producto: $iPhone->description\n";
echo "Producto: $samsung->description\n";
echo "Cliente: $maru->first_name\n";
echo "Cliente: $john->first_name\n";

$reseña = Review::where('product_id', $iPhone->id)
    ->where('customer_id', $maru->id)
    ->first();

if ($reseña) {
    $reseña->delete();
}

// Primera forma de crear una reseña

$reseña = new Review();
$reseña->product()->associate($iPhone);
$reseña->customer()->associate($maru);
$reseña->rating = 5;
$reseña->comment = "Excelente teléfono, muy recomendable.";
$reseña->is_verified_purchase = true;
$reseña->reviewed_at = now();
$reseña->save();

echo "Reseña creada: $reseña\n";

// Segunda forma de crear una reseña
$reseña = Review::updateOrCreate(
    [
        'product_id' => $iPhone->id,
        'customer_id' => $john->id,
    ],
    [
        'rating' => 4,
        'comment' => "Buen teléfono, pero la batería podría ser mejor.",
        'is_verified_purchase' => true,
        'reviewed_at' => now(),
    ]
);

echo "Reseña creada: $reseña\n";

// Listar reseñas de un producto
echo "\nReseñas para {$iPhone->name}:\n";
$reseñas = $iPhone->reviews()->with('customer')->get();

foreach ($reseñas as $r) {
    echo "$r\n";
}

echo "Cantidad de reseñas: " . $iPhone->reviewsCount() . "\n";
echo "Promedio de rating: " . number_format($iPhone->averageRating(), 1) . "\n";
