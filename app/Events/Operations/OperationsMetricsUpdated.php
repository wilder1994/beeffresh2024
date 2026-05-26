<?php

declare(strict_types=1);

namespace App\Events\Operations;

use App\Support\Realtime\OperationsMetricsPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OperationsMetricsUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @param  array<string, mixed>  $metrics */
    public function __construct(
        public readonly array $metrics,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('operations.dashboard'),
            new PrivateChannel('operations.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'operations.metrics.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'metrics' => $this->metrics,
        ];
    }

    public static function fromPayloadBuilder(OperationsMetricsPayload $builder): self
    {
        return new self($builder->toArray());
    }
}
