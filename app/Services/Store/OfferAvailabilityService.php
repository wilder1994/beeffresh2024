<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\CartUnitConverter;

final class OfferAvailabilityService
{
    public function __construct(
        private readonly CartUnitConverter $unitConverter,
    ) {}

    public function availableUnits(Offer $offer): int
    {
        if (! $offer->is_active || $offer->image === '') {
            return 0;
        }

        if ($offer->type === OfferType::Volume) {
            return $this->volumeAvailableUnits($offer);
        }

        return $this->bundleAvailableUnits($offer);
    }

    public function isVisibleOnStorefront(Offer $offer, bool $forCinta = false): bool
    {
        if (! $offer->is_active || $offer->image === '') {
            return false;
        }

        if ($forCinta && ! $offer->show_on_cinta) {
            return false;
        }

        if (! $forCinta && ! $offer->show_on_home) {
            return false;
        }

        return $this->availableUnits($offer) > 0;
    }

    public function availabilityLabel(Offer $offer): string
    {
        $units = $this->availableUnits($offer);

        if ($units <= 0) {
            return '';
        }

        if ($units === 1) {
            return '¡Último disponible!';
        }

        if ($units <= 10) {
            return 'Solo '.$units.' disponibles';
        }

        return $units.'+ disponibles';
    }

    private function volumeAvailableUnits(Offer $offer): int
    {
        $product = $offer->product;

        if ($product === null || ! $this->productIsEligible($product)) {
            return 0;
        }

        $minQty = (float) $offer->volume_min_quantity;
        if ($minQty <= 0) {
            return 0;
        }

        $saleUnit = StockUnit::tryFrom((string) $offer->volume_sale_unit) ?? StockUnit::Kg;
        $requiredStock = $this->unitConverter->toStockUnits(
            $minQty,
            $saleUnit,
            $product->stock_unit ?? StockUnit::Kg
        );

        if ($requiredStock <= 0) {
            return 0;
        }

        return (int) floor((float) $product->stock / $requiredStock);
    }

    private function bundleAvailableUnits(Offer $offer): int
    {
        $offer->loadMissing(['items.product']);

        if ($offer->items->isEmpty()) {
            return 0;
        }

        $minimum = PHP_INT_MAX;

        foreach ($offer->items as $item) {
            $product = $item->product;

            if ($product === null || ! $this->productIsEligible($product)) {
                return 0;
            }

            $requiredStock = $this->unitConverter->toStockUnits(
                (float) $item->quantity,
                $item->sale_unit ?? StockUnit::Kg,
                $product->stock_unit ?? StockUnit::Kg
            );

            if ($requiredStock <= 0) {
                return 0;
            }

            $available = (int) floor((float) $product->stock / $requiredStock);
            $minimum = min($minimum, $available);
        }

        return $minimum === PHP_INT_MAX ? 0 : $minimum;
    }

    private function productIsEligible(Product $product): bool
    {
        return $product->status === ProductStatus::Available
            && (float) $product->stock > 0
            && filled($product->image);
    }
}
