<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\ProductStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicCatalogViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_index_uses_compact_home_product_cards(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'status' => ProductStatus::Available,
            'stock' => 25,
            'image' => 'products/demo.jpg',
            'price_per_kg' => 50000,
            'price_per_lb' => 25000,
        ]);

        Storage::disk('public')->put('products/demo.jpg', 'fake');

        $this->get(route('products.public.index'))
            ->assertOk()
            ->assertSee('bf-home-products__grid', false)
            ->assertSee('bf-home-product-card', false)
            ->assertSee($product->name)
            ->assertSee('Ver producto', false)
            ->assertDontSee('data-product-purchase', false);
    }

    public function test_catalog_product_show_includes_purchase_block(): void
    {
        $product = Product::factory()->create([
            'status' => ProductStatus::Available,
            'stock' => 10,
            'price_per_kg' => 40000,
            'price_per_lb' => 20000,
        ]);

        $this->get(route('products.public.show', $product))
            ->assertOk()
            ->assertSee('data-product-purchase', false)
            ->assertSee('bf-store-product-detail', false);
    }
}
