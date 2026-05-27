<?php

declare(strict_types=1);

namespace App\Events\Couriers;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierPresenceUpdated implements ShouldBroadcast
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

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('operations.couriers'),
            new PrivateChannel('operations.map'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'courier.presence.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['presence' => $this->payload];
    }
}
