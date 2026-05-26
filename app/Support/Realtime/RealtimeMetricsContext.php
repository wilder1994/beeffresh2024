<?php

declare(strict_types=1);

namespace App\Support\Realtime;

/** Evita métricas duplicadas stock + order en la misma petición HTTP. */
final class RealtimeMetricsContext
{
    private static bool $metricsScheduledThisRequest = false;

    public static function markMetricsScheduled(): void
    {
        self::$metricsScheduledThisRequest = true;
    }

    public static function wasMetricsScheduled(): bool
    {
        return self::$metricsScheduledThisRequest;
    }

    public static function reset(): void
    {
        self::$metricsScheduledThisRequest = false;
    }
}
