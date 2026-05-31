<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Support\Orders\OrderOperationsScope;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessOrderOperations()
            || $user->canAccessCourierModule()
            || $user->isDispatcher();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isCourier()) {
            return $this->viewAsCourier($user, $order);
        }

        if ($user->isCustomer()) {
            return $order->user_id === $user->id;
        }

        return OrderOperationsScope::canViewOrder($user, $order);
    }

    public function accept(User $user, Order $order): bool
    {
        return $user->isCourier()
            && $order->status === \App\Enums\OrderStatus::ReadyForDelivery
            && $order->courier_id === null
            && (bool) $user->employeeProfile?->available
            && ! app(\App\Services\Orders\CourierAssignmentService::class)->courierHasActiveDelivery($user);
    }

    public function transition(User $user, Order $order): bool
    {
        if ($user->isCourier() && $order->courier_id === $user->id) {
            return true;
        }

        return OrderOperationsScope::canTransitionOrder($user, $order);
    }

    public function assign(User $user, Order $order): bool
    {
        if ($user->isCourier()) {
            return false;
        }

        return OrderOperationsScope::canTransitionOrder($user, $order);
    }

    public function reassignDispatcher(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function recordLocation(User $user): bool
    {
        return $user->canAccessCourierModule() || $user->isCourier();
    }

    public function addDeliveryProof(User $user, Order $order): bool
    {
        if ($user->isAdmin() || ($user->canAccessOrderOperations() && ! $user->isDispatcher())) {
            return true;
        }

        return $user->isCourier()
            && $order->courier_id === $user->id
            && in_array($order->status, [
                \App\Enums\OrderStatus::InTransit,
                \App\Enums\OrderStatus::DeliveryFailed,
            ], true);
    }

    private function viewAsCourier(User $user, Order $order): bool
    {
        if ($order->courier_id === $user->id) {
            return true;
        }

        return $order->status === \App\Enums\OrderStatus::ReadyForDelivery
            && $order->courier_id === null
            && (bool) $user->employeeProfile?->available;
    }
}
