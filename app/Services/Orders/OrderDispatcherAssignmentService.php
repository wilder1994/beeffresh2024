<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class OrderDispatcherAssignmentService
{
    public function claimForPreparing(Order $order, User $dispatcher): Order
    {
        return $this->claimIfAvailable($order, $dispatcher);
    }

    public function claimIfAvailable(Order $order, User $dispatcher): Order
    {
        if (! $dispatcher->isAdmin() && ! $dispatcher->isDispatcher()) {
            throw new RuntimeException('No autorizado para tomar pedidos.');
        }

        return DB::transaction(function () use ($order, $dispatcher): Order {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->handled_by_user_id !== null
                && $locked->handled_by_user_id !== $dispatcher->id
                && ! $dispatcher->isAdmin()) {
                throw new RuntimeException('Este pedido ya está asignado a otro despachador.');
            }

            if ($locked->handled_by_user_id === null) {
                $locked->handled_by_user_id = $dispatcher->id;
                $locked->handled_at = now();
                $locked->save();
            }

            return $locked->fresh(['user', 'courier', 'handledBy']);
        });
    }

    public function reassign(Order $order, User $newDispatcher, User $admin): Order
    {
        if (! $admin->isAdmin()) {
            throw new RuntimeException('Solo un administrador puede reasignar despachadores.');
        }

        if (! $newDispatcher->isDispatcher()) {
            throw new RuntimeException('El usuario destino no es despachador.');
        }

        $order->handled_by_user_id = $newDispatcher->id;
        $order->handled_at = now();
        $order->save();

        return $order->fresh(['user', 'courier', 'handledBy']);
    }
}
