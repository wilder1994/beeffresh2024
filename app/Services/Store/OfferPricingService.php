<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartUnitConverter;

final class OfferPricingService
{
    public function __construct(
        private readonly CartUnitConverter $unitConverter,
    ) {}

    /** Precio de referencia (precio real del catálogo, sin promo de producto). */
    public function referenceTotal(Offer $offer): float
    {
        if ($offer->type === OfferType::Volume) {
            return $this->volumeReferenceTotal($offer);
        }

        return $this->bundleReferenceTotal($offer);
    }

    public function offerTotal(Offer $offer): float
    {
        if ($offer->type === OfferType::Volume) {
            $unit = StockUnit::tryFrom((string) $offer->volume_sale_unit) ?? StockUnit::Kg;
            $minQty = (float) $offer->volume_min_quantity;

            return round($minQty * $this->volumeOfferUnitPrice($offer, $unit), 2);
        }

        return round((float) $offer->offer_price, 2);
    }

    public function volumeOfferUnitPrice(Offer $offer, StockUnit $unit): float
    {
        return match ($unit) {
            StockUnit::Lb => (float) ($offer->volume_offer_price_lb ?? 0),
            StockUnit::Kg => (float) ($offer->volume_offer_price_kg ?? 0),
        };
    }

    public function referenceUnitPrice(Product $product, StockUnit $unit): float
    {
        return match ($unit) {
            StockUnit::Lb => (float) $product->price_per_lb,
            StockUnit::Kg => (float) $product->price_per_kg,
        };
    }

    private function volumeReferenceTotal(Offer $offer): float
    {
        $product = $offer->product;
        if ($product === null) {
            return 0.0;
        }

        $unit = StockUnit::tryFrom((string) $offer->volume_sale_unit) ?? StockUnit::Kg;
        $minQty = (float) $offer->volume_min_quantity;

        return round($minQty * $this->referenceUnitPrice($product, $unit), 2);
    }

    /**
     * @param  iterable<int, array{product_id?: int|string, quantity?: float|string, sale_unit?: string}>  $lines
     * @param  iterable<int, Product>|array<int|string, Product>  $productsById
     */
    public function bundleReferenceTotalFromLines(iterable $lines, iterable $productsById): float
    {
        $indexed = [];
        foreach ($productsById as $key => $product) {
            if ($product instanceof Product) {
                $indexed[(string) $product->id] = $product;
            }
        }

        $total = 0.0;

        foreach ($lines as $line) {
            $productId = (string) ($line['product_id'] ?? '');
            if ($productId === '' || ! isset($indexed[$productId])) {
                continue;
            }

            $unit = StockUnit::tryFrom((string) ($line['sale_unit'] ?? 'kg')) ?? StockUnit::Kg;
            $qty = (float) ($line['quantity'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $total += $qty * $this->referenceUnitPrice($indexed[$productId], $unit);
        }

        return round($total, 2);
    }

    private function bundleReferenceTotal(Offer $offer): float
    {
        $offer->loadMissing(['items.product']);

        $lines = $offer->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'sale_unit' => $item->sale_unit?->value ?? StockUnit::Kg->value,
        ])->all();

        $products = $offer->items
            ->pluck('product')
            ->filter()
            ->keyBy(fn (Product $product) => (string) $product->id)
            ->all();

        return $this->bundleReferenceTotalFromLines($lines, $products);
    }

    public function stockRequiredForBundle(Offer $offer, int $bundleCount = 1): array
    {
        $offer->loadMissing(['items.product']);
        $requirements = [];

        foreach ($offer->items as $item) {
            $product = $item->product;
            if ($product === null) {
                continue;
            }

            $qty = (float) $item->quantity * $bundleCount;
            $stockDelta = $this->unitConverter->toStockUnits(
                $qty,
                $item->sale_unit ?? StockUnit::Kg,
                $product->stock_unit ?? StockUnit::Kg
            );

            $requirements[] = [
                'product' => $product,
                'stock_delta' => $stockDelta,
            ];
        }

        return $requirements;
    }
}
