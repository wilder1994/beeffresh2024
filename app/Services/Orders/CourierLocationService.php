<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\CourierLocation;
use App\Models\User;
use App\Services\Realtime\CourierLocationBroadcastService;

final class CourierLocationService
{
    public function __construct(
        private readonly CourierLocationBroadcastService $locationBroadcast,
    ) {}

    public function record(
        User $courier,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
    ): CourierLocation {
        $location = CourierLocation::query()->create([
            'user_id' => $courier->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'recorded_at' => now(),
        ]);

        $this->locationBroadcast->dispatch($courier, $location);

        return $location;
    }

    public function latestFor(User $courier): ?CourierLocation
    {
        return CourierLocation::query()
            ->where('user_id', $courier->id)
            ->latest('recorded_at')
            ->first();
    }
}
