<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Jobs\Realtime\BroadcastOperationsMetricsJob;
use App\Support\Realtime\RealtimeMetricsContext;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;
use Illuminate\Support\Facades\Cache;

final class OperationsMetricsBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    private const LOCK_KEY = 'bf:ops:metrics:coalesce';

    private const LOCK_SECONDS = 2;

    public function dispatch(): void
    {
        $this->afterCommitBroadcast(function (): void {
            if (! Cache::add(self::LOCK_KEY, 1, self::LOCK_SECONDS)) {
                return;
            }

            RealtimeMetricsContext::markMetricsScheduled();
            BroadcastOperationsMetricsJob::dispatch();
        });
    }
}
