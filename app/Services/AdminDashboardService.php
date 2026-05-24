<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Receta;
use App\Models\VideoReceta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class AdminDashboardService
{
    /**
     * @return array{
     *   kpi: array{orders_today: int, revenue_today: float, revenue_month: float, pending: int, products_catalog: int},
     *   catalog_counts: array{productos: int, videos: int, recetas: int, combos: int},
     *   orders_by_day: list<array{label: string, short: string, count: int}>,
     *   max_day_count: int,
     *   recent_orders: Collection<int, Order>,
     *   low_stock: Collection<int, Product>,
     *   alerts: list<array{type: string, message: string}>
     * }
     */
    public function metrics(): array
    {
        $now = Carbon::now();

        $ordersToday = Order::query()->whereDate('created_at', $now->toDateString())->count();

        $pendingCount = Order::query()->where('status', OrderStatus::Pending)->count();

        $revenueMonth = (float) Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('total');

        $revenueToday = (float) Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereDate('created_at', $now->toDateString())
            ->sum('total');

        $ordersByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->startOfDay();
            $ordersByDay[] = [
                'label' => $d->translatedFormat('D j'),
                'short' => $d->format('d/m'),
                'count' => Order::query()->whereDate('created_at', $d->toDateString())->count(),
            ];
        }

        $counts = array_column($ordersByDay, 'count');
        $maxDayCount = max(1, ...$counts);

        $recentOrders = Order::query()
            ->with(['user:id,first_name,last_name,email'])
            ->latest()
            ->limit(8)
            ->get();

        $lowStock = Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('stock')
            ->limit(12)
            ->get(['id', 'name', 'stock', 'min_stock']);

        $alerts = [];
        if ($pendingCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf('%d pedido(s) en estado pendiente.', $pendingCount),
            ];
        }
        if ($lowStock->isNotEmpty()) {
            $alerts[] = [
                'type' => 'danger',
                'message' => sprintf('%d producto(s) con stock bajo.', $lowStock->count()),
            ];
        }

        return [
            'kpi' => [
                'orders_today' => $ordersToday,
                'revenue_today' => $revenueToday,
                'revenue_month' => $revenueMonth,
                'pending' => $pendingCount,
                'products_catalog' => Product::query()->count(),
            ],
            'catalog_counts' => [
                'productos' => Product::query()->count(),
                'videos' => VideoReceta::query()->count(),
                'recetas' => Receta::query()->count(),
                'combos' => Offer::query()->count(),
            ],
            'orders_by_day' => $ordersByDay,
            'max_day_count' => $maxDayCount,
            'recent_orders' => $recentOrders,
            'low_stock' => $lowStock,
            'alerts' => $alerts,
        ];
    }
}
