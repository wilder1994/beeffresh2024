<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Jobs\Realtime\BroadcastCourierLocationJob;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\User;
use App\Support\Couriers\CourierLocationRateLimiter;
use App\Support\Realtime\CourierLocationPayload;
use App\Support\Realtime\OperationsMapPayload;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;
use Illuminate\Support\Facades\Cache;

final class CourierLocationBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    private const COALESCE_KEY = 'bf:courier:loc:coalesce:';

    public function __construct(
        private readonly CourierLocationRateLimiter $rateLimiter,
        private readonly OperationsMapBroadcastService $mapBroadcast,
        private readonly TrackingBroadcastService $trackingBroadcast,
    ) {}

    public function dispatch(User $courier, CourierLocation $location): void
    {
        if (! $this->rateLimiter->shouldBroadcast(
            $courier->id,
            (float) $location->latitude,
            (float) $location->longitude,
        )) {
            return;
        }

        $activeOrder = $this->activeOrderForCourier($courier);
        $payload = CourierLocationPayload::from($courier, $location, $activeOrder);

        $this->afterCommitBroadcast(function () use ($payload, $activeOrder): void {
            $courierId = (int) $payload['courier_id'];
            if (! Cache::add(self::COALESCE_KEY.$courierId, 1, 3)) {
                return;
            }

            BroadcastCourierLocationJob::dispatch($payload);

            $this->mapBroadcast->dispatchPayload(OperationsMapPayload::fromCourier($payload));

            if ($activeOrder !== null) {
                $this->trackingBroadcast->dispatch($activeOrder);
            }
        });
    }

    private function activeOrderForCourier(User $courier): ?Order
    {
        $assignment = OrderAssignment::query()
            ->where('courier_id', $courier->id)
            ->where('is_active', true)
            ->with('order')
            ->latest('assigned_at')
            ->first();

        return $assignment?->order;
    }
}
