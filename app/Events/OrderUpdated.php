<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use App\Support\Realtime\OrderBroadcastPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast de cambios operacionales de pedido.
 * Canales privados (antes era público operations.orders — inseguro).
 */
class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('operations.orders'),
            new PrivateChannel('orders.'.$this->order->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'order' => OrderBroadcastPayload::fromOrder($this->order),
        ];
    }
}
