<?php

declare(strict_types=1);

namespace App\Services\Realtime;

use App\Events\Catalog\ProductAvailabilityUpdated;
use App\Events\Catalog\ProductStockUpdated;
use App\Models\Product;
use App\Services\Realtime\Concerns\DispatchesBroadcastAfterCommit;
use Illuminate\Support\Collection;

final class StockBroadcastService
{
    use DispatchesBroadcastAfterCommit;

    public function __construct(
        private readonly OperationsMetricsBroadcastService $metricsBroadcast,
    ) {}

    public function dispatch(Product $product, bool $dispatchMetrics = true): void
    {
        $this->afterCommitBroadcast(function () use ($product, $dispatchMetrics): void {
            $fresh = $product->fresh() ?? $product;

            event(new ProductStockUpdated($fresh));
            event(new ProductAvailabilityUpdated($fresh));

            if ($dispatchMetrics) {
                $this->metricsBroadcast->dispatch();
            }
        });
    }

    /**
     * @param  iterable<int, Product|int>  $products
     */
    public function dispatchMany(iterable $products): void
    {
        $ids = Collection::make($products)
            ->map(fn (Product|int $item): int => $item instanceof Product ? $item->id : (int) $item)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return;
        }

        $this->afterCommitBroadcast(function () use ($ids): void {
            Product::query()->whereIn('id', $ids)->each(function (Product $product): void {
                event(new ProductStockUpdated($product));
                event(new ProductAvailabilityUpdated($product));
            });

            $this->metricsBroadcast->dispatch();
        });
    }
}
