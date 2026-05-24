<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case PendingPayment = 'pending_payment';
    case Processing = 'processing';
    case Approved = 'approved';
    case Declined = 'declined';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pendiente de pago',
            self::Processing => 'Procesando',
            self::Approved => 'Aprobado',
            self::Declined => 'Rechazado',
            self::Failed => 'Fallido',
            self::Expired => 'Expirado',
            self::Refunded => 'Reembolsado',
            self::PartiallyRefunded => 'Reembolso parcial',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-amber-100 text-amber-800',
            self::Processing => 'bg-blue-100 text-blue-800',
            self::Approved => 'bg-emerald-100 text-emerald-800',
            self::Declined => 'bg-red-100 text-red-800',
            self::Failed => 'bg-red-100 text-red-800',
            self::Expired => 'bg-gray-100 text-gray-800',
            self::Refunded => 'bg-purple-100 text-purple-800',
            self::PartiallyRefunded => 'bg-purple-100 text-purple-800',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Approved,
            self::Declined,
            self::Failed,
            self::Expired,
            self::Refunded,
            self::PartiallyRefunded,
        ], true);
    }

    public function allowsRetry(): bool
    {
        return in_array($this, [
            self::Declined,
            self::Failed,
            self::Expired,
        ], true);
    }
}
