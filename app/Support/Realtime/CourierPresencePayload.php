<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Models\Order;
use App\Models\User;

final class CourierPresencePayload
{
    /**
     * @return array<string, mixed>
     */
    public static function fromCourier(User $courier, ?Order $activeOrder = null): array
    {
        $courier->loadMissing('employeeProfile');

        return [
            'courier_id' => $courier->id,
            'online' => true,
            'available' => (bool) $courier->employeeProfile?->available,
            'last_seen_at' => now()->toIso8601String(),
            'active_order_id' => $activeOrder?->id,
            'courier_name' => $courier->name,
        ];
    }
}
