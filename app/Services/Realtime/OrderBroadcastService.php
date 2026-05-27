<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Events\OrderUpdated;
use App\Models\Order;
use App\Support\Realtime\RealtimeMetricsContext;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;

final class OrderBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    public function __construct(
        private readonly OperationsMetricsBroadcastService $metricsBroadcast,
        private readonly TrackingBroadcastService $trackingBroadcast,
        private readonly OperationsMapBroadcastService $mapBroadcast,
    ) {}

    public function dispatch(Order $order, bool $dispatchMetrics = true): void
    {
        $skipMetrics = ! $dispatchMetrics || RealtimeMetricsContext::wasMetricsScheduled();

        $this->afterCommitBroadcast(function () use ($order, $skipMetrics): void {
            $fresh = $order->fresh(['user', 'courier', 'items']) ?? $order;

            event(new OrderUpdated($fresh));

            $this->trackingBroadcast->dispatch($fresh);
            $this->mapBroadcast->dispatchForOrder($fresh);

            if (! $skipMetrics) {
                $this->metricsBroadcast->dispatch();
            }
        });
    }
}
