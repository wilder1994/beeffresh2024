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
use Tests\TestCase;

class OrderDispatcherOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_dispatcher_claims_order_when_starting_preparation(): void
    {
        $customer = $this->customer();
        $dispatcher = $this->dispatcherOne();
        $order = $this->createPendingOrder($customer);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.start-preparing', $order))
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame($dispatcher->id, $order->handled_by_user_id);
        $this->assertNotNull($order->handled_at);
        $this->assertSame(OrderStatus::Preparing, $order->status);
    }

    public function test_dispatcher_sees_own_orders_and_unclaimed_pending_pool(): void
    {
        $customer = $this->customer();
        $dispatcherOne = $this->dispatcherOne();
        $dispatcherTwo = $this->dispatcherTwo();

        $mine = $this->createPendingOrder($customer);
        $mine->update([
            'handled_by_user_id' => $dispatcherOne->id,
            'handled_at' => now(),
            'status' => OrderStatus::Preparing,
        ]);

        $pool = $this->createPendingOrder($customer);

        $other = $this->createPendingOrder($customer);
        $other->update([
            'handled_by_user_id' => $dispatcherTwo->id,
            'handled_at' => now(),
            'status' => OrderStatus::Preparing,
        ]);

        $this->actingAs($dispatcherOne)
            ->get(route('admin.pedidos.index'))
            ->assertOk()
            ->assertSee('Mis pedidos')
            ->assertSee('#'.$mine->id)
            ->assertSee('#'.$pool->id)
            ->assertDontSee('#'.$other->id);
    }

    public function test_second_dispatcher_cannot_take_order_already_claimed(): void
    {
        $customer = $this->customer();
        $dispatcherOne = $this->dispatcherOne();
        $dispatcherTwo = $this->dispatcherTwo();
        $order = $this->createPendingOrder($customer);

        $this->actingAs($dispatcherOne)
            ->post(route('admin.pedidos.start-preparing', $order))
            ->assertRedirect();

        $this->actingAs($dispatcherTwo)
            ->post(route('admin.pedidos.start-preparing', $order))
            ->assertForbidden();

        $order->refresh();
        $this->assertSame($dispatcherOne->id, $order->handled_by_user_id);
    }

    public function test_admin_can_reassign_dispatcher(): void
    {
        $customer = $this->customer();
        $admin = User::query()->where('email', 'admin1@demo.beeffresh.test')->firstOrFail();
        $dispatcherOne = $this->dispatcherOne();
        $dispatcherTwo = $this->dispatcherTwo();
        $order = $this->createPendingOrder($customer);

        $this->actingAs($dispatcherOne)
            ->post(route('admin.pedidos.start-preparing', $order));

        $this->actingAs($admin)
            ->post(route('admin.pedidos.reassign-dispatcher', $order), [
                'dispatcher_id' => $dispatcherTwo->id,
            ])
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame($dispatcherTwo->id, $order->handled_by_user_id);
    }

    public function test_dispatcher_cannot_access_executive_dashboard(): void
    {
        $this->actingAs($this->dispatcherOne())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_executive_dashboard(): void
    {
        $admin = User::query()->where('email', 'admin1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel ejecutivo');
    }

    public function test_dispatcher_dashboard_feed_excludes_revenue(): void
    {
        $this->actingAs($this->dispatcherOne())
            ->getJson(route('dispatch.dashboard.feed'))
            ->assertOk()
            ->assertJsonMissing(['revenue_today'])
            ->assertJsonMissing(['revenue_month'])
            ->assertJsonStructure([
                'kpi' => [
                    'handled_active',
                    'pending_pool',
                    'preparing',
                ],
            ]);
    }

    public function test_executive_dashboard_feed_includes_revenue(): void
    {
        $admin = User::query()->where('email', 'admin1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('admin.dashboard.feed'))
            ->assertOk()
            ->assertJsonStructure([
                'kpi' => [
                    'revenue_today',
                    'revenue_month',
                ],
            ]);
    }

    private function customer(): User
    {
        return User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
    }

    private function dispatcherOne(): User
    {
        return User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
    }

    private function dispatcherTwo(): User
    {
        return User::query()->where('email', 'despachador2@demo.beeffresh.test')->firstOrFail();
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
