<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Models\Order;

/** Payload unificado para OrderUpdated, feed operacional y parches DOM (Fase 1). */
final class OrderBroadcastPayload
{
    /** @return array<string, mixed> */
    public static function fromOrder(Order $order): array
    {
        $order->loadMissing(['courier:id,first_name,last_name', 'user:id,first_name,last_name,email']);

        return [
            'id' => $order->id,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'status_badge_class' => $order->status->badgeClass(),
            'courier_id' => $order->courier_id,
            'courier_name' => $order->courier?->name,
            'customer_name' => $order->shipping_recipient_name ?? $order->user?->name,
            'shipping_zone' => $order->shipping_address_line2 ?? $order->shipping_city ?? '—',
            'total' => (float) $order->total,
            'delivery_attempt' => $order->delivery_attempt,
            'assigned_at' => $order->assigned_at?->toIso8601String(),
            'ready_at' => $order->ready_at?->toIso8601String(),
            'picked_up_at' => $order->picked_up_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'updated_human' => $order->updated_at?->diffForHumans(short: true),
            'show_url' => route('admin.pedidos.show', $order),
        ];
    }
}
