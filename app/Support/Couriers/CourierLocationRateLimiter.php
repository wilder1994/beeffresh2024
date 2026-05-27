<?php

declare(strict_types=1);

namespace App\Support\Couriers;

use Illuminate\Support\Facades\Cache;

final class CourierLocationRateLimiter
{
    private const CACHE_PREFIX = 'bf:courier:loc:broadcast:';

    public function shouldBroadcast(
        int $courierId,
        float $latitude,
        float $longitude,
        bool $onActiveRoute = false,
    ): bool {
        $cfg = config('realtime.courier_location_broadcast', []);
        $minSeconds = $onActiveRoute
            ? (int) ($cfg['min_seconds_active'] ?? 2)
            : (int) ($cfg['min_seconds_idle'] ?? 8);
        $minMeters = $onActiveRoute
            ? (float) ($cfg['min_meters_active'] ?? 12)
            : (float) ($cfg['min_meters_idle'] ?? 35);
        $staleSeconds = $onActiveRoute ? 20 : 45;

        $key = self::CACHE_PREFIX.$courierId;
        /** @var array{lat: float, lng: float, at: int}|null $last */
        $last = Cache::get($key);

        $now = now()->timestamp;

        if ($last === null) {
            Cache::put($key, ['lat' => $latitude, 'lng' => $longitude, 'at' => $now], 600);

            return true;
        }

        if (($now - (int) $last['at']) < $minSeconds) {
            return false;
        }

        $meters = $this->distanceMeters(
            (float) $last['lat'],
            (float) $last['lng'],
            $latitude,
            $longitude,
        );

        if ($meters < $minMeters && ($now - (int) $last['at']) < $staleSeconds) {
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
