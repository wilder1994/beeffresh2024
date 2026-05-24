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
use App\Services\Orders\OrderTrackingTimelineBuilder;
use App\Services\Orders\OrderWorkflowService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_ready_order_shows_upcoming_delivery_steps(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $order = $this->createPendingOrder($customer);
        $workflow = app(OrderWorkflowService::class);

        $workflow->logInitialStatus($order, $dispatcher);
        $workflow->transition($order, OrderStatus::Preparing, $dispatcher);
        $workflow->transition($order, OrderStatus::ReadyForDelivery, $dispatcher);

        $order->refresh()->load('statusLogs');

        $timeline = app(OrderTrackingTimelineBuilder::class)->build($order);

        $this->assertCount(6, $timeline);
        $this->assertSame('completed', $timeline[0]['state']);
        $this->assertSame('pending', $timeline[0]['status']);
        $this->assertSame('completed', $timeline[2]['state']);
        $this->assertSame('ready_for_delivery', $timeline[2]['status']);
        $this->assertSame('upcoming', $timeline[3]['state']);
        $this->assertSame('picked_up', $timeline[3]['status']);
        $this->assertSame('upcoming', $timeline[5]['state']);
        $this->assertSame('delivered', $timeline[5]['status']);
    }

    public function test_tracking_page_shows_upcoming_steps_for_ready_order(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $order = $this->createPendingOrder($customer);
        $workflow = app(OrderWorkflowService::class);

        $workflow->logInitialStatus($order, $dispatcher);
        $workflow->transition($order, OrderStatus::Preparing, $dispatcher);
        $workflow->transition($order, OrderStatus::ReadyForDelivery, $dispatcher);

        $this->actingAs($customer)
            ->get(route('orders.tracking.show', $order))
            ->assertOk()
            ->assertSee('Recogido')
            ->assertSee('En tránsito')
            ->assertSee('Entregado')
            ->assertSee('Pendiente');

        $this->actingAs($customer)
            ->getJson(route('orders.tracking.feed', $order))
            ->assertOk()
            ->assertJsonCount(6, 'timeline')
            ->assertJsonPath('timeline.3.state', 'upcoming')
            ->assertJsonPath('timeline.3.status', 'picked_up');
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
