<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Events\Tracking\OrderTrackingUpdated;
use App\Models\Order;
use App\Support\Realtime\TrackingPayload;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;
use Illuminate\Support\Facades\Cache;

final class TrackingBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    private const COALESCE_KEY = 'bf:tracking:coalesce:';

    public function __construct(
        private readonly TrackingPayload $trackingPayload,
    ) {}

    public function dispatch(Order $order): void
    {
        $this->afterCommitBroadcast(function () use ($order): void {
            if (! Cache::add(self::COALESCE_KEY.$order->id, 1, 2)) {
                return;
            }

            $fresh = $order->fresh(['courier', 'statusLogs']) ?? $order;
            $payload = $this->trackingPayload->forOrder($fresh);

            event(new OrderTrackingUpdated($payload));
        });
    }
}
