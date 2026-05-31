<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Support\Orders\OrderOperationsScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class OrderOperationsQueryService
{
    /**
     * @return array{
     *   totals: array{active: int, pending: int, preparing: int, ready: int, in_delivery: int, delivered_today: int, failed: int},
     *   revenue_today: float,
     *   revenue_month: float,
     *   available_couriers: int,
     *   busy_couriers: int
     * }
     */
    public function metrics(?Carbon $reference = null): array
    {
        return $this->metricsForQuery(Order::query(), $reference);
    }

    /**
     * Métricas operacionales scoped al despachador (sin ingresos).
     *
     * @return array{
     *   totals: array{active: int, pending: int, pending_pool: int, preparing: int, ready: int, in_delivery: int, delivered_today: int, failed: int},
     *   available_couriers: int,
     *   busy_couriers: int
     * }
     */
    public function metricsForUser(User $user, ?Carbon $reference = null): array
    {
        $query = Order::query();
        OrderOperationsScope::applyToQuery($query, $user);

        $metrics = $this->metricsForQuery($query, $reference, includeRevenue: false);

        $pendingPool = (int) Order::query()
            ->where('status', OrderStatus::Pending)
            ->whereNull('handled_by_user_id')
            ->count();

        $metrics['totals']['pending_pool'] = $pendingPool;
        unset($metrics['revenue_today'], $metrics['revenue_month']);

        return $metrics;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Order>  $baseQuery
     * @return array{
     *   totals: array{active: int, pending: int, preparing: int, ready: int, in_delivery: int, delivered_today: int, failed: int},
     *   revenue_today: float,
     *   revenue_month: float,
     *   available_couriers: int,
     *   busy_couriers: int
     * }
     */
    private function metricsForQuery($baseQuery, ?Carbon $reference = null, bool $includeRevenue = true): array
    {
        $now = $reference ?? Carbon::now();

        $activeCount = (clone $baseQuery)->activeForOperations()->count();
        $pendingCount = (clone $baseQuery)->where('status', OrderStatus::Pending)->count();
        $preparingCount = (clone $baseQuery)->where('status', OrderStatus::Preparing)->count();
        $readyCount = (clone $baseQuery)->where('status', OrderStatus::ReadyForDelivery)->count();
        $inDeliveryCount = (clone $baseQuery)->whereIn('status', [
            OrderStatus::PickedUp->value,
            OrderStatus::InTransit->value,
        ])->count();
        $deliveredTodayCount = (clone $baseQuery)
            ->where('status', OrderStatus::Delivered)
            ->whereDate('delivered_at', $now->toDateString())
            ->count();
        $failedCount = (clone $baseQuery)->where('status', OrderStatus::DeliveryFailed)->count();

        $revenueToday = 0.0;
        $revenueMonth = 0.0;

        if ($includeRevenue) {
            $revenueToday = (float) Order::query()
                ->where('status', OrderStatus::Delivered)
                ->whereDate('delivered_at', $now->toDateString())
                ->sum('total');

            $revenueMonth = (float) Order::query()
                ->where('status', OrderStatus::Delivered)
                ->whereYear('delivered_at', $now->year)
                ->whereMonth('delivered_at', $now->month)
                ->sum('total');
        }

        $availableCouriers = User::query()
            ->whereHas('employeeProfile', fn ($q) => $q->where('available', true)
                ->whereHas('position', fn ($p) => $p->where('slug', \App\Models\Position::SLUG_DELIVERY)))
            ->count();

        $busyCouriers = User::query()
            ->whereHas('employeeProfile', fn ($q) => $q->where('available', false)
                ->whereHas('position', fn ($p) => $p->where('slug', \App\Models\Position::SLUG_DELIVERY)))
            ->count();

        return [
            'totals' => [
                'active' => $activeCount,
                'pending' => $pendingCount,
                'preparing' => $preparingCount,
                'ready' => $readyCount,
                'in_delivery' => $inDeliveryCount,
                'delivered_today' => $deliveredTodayCount,
                'failed' => $failedCount,
            ],
            'revenue_today' => $revenueToday,
            'revenue_month' => $revenueMonth,
            'available_couriers' => $availableCouriers,
            'busy_couriers' => $busyCouriers,
        ];
    }

    /**
     * @param  array{
     *   status?: OrderStatus|list<OrderStatus>|null,
     *   courier_id?: int|null,
     *   search?: string|null,
     *   from?: string|null,
     *   to?: string|null,
     *   active_only?: bool|null,
     *   scope_user?: User|null
     * }  $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['user:id,first_name,last_name,email,phone', 'courier:id,first_name,last_name', 'handledBy:id,first_name,last_name'])
            ->latest();

        if (! empty($filters['scope_user']) && $filters['scope_user'] instanceof User) {
            OrderOperationsScope::applyToQuery($query, $filters['scope_user']);
        }

        if (! empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (! empty($filters['courier_id'])) {
            $query->assignedToCourier((int) $filters['courier_id']);
        }

        if (! empty($filters['active_only'])) {
            $query->activeForOperations();
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($term): void {
                $q->where('id', 'like', $term)
                    ->orWhere('tracking_token', 'like', $term)
                    ->orWhere('shipping_recipient_name', 'like', $term)
                    ->orWhere('shipping_phone', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term));
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return Collection<int, Order> */
    public function courierPoolOrders(): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::ReadyForDelivery)
            ->whereNull('courier_id')
            ->with(['user:id,first_name,last_name,phone'])
            ->latest('ready_at')
            ->get();
    }

    /** @return Collection<int, Order> */
    public function courierActiveOrders(User $courier): Collection
    {
        return Order::query()
            ->forCourier($courier)
            ->whereIn('status', [
                OrderStatus::ReadyForDelivery->value,
                OrderStatus::PickedUp->value,
                OrderStatus::InTransit->value,
            ])
            ->with(['user:id,first_name,last_name,phone', 'items'])
            ->latest('assigned_at')
            ->get();
    }
}
