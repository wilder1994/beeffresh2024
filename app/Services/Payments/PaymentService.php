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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayManager $gateways,
        private readonly CheckoutQuoteService $quotes,
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

    private function generateReference(): string
    {
        do {
            $reference = 'BF-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
