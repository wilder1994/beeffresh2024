<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Domain\Catalog\StockUnit;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderOperationsFlowTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_full_order_operations_flow_until_delivery(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = $this->createPendingOrder($customer);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.start-preparing', $order))
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::Preparing, $order->status);
        $this->assertSame($dispatcher->id, $order->handled_by_user_id);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.mark-ready', $order))
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::ReadyForDelivery, $order->status);
        $this->assertNull($order->courier_id);

        $this->actingAs($courier)
            ->post(route('courier.orders.accept', $order))
            ->assertRedirect(route('courier.orders.show', $order));

        $order->refresh();
        $this->assertSame($courier->id, $order->courier_id);

        $this->actingAs($courier)
            ->post(route('courier.orders.picked-up', $order))
            ->assertRedirect(route('courier.orders.show', $order));

        $this->actingAs($courier)
            ->post(route('courier.orders.in-transit', $order))
            ->assertRedirect(route('courier.orders.show', $order));

        $this->actingAs($courier)
            ->post(route('courier.orders.delivered', $order), [
                'signature' => self::SIGNATURE,
                'latitude' => 6.208869,
                'longitude' => -75.567983,
            ])
            ->assertRedirect(route('courier.orders.index'));

        $order->refresh();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertTrue((bool) $courier->fresh()->employeeProfile?->available);
        $this->assertDatabaseHas('delivery_proofs', [
            'order_id' => $order->id,
            'type' => 'signature',
        ]);

        $this->actingAs($customer)
            ->get(route('orders.tracking.show', $order))
            ->assertOk()
            ->assertSee('Entregado');
    }

    public function test_failed_delivery_returns_order_to_store_and_allows_redispatch(): void
    {
        Storage::fake('public');

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = $this->createPendingOrder($customer);

        $this->actingAs($dispatcher)->post(route('admin.pedidos.start-preparing', $order));
        $this->actingAs($dispatcher)->post(route('admin.pedidos.mark-ready', $order));
        $this->actingAs($courier)->post(route('courier.orders.accept', $order));
        $this->actingAs($courier)->post(route('courier.orders.picked-up', $order));
        $this->actingAs($courier)->post(route('courier.orders.in-transit', $order));

        $this->actingAs($courier)
            ->post(route('courier.orders.failed', $order), [
                'media' => UploadedFile::fake()->image('evidence.jpg'),
                'notes' => 'No había nadie en el domicilio.',
            ])
            ->assertRedirect(route('courier.orders.index'));

        $order->refresh();
        $this->assertSame(OrderStatus::ReturnedToStore, $order->status);
        $this->assertTrue((bool) $courier->fresh()->employeeProfile?->available);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.redispatch', $order), [
                'redelivery_fee' => 5000,
                'note' => 'Segundo intento',
            ])
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::Preparing, $order->status);
        $this->assertSame($dispatcher->id, $order->handled_by_user_id);
        $this->assertSame(2, $order->delivery_attempt);
        $this->assertSame('5000.00', $order->redelivery_fee);
    }

    public function test_guest_can_track_order_by_token(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->createPendingOrder($customer);

        $this->get(route('orders.tracking.guest', $order->tracking_token))
            ->assertOk()
            ->assertSee('Seguimiento del pedido');

        $this->get(route('orders.tracking.guest-feed', $order->tracking_token))
            ->assertOk()
            ->assertJsonPath('order.status', OrderStatus::Pending->value);
    }

    private function createPendingOrder(User $customer): Order
    {
        $product = Product::factory()->create([
            'price_per_lb' => 20000,
            'stock' => 50,
        ]);

        $shipping = $customer->snapshotShippingFromProfile();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '40000.00',
            'status' => OrderStatus::Pending,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            ...$shipping,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'sale_unit' => StockUnit::Lb,
            'unit_price' => '20000.00',
            'subtotal' => '40000.00',
        ]);

        return $order;
    }
}
