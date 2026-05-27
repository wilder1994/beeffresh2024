<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Operations\OperationsMapUpdated;
use App\Jobs\Realtime\BroadcastOperationsMapJob;
use App\Models\Order;
use App\Models\User;
use App\Services\Realtime\OperationsMapBroadcastService;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OperationsMapRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_operations_map_broadcast_job_coalesces_and_emits_event(): void
    {
        Queue::fake();
        Event::fake([OperationsMapUpdated::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = Order::query()->create([
            'user_id' => $customer->id,
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

        app(OperationsMapBroadcastService::class)->dispatchForOrder($order);
        app(OperationsMapBroadcastService::class)->dispatchForOrder($order);

        Queue::assertPushed(BroadcastOperationsMapJob::class, 1);
    }

    public function test_operations_map_updated_event_channels(): void
    {
        $event = new OperationsMapUpdated([
            'order_id' => 9,
            'lat' => 6.24,
            'lng' => -75.58,
            'status' => 'in_transit',
        ]);

        $channels = collect($event->broadcastOn())->map->name->all();

        $this->assertContains('private-operations.map', $channels);
        $this->assertContains('private-operations.orders', $channels);
        $this->assertSame('operations.map.updated', $event->broadcastAs());
    }
}
