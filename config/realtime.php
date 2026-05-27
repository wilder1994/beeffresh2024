<?php

declare(strict_types=1);

return [
    /*
    | GPS domiciliario (panel /domiciliario) → mapa operativo y seguimiento cliente.
    */
    'courier_gps' => [
        'interval_active_ms' => (int) env('BF_COURIER_GPS_ACTIVE_MS', 12_000),
        'interval_idle_ms' => (int) env('BF_COURIER_GPS_IDLE_MS', 45_000),
        'min_send_meters' => (float) env('BF_COURIER_GPS_MIN_METERS', 8),
    ],

    /*
    | Throttle broadcast WS (CourierLocationRateLimiter).
    */
    'courier_location_broadcast' => [
        'min_seconds_active' => 2,
        'min_meters_active' => 12,
        'min_seconds_idle' => 8,
        'min_meters_idle' => 35,
    ],
];
