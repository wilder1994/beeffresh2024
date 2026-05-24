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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        return $this->parseTransaction(
            $transaction,
            (string) ($payload['event'] ?? 'unknown'),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchTransaction(string $transactionId): ?array
    {
        $privateKey = (string) $this->config('private_key');
        if ($privateKey === '') {
            return null;
        }

        $apiBase = rtrim((string) $this->config('api_base'), '/');
        $response = Http::withToken($privateKey)
            ->acceptJson()
            ->timeout(12)
            ->get("{$apiBase}/transactions/{$transactionId}");

        if (! $response->successful()) {
            Log::channel('payments')->warning('Wompi transaction fetch failed', [
                'transaction_id' => $transactionId,
                'http_status' => $response->status(),
            ]);

            return null;
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findLatestTransactionByReference(Payment $payment): ?array
    {
        $privateKey = (string) $this->config('private_key');
        if ($privateKey === '') {
            return null;
        }

        $createdAt = $payment->created_at instanceof Carbon
            ? $payment->created_at
            : Carbon::parse($payment->created_at ?? now());

        $apiBase = rtrim((string) $this->config('api_base'), '/');
        $response = Http::withToken($privateKey)
            ->acceptJson()
            ->timeout(12)
            ->get("{$apiBase}/transactions", [
                'reference' => $payment->reference,
                'from_date' => $createdAt->copy()->subDay()->format('Y-m-d'),
                'until_date' => now()->addDay()->format('Y-m-d'),
                'page' => 1,
                'page_size' => 5,
            ]);

        if (! $response->successful()) {
            Log::channel('payments')->warning('Wompi transaction lookup by reference failed', [
                'reference' => $payment->reference,
                'http_status' => $response->status(),
            ]);

            return null;
        }

        $items = $response->json('data');
        if (! is_array($items) || $items === []) {
            return null;
        }

        /** @var array<string, mixed> $latest */
        $latest = $items[0];

        return $latest;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public function parseTransaction(array $transaction, string $eventType = 'status_poll'): WebhookProcessResult
    {
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
            eventType: $eventType,
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
