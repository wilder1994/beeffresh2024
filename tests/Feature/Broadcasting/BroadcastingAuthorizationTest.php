<?php

declare(strict_types=1);

namespace Tests\Feature\Broadcasting;

use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);

        // Auth HTTP requiere driver Pusher/Reverb; `log` no valida canales (Fase 0 tests).
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

    public function test_user_can_authorize_own_private_user_channel(): void
    {
        $user = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-App.Models.User.'.$user->id,
            ])
            ->assertOk();
    }

    public function test_user_cannot_authorize_another_users_channel(): void
    {
        $user = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $other = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-App.Models.User.'.$other->id,
            ])
            ->assertForbidden();
    }

    public function test_staff_can_authorize_operations_orders_channel(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($dispatcher)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-operations.orders',
            ])
            ->assertOk();
    }

    public function test_customer_cannot_authorize_operations_orders_channel(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($customer)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-operations.orders',
            ])
            ->assertForbidden();
    }

    public function test_customer_can_authorize_own_order_channel(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-orders.'.$order->id,
            ])
            ->assertOk();
    }

    public function test_customer_cannot_authorize_foreign_order_channel(): void
    {
        $owner = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $intruder = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($owner);

        $this->actingAs($intruder)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-orders.'.$order->id,
            ])
            ->assertForbidden();
    }

    public function test_courier_can_authorize_own_courier_channel(): void
    {
        $courier = User::query()->where('email', 'empleado2@demo.beeffresh.test')->firstOrFail();

        $this->actingAs($courier)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-couriers.'.$courier->id,
            ])
            ->assertOk();
    }

    public function test_customer_can_authorize_own_payment_channel(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-AUTH-TEST',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Processing,
        ]);

        $this->actingAs($customer)
            ->post('/broadcasting/auth', [
                'socket_id' => '1.1',
                'channel_name' => 'private-payments.'.$payment->uuid,
            ])
            ->assertOk();
    }

    private function makeOrder(User $customer): Order
    {
        return Order::query()->create([
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
    }
}
