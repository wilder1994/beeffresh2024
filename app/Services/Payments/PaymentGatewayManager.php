<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use App\Services\Payments\Gateways\EpaycoGateway;
use App\Services\Payments\Gateways\MercadoPagoGateway;
use App\Services\Payments\Gateways\PaypalGateway;
use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\Gateways\WompiGateway;
use InvalidArgumentException;

final class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $drivers = [];

    public function driver(?string $gateway = null): PaymentGatewayInterface
    {
        $gateway ??= (string) config('payments.default_gateway', 'wompi');

        if (isset($this->drivers[$gateway])) {
            return $this->drivers[$gateway];
        }

        $this->drivers[$gateway] = match ($gateway) {
            PaymentGateway::Wompi->value => app(WompiGateway::class),
            PaymentGateway::Paypal->value => app(PaypalGateway::class),
            PaymentGateway::MercadoPago->value => app(MercadoPagoGateway::class),
            PaymentGateway::Stripe->value => app(StripeGateway::class),
            PaymentGateway::Epayco->value => app(EpaycoGateway::class),
            default => throw new InvalidArgumentException("Pasarela desconocida: {$gateway}"),
        };

        return $this->drivers[$gateway];
    }

    public function defaultGateway(): PaymentGatewayInterface
    {
        return $this->driver();
    }
}
