<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Catalog\StockUnit;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartSessionService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private readonly CartSessionService $cartSession,
    ) {}

    /**
     * @param  array<string|int, array<string, mixed>>  $cartSession
     */
    public function finalizeCart(User $user, array $cartSession): Order
    {
        if ($cartSession === []) {
            throw new InvalidArgumentException('El carrito está vacío.');
        }

        return DB::transaction(function () use ($user, $cartSession) {
            $ids = $this->cartSession->productIds($cartSession);

            /** @var \Illuminate\Support\Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0.0;
            $lines = [];

            foreach ($cartSession as $lineKey => $item) {
                if (! is_array($item) || ! isset($item['cantidad'])) {
                    continue;
                }

                [$productId, $saleUnit] = $this->cartSession->parseLineKey($lineKey);
                $cantidad = $this->cartSession->normalizeQuantity($item['cantidad']);

                $product = $products->get($productId);
                if ($product === null) {
                    throw new RuntimeException("El producto #{$productId} ya no está disponible.");
                }

                $stockNeeded = $this->cartSession->stockRequired($product, $cantidad, $saleUnit);

                if ((float) $product->stock < $stockNeeded) {
                    throw new RuntimeException("Stock insuficiente para: {$product->name}");
                }

                $unitPrice = $this->cartSession->unitPrice($product, $saleUnit);
                $subtotal = round($unitPrice * $cantidad, 2);
                $total += $subtotal;

                $lines[] = [
                    'product' => $product,
                    'sale_unit' => $saleUnit,
                    'quantity' => $cantidad,
                    'stock_delta' => $stockNeeded,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                ];
            }

            if ($lines === []) {
                throw new InvalidArgumentException('El carrito está vacío.');
            }

            $shipping = $user->isCustomer()
                ? $user->snapshotShippingFromProfile()
                : array_fill_keys([
                    'shipping_recipient_name',
                    'shipping_phone',
                    'shipping_document_number',
                    'shipping_address_line1',
                    'shipping_address_line2',
                    'shipping_city',
                    'shipping_state',
                    'shipping_postal_code',
                    'shipping_country',
                    'shipping_notes',
                ], null);
            $shipping['shipping_recipient_name'] = $shipping['shipping_recipient_name'] ?? $user->name;

            $order = Order::query()->create(array_merge([
                'user_id' => $user->id,
                'total' => number_format($total, 2, '.', ''),
                'status' => OrderStatus::Pending,
            ], $shipping));

            foreach ($lines as $line) {
                /** @var Product $p */
                $p = $line['product'];
                /** @var StockUnit $saleUnit */
                $saleUnit = $line['sale_unit'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'sale_unit' => $saleUnit,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $p->stock = (float) $p->stock - (float) $line['stock_delta'];
                $p->save();
            }

            return $order->fresh(['items.product']);
        });
    }
}
