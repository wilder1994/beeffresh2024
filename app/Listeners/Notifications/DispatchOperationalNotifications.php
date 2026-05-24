<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Enums\Notifications\NotificationType;
use App\Events\Orders\OrderAssigned;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderFailed;
use App\Events\Orders\OrderInTransit;
use App\Events\Orders\OrderPaid;
use App\Events\Orders\OrderPickedUp;
use App\Events\Orders\OrderPreparing;
use App\Events\Orders\OrderReadyForDelivery;
use App\Events\Orders\OrderReturnedToStore;
use App\Events\Orders\OrderUnassigned;
use App\Events\Orders\OrderDelayed;
use App\Events\Payments\PaymentDeclined;
use App\Events\Payments\PaymentWebhookFailed;
use App\Listeners\Notifications\Concerns\BuildsNotificationPayload;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class DispatchOperationalNotifications implements ShouldQueue
{
    use BuildsNotificationPayload;

    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handleOrderPaid(OrderPaid $event): void
    {
        $payload = array_merge(
            $this->orderPayload($event->order),
            $this->paymentPayload($event->payment),
        );

        $this->notifications->notifyType(NotificationType::OrderReceived, $payload);
        $this->notifications->notifyType(NotificationType::PaymentConfirmed, $payload);
    }

    public function handleOrderPreparing(OrderPreparing $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderPreparing,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderReady(OrderReadyForDelivery $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderReadyForDelivery,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderAssigned(OrderAssigned $event): void
    {
        $payload = array_merge($this->orderPayload($event->order), [
            'courier' => $event->courier,
        ]);

        $type = $event->reassigned
            ? NotificationType::OrderReassigned
            : NotificationType::OrderAssigned;

        $this->notifications->notifyType($type, $payload);
    }

    public function handleOrderPickedUp(OrderPickedUp $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderPickedUp,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderInTransit(OrderInTransit $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderInTransit,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderDelivered(OrderDelivered $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderDelivered,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderFailed(OrderFailed $event): void
    {
        $payload = array_merge($this->orderPayload($event->order), [
            'note' => $event->note,
        ]);

        $this->notifications->notifyType(NotificationType::OrderFailed, $payload);
        $this->notifications->notifyType(NotificationType::DeliveryFailedCourier, $payload);
    }

    public function handleOrderReturned(OrderReturnedToStore $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderReturnedToStore,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderUnassigned(OrderUnassigned $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderUnassigned,
            $this->orderPayload($event->order),
        );
    }

    public function handleOrderDelayed(OrderDelayed $event): void
    {
        $this->notifications->notifyType(
            NotificationType::OrderDelayed,
            $this->orderPayload($event->order),
        );
    }

    public function handlePaymentDeclined(PaymentDeclined $event): void
    {
        $this->notifications->notifyType(
            NotificationType::PaymentDeclined,
            $this->paymentPayload($event->payment),
        );
    }

    public function handleWebhookFailed(PaymentWebhookFailed $event): void
    {
        $this->notifications->notifyType(NotificationType::WebhookFailed, [
            'reference' => $event->reference,
            'error' => $event->error,
            'payload' => $event->payload,
        ]);
    }

    public function subscribe($events): array
    {
        return [
            OrderPaid::class => 'handleOrderPaid',
            OrderPreparing::class => 'handleOrderPreparing',
            OrderReadyForDelivery::class => 'handleOrderReady',
            OrderAssigned::class => 'handleOrderAssigned',
            OrderPickedUp::class => 'handleOrderPickedUp',
            OrderInTransit::class => 'handleOrderInTransit',
            OrderDelivered::class => 'handleOrderDelivered',
            OrderFailed::class => 'handleOrderFailed',
            OrderReturnedToStore::class => 'handleOrderReturned',
            OrderUnassigned::class => 'handleOrderUnassigned',
            OrderDelayed::class => 'handleOrderDelayed',
            PaymentDeclined::class => 'handlePaymentDeclined',
            PaymentWebhookFailed::class => 'handleWebhookFailed',
        ];
    }
}
