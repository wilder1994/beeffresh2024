<?php

declare(strict_types=1);

namespace App\Events\Payments;

use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Estado de pago actualizado — coexistirá con polling hasta Fase 1.
 */
class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Payment $payment,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payments.'.$this->payment->uuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.status.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        /** @var PaymentService $payments */
        $payments = app(PaymentService::class);

        return [
            'payment' => $payments->realtimePayload($this->payment),
        ];
    }
}
