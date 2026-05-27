<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Tracking\OrderTrackingUpdated;
use App\Models\Order;
use App\Models\User;
use App\Services\Realtime\TrackingBroadcastService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderTrackingRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_order_tracking_updated_broadcasts_guest_and_order_channels(): void
    {
        Event::fake([OrderTrackingUpdated::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '10000.00',
            'status' => OrderStatus::Pending,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            'shipping_recipient_name' => $customer->name,
            'shipping_phone' => $customer->phone,
            'shipping_address_line1' => 'Calle 1',
            'shipping_city' => 'Medellín',
            'shipping_state' => 'Antioquia',
            'shipping_country' => 'CO',
        ]);

        app(TrackingBroadcastService::class)->dispatch($order);

        Event::assertDispatched(OrderTrackingUpdated::class, function (OrderTrackingUpdated $event) use ($order): bool {
            $channels = collect($event->broadcastOn())->map->name->all();
            $tracking = $event->broadcastWith()['tracking'] ?? [];

            return in_array('private-orders.'.$order->id, $channels, true)
                && in_array('tracking.'.$order->tracking_token, $channels, true)
                && $event->broadcastAs() === 'order.tracking.updated'
                && ($tracking['order_id'] ?? null) === $order->id
                && is_array($tracking['timeline'] ?? null);
        });
    }
}
