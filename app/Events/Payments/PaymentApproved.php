<?php

declare(strict_types=1);

namespace App\Events\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
        public Order $order,
    ) {}
}
