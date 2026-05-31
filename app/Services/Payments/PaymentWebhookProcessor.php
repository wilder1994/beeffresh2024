<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookStatus;
use App\Events\Orders\OrderPaid;
use App\Events\Payments\PaymentApproved;
use App\Events\Payments\PaymentDeclined;
use App\Events\Payments\PaymentStatusUpdated;
use App\Events\Payments\PaymentWebhookFailed;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PaymentWebhook;
use App\Models\User;
use App\Services\Catalog\CartStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaymentWebhookProcessor
{
    public function __construct(
        private readonly PaymentGatewayManager $gateways,
        private readonly OrderFulfillmentService $fulfillment,
        private readonly CartStorage $cartStorage,
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

            event(new PaymentWebhookFailed(
                (string) data_get($payload, 'data.transaction.reference', 'unknown'),
                $e->getMessage(),
                $payload,
            ));

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

        /** @var array{type: string, user: User, order?: \App\Models\Order}|null $notificationContext */
        $notificationContext = null;

        $payment = DB::transaction(function () use ($payment, $status, $transactionId, $paymentMethod, $gatewayResponse, &$notificationContext): Payment {
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

                    $notificationContext = [
                        'type' => 'approved',
                        'user' => $user,
                        'order' => $order,
                    ];
                }
            }

            if (in_array($status, [PaymentStatus::Declined, PaymentStatus::Failed, PaymentStatus::Expired], true)) {
                $payment->failed_at = now();

                if ($payment->user !== null) {
                    $notificationContext = [
                        'type' => 'declined',
                        'user' => $payment->user,
                    ];
                }
            }

            $payment->save();

            return $payment->fresh(['order']);
        });

        $this->dispatchPaymentNotifications($payment, $notificationContext);

        if ($status === PaymentStatus::Approved) {
            $this->cartStorage->forgetForUser($payment->user_id);
        }

        event(new PaymentStatusUpdated($payment));

        return $payment;
    }

    /**
     * @param  array{type: string, user: User, order?: \App\Models\Order}|null  $context
     */
    private function dispatchPaymentNotifications(Payment $payment, ?array $context): void
    {
        if ($context === null) {
            return;
        }

        try {
            if ($context['type'] === 'approved' && isset($context['order'])) {
                event(new PaymentApproved($payment, $context['order']));
                event(new OrderPaid($context['order'], $payment));

                return;
            }

            if ($context['type'] === 'declined') {
                event(new PaymentDeclined($payment));
            }
        } catch (\Throwable $e) {
            Log::channel('payments')->warning('Payment notification failed', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'type' => $context['type'],
                'error' => $e->getMessage(),
            ]);
        }
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
