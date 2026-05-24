<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Enums\OrderStatus;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartSessionService;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly OfferAvailabilityService $offerAvailability,
        private readonly OfferPricingService $offerPricing,
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
            $productIds = $this->cartSession->productIds($cartSession);
            $offerIds = $this->cartSession->offerIds($cartSession);

            /** @var \Illuminate\Support\Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /** @var \Illuminate\Support\Collection<int, Offer> $offers */
            $offers = Offer::query()
                ->whereIn('id', $offerIds)
                ->with(['items.product'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0.0;
            $lines = [];

            foreach ($cartSession as $lineKey => $item) {
                if (! is_array($item) || ! isset($item['cantidad'])) {
                    continue;
                }

                $cantidad = $this->cartSession->normalizeQuantity($item['cantidad']);

                if ($this->cartSession->isOfferLine($lineKey)) {
                    $offerId = $this->cartSession->parseOfferLineKey($lineKey);
                    $offer = $offers->get($offerId);

                    if ($offer === null || ! $offer->isBundle()) {
                        throw new RuntimeException('La oferta ya no está disponible.');
                    }

                    $bundleCount = (int) $cantidad;
                    if ($this->offerAvailability->availableUnits($offer) < $bundleCount) {
                        throw new RuntimeException("Stock insuficiente para: {$offer->name}");
                    }

                    $unitPrice = (float) $offer->offer_price;
                    $subtotal = round($unitPrice * $bundleCount, 2);
                    $total += $subtotal;

                    $lines[] = [
                        'type' => 'offer',
                        'offer' => $offer,
                        'quantity' => $bundleCount,
                        'unit_price' => number_format($unitPrice, 2, '.', ''),
                        'subtotal' => number_format($subtotal, 2, '.', ''),
                    ];

                    continue;
                }

                [$productId, $saleUnit] = $this->cartSession->parseProductLineKey($lineKey);
                $product = $products->get($productId);

                if ($product === null) {
                    throw new RuntimeException("El producto #{$productId} ya no está disponible.");
                }

                $stockNeeded = $this->cartSession->stockRequired($product, $cantidad, $saleUnit);

                if ((float) $product->stock < $stockNeeded) {
                    throw new RuntimeException("Stock insuficiente para: {$product->name}");
                }

                $unitPrice = $this->cartSession->unitPrice($product, $saleUnit, $cantidad);
                $subtotal = round($unitPrice * $cantidad, 2);
                $total += $subtotal;

                $lines[] = [
                    'type' => 'product',
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
                if ($line['type'] === 'offer') {
                    /** @var Offer $offer */
                    $offer = $line['offer'];
                    $bundleCount = (int) $line['quantity'];

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'offer_id' => $offer->id,
                        'line_label' => $offer->name,
                        'sale_unit' => StockUnit::Pack,
                        'quantity' => $bundleCount,
                        'unit_price' => $line['unit_price'],
                        'subtotal' => $line['subtotal'],
                    ]);

                    foreach ($this->offerPricing->stockRequiredForBundle($offer, $bundleCount) as $requirement) {
                        /** @var Product $product */
                        $product = $requirement['product'];
                        $locked = Product::query()->lockForUpdate()->find($product->id);
                        if ($locked === null) {
                            continue;
                        }
                        $locked->stock = (float) $locked->stock - (float) $requirement['stock_delta'];
                        $locked->save();
                    }

                    continue;
                }

                /** @var Product $product */
                $product = $line['product'];
                /** @var StockUnit $saleUnit */
                $saleUnit = $line['sale_unit'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'line_label' => $product->name,
                    'sale_unit' => $saleUnit,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $product->stock = (float) $product->stock - (float) $line['stock_delta'];
                $product->save();
            }

            return $order->fresh(['items.product', 'items.offer']);
        });
    }
}
