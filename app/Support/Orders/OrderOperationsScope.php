<?php

declare(strict_types=1);

namespace App\Support\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Alcance de pedidos operacionales por rol (admin global, despachador personal + cola pendiente). */
final class OrderOperationsScope
{
    public static function userSeesAllOrders(User $user): bool
    {
        return $user->isAdmin()
            || ($user->canAccessOrderOperations() && ! $user->isDispatcher());
    }

    /** @param Builder<Order> $query */
    public static function applyToQuery(Builder $query, User $user): Builder
    {
        if (self::userSeesAllOrders($user)) {
            return $query;
        }

        if ($user->isDispatcher()) {
            return $query->where(function (Builder $scoped) use ($user): void {
                $scoped->where('handled_by_user_id', $user->id)
                    ->orWhere(function (Builder $pool): void {
                        $pool->where('status', OrderStatus::Pending)
                            ->whereNull('handled_by_user_id');
                    });
            });
        }

        return $query;
    }

    public static function canViewOrder(User $user, Order $order): bool
    {
        if (self::userSeesAllOrders($user)) {
            return true;
        }

        if ($user->isCourier()) {
            return false;
        }

        if ($user->isDispatcher()) {
            return $order->handled_by_user_id === $user->id
                || ($order->status === OrderStatus::Pending && $order->handled_by_user_id === null);
        }

        return $user->canAccessOrderOperations();
    }

    public static function canTransitionOrder(User $user, Order $order): bool
    {
        if ($user->isAdmin() || ($user->canAccessOrderOperations() && ! $user->isDispatcher())) {
            return true;
        }

        if ($user->isDispatcher()) {
            if ($order->handled_by_user_id === $user->id) {
                return true;
            }

            return $order->status === OrderStatus::Pending && $order->handled_by_user_id === null;
        }

        return false;
    }
}
