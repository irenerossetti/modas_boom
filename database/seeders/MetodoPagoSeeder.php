<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metodos = [
            [
                'nombre' => 'Efectivo',
                'tipo' => 'manual',
                'descripcion' => 'Pago en efectivo al momento de la entrega',
                'icono' => 'fas fa-money-bill-wave',
                'color' => '#10B981',
                'activo' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Transferencia Bancaria',
                'tipo' => 'manual',
                'descripcion' => 'Transferencia a cuenta bancaria',
                'icono' => 'fas fa-university',
                'color' => '#8B5CF6',
                'activo' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Stripe',
                'tipo' => 'automatico',
                'descripcion' => 'Pago con tarjeta de crédito/débito vía Stripe',
                'icono' => 'fab fa-stripe',
                'color' => '#3B82F6',
                'activo' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'QR Personalizado',
                'tipo' => 'qr',
                'descripcion' => 'Pago mediante código QR personalizado',
                'icono' => 'fas fa-qrcode',
                'color' => '#6366F1',
                'activo' => true,
                'orden' => 4
            ]
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::create($metodo);
        }
    }
}
