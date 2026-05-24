<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->orderBy('id')->get();

        if ($products->count() < 2) {
            return;
        }

        $image = $this->storeImage();

        $bundleItems = $products->take(2);

        $bundle = Offer::query()->create([
            'type' => OfferType::Bundle,
            'name' => 'Pack parrilla BEEF FRESH',
            'slug' => 'pack-parrilla-beef-fresh',
            'description' => 'Selección ideal para asado: dos cortes premium con precio especial.',
            'image' => $image,
            'offer_price' => 89900,
            'is_active' => true,
            'show_on_cinta' => true,
            'show_on_home' => true,
            'sort_order' => 0,
        ]);

        foreach ($bundleItems->values() as $index => $product) {
            $bundle->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'sale_unit' => StockUnit::Kg,
                'sort_order' => $index,
            ]);
        }

        $volumeProduct = $products->skip(2)->first() ?? $products->first();
        $volumePriceLb = round((float) $volumeProduct->price_per_lb * 0.88, 2);

        Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Ahorro por volumen · '.$volumeProduct->name,
            'slug' => 'ahorro-volumen-'.Str::slug($volumeProduct->name),
            'description' => 'Precio especial al comprar 3 lb o más.',
            'image' => $this->storeImage(),
            'product_id' => $volumeProduct->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Lb->value,
            'volume_offer_price_lb' => $volumePriceLb,
            'volume_offer_price_kg' => null,
            'is_active' => true,
            'show_on_cinta' => false,
            'show_on_home' => true,
            'sort_order' => 1,
        ]);
    }

    private function storeImage(): string
    {
        $filename = Str::uuid()->toString().'.jpg';
        $source = public_path('logos/logo.jpeg');

        Storage::disk('public')->put(
            'offers/'.$filename,
            (string) file_get_contents($source)
        );

        return $filename;
    }
}
