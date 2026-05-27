<?php

declare(strict_types=1);

namespace App\Support\Couriers;

use Illuminate\Support\Facades\Cache;

final class CourierLocationRateLimiter
{
    private const CACHE_PREFIX = 'bf:courier:loc:broadcast:';

    private const MIN_SECONDS = 3;

    private const MIN_METERS = 25;

    public function shouldBroadcast(int $courierId, float $latitude, float $longitude): bool
    {
        $key = self::CACHE_PREFIX.$courierId;
        /** @var array{lat: float, lng: float, at: int}|null $last */
        $last = Cache::get($key);

        $now = now()->timestamp;

        if ($last === null) {
            Cache::put($key, ['lat' => $latitude, 'lng' => $longitude, 'at' => $now], 600);

            return true;
        }

        if (($now - (int) $last['at']) < self::MIN_SECONDS) {
            return false;
        }

        $meters = $this->distanceMeters(
            (float) $last['lat'],
            (float) $last['lng'],
            $latitude,
            $longitude,
        );

        if ($meters < self::MIN_METERS && ($now - (int) $last['at']) < 30) {
            return false;
        }

        Cache::put($key, ['lat' => $latitude, 'lng' => $longitude, 'at' => $now], 600);

        return true;
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
