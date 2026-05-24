<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Domain\Catalog\StockUnit;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartSessionService;
use App\Services\Orders\OrderWorkflowService;
use App\Services\Store\OfferPricingService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class OrderFulfillmentService
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly OfferPricingService $offerPricing,
        private readonly OrderWorkflowService $orderWorkflow,
    ) {}

    public function fulfillFromPayment(Payment $payment, User $user, CheckoutSessionData $session): Order
    {
        if ($payment->order_id !== null) {
            $existing = Order::query()->find($payment->order_id);
            if ($existing !== null) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($payment, $user, $session): Order {
            $productIds = $this->cartSession->productIds($session->cartSnapshot);
            $offerIds = $this->cartSession->offerIds($session->cartSnapshot);

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $offers = Offer::query()
                ->whereIn('id', $offerIds)
                ->with(['items.product'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($session->lines as $line) {
                if ($line->type === 'offer') {
                    $offerId = (int) ($line->meta['offer_id'] ?? 0);
                    $offer = $offers->get($offerId);
                    if ($offer === null) {
                        throw new RuntimeException('Oferta no disponible al confirmar pago.');
                    }
                    $requirements = $this->offerPricing->stockRequiredForBundle($offer, (int) $line->quantity);
                    foreach ($requirements as $requirement) {
                        /** @var Product $product */
                        $product = $requirement['product'];
                        $locked = $products->get($product->id) ?? Product::query()->lockForUpdate()->find($product->id);
                        if ($locked === null || (float) $locked->stock < (float) $requirement['stock_delta']) {
                            throw new RuntimeException("Stock insuficiente para completar: {$offer->name}");
                        }
                    }
                    continue;
                }

                $productId = (int) ($line->meta['product_id'] ?? 0);
                $stockDelta = (float) ($line->meta['stock_delta'] ?? 0);
                $product = $products->get($productId);
                if ($product === null || (float) $product->stock < $stockDelta) {
                    throw new RuntimeException("Stock insuficiente para: {$line->name}");
                }
            }

            $order = Order::query()->create(array_merge([
                'user_id' => $user->id,
                'total' => number_format($session->total, 2, '.', ''),
                'status' => OrderStatus::Pending,
                'payment_method' => PaymentMethod::Online,
                'tracking_token' => Order::generateTrackingToken(),
            ], $session->shipping));

            $this->orderWorkflow->logInitialStatus(
                $order,
                $user,
                'Pedido creado tras pago aprobado #'.$payment->reference,
            );

            foreach ($session->lines as $line) {
                if ($line->type === 'offer') {
                    $offerId = (int) ($line->meta['offer_id'] ?? 0);
                    /** @var Offer $offer */
                    $offer = $offers->get($offerId);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'offer_id' => $offer->id,
                        'line_label' => $offer->name,
                        'sale_unit' => StockUnit::Pack,
                        'quantity' => (int) $line->quantity,
                        'unit_price' => number_format($line->unitPrice, 2, '.', ''),
                        'subtotal' => number_format($line->subtotal, 2, '.', ''),
                    ]);

                    foreach ($this->offerPricing->stockRequiredForBundle($offer, (int) $line->quantity) as $requirement) {
                        $locked = Product::query()->lockForUpdate()->find($requirement['product']->id);
                        if ($locked === null) {
                            continue;
                        }
                        $locked->stock = (float) $locked->stock - (float) $requirement['stock_delta'];
                        $locked->save();
                    }

                    continue;
                }

                $productId = (int) ($line->meta['product_id'] ?? 0);
                $saleUnit = StockUnit::from($line->saleUnit);
                /** @var Product $product */
                $product = $products->get($productId);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'line_label' => $product->name,
                    'sale_unit' => $saleUnit,
                    'quantity' => $line->quantity,
                    'unit_price' => number_format($line->unitPrice, 2, '.', ''),
                    'subtotal' => number_format($line->subtotal, 2, '.', ''),
                ]);

                $product->stock = (float) $product->stock - (float) ($line->meta['stock_delta'] ?? 0);
                $product->save();
            }

            $payment->order_id = $order->id;
            $payment->save();

            return $order->fresh(['items.product', 'items.offer']);
        });
    }
}
