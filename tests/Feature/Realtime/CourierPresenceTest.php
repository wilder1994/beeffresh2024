<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Couriers\CourierPresenceUpdated;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\CourierAssignmentService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourierPresenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_mark_courier_busy_dispatches_presence_updated(): void
    {
        Event::fake([CourierPresenceUpdated::class]);

        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'courier_id' => $courier->id,
            'total' => '10000.00',
            'status' => OrderStatus::InTransit,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            'shipping_recipient_name' => $customer->name,
            'shipping_phone' => $customer->phone,
            'shipping_address_line1' => 'Calle 1',
            'shipping_city' => 'Medellín',
            'shipping_state' => 'Antioquia',
            'shipping_country' => 'CO',
            'shipping_latitude' => 6.2442,
            'shipping_longitude' => -75.5812,
        ]);

        app(CourierAssignmentService::class)->markCourierBusy($courier);

        Event::assertDispatched(CourierPresenceUpdated::class, function (CourierPresenceUpdated $event) use ($courier, $order): bool {
            $channels = collect($event->broadcastOn())->map->name->all();
            $presence = $event->broadcastWith()['presence'] ?? [];

            return in_array('private-operations.couriers', $channels, true)
                && ($presence['courier_id'] ?? null) === $courier->id
                && ($presence['available'] ?? true) === false
                && ($presence['active_order_id'] ?? null) === $order->id
                && $event->broadcastAs() === 'courier.presence.updated';
        });
    }
}
