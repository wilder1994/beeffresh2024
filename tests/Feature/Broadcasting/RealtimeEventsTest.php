<?php

declare(strict_types=1);

namespace Tests\Feature\Broadcasting;

use App\Enums\Notifications\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\NotificationCreated;
use App\Events\OrderUpdated;
use App\Events\Payments\PaymentStatusUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Orders\OrderWorkflowService;
use App\Services\Payments\PaymentWebhookProcessor;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_order_updated_broadcasts_on_private_channels(): void
    {
        Event::fake([OrderUpdated::class]);

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

        app(OrderWorkflowService::class)->transition($order, OrderStatus::Preparing);

        Event::assertDispatched(OrderUpdated::class, function (OrderUpdated $event) use ($order): bool {
            $channelNames = collect($event->broadcastOn())->map->name->all();

            return $event->order->id === $order->id
                && in_array('private-operations.orders', $channelNames, true)
                && in_array('private-orders.'.$order->id, $channelNames, true)
                && $event->broadcastAs() === 'order.updated';
        });
    }

    public function test_notification_created_broadcasts_to_user_private_channel(): void
    {
        Event::fake([NotificationCreated::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        app(NotificationRepository::class)->createInboxNotification(
            $customer,
            NotificationType::OrderPreparing,
            'Pedido en preparación',
            'Tu pedido está siendo preparado.',
            ['order_id' => 1],
        );

        Event::assertDispatched(NotificationCreated::class, function (NotificationCreated $event) use ($customer): bool {
            return $event->broadcastOn()[0]->name === 'private-App.Models.User.'.$customer->id
                && $event->broadcastAs() === 'notification.created';
        });
    }

    public function test_payment_status_updated_broadcasts_on_payment_channel(): void
    {
        Event::fake([PaymentStatusUpdated::class]);

        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-RT-TEST',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Processing,
        ]);

        app(PaymentWebhookProcessor::class)->applyPaymentStatus(
            $payment,
            PaymentStatus::Declined,
            'txn-test-declined',
            'CARD',
            ['status' => 'DECLINED'],
        );

        Event::assertDispatched(PaymentStatusUpdated::class, function (PaymentStatusUpdated $event) use ($payment): bool {
            return $event->payment->uuid === $payment->uuid
                && $event->broadcastOn()[0]->name === 'private-payments.'.$payment->uuid
                && $event->broadcastAs() === 'payment.status.updated';
        });
    }
}
