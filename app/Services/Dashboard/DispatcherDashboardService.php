<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderOperationsQueryService;
use App\Support\Orders\OrderOperationsScope;
use Illuminate\Support\Carbon;

final class DispatcherDashboardService
{
    public function __construct(
        private readonly OrderOperationsQueryService $operations,
        private readonly OperationalAnalyticsService $analytics,
    ) {}

    /** @return array<string, mixed> */
    public function metrics(User $dispatcher, ?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        $ops = $this->operations->metricsForUser($dispatcher, $now);
        $analytics = $this->analytics->snapshot($dispatcher, $now);

        $recentOrders = Order::query()
            ->tap(fn ($q) => OrderOperationsScope::applyToQuery($q, $dispatcher))
            ->with(['user:id,first_name,last_name,email'])
            ->latest()
            ->limit(8)
            ->get();

        return [
            'generated_at' => $now->toIso8601String(),
            'scope' => 'dispatcher',
            'dispatcher' => [
                'id' => $dispatcher->id,
                'name' => $dispatcher->name,
            ],
            'kpi' => [
                'handled_active' => $ops['totals']['active'],
                'pending_pool' => $ops['totals']['pending_pool'],
                'preparing' => $ops['totals']['preparing'],
                'ready' => $ops['totals']['ready'],
                'in_delivery' => $ops['totals']['in_delivery'],
                'delivered_today' => $ops['totals']['delivered_today'],
                'failed' => $ops['totals']['failed'],
                'available_couriers' => $ops['available_couriers'],
                'busy_couriers' => $ops['busy_couriers'],
                'avg_ready_to_delivered_minutes' => $analytics['avg_ready_to_delivered_minutes'],
                'sla_percent' => $analytics['sla_percent'],
            ],
            'analytics' => $this->stripFinancialFields($analytics),
            'recent_orders' => $recentOrders,
        ];
    }

    /** @return array<string, mixed> */
    public function feed(User $dispatcher): array
    {
        $payload = $this->metrics($dispatcher);

        return [
            'generated_at' => $payload['generated_at'],
            'kpi' => $payload['kpi'],
            'analytics' => $payload['analytics'],
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    private function stripFinancialFields(array $analytics): array
    {
        $analytics['sales_by_hour'] = array_map(
            static fn (array $row): array => [
                'hour' => $row['hour'],
                'label' => $row['label'],
                'orders' => $row['orders'],
            ],
            $analytics['sales_by_hour'] ?? [],
        );

        unset($analytics['dispatcher_ranking']);

        return $analytics;
    }
}
