<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Notifications\NotificationMetricsService;
use App\Services\Orders\OrderOperationsQueryService;
use App\Support\Orders\OrderOperationsScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class AdminExecutiveDashboardService
{
    public function __construct(
        private readonly OrderOperationsQueryService $operations,
        private readonly OperationalAnalyticsService $analytics,
        private readonly NotificationMetricsService $notificationMetrics,
    ) {}

    /** @return array<string, mixed> */
    public function metrics(?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        $ops = $this->operations->metrics($now);
        $analytics = $this->analytics->snapshot(null, $now);

        $recentOrders = Order::query()
            ->with(['user:id,first_name,last_name,email', 'handledBy:id,first_name,last_name'])
            ->latest()
            ->limit(8)
            ->get();

        $lowStock = Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('stock')
            ->limit(8)
            ->get(['id', 'name', 'stock', 'min_stock']);

        return [
            'generated_at' => $now->toIso8601String(),
            'scope' => 'executive',
            'kpi' => [
                'orders_today' => Order::query()->whereDate('created_at', $now->toDateString())->count(),
                'revenue_today' => $ops['revenue_today'],
                'revenue_month' => $ops['revenue_month'],
                'delivered_today' => $ops['totals']['delivered_today'],
                'failed' => $ops['totals']['failed'],
                'active' => $ops['totals']['active'],
                'available_couriers' => $ops['available_couriers'],
                'busy_couriers' => $ops['busy_couriers'],
                'avg_delivery_minutes' => $analytics['avg_delivery_minutes'],
                'sla_percent' => $analytics['sla_percent'],
                'low_stock_count' => Product::query()
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->where('min_stock', '>', 0)
                    ->count(),
            ],
            'analytics' => $analytics,
            'recent_orders' => $recentOrders,
            'low_stock' => $lowStock,
            'notification_metrics' => $this->notificationMetrics->dashboardSummary(),
        ];
    }

    /** @return array<string, mixed> */
    public function feed(): array
    {
        $payload = $this->metrics();

        return [
            'generated_at' => $payload['generated_at'],
            'kpi' => $payload['kpi'],
            'analytics' => $payload['analytics'],
        ];
    }
}
