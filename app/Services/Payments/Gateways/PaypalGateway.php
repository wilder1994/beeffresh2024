<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentGateway;

final class PaypalGateway extends AbstractPlaceholderGateway
{
    public function gateway(): PaymentGateway
    {
        return PaymentGateway::Paypal;
    }
}
