<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_linked_to_order_cannot_be_deleted_via_web(): void
    {
        $admin = User::factory()->admin()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::query()->create([
            'user_id' => $buyer->id,
            'total' => '100.00',
            'status' => OrderStatus::Pending,
            'shipping_recipient_name' => $buyer->name,
            'shipping_phone' => '8090000000',
            'shipping_document_number' => null,
            'shipping_address_line1' => 'Calle 1',
            'shipping_address_line2' => null,
            'shipping_city' => 'Santo Domingo',
            'shipping_state' => 'DN',
            'shipping_postal_code' => null,
            'shipping_country' => 'CO',
            'shipping_notes' => null,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => '100.00',
            'subtotal' => '100.00',
        ]);

        $response = $this->actingAs($admin)->from(route('catalog.products.index'))->delete(route('catalog.products.destroy', $product));

        $response->assertRedirect(route('catalog.products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_without_orders_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('catalog.products.destroy', $product));

        $response->assertRedirect(route('catalog.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
