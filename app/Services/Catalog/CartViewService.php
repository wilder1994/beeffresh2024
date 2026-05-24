<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Collection;

final class CartViewService
{
    public function __construct(
        private readonly CartSessionService $cartSession,
    ) {}

    /**
     * @param  array<string|int, array<string, mixed>>  $cartSession
     * @return array{lineas: list<array<string, mixed>>, total: float, itemCount: float}
     */
    public function summarize(array $cartSession): array
    {
        $productIds = $this->cartSession->productIds($cartSession);
        $offerIds = $this->cartSession->offerIds($cartSession);

        /** @var Collection<int, Product> $products */
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        /** @var Collection<int, Offer> $offers */
        $offers = Offer::query()->whereIn('id', $offerIds)->get()->keyBy('id');

        $lineas = [];
        $total = 0.0;
        $itemCount = 0.0;

        foreach ($cartSession as $lineKey => $item) {
            if (! is_array($item) || ! isset($item['cantidad'])) {
                continue;
            }

            $line = $this->buildLine((string) $lineKey, $item, $products, $offers);

            if ($line === null) {
                continue;
            }

            $lineas[] = $line;
            $total += (float) $line['subtotal'];
            $itemCount += (float) $line['cantidad'];
        }

        return [
            'lineas' => $lineas,
            'total' => $total,
            'itemCount' => $itemCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Offer>  $offers
     * @return array<string, mixed>|null
     */
    private function buildLine(string $lineKey, array $item, Collection $products, Collection $offers): ?array
    {
        $cantidad = (float) $item['cantidad'];

        if ($this->cartSession->isOfferLine($lineKey)) {
            $offer = $offers->get($this->cartSession->parseOfferLineKey($lineKey));

            if ($offer === null) {
                return null;
            }

            $precio = (float) $offer->offer_price;

            return [
                'line_key' => $lineKey,
                'tipo' => 'offer',
                'offer_id' => $offer->id,
                'nombre' => $offer->name,
                'precio' => $precio,
                'cantidad' => $cantidad,
                'sale_unit' => StockUnit::Pack,
                'subtotal' => $precio * $cantidad,
                'imagen_url' => $offer->imageUrl(),
                'pricing_tier' => null,
                'pricing_label' => null,
            ];
        }

        [$productId, $saleUnit] = $this->cartSession->parseProductLineKey($lineKey);
        $product = $products->get($productId);

        if ($product === null) {
            return null;
        }

        $precio = $this->cartSession->unitPrice($product, $saleUnit, $cantidad);
        $quote = $this->cartSession->priceQuote($product, $saleUnit, $cantidad);

        return [
            'line_key' => $lineKey,
            'tipo' => 'product',
            'product_id' => $product->id,
            'nombre' => $product->name,
            'precio' => $precio,
            'cantidad' => $cantidad,
            'sale_unit' => $saleUnit,
            'subtotal' => $precio * $cantidad,
            'imagen_url' => $product->imageUrl(),
            'pricing_tier' => $quote->tier,
            'pricing_label' => $quote->pricingLabel(),
        ];
    }
}
