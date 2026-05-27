<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Events\Couriers\CourierPresenceUpdated;
use App\Models\Order;
use App\Models\User;
use App\Support\Realtime\CourierPresencePayload;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;

final class CourierPresenceBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    public function dispatch(User $courier, ?Order $activeOrder = null): void
    {
        $this->afterCommitBroadcast(function () use ($courier, $activeOrder): void {
            $fresh = $courier->fresh('employeeProfile') ?? $courier;
            $payload = CourierPresencePayload::fromCourier($fresh, $activeOrder);

            event(new CourierPresenceUpdated($payload));
        });
    }
}
