<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Models\Order;

final class OperationsMapPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function fromOrder(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'lat' => $order->shipping_latitude !== null ? (float) $order->shipping_latitude : null,
            'lng' => $order->shipping_longitude !== null ? (float) $order->shipping_longitude : null,
            'status' => $order->status->value,
            'courier_id' => $order->courier_id,
            'priority' => null,
            'delayed' => false,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $courierPayload
     * @return array<string, mixed>
     */
    public static function fromCourier(array $courierPayload): array
    {
        return [
            'courier_id' => $courierPayload['courier_id'] ?? null,
            'lat' => $courierPayload['lat'] ?? null,
            'lng' => $courierPayload['lng'] ?? null,
            'available' => $courierPayload['available'] ?? null,
            'courier_name' => $courierPayload['courier_name'] ?? null,
            'updated_at' => $courierPayload['updated_at'] ?? null,
        ];
    }
}
