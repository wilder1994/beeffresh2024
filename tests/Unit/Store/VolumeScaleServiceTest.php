<?php

declare(strict_types=1);

namespace Tests\Unit\Store;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartUnitConverter;
use App\Services\Catalog\ProductPromotionResolver;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use App\Services\Store\VolumeScaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolumeScaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private VolumeScaleService $scale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scale = new VolumeScaleService(
            new OfferAvailabilityService(new CartUnitConverter),
            new OfferPricingService(new CartUnitConverter),
            new ProductPromotionResolver,
        );
    }

    public function test_meets_minimum_across_units(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'stock' => 100,
        ]);
        $offer = $this->createVolumeOffer($product, minQty: 3, unit: StockUnit::Lb, priceLb: 19500);

        $this->assertFalse($this->scale->meetsMinimum($offer, 2, StockUnit::Lb));
        $this->assertTrue($this->scale->meetsMinimum($offer, 3, StockUnit::Lb));
        $this->assertFalse($this->scale->meetsMinimum($offer, 1, StockUnit::Kg));
        $this->assertTrue($this->scale->meetsMinimum($offer, 1.5, StockUnit::Kg));
    }

    public function test_quote_uses_promo_until_volume_threshold(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'price_per_kg' => 50000,
            'price_per_lb' => 25000,
            'promo_price_lb' => 20000,
            'promo_price_kg' => 40000,
            'promo_start' => now()->subDay(),
            'promo_end' => now()->addMonth(),
            'stock' => 100,
        ]);

        $this->createVolumeOffer($product, minQty: 3, unit: StockUnit::Lb, priceLb: 19500);

        $twoLb = $this->scale->quote($product, StockUnit::Lb, 2);
        $this->assertSame('promo', $twoLb->tier);
        $this->assertSame(20000.0, $twoLb->unitPrice);
        $this->assertStringContainsString('Te faltan', (string) $twoLb->feedbackMessage);

        $threeLb = $this->scale->quote($product, StockUnit::Lb, 3);
        $this->assertSame('volume', $threeLb->tier);
        $this->assertSame(19500.0, $threeLb->unitPrice);
        $this->assertTrue($threeLb->volumeActive);

        $tenLb = $this->scale->quote($product, StockUnit::Lb, 10);
        $this->assertSame('volume', $tenLb->tier);
        $this->assertSame(19500.0, $tenLb->unitPrice);
    }

    public function test_quote_uses_catalog_without_promo(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'price_per_lb' => 25000,
            'stock' => 100,
        ]);

        $this->createVolumeOffer($product, minQty: 3, unit: StockUnit::Lb, priceLb: 22000);

        $quote = $this->scale->quote($product, StockUnit::Lb, 2);
        $this->assertSame('catalog', $quote->tier);
        $this->assertSame(25000.0, $quote->unitPrice);

        $volume = $this->scale->quote($product, StockUnit::Lb, 3);
        $this->assertSame('volume', $volume->tier);
        $this->assertSame(22000.0, $volume->unitPrice);
    }

    public function test_validate_scale_price_rejects_equal_or_higher_than_baseline(): void
    {
        $product = Product::factory()->create([
            'price_per_lb' => 25000,
            'promo_price_lb' => 20000,
            'promo_start' => now()->subDay(),
            'promo_end' => now()->addMonth(),
        ]);

        $this->assertNotNull($this->scale->validateScaleUnitPrice($product, StockUnit::Lb, 20000));
        $this->assertNotNull($this->scale->validateScaleUnitPrice($product, StockUnit::Lb, 21000));
        $this->assertNull($this->scale->validateScaleUnitPrice($product, StockUnit::Lb, 19500));
    }

    public function test_volume_unit_price_converts_between_units(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'stock' => 100,
        ]);
        $offer = $this->createVolumeOffer($product, minQty: 3, unit: StockUnit::Lb, priceLb: 20000);

        $this->assertSame(20000.0, $this->scale->volumeUnitPriceInSaleUnit($offer, StockUnit::Lb));
        $this->assertSame(40000.0, $this->scale->volumeUnitPriceInSaleUnit($offer, StockUnit::Kg));
    }

    private function createVolumeOffer(
        Product $product,
        float $minQty,
        StockUnit $unit,
        ?float $priceLb = null,
        ?float $priceKg = null,
    ): Offer {
        return Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Escala test',
            'slug' => 'escala-test-'.$product->id,
            'image' => 'test.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => $minQty,
            'volume_sale_unit' => $unit->value,
            'volume_offer_price_lb' => $priceLb,
            'volume_offer_price_kg' => $priceKg,
            'is_active' => true,
            'show_on_home' => false,
        ]);
    }
}
