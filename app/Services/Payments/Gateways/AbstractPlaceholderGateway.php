<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\DataTransferObjects\Payments\GatewayCheckoutData;
use App\DataTransferObjects\Payments\WebhookProcessResult;
use App\Enums\PaymentGateway;
use App\Models\Payment;
use RuntimeException;

abstract class AbstractPlaceholderGateway implements PaymentGatewayInterface
{
    abstract public function gateway(): PaymentGateway;

    public function isConfigured(): bool
    {
        return false;
    }

    public function buildCheckout(Payment $payment, CheckoutSessionData $session): GatewayCheckoutData
    {
        throw new RuntimeException(sprintf(
            'La pasarela %s aún no está integrada.',
            $this->gateway()->label(),
        ));
    }

    public function verifyWebhookSignature(array $payload, ?string $checksumHeader): bool
    {
        return false;
    }

    public function parseWebhookPayload(array $payload): WebhookProcessResult
    {
        throw new RuntimeException('Webhook no soportado para esta pasarela.');
    }
}
