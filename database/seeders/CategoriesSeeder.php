<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // leer las categorías desde un archivo JSON
        $json = file_get_contents(database_path('seeders/categories.json'));
        $categories = json_decode($json, true);

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'color' => $category['color'],
                    'is_active' => $category['is_active']
                ]
            );
        }
    }
}
