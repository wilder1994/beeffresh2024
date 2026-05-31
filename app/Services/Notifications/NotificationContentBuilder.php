<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\NotificationActionUrl;

final class NotificationContentBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(NotificationType $type, array $payload, ?User $recipient = null): NotificationContent
    {
        $template = config('notifications.content.'.$type->value, []);

        if ($recipient !== null && $recipient->isCourier()) {
            $courierTemplate = config('notifications.content_courier.'.$type->value, []);
            if ($courierTemplate !== []) {
                $template = array_merge($template, $courierTemplate);
            }
        } elseif ($recipient !== null && $recipient->isStaff()) {
            $operationsTemplate = config('notifications.content_operations.'.$type->value, []);
            if ($operationsTemplate !== []) {
                $template = array_merge($template, $operationsTemplate);
            }
        }

        $replacements = $this->replacements($payload);

        $title = $this->interpolate((string) ($template['title'] ?? $type->label()), $replacements);
        $body = $this->interpolate((string) ($template['body'] ?? $type->label()), $replacements);
        $actionLabel = isset($template['action_label'])
            ? $this->interpolate((string) $template['action_label'], $replacements)
            : null;

        return new NotificationContent(
            type: $type,
            title: $title,
            body: $body,
            actionUrl: $this->resolveActionUrl($type, $payload, $recipient),
            actionLabel: $actionLabel,
            payload: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function replacements(array $payload): array
    {
        $order = $payload['order'] ?? null;
        $payment = $payload['payment'] ?? null;

        return [
            '{order_id}' => (string) ($payload['order_id'] ?? ($order instanceof Order ? $order->id : '')),
            '{customer_name}' => (string) ($payload['customer_name'] ?? ($order instanceof Order ? ($order->shipping_recipient_name ?? $order->user?->name ?? '') : '')),
            '{status_label}' => (string) ($payload['status_label'] ?? ''),
            '{amount}' => (string) ($payload['amount'] ?? ($order instanceof Order ? number_format((float) $order->total, 0, ',', '.') : '')),
            '{reference}' => (string) ($payload['reference'] ?? ($payment instanceof Payment ? $payment->reference : '')),
            '{error}' => (string) ($payload['error'] ?? ''),
            '{product_name}' => (string) ($payload['product_name'] ?? ''),
            '{affected_offers}' => (string) ($payload['affected_offers'] ?? ''),
        ];
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function interpolate(string $text, array $replacements): string
    {
        return strtr($text, $replacements);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveActionUrl(NotificationType $type, array $payload, ?User $recipient = null): ?string
    {
        $order = $payload['order'] ?? null;
        $payment = $payload['payment'] ?? null;

        if ($order instanceof Order) {
            if ($recipient !== null && $recipient->isStaff() && ! $recipient->isCourier()) {
                return match ($type) {
                    NotificationType::OrderAssigned,
                    NotificationType::OrderReassigned,
                    NotificationType::DeliveryFailedCourier => NotificationActionUrl::route('courier.orders.show', $order),
                    default => NotificationActionUrl::route('admin.pedidos.show', $order),
                };
            }

            if ($recipient !== null && $recipient->isCourier()) {
                return match ($type) {
                    NotificationType::OrderReadyForDelivery => NotificationActionUrl::route('courier.orders.index'),
                    NotificationType::OrderAssigned,
                    NotificationType::OrderReassigned,
                    NotificationType::DeliveryFailedCourier => NotificationActionUrl::route('courier.orders.show', $order),
                    default => NotificationActionUrl::route('courier.orders.index'),
                };
            }

            return match ($type) {
                NotificationType::OrderAssigned,
                NotificationType::OrderReassigned,
                NotificationType::DeliveryFailedCourier => NotificationActionUrl::route('courier.orders.show', $order),
                NotificationType::OrderUnassigned,
                NotificationType::OrderDelayed,
                NotificationType::OrderReturnedToStore => NotificationActionUrl::route('admin.pedidos.show', $order),
                default => NotificationActionUrl::route('orders.tracking.show', $order),
            };
        }

        if ($payment instanceof Payment) {
            return match ($type) {
                NotificationType::PaymentDeclined => NotificationActionUrl::route('payments.failed', $payment->uuid),
                default => NotificationActionUrl::route('payments.status', $payment->uuid),
            };
        }

        if ($type === NotificationType::WebhookFailed) {
            return NotificationActionUrl::route('admin.payments.index');
        }

        if ($type === NotificationType::InventoryOutOfStock) {
            return NotificationActionUrl::route('catalog.inventory.index');
        }

        return NotificationActionUrl::normalize($payload['action_url'] ?? null);
    }
}
