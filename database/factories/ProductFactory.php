<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\SaleType;
use App\Domain\Catalog\StockUnit;
use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $priceKg = fake()->randomFloat(2, 15000, 80000);

        return [
            'meat_type_id' => MeatType::factory(),
            'meat_cut_id' => function (array $attributes) {
                $typeId = $attributes['meat_type_id'];

                return MeatCut::factory()->create([
                    'meat_type_id' => $typeId instanceof MeatType ? $typeId->getKey() : $typeId,
                ])->id;
            },
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'sku' => strtoupper(fake()->unique()->bothify('???-???-####')),
            'description' => fake()->sentence(),
            'image' => null,
            'status' => ProductStatus::Available,
            'price_per_kg' => $priceKg,
            'price_per_lb' => round($priceKg / 2, 2),
            'promo_price_kg' => null,
            'promo_price_lb' => null,
            'promo_start' => null,
            'promo_end' => null,
            'stock' => fake()->randomFloat(2, 5, 100),
            'stock_unit' => StockUnit::Kg,
            'min_stock' => 5,
            'sale_type' => SaleType::VariableWeight,
            'featured' => false,
        ];
    }
}
