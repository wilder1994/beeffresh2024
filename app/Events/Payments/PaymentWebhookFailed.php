<?php

declare(strict_types=1);

namespace App\Events\Payments;

use Illuminate\Foundation\Events\Dispatchable;

final class PaymentWebhookFailed
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $reference,
        public readonly string $error,
        public readonly array $payload = [],
    ) {}
}
