<?php

declare(strict_types=1);

namespace App\Events\Couriers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {}

    /** @return array<int, Channel|PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('operations.map'),
            new PrivateChannel('couriers.'.$this->payload['courier_id']),
        ];

        if (($this->payload['order_id'] ?? null) !== null) {
            $channels[] = new PrivateChannel('orders.'.$this->payload['order_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'courier.location.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['location' => $this->payload];
    }
}
