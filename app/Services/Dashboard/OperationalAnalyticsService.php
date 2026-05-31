<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Position;
use App\Models\User;
use App\Support\Orders\OrderOperationsScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Consultas analíticas compartidas para dashboards operacionales. */
final class OperationalAnalyticsService
{
    /**
     * @return array{
     *   avg_delivery_minutes: float|null,
     *   avg_ready_to_delivered_minutes: float|null,
     *   sla_percent: float|null,
     *   sales_by_hour: list<array{hour: int, label: string, orders: int, revenue: float}>,
     *   orders_by_zone: list<array{zone: string, count: int}>,
     *   heatmap_points: list<array{lat: float, lng: float, weight: int}>,
     *   dispatcher_ranking: list<array{dispatcher_id: int, name: string, handled_today: int, delivered_today: int}>,
     *   stage_funnel: array{pending: int, preparing: int, ready: int, in_delivery: int, delivered_today: int, failed: int}
     * }
     */
    public function snapshot(?User $scopeUser = null, ?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        $today = $now->toDateString();
        $slaMinutes = (int) config('orders.dispatch_sla_minutes', 90);

        $base = Order::query();
        if ($scopeUser !== null && ! OrderOperationsScope::userSeesAllOrders($scopeUser)) {
            $base = OrderOperationsScope::applyToQuery($base, $scopeUser);
        }

        $deliveredTodayQuery = (clone $base)
            ->where('status', OrderStatus::Delivered)
            ->whereDate('delivered_at', $today);

        $avgDelivery = (clone $deliveredTodayQuery)
            ->whereNotNull('assigned_at')
            ->whereNotNull('delivered_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at)) as avg_minutes')
            ->value('avg_minutes');

        $avgReadyToDelivered = (clone $deliveredTodayQuery)
            ->whereNotNull('ready_at')
            ->whereNotNull('delivered_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, ready_at, delivered_at)) as avg_minutes')
            ->value('avg_minutes');

        $slaEligible = (clone $deliveredTodayQuery)
            ->whereNotNull('ready_at')
            ->whereNotNull('delivered_at')
            ->count();

        $slaMet = (clone $deliveredTodayQuery)
            ->whereNotNull('ready_at')
            ->whereNotNull('delivered_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, ready_at, delivered_at) <= ?', [$slaMinutes])
            ->count();

        $includeRevenue = $scopeUser === null || OrderOperationsScope::userSeesAllOrders($scopeUser);

        $salesByHour = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourQuery = (clone $deliveredTodayQuery)->whereRaw('HOUR(delivered_at) = ?', [$hour]);
            $salesByHour[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'orders' => (int) (clone $hourQuery)->count(),
                'revenue' => $includeRevenue ? (float) (clone $hourQuery)->sum('total') : 0.0,
            ];
        }

        $zoneQuery = (clone $base)
            ->whereDate('created_at', '>=', $now->copy()->subDays(6)->toDateString())
            ->selectRaw("COALESCE(NULLIF(shipping_address_line2, ''), NULLIF(shipping_city, ''), 'Sin zona') as zone_label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('zone_label')
            ->orderByDesc('total')
            ->limit(8);

        $ordersByZone = $zoneQuery->get()->map(fn ($row): array => [
            'zone' => (string) $row->zone_label,
            'count' => (int) $row->total,
        ])->values()->all();

        $heatmapQuery = (clone $base)
            ->whereNotNull('shipping_latitude')
            ->whereNotNull('shipping_longitude')
            ->where(function (Builder $q) use ($today): void {
                $q->whereDate('created_at', $today)
                    ->orWhere(function (Builder $active): void {
                        $active->activeForOperations();
                    });
            })
            ->select(['shipping_latitude', 'shipping_longitude']);

        $heatmapPoints = $heatmapQuery->get()->map(fn (Order $order): array => [
            'lat' => (float) $order->shipping_latitude,
            'lng' => (float) $order->shipping_longitude,
            'weight' => 1,
        ])->values()->all();

        $dispatcherRanking = [];
        if ($scopeUser === null || OrderOperationsScope::userSeesAllOrders($scopeUser)) {
            $dispatcherRanking = User::query()
                ->whereHas('employeeProfile.position', fn ($q) => $q->where('slug', Position::SLUG_DISPATCH))
                ->withCount([
                    'handledOrders as handled_today_count' => fn ($q) => $q->whereDate('handled_at', $today),
                    'handledOrders as delivered_today_count' => fn ($q) => $q
                        ->where('status', OrderStatus::Delivered)
                        ->whereDate('delivered_at', $today),
                ])
                ->orderByDesc('delivered_today_count')
                ->limit(8)
                ->get()
                ->map(fn (User $dispatcher): array => [
                    'dispatcher_id' => $dispatcher->id,
                    'name' => $dispatcher->name,
                    'handled_today' => (int) ($dispatcher->handled_today_count ?? 0),
                    'delivered_today' => (int) ($dispatcher->delivered_today_count ?? 0),
                ])
                ->values()
                ->all();
        }

        $stageFunnel = [
            'pending' => (int) (clone $base)->where('status', OrderStatus::Pending)->count(),
            'preparing' => (int) (clone $base)->where('status', OrderStatus::Preparing)->count(),
            'ready' => (int) (clone $base)->where('status', OrderStatus::ReadyForDelivery)->count(),
            'in_delivery' => (int) (clone $base)->whereIn('status', [
                OrderStatus::PickedUp->value,
                OrderStatus::InTransit->value,
            ])->count(),
            'delivered_today' => (int) (clone $deliveredTodayQuery)->count(),
            'failed' => (int) (clone $base)->where('status', OrderStatus::DeliveryFailed)->count(),
        ];

        return [
            'avg_delivery_minutes' => $avgDelivery !== null ? round((float) $avgDelivery, 1) : null,
            'avg_ready_to_delivered_minutes' => $avgReadyToDelivered !== null ? round((float) $avgReadyToDelivered, 1) : null,
            'sla_percent' => $slaEligible > 0 ? round(($slaMet / $slaEligible) * 100, 1) : null,
            'sales_by_hour' => $salesByHour,
            'orders_by_zone' => $ordersByZone,
            'heatmap_points' => $heatmapPoints,
            'dispatcher_ranking' => $dispatcherRanking,
            'stage_funnel' => $stageFunnel,
        ];
    }
}
