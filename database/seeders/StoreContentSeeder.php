<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StoreBanner;
use App\Models\StoreHighlight;
use Illuminate\Database\Seeder;

class StoreContentSeeder extends Seeder
{
    public function run(): void
    {
        StoreBanner::query()->create([
            'title' => 'Fin de semana parrillero',
            'description' => '15% en cortes seleccionados para la parrilla. Válido sábado y domingo.',
            'image' => null,
            'link' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        StoreBanner::query()->create([
            'title' => 'Despensa familiar',
            'description' => 'Combos de carne molida, pollo y cerdo con precio especial.',
            'image' => null,
            'link' => null,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $highlights = [
            ['title' => 'Lomo fino', 'description' => 'Corte premium para ocasiones especiales.'],
            ['title' => 'Costilla BBQ', 'description' => 'Marinada lista para la parrilla.'],
            ['title' => 'Pechuga de pollo', 'description' => 'Fileteada, sin piel.'],
            ['title' => 'Pernil entero', 'description' => 'Ideal para horno y festividades.'],
        ];

        foreach ($highlights as $index => $highlight) {
            StoreHighlight::query()->create([
                'title' => $highlight['title'],
                'description' => $highlight['description'],
                'image' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
