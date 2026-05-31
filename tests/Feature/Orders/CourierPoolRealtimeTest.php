<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderUpdated;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\CourierAssignmentService;
use App\Services\Orders\OrderWorkflowService;
use App\Services\Realtime\OrderBroadcastService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourierPoolRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_courier_pool_feed_lists_ready_unassigned_orders(): void
    {
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeReadyPoolOrder();

        $this->actingAs($courier)
            ->getJson(route('courier.orders.pool-feed'))
            ->assertOk()
            ->assertJsonPath('can_accept', true)
            ->assertJsonFragment(['id' => $order->id, 'in_pool' => true]);
    }

    public function test_courier_pool_card_fragment_renders_accept_button_when_available(): void
    {
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeReadyPoolOrder();

        $response = $this->actingAs($courier)
            ->getJson(route('courier.orders.pool-card', $order))
            ->assertOk();

        $this->assertStringContainsString('Aceptar pedido', (string) $response->json('html'));
        $this->assertTrue((bool) $response->json('order.in_pool'));
    }

    public function test_order_updated_broadcasts_on_couriers_pool_channel(): void
    {
        Event::fake([OrderUpdated::class]);

        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $order = Order::query()->create([
            'user_id' => User::query()->where('email', 'cliente2@demo.beeffresh.test')->value('id'),
            'total' => '10000.00',
            'status' => OrderStatus::Preparing,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            'shipping_recipient_name' => 'Cliente Demo',
            'shipping_phone' => '3001234567',
            'shipping_address_line1' => 'Calle 1',
            'shipping_city' => 'Medellín',
            'shipping_state' => 'Antioquia',
            'shipping_country' => 'CO',
        ]);

        $order = app(OrderWorkflowService::class)->transitionSilent($order, OrderStatus::ReadyForDelivery, $dispatcher);

        DB::transaction(function () use ($order): void {
            app(OrderBroadcastService::class)->dispatch($order->fresh(['user', 'courier', 'items']));
        });

        Event::assertDispatched(OrderUpdated::class, function (OrderUpdated $event) use ($order): bool {
            $channelNames = collect($event->broadcastOn())->map->name->all();

            return $event->order->id === $order->id
                && in_array('private-couriers.pool', $channelNames, true)
                && ($event->broadcastWith()['order']['in_pool'] ?? false) === true;
        });
    }

    public function test_pool_card_fragment_not_found_when_order_leaves_pool(): void
    {
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeReadyPoolOrder();

        app(CourierAssignmentService::class)->claimByCourier($order->fresh(), $courier);

        $this->actingAs($courier)
            ->getJson(route('courier.orders.pool-card', $order))
            ->assertNotFound();
    }

    private function makeReadyPoolOrder(): Order
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '15000.00',
            'status' => OrderStatus::Preparing,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            ...$customer->snapshotShippingFromProfile(),
        ]);

        return app(OrderWorkflowService::class)->transitionSilent($order, OrderStatus::ReadyForDelivery, $dispatcher);
    }
}
