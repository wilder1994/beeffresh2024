<?php

declare(strict_types=1);

namespace App\Events\Tracking;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderTrackingUpdated implements ShouldBroadcast
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
            new PrivateChannel('orders.'.$this->payload['order_id']),
        ];

        $token = $this->payload['tracking_token'] ?? null;
        if (is_string($token) && $token !== '') {
            $channels[] = new Channel('tracking.'.$token);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.tracking.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['tracking' => $this->payload];
    }
}
