<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\DataTransferObjects\Payments\GatewayCheckoutData;
use App\DataTransferObjects\Payments\WebhookProcessResult;
use App\Enums\PaymentGateway;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function gateway(): PaymentGateway;

    public function isConfigured(): bool;

    public function buildCheckout(Payment $payment, CheckoutSessionData $session): GatewayCheckoutData;

    public function verifyWebhookSignature(array $payload, ?string $checksumHeader): bool;

    public function parseWebhookPayload(array $payload): WebhookProcessResult;
}
