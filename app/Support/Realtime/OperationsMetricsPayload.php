<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Orders\OrderOperationsQueryService;
use Illuminate\Support\Carbon;

/** Snapshot operacional mínimo para parche DOM (Fase 1.5). */
final class OperationsMetricsPayload
{
    public function __construct(
        private readonly OrderOperationsQueryService $operationsMetrics,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(?Carbon $reference = null): array
    {
        $metrics = $this->operationsMetrics->metrics($reference);
        $totals = $metrics['totals'];

        return [
            'pending' => $totals['pending'],
            'preparing' => $totals['preparing'],
            'ready' => $totals['ready'],
            'in_transit' => $totals['in_delivery'],
            'delivered_today' => $totals['delivered_today'],
            'failed' => $totals['failed'],
            'delayed' => $this->delayedCount($reference),
            'active_couriers' => $metrics['busy_couriers'],
            'available_couriers' => $metrics['available_couriers'],
            'revenue_today' => $metrics['revenue_today'],
            'low_stock_count' => $this->lowStockCount(),
            'generated_at' => ($reference ?? Carbon::now())->toIso8601String(),
        ];
    }

    private function delayedCount(?Carbon $reference): int
    {
        $minutes = (int) config('notifications.delayed_order_minutes', 45);
        $threshold = ($reference ?? Carbon::now())->copy()->subMinutes($minutes);

        return Order::query()
            ->whereIn('status', [
                OrderStatus::Pending->value,
                OrderStatus::Preparing->value,
                OrderStatus::ReadyForDelivery->value,
            ])
            ->where('updated_at', '<=', $threshold)
            ->count();
    }

    private function lowStockCount(): int
    {
        return Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->count();
    }
}
