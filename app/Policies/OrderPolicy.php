<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

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
        if ($user->canAccessOrderOperations() || $user->isDispatcher()) {
            return true;
        }

        if ($user->isCourier()) {
            if ($order->courier_id === $user->id) {
                return true;
            }

            return $order->status === OrderStatus::ReadyForDelivery
                && $order->courier_id === null
                && (bool) $user->employeeProfile?->available;
        }

        return $user->isCustomer() && $order->user_id === $user->id;
    }

    public function accept(User $user, Order $order): bool
    {
        return $user->isCourier()
            && $order->status === OrderStatus::ReadyForDelivery
            && $order->courier_id === null
            && (bool) $user->employeeProfile?->available
            && ! app(\App\Services\Orders\CourierAssignmentService::class)->courierHasActiveDelivery($user);
    }

    public function transition(User $user, Order $order): bool
    {
        if ($user->canAccessOrderOperations() || $user->isDispatcher()) {
            return true;
        }

        if ($user->isCourier() && $order->courier_id === $user->id) {
            return true;
        }

        return false;
    }

    public function assign(User $user, Order $order): bool
    {
        return $user->canAccessOrderOperations() || $user->isDispatcher();
    }

    public function recordLocation(User $user): bool
    {
        return $user->canAccessCourierModule() || $user->isCourier();
    }

    public function addDeliveryProof(User $user, Order $order): bool
    {
        if ($user->canAccessOrderOperations() || $user->isDispatcher()) {
            return true;
        }

        return $user->isCourier()
            && $order->courier_id === $user->id
            && in_array($order->status, [
                OrderStatus::InTransit,
                OrderStatus::DeliveryFailed,
            ], true);
    }
}
