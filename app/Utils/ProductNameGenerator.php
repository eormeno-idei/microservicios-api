<?php

namespace App\Utils;

class ProductNameGenerator
{
    /**
     * Genera un nombre de producto coherente con su descripción
     *
     * @return array Con 'name' y 'description'
     */
    public static function generateProductData(): array
    {
        $products = [
            [
                'name' => 'Smartphone Pro Max',
                'description' => 'Tecnología avanzada al mejor precio del mercado.'
            ],
            [
                'name' => 'Auriculares Bluetooth Premium',
                'description' => 'Diseño ergonómico pensado para la comodidad del usuario.'
            ],
            [
                'name' => 'Laptop UltraBook Elite',
                'description' => 'Ideal para profesionales y usuarios exigentes.'
            ],
            [
                'name' => 'Cafetera Espresso Deluxe',
                'description' => 'Fácil de usar y mantener, perfecto para principiantes.'
            ],
            [
                'name' => 'Monitor Gaming 4K',
                'description' => 'Compatible con todos los sistemas modernos.'
            ],
            [
                'name' => 'Reloj Inteligente Sport',
                'description' => 'Resistente al agua y condiciones extremas.'
            ],
            [
                'name' => 'Cargador Solar Portátil',
                'description' => 'Eco-friendly y sostenible con el medio ambiente.'
            ],
            [
                'name' => 'Aspiradora Robot Smart',
                'description' => 'Bajo consumo energético y alta eficiencia.'
            ],
            [
                'name' => 'Tablet Gráfica Professional',
                'description' => 'Recomendado por expertos en la industria.'
            ],
            [
                'name' => 'Aire Acondicionado Split',
                'description' => 'Instalación rápida y sencilla en pocos minutos.'
            ],
            [
                'name' => 'Cámara Reflex Digital',
                'description' => 'Producto de alta calidad con acabados premium.'
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'description' => 'Disponible en múltiples colores y tamaños.'
            ],
            [
                'name' => 'Microondas Inverter',
                'description' => 'Certificado bajo estrictos estándares de calidad.'
            ],
            [
                'name' => 'Bicicleta Eléctrica Urban',
                'description' => 'Excelente relación calidad-precio garantizada.'
            ],
            [
                'name' => 'Proyector LED 1080p',
                'description' => 'Incluye accesorios complementarios sin costo adicional.'
            ],
            [
                'name' => 'Silla Gamer Ergonómica',
                'description' => 'Diseño innovador y funcional para uso diario.'
            ],
            [
                'name' => 'Router WiFi 6 Mesh',
                'description' => 'Con garantía extendida y soporte técnico incluido.'
            ],
            [
                'name' => 'Licuadora Alta Potencia',
                'description' => 'Fabricado con materiales resistentes y duraderos.'
            ],
            [
                'name' => 'Drone Cámara 4K',
                'description' => 'Producto bestseller en su categoría.'
            ],
            [
                'name' => 'Altavoz Bluetooth Waterproof',
                'description' => 'Envío gratuito a todo el país en 24-48 horas.'
            ]
        ];

        return $products[array_rand($products)];
    }
}
