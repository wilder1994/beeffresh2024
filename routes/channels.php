<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels (privados)
|--------------------------------------------------------------------------
| Autorización vía policies Spatie / ownership. Sin canales públicos para
| pedidos, pagos u operaciones sensibles.
*/

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === $id;
});

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId): bool {
    $order = Order::query()->find($orderId);

    if ($order === null) {
        return false;
    }

    return $user->can('view', $order);
});

Broadcast::channel('operations.orders', function (User $user): bool {
    return $user->canAccessOrderOperations() || $user->isDispatcher();
});

Broadcast::channel('operations.dashboard', function (User $user): bool {
    return $user->canAccessOrderOperations() || $user->isDispatcher() || $user->isAdmin();
});

Broadcast::channel('couriers.{courierId}', function (User $user, int $courierId): bool {
    if ((int) $user->id === $courierId && $user->canAccessCourierModule()) {
        return true;
    }

    return $user->canAccessOrderOperations() || $user->isDispatcher();
});

Broadcast::channel('payments.{paymentUuid}', function (User $user, string $paymentUuid): bool {
    $payment = Payment::query()->where('uuid', $paymentUuid)->first();

    if ($payment === null) {
        return false;
    }

    return $user->can('view', $payment);
});
