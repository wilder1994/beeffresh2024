<?php

declare(strict_types=1);

namespace App\Jobs\Realtime;

use App\Events\Operations\OperationsMapUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BroadcastOperationsMapJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 1;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        $orderId = $this->payload['order_id'] ?? null;
        $courierId = $this->payload['courier_id'] ?? null;

        if ($orderId !== null) {
            return 'bf-ops-map-order-'.$orderId;
        }

        return 'bf-ops-map-courier-'.($courierId ?? '0');
    }

    public function handle(): void
    {
        event(new OperationsMapUpdated($this->payload));
    }
}
