<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutLineItem;
use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Domain\Catalog\StockUnit;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartSessionService;
use App\Services\Store\OfferAvailabilityService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

final class CheckoutQuoteService
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly OfferAvailabilityService $offerAvailability,
    ) {}

    /**
     * @param  array<string|int, array<string, mixed>>  $cartSession
     */
    public function build(User $user, array $cartSession, ?string $notes = null): CheckoutSessionData
    {
        if ($cartSession === []) {
            throw new InvalidArgumentException('El carrito está vacío.');
        }

        $productIds = $this->cartSession->productIds($cartSession);
        $offerIds = $this->cartSession->offerIds($cartSession);

        /** @var Collection<int, Product> $products */
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        /** @var Collection<int, Offer> $offers */
        $offers = Offer::query()->whereIn('id', $offerIds)->with(['items.product'])->get()->keyBy('id');

        $lines = [];
        $subtotal = 0.0;

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
                $lineSubtotal = round($unitPrice * $bundleCount, 2);
                $subtotal += $lineSubtotal;

                $lines[] = new CheckoutLineItem(
                    type: 'offer',
                    name: $offer->name,
                    quantity: (float) $bundleCount,
                    saleUnit: StockUnit::Pack->value,
                    unitPrice: $unitPrice,
                    subtotal: $lineSubtotal,
                    meta: ['offer_id' => $offer->id],
                );

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
            $quote = $this->cartSession->priceQuote($product, $saleUnit, $cantidad);
            $lineSubtotal = round($unitPrice * $cantidad, 2);
            $subtotal += $lineSubtotal;

            $lines[] = new CheckoutLineItem(
                type: 'product',
                name: $product->name,
                quantity: $cantidad,
                saleUnit: $saleUnit->value,
                unitPrice: $unitPrice,
                subtotal: $lineSubtotal,
                meta: [
                    'product_id' => $product->id,
                    'stock_delta' => $stockNeeded,
                    'pricing_tier' => $quote->tier,
                    'pricing_label' => $quote->pricingLabel(),
                ],
            );
        }

        if ($lines === []) {
            throw new InvalidArgumentException('El carrito está vacío.');
        }

        $shippingFee = (float) config('payments.shipping_fee', 0);
        $discount = 0.0;
        $total = round($subtotal + $shippingFee - $discount, 2);

        $shipping = $user->snapshotShippingFromProfile();
        $shipping['shipping_recipient_name'] = $shipping['shipping_recipient_name'] ?? $user->name;

        return new CheckoutSessionData(
            lines: $lines,
            cartSnapshot: $cartSession,
            shipping: $shipping,
            subtotal: $subtotal,
            shippingFee: $shippingFee,
            discount: $discount,
            total: $total,
            notes: $notes,
        );
    }
}
