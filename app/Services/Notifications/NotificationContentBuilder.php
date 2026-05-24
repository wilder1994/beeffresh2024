<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationType;
use App\Models\Order;
use App\Models\Payment;

final class NotificationContentBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(NotificationType $type, array $payload): NotificationContent
    {
        $template = config('notifications.content.'.$type->value, []);
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
            actionUrl: $this->resolveActionUrl($type, $payload),
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
    private function resolveActionUrl(NotificationType $type, array $payload): ?string
    {
        $order = $payload['order'] ?? null;
        $payment = $payload['payment'] ?? null;

        if ($order instanceof Order) {
            return match ($type) {
                NotificationType::OrderAssigned,
                NotificationType::OrderReassigned,
                NotificationType::DeliveryFailedCourier => route('courier.orders.show', $order),
                NotificationType::OrderUnassigned,
                NotificationType::OrderDelayed,
                NotificationType::OrderReturnedToStore => route('admin.pedidos.show', $order),
                default => route('orders.tracking.show', $order),
            };
        }

        if ($payment instanceof Payment) {
            return match ($type) {
                NotificationType::PaymentDeclined => route('payments.failed', $payment->uuid),
                default => route('payments.status', $payment->uuid),
            };
        }

        if ($type === NotificationType::WebhookFailed) {
            return route('admin.payments.index');
        }

        return $payload['action_url'] ?? null;
    }
}
