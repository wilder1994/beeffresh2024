<?php

declare(strict_types=1);

namespace Tests\Unit\Store;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartUnitConverter;
use App\Services\Store\OfferPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private OfferPricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricing = new OfferPricingService(new CartUnitConverter);
    }

    public function test_storefront_card_prices_for_volume_uses_unit_prices_not_totals(): void
    {
        $product = Product::factory()->create([
            'price_per_kg' => 57053,
            'price_per_lb' => 28526.5,
        ]);

        $offer = Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Promo por cantidad',
            'slug' => 'promo-por-cantidad',
            'image' => 'demo.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Kg->value,
            'volume_offer_price_kg' => 50207,
            'volume_offer_price_lb' => 25103.5,
            'is_active' => true,
        ]);

        $prices = $this->pricing->storefrontCardPrices($offer);

        $this->assertSame(57053.0, $prices['reference']);
        $this->assertSame(50207.0, $prices['offer']);
        $this->assertSame('/kg', $prices['unit_suffix']);
        $this->assertSame('Precio especial al comprar 3 kg o más.', $prices['volume_summary']);
        $this->assertSame('$50.207/kg', $this->pricing->storefrontPriceLabel($offer));
    }

    public function test_storefront_card_prices_for_volume_respects_lb_unit(): void
    {
        $product = Product::factory()->create([
            'price_per_kg' => 57053,
            'price_per_lb' => 28527,
        ]);

        $offer = Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Promo por libra',
            'slug' => 'promo-por-libra',
            'image' => 'demo.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Lb->value,
            'volume_offer_price_lb' => 25103.52,
            'is_active' => true,
        ]);

        $prices = $this->pricing->storefrontCardPrices($offer);

        $this->assertSame(28527.0, $prices['reference']);
        $this->assertSame(25103.52, $prices['offer']);
        $this->assertSame('/lb', $prices['unit_suffix']);
        $this->assertSame('Precio especial al comprar 3 lb o más.', $prices['volume_summary']);
        $this->assertSame('$25.104/lb', $this->pricing->storefrontPriceLabel($offer));
    }

    public function test_storefront_card_prices_for_bundle_uses_pack_totals(): void
    {
        $product = Product::factory()->create([
            'price_per_kg' => 40000,
            'price_per_lb' => 20000,
        ]);

        $offer = Offer::query()->create([
            'type' => OfferType::Bundle,
            'name' => 'Pack parrillero',
            'slug' => 'pack-parrillero',
            'image' => 'pack.jpg',
            'offer_price' => 95000,
            'is_active' => true,
        ]);

        $offer->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'sale_unit' => StockUnit::Kg->value,
        ]);

        $prices = $this->pricing->storefrontCardPrices($offer);

        $this->assertSame(80000.0, $prices['reference']);
        $this->assertSame(95000.0, $prices['offer']);
        $this->assertNull($prices['unit_suffix']);
        $this->assertNull($prices['volume_summary']);
        $this->assertSame('$95.000', $this->pricing->storefrontPriceLabel($offer));
    }
}
