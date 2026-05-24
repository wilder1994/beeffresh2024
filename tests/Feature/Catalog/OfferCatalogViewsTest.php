<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferCatalogViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundles_and_volumes_listings_are_separate(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create();

        Offer::query()->create([
            'type' => OfferType::Bundle,
            'name' => 'Pack parrilla',
            'slug' => 'pack-parrilla',
            'image' => 'demo.jpg',
            'offer_price' => 89900,
            'is_active' => true,
        ]);

        Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Ahorro por volumen',
            'slug' => 'ahorro-volumen',
            'image' => 'demo.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Lb->value,
            'volume_offer_price_lb' => 20000,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('catalog.offers.bundles'))
            ->assertOk()
            ->assertSee('Pack parrilla')
            ->assertDontSee($product->name);

        $this->actingAs($user)
            ->get(route('catalog.offers.volumes'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertDontSee('Pack parrilla');
    }
}
