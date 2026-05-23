<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Models\Product;
use Illuminate\Support\Carbon;

final class ProductPromotionResolver
{
    public function isActive(Product $product, ?Carbon $at = null): bool
    {
        if ($product->promo_price_kg === null && $product->promo_price_lb === null) {
            return false;
        }

        $at ??= Carbon::now();

        if ($product->promo_start !== null && $at->lt($product->promo_start->copy()->startOfDay())) {
            return false;
        }

        if ($product->promo_end !== null && $at->gt($product->promo_end->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function effectivePriceKg(Product $product, ?Carbon $at = null): float
    {
        if ($this->isActive($product, $at) && $product->promo_price_kg !== null) {
            return (float) $product->promo_price_kg;
        }

        return (float) $product->price_per_kg;
    }

    public function effectivePriceLb(Product $product, ?Carbon $at = null): float
    {
        if ($this->isActive($product, $at) && $product->promo_price_lb !== null) {
            return (float) $product->promo_price_lb;
        }

        return (float) $product->price_per_lb;
    }

    public function effectivePrice(Product $product, StockUnit $unit, ?Carbon $at = null): float
    {
        return match ($unit) {
            StockUnit::Lb => $this->effectivePriceLb($product, $at),
            StockUnit::Kg => $this->effectivePriceKg($product, $at),
        };
    }
}
