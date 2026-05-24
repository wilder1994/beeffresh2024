<?php

declare(strict_types=1);

namespace App\Notifications\Payments;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Payment $payment,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pago aprobado · BEEF FRESH')
            ->greeting('¡Gracias por tu compra!')
            ->line('Tu pago '.$this->payment->reference.' fue aprobado correctamente.')
            ->action('Ver seguimiento', route('payments.status', $this->payment->uuid));
    }
}
