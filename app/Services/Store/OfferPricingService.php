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
            $unit = $this->volumeSaleUnit($offer);
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

    public function volumeStorefrontSummary(Offer $offer): ?string
    {
        if ($offer->type !== OfferType::Volume) {
            return null;
        }

        $unit = $this->volumeSaleUnit($offer);
        $minQty = (float) $offer->volume_min_quantity;

        if ($minQty <= 0) {
            return null;
        }

        return $this->volumeSummaryText($minQty, $unit);
    }

    public function volumeSummaryText(float $minQty, StockUnit $unit): string
    {
        $minDisplay = $this->formatMinQuantityDisplay($minQty);

        return "Precio especial al comprar {$minDisplay} {$unit->value} o más.";
    }

    /**
     * Precios para tarjetas públicas (home, cinta). Volume → unitario; pack → total del pack.
     *
     * @return array{
     *     reference: float,
     *     offer: float,
     *     unit_suffix: string|null,
     *     volume_summary: string|null,
     * }
     */
    public function storefrontCardPrices(Offer $offer): array
    {
        if ($offer->type === OfferType::Volume) {
            $product = $offer->product;
            if ($product === null) {
                return [
                    'reference' => 0.0,
                    'offer' => 0.0,
                    'unit_suffix' => null,
                    'volume_summary' => null,
                ];
            }

            $unit = $this->volumeSaleUnit($offer);

            return [
                'reference' => $this->referenceUnitPrice($product, $unit),
                'offer' => $this->volumeOfferUnitPrice($offer, $unit),
                'unit_suffix' => '/'.$unit->value,
                'volume_summary' => $this->volumeStorefrontSummary($offer),
            ];
        }

        return [
            'reference' => $this->referenceTotal($offer),
            'offer' => $this->offerTotal($offer),
            'unit_suffix' => null,
            'volume_summary' => null,
        ];
    }

    public function storefrontPriceLabel(Offer $offer): string
    {
        $prices = $this->storefrontCardPrices($offer);
        $formatted = '$'.number_format($prices['offer'], 0, ',', '.');

        if ($prices['unit_suffix'] !== null) {
            return $formatted.$prices['unit_suffix'];
        }

        return $formatted;
    }

    private function volumeReferenceTotal(Offer $offer): float
    {
        $product = $offer->product;
        if ($product === null) {
            return 0.0;
        }

        $unit = $this->volumeSaleUnit($offer);
        $minQty = (float) $offer->volume_min_quantity;

        return round($minQty * $this->referenceUnitPrice($product, $unit), 2);
    }

    private function volumeSaleUnit(Offer $offer): StockUnit
    {
        return StockUnit::resolve($offer->volume_sale_unit);
    }

    private function formatMinQuantityDisplay(float $minQty): string
    {
        if (fmod($minQty, 1.0) === 0.0) {
            return (string) (int) $minQty;
        }

        return rtrim(rtrim(number_format($minQty, 1, ',', '.'), '0'), ',');
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
