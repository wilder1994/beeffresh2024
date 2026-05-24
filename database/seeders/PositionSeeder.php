<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Cajero', 'slug' => 'cajero', 'description' => 'Punto de venta y cobros.'],
            ['name' => 'Registro de pedidos', 'slug' => 'registro-pedidos', 'description' => 'Captura y seguimiento de pedidos.'],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'description' => 'Coordinación de operaciones.'],
            ['name' => 'Atención al cliente', 'slug' => 'atencion-cliente', 'description' => 'Consultas y soporte.'],
            ['name' => 'Inventario', 'slug' => 'inventario', 'description' => 'Stock y almacén.'],
            ['name' => 'Producción', 'slug' => 'produccion', 'description' => 'Preparación y corte.'],
            ['name' => 'Domiciliario', 'slug' => Position::SLUG_DELIVERY, 'description' => 'Entregas a domicilio.'],
            ['name' => 'Despachador', 'slug' => Position::SLUG_DISPATCH, 'description' => 'Asignación y seguimiento de entregas.'],
        ];

        foreach ($rows as $row) {
            Position::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
