<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentAttemptType: string
{
    case CheckoutInit = 'checkout_init';
    case Webhook = 'webhook';
    case RedirectReturn = 'redirect_return';
    case StatusPoll = 'status_poll';
}
