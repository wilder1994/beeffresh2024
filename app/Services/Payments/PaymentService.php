<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\DataTransferObjects\Payments\GatewayCheckoutData;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Services\Catalog\CartSessionService;
use App\Services\Payments\Gateways\WompiGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayManager $gateways,
        private readonly CheckoutQuoteService $quotes,
        private readonly PaymentWebhookProcessor $webhookProcessor,
        private readonly CartSessionService $cartSession,
    ) {}

    /**
     * @param  array<string|int, array<string, mixed>>  $cartSession
     */
    public function initiate(
        User $user,
        array $cartSession,
        ?string $gateway = null,
        ?string $notes = null,
        ?Request $request = null,
    ): Payment {
        $gatewayKey = $gateway ?? (string) config('payments.default_gateway', 'wompi');
        $driver = $this->gateways->driver($gatewayKey);

        if (! $driver->isConfigured()) {
            throw new RuntimeException('La pasarela de pagos no está configurada. Contacta al administrador.');
        }

        $session = $this->quotes->build($user, $cartSession, $notes);
        $amountInCents = (int) round($session->total * 100);

        if ($amountInCents <= 0) {
            throw new RuntimeException('El total del pedido debe ser mayor a cero.');
        }

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'gateway' => PaymentGateway::from($gatewayKey),
            'reference' => $this->generateReference(),
            'amount' => number_format($session->total, 2, '.', ''),
            'amount_in_cents' => $amountInCents,
            'currency' => (string) config('payments.currency', 'COP'),
            'status' => PaymentStatus::PendingPayment,
            'metadata' => $session->toMetadata(),
            'expires_at' => now()->addMinutes((int) config('payments.payment_ttl_minutes', 60)),
        ]);

        PaymentAttempt::query()->create([
            'payment_id' => $payment->id,
            'type' => PaymentAttemptType::CheckoutInit,
            'status' => PaymentStatus::PendingPayment->value,
            'payload' => $session->toMetadata(),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);

        return $payment->fresh();
    }

    public function checkoutWidget(Payment $payment): GatewayCheckoutData
    {
        $this->guardPaymentAccessible($payment);

        if ($payment->isExpired()) {
            $payment->status = PaymentStatus::Expired;
            $payment->save();
            throw new RuntimeException('Este intento de pago expiró. Vuelve al carrito e intenta de nuevo.');
        }

        $session = CheckoutSessionData::fromMetadata($payment->metadata ?? []);

        return $this->gateways->driver($payment->gateway->value)->buildCheckout($payment, $session);
    }

    public function guardPaymentAccessible(Payment $payment, ?User $user = null): void
    {
        $user ??= auth()->user();
        if ($user === null || $payment->user_id !== $user->id) {
            abort(403);
        }
    }

    public function refreshForPoll(Payment $payment, ?string $transactionId = null): Payment
    {
        $payment->refresh();

        if ($payment->status->isTerminal()) {
            return $payment->load('order');
        }

        if ($transactionId !== null && $transactionId !== '') {
            return $this->syncFromGateway($payment, $transactionId)->load('order');
        }

        return $payment->load('order');
    }

    public function syncFromGateway(Payment $payment, ?string $transactionId = null): Payment
    {
        if ($payment->status->isTerminal()) {
            return $payment;
        }

        if ($payment->gateway !== PaymentGateway::Wompi) {
            return $payment;
        }

        $driver = $this->gateways->driver($payment->gateway->value);
        if (! $driver instanceof WompiGateway) {
            return $payment;
        }

        $txId = filled($transactionId) ? $transactionId : $payment->transaction_id;
        if (! filled($txId)) {
            return $payment;
        }

        $transaction = $driver->fetchTransaction((string) $txId);
        if ($transaction === null) {
            return $payment;
        }

        $result = $driver->parseTransaction($transaction);

        if ($result->reference !== '' && $result->reference !== $payment->reference) {
            Log::channel('payments')->warning('Wompi transaction reference mismatch', [
                'payment_id' => $payment->id,
                'expected_reference' => $payment->reference,
                'received_reference' => $result->reference,
            ]);

            return $payment;
        }

        PaymentAttempt::query()->create([
            'payment_id' => $payment->id,
            'type' => PaymentAttemptType::StatusPoll,
            'status' => $result->status->value,
            'payload' => ['transaction_id' => $txId],
            'response' => $transaction,
        ]);

        $updated = $this->webhookProcessor->applyPaymentStatus(
            $payment,
            $result->status,
            $result->transactionId,
            $result->paymentMethod,
            $result->transaction,
        );

        Log::channel('payments')->info('Payment synced from Wompi API', [
            'payment_id' => $updated->id,
            'reference' => $updated->reference,
            'status' => $updated->status->value,
            'transaction_id' => $updated->transaction_id,
        ]);

        return $updated;
    }

    /**
     * @return array{
     *     status: string,
     *     status_label: string,
     *     terminal: bool,
     *     reference: string,
     *     cart_count: int,
     *     order_id: int|null,
     *     redirect_url: string|null,
     *     tracking_url: string|null,
     *     message: string|null
     * }
     */
    public function pollPayload(Payment $payment): array
    {
        $payment->loadMissing('order');

        $cartCount = $this->clearCartSessionIfApproved($payment);

        return [
            'status' => $payment->status->value,
            'status_label' => $payment->status->label(),
            'terminal' => $payment->status->isTerminal(),
            'reference' => $payment->reference,
            'cart_count' => $cartCount,
            'order_id' => $payment->order_id,
            'redirect_url' => $this->redirectUrlForStatus($payment),
            'tracking_url' => $payment->order !== null
                ? route('orders.tracking.show', $payment->order)
                : null,
            'message' => $this->pollMessage($payment),
        ];
    }

    public function clearCartSessionIfApproved(Payment $payment): int
    {
        if ($payment->status === PaymentStatus::Approved) {
            session()->forget('carrito');
        }

        return (int) round($this->cartSession->totalItemCount(session()->get('carrito', [])));
    }

    private function redirectUrlForStatus(Payment $payment): ?string
    {
        return match ($payment->status) {
            PaymentStatus::Approved => route('payments.success', $payment->uuid),
            PaymentStatus::Processing, PaymentStatus::PendingPayment => route('payments.pending', $payment->uuid),
            PaymentStatus::Declined, PaymentStatus::Failed, PaymentStatus::Expired => route('payments.failed', $payment->uuid),
            default => null,
        };
    }

    private function pollMessage(Payment $payment): ?string
    {
        return match ($payment->status) {
            PaymentStatus::Approved => 'Pago aprobado. Redirigiendo…',
            PaymentStatus::Processing, PaymentStatus::PendingPayment => 'Confirmando con la entidad financiera…',
            PaymentStatus::Declined => 'El pago fue rechazado.',
            PaymentStatus::Failed => 'No se pudo completar el pago.',
            PaymentStatus::Expired => 'El intento de pago expiró.',
            default => null,
        };
    }

    private function generateReference(): string
    {
        do {
            $reference = 'BF-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
