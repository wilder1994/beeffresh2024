<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderFailed;
use App\Events\Orders\OrderInTransit;
use App\Events\Orders\OrderPickedUp;
use App\Events\Orders\OrderPreparing;
use App\Events\Orders\OrderReadyForDelivery;
use App\Events\Orders\OrderReturnedToStore;
use App\Models\Order;
use App\Models\User;

final class OrderDomainEventDispatcher
{
    public function dispatchStatusTransition(
        Order $order,
        OrderStatus $from,
        OrderStatus $to,
        ?User $actor = null,
        ?string $note = null,
    ): void {
        match ($to) {
            OrderStatus::Preparing => event(new OrderPreparing($order, $actor)),
            OrderStatus::ReadyForDelivery => event(new OrderReadyForDelivery($order, $actor)),
            OrderStatus::PickedUp => event(new OrderPickedUp($order, $actor)),
            OrderStatus::InTransit => event(new OrderInTransit($order, $actor)),
            OrderStatus::Delivered => event(new OrderDelivered($order, $actor)),
            OrderStatus::DeliveryFailed => event(new OrderFailed($order, $actor, $note)),
            OrderStatus::ReturnedToStore => event(new OrderReturnedToStore($order, $actor)),
            default => null,
        };
    }
}
