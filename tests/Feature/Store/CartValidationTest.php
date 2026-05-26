<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\ProductStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_validate_flags_unavailable_product(): void
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'status' => ProductStatus::Available,
        ]);

        $lineKey = 'product:'.$product->id.':lb';

        session(['carrito' => [
            $lineKey => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'nombre' => $product->name,
                'precio' => 25000,
                'cantidad' => 2,
            ],
        ]]);

        $this->getJson(route('carrito.validar'))
            ->assertOk()
            ->assertJsonPath('has_invalid', true)
            ->assertJsonPath('checkout_allowed', false)
            ->assertJsonFragment([
                'product_id' => $product->id,
                'can_purchase' => false,
                'availability_label' => 'Agotado',
            ]);
    }

    public function test_cart_validate_allows_purchasable_product(): void
    {
        $product = Product::factory()->create([
            'stock' => 50,
            'status' => ProductStatus::Available,
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

        $this->getJson(route('carrito.validar'))
            ->assertOk()
            ->assertJsonPath('has_invalid', false)
            ->assertJsonPath('checkout_allowed', true)
            ->assertJsonFragment([
                'product_id' => $product->id,
                'can_purchase' => true,
            ]);
    }
}
