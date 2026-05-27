<?php

declare(strict_types=1);

return [
    /*
    | Minutos sin domiciliario antes de alertar a operaciones (comando programable).
    */
    'courier_claim_timeout_minutes' => (int) env('ORDER_COURIER_CLAIM_TIMEOUT_MINUTES', 45),
];
