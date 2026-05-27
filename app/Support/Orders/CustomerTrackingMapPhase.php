<?php

declare(strict_types=1);

namespace App\Support\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

final class CustomerTrackingMapPhase
{
    public const WAITING = 'waiting';

    public const LIVE = 'live';

    public const CLOSED = 'closed';

    public static function forOrder(Order $order): string
    {
        return match ($order->status) {
            OrderStatus::PickedUp, OrderStatus::InTransit => self::LIVE,
            OrderStatus::Delivered => self::CLOSED,
            default => self::WAITING,
        };
    }
}
