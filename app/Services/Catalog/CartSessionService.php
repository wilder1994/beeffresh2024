<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;

final class CartSessionService
{
    public function __construct(
        private readonly ProductPromotionResolver $promotionResolver,
        private readonly CartUnitConverter $unitConverter,
    ) {}

    public function lineKey(int $productId, StockUnit $saleUnit): string
    {
        return $productId.':'.$saleUnit->value;
    }

    /**
     * @return array{0: int, 1: StockUnit}
     */
    public function parseLineKey(string|int $key): array
    {
        $raw = (string) $key;

        if (! str_contains($raw, ':')) {
            return [(int) $raw, StockUnit::Kg];
        }

        [$id, $unit] = explode(':', $raw, 2);

        return [(int) $id, StockUnit::tryFrom($unit) ?? StockUnit::Kg];
    }

    public function parseSaleUnit(mixed $value): StockUnit
    {
        if ($value instanceof StockUnit) {
            return $value;
        }

        return StockUnit::tryFrom(strtolower((string) $value)) ?? StockUnit::Kg;
    }

    public function normalizeQuantity(mixed $value): float
    {
        $qty = is_numeric($value) ? (float) $value : 1.0;

        return max(1.0, round($qty, 2));
    }

    public function unitPrice(Product $product, StockUnit $saleUnit): float
    {
        return $this->promotionResolver->effectivePrice($product, $saleUnit);
    }

    public function stockRequired(Product $product, float $saleQuantity, StockUnit $saleUnit): float
    {
        return $this->unitConverter->toStockUnits(
            $saleQuantity,
            $saleUnit,
            $product->stock_unit ?? StockUnit::Kg
        );
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $cart
     * @return list<int>
     */
    public function productIds(array $cart): array
    {
        $ids = [];

        foreach (array_keys($cart) as $key) {
            [$productId] = $this->parseLineKey($key);
            $ids[] = $productId;
        }

        return array_values(array_unique($ids));
    }

    public function totalItemCount(array $cart): float
    {
        $total = 0.0;

        foreach ($cart as $item) {
            if (is_array($item) && isset($item['cantidad'])) {
                $total += (float) $item['cantidad'];
            }
        }

        return $total;
    }
}
