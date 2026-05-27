<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use App\Support\Orders\CustomerTrackingMapPhase;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTrackingMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_map_phase_waiting_before_pickup(): void
    {
        $order = $this->makeOrder(OrderStatus::ReadyForDelivery);

        $this->assertSame(CustomerTrackingMapPhase::WAITING, CustomerTrackingMapPhase::forOrder($order));
    }

    public function test_map_phase_live_when_in_transit(): void
    {
        $order = $this->makeOrder(OrderStatus::InTransit);

        $this->assertSame(CustomerTrackingMapPhase::LIVE, CustomerTrackingMapPhase::forOrder($order));
    }

    public function test_map_phase_closed_when_delivered(): void
    {
        $order = $this->makeOrder(OrderStatus::Delivered);

        $this->assertSame(CustomerTrackingMapPhase::CLOSED, CustomerTrackingMapPhase::forOrder($order));
    }

    public function test_tracking_feed_includes_map_fields(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder(OrderStatus::PickedUp, $customer);

        $this->actingAs($customer)
            ->getJson(route('orders.tracking.feed', $order))
            ->assertOk()
            ->assertJsonPath('map_phase', 'live')
            ->assertJsonStructure([
                'destination' => ['lat', 'lng'],
                'courier_location',
            ]);
    }

    public function test_tracking_page_shows_two_column_map_panel(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder(OrderStatus::ReadyForDelivery, $customer);

        $this->actingAs($customer)
            ->get(route('orders.tracking.show', $order))
            ->assertOk()
            ->assertSee('bf-tracking-layout', false)
            ->assertSee('Aún no ha sido recogido por el domiciliario', false)
            ->assertSee('tracking-map-canvas', false);
    }

    private function makeOrder(OrderStatus $status, ?User $customer = null): Order
    {
        $customer ??= User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        return Order::query()->create([
            'user_id' => $customer->id,
            'total' => '50000.00',
            'status' => $status,
            'payment_method' => PaymentMethod::OnlineSimulated,
            'tracking_token' => Order::generateTrackingToken(),
            'shipping_recipient_name' => $customer->name,
            'shipping_phone' => $customer->phone,
            'shipping_address_line1' => 'Calle 1',
            'shipping_city' => 'Bogotá',
            'shipping_state' => 'Cundinamarca',
            'shipping_country' => 'CO',
            'shipping_latitude' => 4.711,
            'shipping_longitude' => -74.0721,
        ]);
    }
}
