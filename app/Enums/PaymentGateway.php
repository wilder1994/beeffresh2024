<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentGateway: string
{
    case Wompi = 'wompi';
    case Paypal = 'paypal';
    case MercadoPago = 'mercadopago';
    case Stripe = 'stripe';
    case Epayco = 'epayco';

    public function label(): string
    {
        return match ($this) {
            self::Wompi => 'Wompi',
            self::Paypal => 'PayPal',
            self::MercadoPago => 'Mercado Pago',
            self::Stripe => 'Stripe',
            self::Epayco => 'ePayco',
        };
    }

    public function isImplemented(): bool
    {
        return $this === self::Wompi;
    }
}
