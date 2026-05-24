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

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_customer_can_view_order_history(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->createOrderFor($customer, OrderStatus::Preparing);

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Mis pedidos')
            ->assertSee('Pedido #'.$order->id)
            ->assertSee('En preparación');
    }

    public function test_customer_order_card_links_to_tracking(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->createOrderFor($customer, OrderStatus::Pending);

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee(route('orders.tracking.show', $order, false));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('customer.orders.index'))
            ->assertRedirect(route('login'));
    }

    public function test_employee_cannot_access_customer_order_history(): void
    {
        $employee = User::query()->where('email', 'empleado1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('customer.orders.index'))
            ->assertForbidden();
    }

    public function test_customer_only_sees_own_orders(): void
    {
        $customerA = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $customerB = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $ownOrder = $this->createOrderFor($customerA, OrderStatus::Pending);
        $otherOrder = $this->createOrderFor($customerB, OrderStatus::Delivered);

        $this->actingAs($customerA)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Pedido #'.$ownOrder->id)
            ->assertDontSee('Pedido #'.$otherOrder->id);
    }

    private function createOrderFor(User $customer, OrderStatus $status): Order
    {
        $product = Product::factory()->create(['price_per_lb' => 15000, 'stock' => 20]);

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '15000.00',
            'status' => $status,
            'payment_method' => PaymentMethod::Online,
            'tracking_token' => Order::generateTrackingToken(),
            ...$customer->snapshotShippingFromProfile(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'sale_unit' => StockUnit::Lb,
            'unit_price' => '15000.00',
            'subtotal' => '15000.00',
        ]);

        return $order;
    }
}
