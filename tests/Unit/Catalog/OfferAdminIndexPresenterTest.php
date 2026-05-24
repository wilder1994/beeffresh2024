<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\OfferAdminIndexPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferAdminIndexPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_row_exposes_unit_prices_and_minimum(): void
    {
        $product = Product::factory()->create([
            'name' => 'Lomo fino de Res',
            'price_per_kg' => 57053,
            'price_per_lb' => 28527,
        ]);

        $offer = Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Ahorro por volumen',
            'slug' => 'ahorro-volumen',
            'image' => 'demo.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Kg->value,
            'volume_offer_price_kg' => 20000,
            'volume_offer_price_lb' => 10000,
            'is_active' => true,
        ]);

        $row = app(OfferAdminIndexPresenter::class)->volumeRow($offer);

        $this->assertSame('Lomo fino de Res', $row['product_name']);
        $this->assertSame('≥ 3 kg', $row['min_condition']);
        $this->assertSame('kg', $row['primary_unit']);
        $this->assertSame(20000.0, $row['scale_price']);
        $this->assertSame(10000.0, $row['alternate_scale_price']);
        $this->assertSame('catalog', $row['reference_tier']);
        $this->assertNotNull($row['savings_percent']);
    }

    public function test_bundle_row_keeps_pack_totals(): void
    {
        $offer = Offer::query()->create([
            'type' => OfferType::Bundle,
            'name' => 'Pack parrilla',
            'slug' => 'pack-parrilla',
            'image' => 'demo.jpg',
            'offer_price' => 89900,
            'is_active' => true,
        ]);

        $row = app(OfferAdminIndexPresenter::class)->bundleRow($offer);

        $this->assertSame(89900.0, $row['offer_total']);
        $this->assertSame($offer->id, $row['offer']->id);
    }
}
