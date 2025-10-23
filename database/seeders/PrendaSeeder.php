<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar prendas existentes
        \App\Models\Prenda::truncate();

        $prendas = [
            // ===== CATEGORÍA FORMAL (5 prendas con imágenes reales) =====
            [
                'nombre' => 'Traje Ejecutivo Clásico',
                'descripcion' => 'Elegancia clásica para el mundo corporativo. Traje completo de alta calidad con corte tradicional.',
                'precio' => 350.00,
                'categoria' => 'Formal',
                'imagen' => 'images/editados/Formal/Generated Image October 02, 2025 - 10_03AM.png',
                'colores' => ['Negro', 'Azul Marino', 'Gris Oscuro', 'Carbón'],
                'tallas' => ['S', 'M', 'L', 'XL', 'XXL'],
                'activo' => true,
                'stock' => 50
            ],
            [
                'nombre' => 'Vestido de Noche Elegante',
                'descripcion' => 'Sofisticación para eventos especiales. Diseño elegante y moderno para ocasiones formales.',
                'precio' => 280.00,
                'categoria' => 'Formal',
                'imagen' => 'images/editados/Formal/Generated Image October 02, 2025 - 10_27AM.png',
                'colores' => ['Negro', 'Azul Medianoche', 'Rojo Elegante', 'Verde Esmeralda'],
                'tallas' => ['XS', 'S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 30
            ],
            [
                'nombre' => 'Blazer Moderno Profesional',
                'descripcion' => 'Estilo contemporáneo con toque clásico. Perfecto para el trabajo y reuniones importantes.',
                'precio' => 220.00,
                'categoria' => 'Formal',
                'imagen' => 'images/editados/Formal/Generated Image October 02, 2025 - 10_29AM.png',
                'colores' => ['Negro', 'Gris', 'Azul Marino', 'Beige'],
                'tallas' => ['S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 40
            ],
            [
                'nombre' => 'Conjunto Ejecutivo Premium',
                'descripcion' => 'Profesionalismo y comodidad. Conjunto completo para la oficina con acabados de lujo.',
                'precio' => 320.00,
                'categoria' => 'Formal',
                'imagen' => 'images/editados/Formal/Generated Image October 02, 2025 - 10_37AM.png',
                'colores' => ['Negro', 'Gris Carbón', 'Azul Oscuro', 'Marrón'],
                'tallas' => ['S', 'M', 'L', 'XL', 'XXL'],
                'activo' => true,
                'stock' => 35
            ],
            [
                'nombre' => 'Traje de Gala Exclusivo',
                'descripcion' => 'Para ocasiones especiales y eventos de gala. Diseño exclusivo con detalles únicos.',
                'precio' => 420.00,
                'categoria' => 'Formal',
                'imagen' => 'images/editados/Formal/Generated Image October 02, 2025 - 10_44AM.png',
                'colores' => ['Negro', 'Dorado', 'Plateado', 'Azul Real'],
                'tallas' => ['XS', 'S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 15
            ],

            // ===== CATEGORÍA INFORMAL (4 prendas con imágenes reales) =====
            [
                'nombre' => 'Casual Chic Urbano',
                'descripcion' => 'Estilo urbano con toque elegante. Perfecto para el día a día con un look sofisticado.',
                'precio' => 180.00,
                'categoria' => 'Informal',
                'imagen' => 'images/editados/Informal/Generated Image October 02, 2025 - 10_06AM.png',
                'colores' => ['Beige', 'Rosa Pálido', 'Blanco', 'Gris Claro', 'Crema'],
                'tallas' => ['XS', 'S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 60
            ],
            [
                'nombre' => 'Street Style Moderno',
                'descripcion' => 'Tendencias urbanas modernas. Comodidad y estilo juvenil para el día a día.',
                'precio' => 150.00,
                'categoria' => 'Informal',
                'imagen' => 'images/editados/Informal/Generated Image October 02, 2025 - 10_23AM.png',
                'colores' => ['Negro', 'Blanco', 'Gris', 'Azul Denim', 'Verde Militar'],
                'tallas' => ['S', 'M', 'L', 'XL', 'XXL'],
                'activo' => true,
                'stock' => 45
            ],
            [
                'nombre' => 'Boho Casual Libre',
                'descripcion' => 'Libertad y comodidad expresiva. Estilo bohemio moderno con telas fluidas.',
                'precio' => 160.00,
                'categoria' => 'Informal',
                'imagen' => 'images/editados/Informal/Generated Image October 02, 2025 - 10_40AM.png',
                'colores' => ['Beige', 'Terracota', 'Verde Oliva', 'Crema', 'Mostaza'],
                'tallas' => ['S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 25
            ],
            [
                'nombre' => 'Weekend Vibes Relajado',
                'descripcion' => 'Relajado pero con estilo. Perfecto para fines de semana y momentos de descanso.',
                'precio' => 140.00,
                'categoria' => 'Informal',
                'imagen' => 'images/editados/Informal/Generated Image September 22, 2025 - 6_44PM.png',
                'colores' => ['Azul Claro', 'Blanco', 'Rosa Suave', 'Gris Perla', 'Lavanda'],
                'tallas' => ['XS', 'S', 'M', 'L', 'XL'],
                'activo' => true,
                'stock' => 55
            ]
        ];

        foreach ($prendas as $prenda) {
            \App\Models\Prenda::create($prenda);
        }

        // Mensaje de confirmación
        $categorias = collect($prendas)->groupBy('categoria');
        echo "✅ Se han creado " . count($prendas) . " prendas con imágenes reales:\n";
        foreach ($categorias as $categoria => $items) {
            echo "   - {$categoria}: " . count($items) . " prendas\n";
        }
        echo "   Total: " . count($prendas) . " prendas con imágenes locales\n";
        echo "   ¡Solo productos con imágenes reales del proyecto!\n";
    }
}
