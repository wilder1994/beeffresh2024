<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\User;

final class CourierLocationPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function from(User $courier, CourierLocation $location, ?Order $order = null): array
    {
        $courier->loadMissing('employeeProfile');

        return [
            'courier_id' => $courier->id,
            'order_id' => $order?->id,
            'lat' => (float) $location->latitude,
            'lng' => (float) $location->longitude,
            'heading' => null,
            'speed' => null,
            'accuracy' => $location->accuracy !== null ? (float) $location->accuracy : null,
            'updated_at' => $location->recorded_at?->toIso8601String(),
            'status' => $order?->status->value,
            'courier_name' => $courier->name,
            'available' => (bool) $courier->employeeProfile?->available,
        ];
    }
}
