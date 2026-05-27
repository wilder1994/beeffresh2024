<?php

declare(strict_types=1);

namespace App\Events\Operations;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperationsMapUpdated implements ShouldBroadcast
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
            new PrivateChannel('operations.map'),
            new PrivateChannel('operations.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'operations.map.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['map' => $this->payload];
    }
}
