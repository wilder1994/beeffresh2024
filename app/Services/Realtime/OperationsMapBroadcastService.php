<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Jobs\Realtime\BroadcastOperationsMapJob;
use App\Models\Order;
use App\Support\Realtime\OperationsMapPayload;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;
use Illuminate\Support\Facades\Cache;

final class OperationsMapBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    private const LOCK_KEY = 'bf:ops:map:coalesce';

    private const LOCK_SECONDS = 1;

    public function dispatchForOrder(Order $order): void
    {
        if ($order->shipping_latitude === null || $order->shipping_longitude === null) {
            return;
        }

        $this->dispatchPayload(OperationsMapPayload::fromOrder($order));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchPayload(array $payload): void
    {
        $this->afterCommitBroadcast(function () use ($payload): void {
            if (! Cache::add(self::LOCK_KEY, 1, self::LOCK_SECONDS)) {
                return;
            }

            BroadcastOperationsMapJob::dispatch($payload);
        });
    }
}
