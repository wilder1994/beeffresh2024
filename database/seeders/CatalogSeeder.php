<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\SaleType;
use App\Domain\Catalog\StockUnit;
use App\Domain\Catalog\TaxonomyStatus;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Res' => [
                'slug' => 'res',
                'color' => '#8B2635',
                'cuts' => [
                    'Espaldilla' => 'Corte ideal para guisos y cocción lenta.',
                    'Bola negra' => 'Magra, perfecta para milanesas y asados.',
                    'Lomo fino' => 'Corte premium para parrilla.',
                ],
            ],
            'Cerdo' => [
                'slug' => 'cerdo',
                'color' => '#E8A598',
                'cuts' => [
                    'Pernil' => 'Versátil para horno y ahumados.',
                    'Costilla' => 'Ideal para barbacoa.',
                    'Lomo' => 'Corte magro para medallones.',
                ],
            ],
            'Pollo' => [
                'slug' => 'pollo',
                'color' => '#F4D03F',
                'cuts' => [
                    'Pechuga' => 'Sin hueso, fileteada o entera.',
                    'Muslo' => 'Jugoso para horno.',
                    'Ala' => 'Para parrilla y marinados.',
                ],
            ],
            'Pescado' => [
                'slug' => 'pescado',
                'color' => '#3498DB',
                'cuts' => [
                    'Filete de tilapia' => 'Fresco del día.',
                    'Salmón' => 'Importado, por porción.',
                ],
            ],
        ];

        $skuSeq = 1;

        foreach ($catalog as $typeName => $typeData) {
            $meatType = MeatType::query()->create([
                'name' => $typeName,
                'slug' => $typeData['slug'],
                'color' => $typeData['color'],
                'status' => TaxonomyStatus::Active,
            ]);

            foreach ($typeData['cuts'] as $cutName => $cutDescription) {
                $cutSlug = Str::slug($cutName);

                $meatCut = MeatCut::query()->create([
                    'meat_type_id' => $meatType->id,
                    'name' => $cutName,
                    'slug' => $cutSlug,
                    'description' => $cutDescription,
                    'status' => TaxonomyStatus::Active,
                ]);

                $typeCode = strtoupper(Str::substr($typeData['slug'], 0, 3));
                $cutCode = strtoupper(Str::substr($cutSlug, 0, 3));
                $priceKg = fake()->randomFloat(2, 18000, 65000);

                Product::query()->create([
                    'meat_type_id' => $meatType->id,
                    'meat_cut_id' => $meatCut->id,
                    'name' => $cutName.' de '.$typeName,
                    'slug' => Str::slug($cutName.' '.$typeName),
                    'sku' => sprintf('%s-%s-%04d', $typeCode, $cutCode, $skuSeq++),
                    'description' => $cutDescription,
                    'status' => ProductStatus::Available,
                    'image' => $skuSeq <= 6 ? $this->storeProductImage() : null,
                    'price_per_kg' => $priceKg,
                    'price_per_lb' => round($priceKg / 2, 2),
                    'promo_price_kg' => $skuSeq % 4 === 0 ? round($priceKg * 0.9, 2) : null,
                    'promo_price_lb' => $skuSeq % 4 === 0 ? round($priceKg * 0.45, 2) : null,
                    'promo_start' => $skuSeq % 4 === 0 ? now()->startOfMonth() : null,
                    'promo_end' => $skuSeq % 4 === 0 ? now()->endOfMonth() : null,
                    'stock' => fake()->randomFloat(2, 8, 120),
                    'stock_unit' => StockUnit::Kg,
                    'min_stock' => 10,
                    'sale_type' => SaleType::VariableWeight,
                    'featured' => $skuSeq % 3 === 0,
                    'show_on_cinta' => $skuSeq <= 3,
                ]);
            }
        }
    }

    private function storeProductImage(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $filename = Str::uuid()->toString().'.jpg';
        $source = public_path('logos/logo.jpeg');

        Storage::disk('public')->put(
            'products/'.$filename,
            (string) file_get_contents($source)
        );

        return $cached = $filename;
    }
}
