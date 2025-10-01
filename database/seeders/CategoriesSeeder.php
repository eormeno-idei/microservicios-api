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

        $nuevos = 0;
        $actualizados = 0;

        foreach ($categories as $category) {
            $categoria = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'color' => $category['color'],
                    'is_active' => $category['is_active']
                ]
            );
            if ($categoria->wasRecentlyCreated) {
                $nuevos++;
            } elseif ($categoria->wasChanged()) {
                $actualizados++;
            }
        }

        if ($actualizados > 0 || $nuevos > 0) {
            echo "Categorías nuevas: $nuevos, actualizadas: $actualizados\n";
        }
    }
}
