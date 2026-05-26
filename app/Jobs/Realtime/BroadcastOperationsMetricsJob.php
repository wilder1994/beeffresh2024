<?php

declare(strict_types=1);

namespace App\Jobs\Realtime;

use App\Events\Operations\OperationsMetricsUpdated;
use App\Support\Realtime\OperationsMetricsPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

final class BroadcastOperationsMetricsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 2;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'bf-operations-metrics';
    }

    public function handle(OperationsMetricsPayload $payloadBuilder): void
    {
        $metrics = Cache::remember(
            'bf:ops:metrics:snapshot',
            2,
            static fn (): array => $payloadBuilder->toArray(),
        );

        event(new OperationsMetricsUpdated($metrics));
    }
}
