<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookStatus;
use App\Events\Payments\PaymentApproved;
use App\Events\Payments\PaymentDeclined;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PaymentWebhook;
use App\Models\User;
use App\Notifications\Orders\OrderConfirmedNotification;
use App\Notifications\Payments\PaymentApprovedNotification;
use App\Notifications\Payments\PaymentDeclinedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaymentWebhookProcessor
{
    public function __construct(
        private readonly PaymentGatewayManager $gateways,
        private readonly OrderFulfillmentService $fulfillment,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $gatewayKey, array $payload, ?string $checksumHeader, ?Request $request = null): PaymentWebhook
    {
        $driver = $this->gateways->driver($gatewayKey);
        $idempotencyKey = $this->idempotencyKey($gatewayKey, $payload);

        $existing = PaymentWebhook::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return $existing;
        }

        $checksumValid = $driver->verifyWebhookSignature($payload, $checksumHeader);

        $webhook = PaymentWebhook::query()->create([
            'gateway' => $driver->gateway(),
            'event_type' => (string) ($payload['event'] ?? null),
            'idempotency_key' => $idempotencyKey,
            'payload' => $payload,
            'signature' => $checksumHeader,
            'checksum_valid' => $checksumValid,
            'status' => PaymentWebhookStatus::Received,
        ]);

        if (! $checksumValid) {
            $webhook->status = PaymentWebhookStatus::Ignored;
            $webhook->processed_at = now();
            $webhook->save();

            Log::channel('payments')->warning('Wompi webhook ignored: invalid checksum', [
                'event' => $payload['event'] ?? null,
                'reference' => data_get($payload, 'data.transaction.reference'),
            ]);

            return $webhook;
        }

        try {
            $result = $driver->parseWebhookPayload($payload);
            $payment = Payment::query()->where('reference', $result->reference)->first();

            if ($payment === null) {
                $webhook->status = PaymentWebhookStatus::Ignored;
                $webhook->processed_at = now();
                $webhook->save();

                Log::channel('payments')->warning('Wompi webhook ignored: payment not found', [
                    'reference' => $result->reference,
                ]);

                return $webhook;
            }

            PaymentAttempt::query()->create([
                'payment_id' => $payment->id,
                'type' => PaymentAttemptType::Webhook,
                'status' => $result->status->value,
                'payload' => $payload,
                'response' => $result->transaction,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);

            $this->applyPaymentStatus($payment, $result->status, $result->transactionId, $result->paymentMethod, $result->transaction);

            $webhook->status = PaymentWebhookStatus::Processed;
            $webhook->processed_at = now();
            $webhook->save();

            Log::channel('payments')->info('Wompi webhook processed', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'status' => $result->status->value,
                'transaction_id' => $result->transactionId,
                'event' => $payload['event'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $webhook->status = PaymentWebhookStatus::Failed;
            $webhook->processed_at = now();
            $webhook->save();

            Log::channel('payments')->error('Wompi webhook processing failed', [
                'reference' => data_get($payload, 'data.transaction.reference'),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $webhook;
    }

    /**
     * @param  array<string, mixed>  $gatewayResponse
     */
    public function applyPaymentStatus(
        Payment $payment,
        PaymentStatus $status,
        ?string $transactionId = null,
        ?string $paymentMethod = null,
        array $gatewayResponse = [],
    ): Payment {
        if ($payment->status === PaymentStatus::Approved && $status !== PaymentStatus::Refunded) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $status, $transactionId, $paymentMethod, $gatewayResponse): Payment {
            $payment->status = $status;
            $payment->transaction_id = $transactionId ?? $payment->transaction_id;
            $payment->payment_method = $paymentMethod ?? $payment->payment_method;
            $payment->gateway_response = $gatewayResponse;

            if ($status === PaymentStatus::Approved) {
                $payment->paid_at = now();
                $payment->failed_at = null;

                if ($payment->order_id === null) {
                    /** @var User $user */
                    $user = $payment->user()->firstOrFail();
                    $session = CheckoutSessionData::fromMetadata($payment->metadata ?? []);
                    $order = $this->fulfillment->fulfillFromPayment($payment, $user, $session);

                    event(new PaymentApproved($payment->fresh(), $order));
                    $user->notify(new PaymentApprovedNotification($payment->fresh()));
                    $user->notify(new OrderConfirmedNotification($order));
                }
            }

            if (in_array($status, [PaymentStatus::Declined, PaymentStatus::Failed, PaymentStatus::Expired], true)) {
                $payment->failed_at = now();
                event(new PaymentDeclined($payment->fresh()));
                $payment->user?->notify(new PaymentDeclinedNotification($payment->fresh()));
            }

            $payment->save();

            return $payment->fresh(['order']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function idempotencyKey(string $gatewayKey, array $payload): string
    {
        $transactionId = data_get($payload, 'data.transaction.id');
        $event = (string) ($payload['event'] ?? 'event');
        $timestamp = (string) ($payload['timestamp'] ?? '');

        if (is_string($transactionId) && $transactionId !== '') {
            return hash('sha256', $gatewayKey.'|'.$event.'|'.$transactionId.'|'.$timestamp);
        }

        return hash('sha256', $gatewayKey.'|'.Str::jsonEncode($payload));
    }
}
