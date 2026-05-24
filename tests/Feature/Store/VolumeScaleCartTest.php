<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolumeScaleCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_applies_volume_price_when_minimum_met(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'price_per_lb' => 25000,
            'stock' => 100,
        ]);

        Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Escala',
            'slug' => 'escala-cart',
            'image' => 'offers/test.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Lb->value,
            'volume_offer_price_lb' => 22000,
            'is_active' => true,
        ]);

        $this->postJson(route('carrito.agregar'), [
            'product_id' => $product->id,
            'sale_unit' => 'lb',
            'cantidad' => 3,
        ])->assertOk();

        $response = $this->get(route('carrito.ver'));
        $response->assertOk();
        $response->assertSee('Oferta por volumen aplicada');
        $response->assertSee('$22.000');
    }
}
