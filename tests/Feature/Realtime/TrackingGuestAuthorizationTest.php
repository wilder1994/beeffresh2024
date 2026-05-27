<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Tracking\OrderTrackingUpdated;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingGuestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'bf-test-key',
            'broadcasting.connections.pusher.secret' => 'bf-test-secret',
            'broadcasting.connections.pusher.app_id' => 'bf-test-app',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
            'broadcasting.connections.pusher.options.host' => 'localhost',
            'broadcasting.connections.pusher.options.port' => 6001,
            'broadcasting.connections.pusher.options.scheme' => 'http',
            'broadcasting.connections.pusher.options.useTLS' => false,
        ]);

        $this->app->forgetInstance(\Illuminate\Contracts\Broadcasting\Factory::class);
        require base_path('routes/channels.php');
    }

    public function test_guest_tracking_page_exposes_token_without_private_ops_channels(): void
    {
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
        ]);

        $this->get(route('orders.tracking.guest', $order->tracking_token))
            ->assertOk()
            ->assertSee('name="bf-tracking-token"', false)
            ->assertSee($order->tracking_token, false);
    }

    public function test_guest_cannot_authorize_private_operations_map_channel(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($customer)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-operations.map',
            ])
            ->assertForbidden();
    }

    public function test_tracking_event_uses_public_channel_with_token_only(): void
    {
        $token = Order::generateTrackingToken();
        $event = new OrderTrackingUpdated([
            'order_id' => 1,
            'tracking_token' => $token,
            'status' => 'in_transit',
            'timeline' => [],
        ]);

        $channels = collect($event->broadcastOn())->map->name->all();

        $this->assertContains('tracking.'.$token, $channels);
        $this->assertNotContains('private-tracking.'.$token, $channels);
    }
}
