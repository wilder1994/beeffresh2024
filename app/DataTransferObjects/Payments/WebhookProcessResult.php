<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

use App\Enums\PaymentStatus;

final readonly class WebhookProcessResult
{
    /**
     * @param  array<string, mixed>  $transaction
     */
    public function __construct(
        public string $reference,
        public ?string $transactionId,
        public PaymentStatus $status,
        public ?string $paymentMethod,
        public array $transaction,
        public string $eventType,
    ) {}
}
