<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\Payments\CheckoutQuoteService;
use App\Services\Payments\OrderFulfillmentService;
use InvalidArgumentException;
use RuntimeException;

/**
 * @deprecated Usar PaymentService + OrderFulfillmentService tras pago aprobado.
 */
class CheckoutService
{
    public function __construct(
        private readonly CheckoutQuoteService $quotes,
        private readonly OrderFulfillmentService $fulfillment,
    ) {}

    /**
     * @param  array<string|int, array<string, mixed>>  $cartSession
     */
    public function finalizeCart(User $user, array $cartSession): Order
    {
        throw new RuntimeException(
            'El checkout directo fue reemplazado por el flujo de pago en línea. Usa PaymentService::initiate().'
        );
    }
}
