<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 0;
        for ($i = 0; $i < 50; $i++) {
            $product = Product::query()->inRandomOrder()->first();
            $customer = Customer::query()->inRandomOrder()->first();

            // Verificar que no existe ya una reseña
            $exists = Review::query()
                ->where('product_id', $product->id)
                ->where('customer_id', $customer->id)
                ->exists();

            if (!$exists) {
                Review::factory()
                    ->create([
                        'product_id' => $product->id,
                        'customer_id' => $customer->id,
                    ]);
                $count++;
            }
        }
        $this->command->info("Se crearon {$count} reseñas aleatorias.");
    }
}
