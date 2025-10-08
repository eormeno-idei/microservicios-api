<?php

namespace Database\Factories;

use App\Models\Category;
use App\Utils\ProductNameGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomCategoryId = Category::query()->inRandomOrder()->first()->id ?? 1;
        $productData = ProductNameGenerator::generateProductData();

        return [
            'name' => $productData['name'],
            'price'=> $this->faker->randomFloat(2,50,100),
            'description' => $productData['description'],
            'image_url' => $this->faker->imageUrl(),
            'weight' => $this->faker->randomFloat(2,0,100),
            'stock' => $this->faker->numberBetween(0,1000),
            'is_active' => $this->faker->boolean(80), // 80%
            'category_id' => $randomCategoryId,
        ];
    }
}
