<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierOrderClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_mark_ready_leaves_order_unassigned_and_notifies_pool(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = $this->createPreparingOrder($customer);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.mark-ready', $order))
            ->assertRedirect(route('admin.pedidos.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::ReadyForDelivery, $order->status);
        $this->assertNull($order->courier_id);

        $this->assertTrue(
            Notification::query()
                ->where('user_id', $courier->id)
                ->where('type', NotificationType::OrderReadyForDelivery->value)
                ->exists()
        );
    }

    public function test_courier_claims_order_atomically(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = $this->createPreparingOrder($customer);

        $this->actingAs($dispatcher)->post(route('admin.pedidos.mark-ready', $order));

        $this->actingAs($courier)
            ->post(route('courier.orders.accept', $order))
            ->assertRedirect(route('courier.orders.show', $order));

        $order->refresh();
        $this->assertSame($courier->id, $order->courier_id);
        $this->assertFalse((bool) $courier->fresh()->employeeProfile?->available);
    }

    public function test_dispatcher_can_assign_courier_manually(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $order = $this->createPreparingOrder($customer);
        $this->actingAs($dispatcher)->post(route('admin.pedidos.mark-ready', $order));

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.assign-courier', $order), ['courier_id' => $courier->id])
            ->assertRedirect(route('admin.pedidos.show', $order));

        $this->assertSame($courier->id, $order->fresh()->courier_id);
    }

    private function createPreparingOrder(User $customer): Order
    {
        $shipping = $customer->snapshotShippingFromProfile();

        return Order::query()->create([
            'user_id' => $customer->id,
            'total' => '40000.00',
            'status' => OrderStatus::Preparing,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            ...$shipping,
            'shipping_latitude' => $shipping['shipping_latitude'] ?? 6.2442,
            'shipping_longitude' => $shipping['shipping_longitude'] ?? -75.5812,
        ]);
    }
}
