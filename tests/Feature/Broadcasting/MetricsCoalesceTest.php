<?php

declare(strict_types=1);

namespace Tests\Feature\Broadcasting;

use App\Jobs\Realtime\BroadcastOperationsMetricsJob;
use App\Services\Realtime\OperationsMetricsBroadcastService;
use App\Services\Realtime\StockBroadcastService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MetricsCoalesceTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_metrics_broadcast_coalesces_to_single_job(): void
    {
        Bus::fake();
        Cache::flush();

        $service = app(OperationsMetricsBroadcastService::class);

        $service->dispatch();
        $service->dispatch();
        $service->dispatch();

        Bus::assertDispatched(BroadcastOperationsMetricsJob::class, 1);
    }

    public function test_stock_dispatch_many_emits_single_metrics_job(): void
    {
        Bus::fake();
        Cache::flush();

        $products = Product::factory()->count(3)->create();

        app(StockBroadcastService::class)->dispatchMany($products->pluck('id'));

        Bus::assertDispatched(BroadcastOperationsMetricsJob::class, 1);
    }
}
