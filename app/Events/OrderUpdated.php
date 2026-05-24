<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('operations.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->order->loadMissing(['courier:id,first_name,last_name', 'user:id,first_name,last_name,email']);

        return [
            'order' => [
                'id' => $this->order->id,
                'status' => $this->order->status->value,
                'status_label' => $this->order->status->label(),
                'courier_id' => $this->order->courier_id,
                'courier_name' => $this->order->courier?->name,
                'customer_name' => $this->order->user?->name,
                'total' => (float) $this->order->total,
                'delivery_attempt' => $this->order->delivery_attempt,
                'assigned_at' => $this->order->assigned_at?->toIso8601String(),
                'ready_at' => $this->order->ready_at?->toIso8601String(),
                'picked_up_at' => $this->order->picked_up_at?->toIso8601String(),
                'delivered_at' => $this->order->delivered_at?->toIso8601String(),
                'updated_at' => $this->order->updated_at?->toIso8601String(),
            ],
        ];
    }
}
