<?php

declare(strict_types=1);

namespace App\Listeners\Notifications\Concerns;

use App\Models\Order;
use App\Models\Payment;

trait BuildsNotificationPayload
{
    /**
     * @return array<string, mixed>
     */
    protected function orderPayload(Order $order): array
    {
        $order->loadMissing(['user', 'courier']);

        return [
            'order' => $order,
            'order_id' => $order->id,
            'customer_name' => $order->shipping_recipient_name ?? $order->user?->name,
            'status_label' => $order->status->label(),
            'amount' => number_format((float) $order->total, 0, ',', '.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentPayload(Payment $payment): array
    {
        $payment->loadMissing(['order', 'user']);

        return [
            'payment' => $payment,
            'reference' => $payment->reference,
            'amount' => number_format((float) $payment->amount, 0, ',', '.'),
            'user' => $payment->user,
            'order' => $payment->order,
            'order_id' => $payment->order_id,
        ];
    }
}
