<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Services\Orders\OrderTrackingTimelineBuilder;
use App\Support\Orders\CustomerTrackingMapPhase;

final class TrackingPayload
{
    public function __construct(
        private readonly OrderTrackingTimelineBuilder $timelineBuilder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forOrder(Order $order): array
    {
        $order->loadMissing([
            'courier:id,first_name,last_name',
            'statusLogs' => fn ($q) => $q->oldest(),
        ]);

        $courierLocation = null;
        if ($order->courier_id !== null && $order->courier !== null) {
            $latest = CourierLocation::query()
                ->where('user_id', $order->courier_id)
                ->latest('recorded_at')
                ->first();
            if ($latest instanceof CourierLocation) {
                $courierLocation = [
                    'lat' => (float) $latest->latitude,
                    'lng' => (float) $latest->longitude,
                    'updated_at' => $latest->recorded_at?->toIso8601String(),
                ];
            }
        }

        return [
            'order_id' => $order->id,
            'tracking_token' => $order->tracking_token,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'timeline' => $this->timelineBuilder->build($order),
            'eta' => null,
            'courier' => $order->courier_id !== null ? [
                'id' => $order->courier_id,
                'name' => $order->courier?->name,
            ] : null,
            'courier_location' => $courierLocation,
            'map_phase' => CustomerTrackingMapPhase::forOrder($order),
            'destination' => [
                'lat' => $order->shipping_latitude !== null ? (float) $order->shipping_latitude : null,
                'lng' => $order->shipping_longitude !== null ? (float) $order->shipping_longitude : null,
            ],
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }
}
