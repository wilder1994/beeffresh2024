<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\DataTransferObjects\Notifications\CreateNotificationData;
use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationDeliveryStatus;
use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Events\Orders\OrderPaid;
use App\Events\Orders\OrderPreparing;
use App\Jobs\Notifications\DispatchNotificationDeliveryJob;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Database\Seeders\DemoUsersSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
    }

    public function test_service_creates_inbox_notification_and_delivery(): void
    {
        Queue::fake();

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        app(NotificationService::class)->dispatch(new CreateNotificationData(
            type: NotificationType::OrderPreparing,
            recipients: collect([$customer]),
            payload: [
                'order' => $order,
                'order_id' => $order->id,
                'status_label' => $order->status->label(),
                'amount' => '10000',
            ],
        ));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => NotificationType::OrderPreparing->value,
        ]);

        $this->assertDatabaseHas('notification_deliveries', [
            'user_id' => $customer->id,
            'type' => NotificationType::OrderPreparing->value,
            'channel' => NotificationChannel::Internal->value,
        ]);

        Queue::assertPushed(DispatchNotificationDeliveryJob::class);
    }

    public function test_email_delivery_is_sent(): void
    {
        Mail::fake();

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        $delivery = NotificationDelivery::query()->create([
            'user_id' => $customer->id,
            'type' => NotificationType::OrderPreparing,
            'channel' => NotificationChannel::Email,
            'recipient' => $customer->email,
            'payload' => [
                'order' => $order,
                'order_id' => $order->id,
                'status_label' => $order->status->label(),
                'amount' => '10000',
            ],
            'status' => NotificationDeliveryStatus::Pending,
        ]);

        DispatchNotificationDeliveryJob::dispatchSync($delivery->id);

        $this->assertDatabaseHas('notification_deliveries', [
            'id' => $delivery->id,
            'status' => NotificationDeliveryStatus::Sent->value,
        ]);
    }

    public function test_unread_counter_and_mark_read(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        Notification::query()->create([
            'user_id' => $customer->id,
            'type' => NotificationType::OrderDelivered,
            'title' => 'Entregado',
            'body' => 'Pedido entregado',
            'payload' => [],
        ]);

        $this->actingAs($customer)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        $notification = Notification::query()->where('user_id', $customer->id)->firstOrFail();

        $this->actingAs($customer)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_order_paid_event_dispatches_notifications(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'gateway' => \App\Enums\PaymentGateway::Wompi,
            'reference' => 'BF-TEST-NOTIF',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => \App\Enums\PaymentStatus::Approved,
        ]);

        event(new OrderPaid($order, $payment));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => NotificationType::PaymentConfirmed->value,
        ]);
    }

    public function test_payment_confirmed_notifies_operations_staff(): void
    {
        Queue::fake();

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        app(NotificationService::class)->notifyType(
            NotificationType::PaymentConfirmed,
            [
                'order' => $order,
                'order_id' => $order->id,
                'customer_name' => $customer->name,
                'status_label' => $order->status->label(),
                'amount' => number_format((float) $order->total, 0, ',', '.'),
            ],
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => NotificationType::PaymentConfirmed->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispatcher->id,
            'type' => NotificationType::PaymentConfirmed->value,
        ]);

        $staffNotification = Notification::query()
            ->where('user_id', $dispatcher->id)
            ->where('type', NotificationType::PaymentConfirmed)
            ->firstOrFail();

        $this->assertStringContainsString('Cliente:', $staffNotification->body);
        $this->assertSame(route('admin.pedidos.show', $order, absolute: false), $staffNotification->payload['action_url'] ?? null);
    }

    public function test_failed_delivery_job_marks_failed_after_retries(): void
    {
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        $delivery = NotificationDelivery::query()->create([
            'user_id' => $customer->id,
            'type' => NotificationType::OrderPreparing,
            'channel' => NotificationChannel::Email,
            'recipient' => $customer->email,
            'payload' => [
                'order' => $order,
                'order_id' => $order->id,
                'status_label' => $order->status->label(),
                'amount' => '10000',
            ],
            'status' => NotificationDeliveryStatus::Pending,
        ]);

        try {
            DispatchNotificationDeliveryJob::dispatchSync($delivery->id);
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(
            NotificationDeliveryStatus::Failed,
            $delivery->fresh()->status,
        );
    }

    public function test_order_preparing_transition_triggers_notification_listener(): void
    {
        Event::fake([OrderPreparing::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();
        $order = $this->makeOrder($customer);

        $this->actingAs($dispatcher)
            ->post(route('admin.pedidos.start-preparing', $order))
            ->assertRedirect();

        Event::assertDispatched(OrderPreparing::class);
    }

    public function test_mark_all_read_redirects_to_index(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Pedido en preparación',
            'body' => 'Pedido #22 en preparación',
            'payload' => [],
        ]);

        $this->actingAs($dispatcher)
            ->patch(route('notifications.mark-all-read'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('status');

        $this->assertSame(0, Notification::query()->where('user_id', $dispatcher->id)->whereNull('read_at')->count());
    }

    public function test_mark_all_read_accepts_json(): void
    {
        $dispatcher = User::query()->where('email', 'despachador1@demo.beeffresh.test')->firstOrFail();

        Notification::query()->create([
            'user_id' => $dispatcher->id,
            'type' => NotificationType::OrderPreparing,
            'title' => 'Pedido en preparación',
            'body' => 'Pedido #22 en preparación',
            'payload' => [],
        ]);

        $this->actingAs($dispatcher)
            ->patchJson(route('notifications.mark-all-read'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('unread_count', 0);
    }

    private function makeOrder(User $customer): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'total' => '10000.00',
            'status' => OrderStatus::Pending,
            'payment_method' => \App\Enums\PaymentMethod::OnlineSimulated,
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
