<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CheckoutService
{
    /**
     * Registra pedido, líneas y descuenta stock en una transacción.
     *
     * @param  array<string|int, array<string, mixed>>  $cartSession
     */
    public function finalizeCart(User $user, array $cartSession): Order
    {
        if ($cartSession === []) {
            throw new InvalidArgumentException('El carrito está vacío.');
        }

        return DB::transaction(function () use ($user, $cartSession) {
            $ids = [];
            foreach ($cartSession as $key => $item) {
                $pid = isset($item['producto_id']) ? (int) $item['producto_id'] : (int) $key;
                $ids[] = $pid;
            }
            $ids = array_unique($ids);

            /** @var \Illuminate\Support\Collection<int, Producto> $productos */
            $productos = Producto::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0.0;
            $lines = [];

            foreach ($cartSession as $key => $item) {
                $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : (int) $key;
                $cantidad = isset($item['cantidad']) ? max(1, (int) $item['cantidad']) : 1;

                $producto = $productos->get($productoId);
                if ($producto === null) {
                    throw new RuntimeException("El producto #{$productoId} ya no está disponible.");
                }

                if ($producto->stock < $cantidad) {
                    throw new RuntimeException("Stock insuficiente para: {$producto->nombre}");
                }

                $unitPrice = (float) $producto->precio;
                $subtotal = round($unitPrice * $cantidad, 2);
                $total += $subtotal;

                $lines[] = [
                    'producto' => $producto,
                    'quantity' => $cantidad,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                ];
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total' => number_format($total, 2, '.', ''),
                'status' => OrderStatus::Pending,
            ]);

            foreach ($lines as $line) {
                /** @var Producto $p */
                $p = $line['producto'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'producto_id' => $p->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $p->stock -= $line['quantity'];
                $p->save();
            }

            return $order->fresh(['items.producto']);
        });
    }
}
