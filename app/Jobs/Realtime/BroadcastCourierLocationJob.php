<?php

declare(strict_types=1);

namespace App\Jobs\Realtime;

use App\Events\Couriers\CourierLocationUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BroadcastCourierLocationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 3;

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
        return 'bf-courier-loc-'.($this->payload['courier_id'] ?? '0');
    }

    public function handle(): void
    {
        event(new CourierLocationUpdated($this->payload));
    }
}
