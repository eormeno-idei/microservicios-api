<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $rating = $this->faker->numberBetween(1, 5);
        $comment = $this->generateCommentByRating($rating);
        // $product = Product::query()->inRandomOrder()->first();
        // $customer = Customer::query()->inRandomOrder()->first();
        $verified = $this->faker->boolean(75); // 75% compras verificadas
        $reviewedAt = $this->faker->dateTimeBetween('-2 years', 'now');

        return [
            // 'product_id' => $product->id,
            // 'customer_id' => $customer->id,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified_purchase' => $verified,
            'reviewed_at' => $reviewedAt,
        ];
    }

    /**
     * Genera comentarios coherentes según el rating
     */
    private function generateCommentByRating(int $rating): string
    {
        $comments = [
            1 => [
                'Muy decepcionante, no cumple las expectativas.',
                'Producto de mala calidad, no lo recomiendo.',
                'Llegó defectuoso y el servicio al cliente es pésimo.',
                'No vale la pena el precio, muy decepcionado.',
                'Calidad muy por debajo de lo esperado.'
            ],
            2 => [
                'No está mal pero esperaba más por el precio.',
                'Funciona pero tiene algunos defectos menores.',
                'Cumple lo básico, aunque podría ser mejor.',
                'Por el precio esperaba mejor calidad.',
                'Aceptable pero hay mejores opciones en el mercado.'
            ],
            3 => [
                'Producto correcto, cumple su función.',
                'Está bien para el precio que tiene.',
                'Sin más, hace lo que promete.',
                'Calidad-precio aceptable, aunque mejorable.',
                'Producto estándar, nada extraordinario.'
            ],
            4 => [
                'Muy buen producto, lo recomiendo.',
                'Excelente calidad-precio, muy satisfecho.',
                'Cumple perfectamente las expectativas.',
                'Gran producto, muy contento con la compra.',
                'Muy buena calidad, llegó rápido y bien empacado.'
            ],
            5 => [
                '¡Excelente! Superó todas mis expectativas.',
                'Producto fantástico, calidad premium.',
                'Perfecto en todos los aspectos, 100% recomendado.',
                '¡Increíble! La mejor compra que he hecho.',
                'Calidad excepcional, volveré a comprar sin duda.'
            ]
        ];

        $ratingComments = $comments[$rating];
        return $this->faker->randomElement($ratingComments);
    }
}
