<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case OnlineSimulated = 'online_simulated';
    case Online = 'online';
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::OnlineSimulated => 'Pago en línea (simulado)',
            self::Online => 'Pago en línea',
            self::Cash => 'Efectivo',
            self::Card => 'Tarjeta',
            self::Transfer => 'Transferencia',
        };
    }
}
