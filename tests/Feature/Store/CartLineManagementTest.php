<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartLineManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_product_quantity_in_cart(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'price_per_lb' => 25000,
            'stock' => 100,
        ]);

        $lineKey = 'product:'.$product->id.':lb';

        session(['carrito' => [
            $lineKey => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'nombre' => $product->name,
                'precio' => 25000,
                'cantidad' => 1,
            ],
        ]]);

        $this->from(route('carrito.ver'))
            ->patch(route('carrito.linea.actualizar'), [
                'line_key' => $lineKey,
                'cantidad' => 3,
            ])
            ->assertRedirect(route('carrito.ver'))
            ->assertSessionHas('success');

        $cart = session('carrito');
        $this->assertSame(3.0, (float) $cart[$lineKey]['cantidad']);
    }

    public function test_user_can_remove_line_from_cart(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'stock' => 10,
        ]);

        $lineKey = 'product:'.$product->id.':kg';

        session(['carrito' => [
            $lineKey => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'kg',
                'nombre' => $product->name,
                'precio' => 50000,
                'cantidad' => 2,
            ],
        ]]);

        $this->from(route('carrito.ver'))
            ->delete(route('carrito.linea.eliminar'), [
                'line_key' => $lineKey,
            ])
            ->assertRedirect(route('carrito.ver'))
            ->assertSessionHas('success');

        $this->assertSame([], session('carrito'));
    }

    public function test_updating_quantity_recalculates_volume_pricing(): void
    {
        $product = Product::factory()->create([
            'image' => 'products/test.jpg',
            'price_per_lb' => 25000,
            'stock' => 100,
        ]);

        Offer::query()->create([
            'type' => OfferType::Volume,
            'name' => 'Escala',
            'slug' => 'escala-update',
            'image' => 'offers/test.jpg',
            'product_id' => $product->id,
            'volume_min_quantity' => 3,
            'volume_sale_unit' => StockUnit::Lb->value,
            'volume_offer_price_lb' => 22000,
            'is_active' => true,
        ]);

        $lineKey = 'product:'.$product->id.':lb';

        session(['carrito' => [
            $lineKey => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'nombre' => $product->name,
                'precio' => 25000,
                'cantidad' => 1,
            ],
        ]]);

        $this->patch(route('carrito.linea.actualizar'), [
            'line_key' => $lineKey,
            'cantidad' => 3,
        ])->assertRedirect(route('carrito.ver'));

        $cart = session('carrito');
        $this->assertSame('volume', $cart[$lineKey]['pricing_tier']);
        $this->assertSame(22000.0, (float) $cart[$lineKey]['precio']);
    }
}
