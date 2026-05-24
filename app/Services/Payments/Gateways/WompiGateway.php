<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\DataTransferObjects\Payments\GatewayCheckoutData;
use App\DataTransferObjects\Payments\WebhookProcessResult;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Arr;

final class WompiGateway implements PaymentGatewayInterface
{
    public function gateway(): PaymentGateway
    {
        return PaymentGateway::Wompi;
    }

    public function isConfigured(): bool
    {
        return filled($this->config('public_key'))
            && filled($this->config('integrity_secret'));
    }

    public function buildCheckout(Payment $payment, CheckoutSessionData $session): GatewayCheckoutData
    {
        $integrity = $this->integritySignature(
            $payment->reference,
            (int) $payment->amount_in_cents,
            $payment->currency,
        );

        return new GatewayCheckoutData(
            widgetScriptUrl: (string) $this->config('widget_url'),
            widgetConfig: [
                'currency' => $payment->currency,
                'amountInCents' => (int) $payment->amount_in_cents,
                'reference' => $payment->reference,
                'publicKey' => (string) $this->config('public_key'),
                'signature' => ['integrity' => $integrity],
                'redirectUrl' => route('payments.return', $payment->uuid),
                'customerData' => [
                    'email' => $payment->user?->email ?? Arr::get($session->shipping, 'shipping_recipient_name'),
                ],
            ],
        );
    }

    public function verifyWebhookSignature(array $payload, ?string $checksumHeader): bool
    {
        $secret = (string) $this->config('events_secret');
        if ($secret === '') {
            return false;
        }

        $signature = $payload['signature'] ?? null;
        if (! is_array($signature)) {
            return false;
        }

        $properties = $signature['properties'] ?? [];
        if (! is_array($properties) || $properties === []) {
            return false;
        }

        $timestamp = $payload['timestamp'] ?? null;
        if (! is_numeric($timestamp)) {
            return false;
        }

        $concatenated = '';
        foreach ($properties as $property) {
            $value = Arr::get($payload['data'] ?? [], (string) $property);
            $concatenated .= $value ?? '';
        }
        $concatenated .= (string) $timestamp;
        $concatenated .= $secret;

        $expected = hash('sha256', $concatenated);
        $provided = (string) ($signature['checksum'] ?? $checksumHeader ?? '');

        return hash_equals($expected, $provided);
    }

    public function parseWebhookPayload(array $payload): WebhookProcessResult
    {
        $transaction = $payload['data']['transaction'] ?? [];
        if (! is_array($transaction)) {
            $transaction = [];
        }

        $reference = (string) ($transaction['reference'] ?? '');
        $transactionId = isset($transaction['id']) ? (string) $transaction['id'] : null;
        $wompiStatus = strtoupper((string) ($transaction['status'] ?? 'PENDING'));
        $paymentMethod = isset($transaction['payment_method_type'])
            ? (string) $transaction['payment_method_type']
            : null;

        return new WebhookProcessResult(
            reference: $reference,
            transactionId: $transactionId,
            status: $this->mapWompiStatus($wompiStatus),
            paymentMethod: $paymentMethod,
            transaction: $transaction,
            eventType: (string) ($payload['event'] ?? 'unknown'),
        );
    }

    public function integritySignature(string $reference, int $amountInCents, string $currency): string
    {
        $secret = (string) $this->config('integrity_secret');
        $payload = $reference.$amountInCents.$currency.$secret;

        return hash('sha256', $payload);
    }

    private function mapWompiStatus(string $status): PaymentStatus
    {
        return match ($status) {
            'APPROVED' => PaymentStatus::Approved,
            'DECLINED' => PaymentStatus::Declined,
            'VOIDED' => PaymentStatus::Failed,
            'ERROR' => PaymentStatus::Failed,
            'PENDING' => PaymentStatus::Processing,
            default => PaymentStatus::Processing,
        };
    }

    /**
     * @return mixed
     */
    private function config(string $key): mixed
    {
        return config('payments.gateways.wompi.'.$key);
    }
}
