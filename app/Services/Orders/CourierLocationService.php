<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\CourierLocation;
use App\Models\User;

final class CourierLocationService
{
    public function record(
        User $courier,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
    ): CourierLocation {
        return CourierLocation::query()->create([
            'user_id' => $courier->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'recorded_at' => now(),
        ]);
    }

    public function latestFor(User $courier): ?CourierLocation
    {
        return CourierLocation::query()
            ->where('user_id', $courier->id)
            ->latest('recorded_at')
            ->first();
    }
}
