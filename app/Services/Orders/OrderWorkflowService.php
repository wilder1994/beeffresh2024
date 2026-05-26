<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Services\Realtime\OrderBroadcastService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class OrderWorkflowService
{
    public function __construct(
        private readonly OrderDomainEventDispatcher $domainEvents,
        private readonly OrderBroadcastService $orderBroadcast,
    ) {}

    public function transition(
        Order $order,
        OrderStatus $toStatus,
        ?User $actor = null,
        ?string $note = null,
        bool $broadcast = true,
    ): Order {
        $fromStatus = $order->status;

        if (! $fromStatus->canTransitionTo($toStatus)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Transición inválida de %s a %s.',
                    $fromStatus->label(),
                    $toStatus->label()
                )
            );
        }

        return DB::transaction(function () use ($order, $fromStatus, $toStatus, $actor, $note, $broadcast): Order {
            $order->status = $toStatus;
            $this->applyStatusTimestamps($order, $toStatus);
            $order->save();

            OrderStatusLog::query()->create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'user_id' => $actor?->id,
                'note' => $note,
            ]);

            $fresh = $order->fresh(['user', 'courier', 'items']);

            if ($broadcast) {
                $this->orderBroadcast->dispatch($fresh);
            }

            $this->domainEvents->dispatchStatusTransition($fresh, $fromStatus, $toStatus, $actor, $note);

            return $fresh;
        });
    }

    public function transitionSilent(
        Order $order,
        OrderStatus $toStatus,
        ?User $actor = null,
        ?string $note = null,
    ): Order {
        return $this->transition($order, $toStatus, $actor, $note, broadcast: false);
    }

    public function logInitialStatus(Order $order, ?User $actor = null, ?string $note = null): OrderStatusLog
    {
        return OrderStatusLog::query()->create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => $order->status,
            'user_id' => $actor?->id,
            'note' => $note ?? 'Pedido creado.',
        ]);
    }

    private function applyStatusTimestamps(Order $order, OrderStatus $toStatus): void
    {
        $now = now();

        match ($toStatus) {
            OrderStatus::ReadyForDelivery => $order->ready_at ??= $now,
            OrderStatus::PickedUp => $order->picked_up_at ??= $now,
            OrderStatus::Delivered => $order->delivered_at ??= $now,
            default => null,
        };
    }
}
