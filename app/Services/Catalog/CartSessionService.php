<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Models\Product;
use App\Services\Store\ProductBestPriceResolver;

final class CartSessionService
{
    public function __construct(
        private readonly ProductBestPriceResolver $bestPrice,
        private readonly CartUnitConverter $unitConverter,
    ) {}

    public function productLineKey(int $productId, StockUnit $saleUnit): string
    {
        return 'product:'.$productId.':'.$saleUnit->value;
    }

    public function offerLineKey(int $offerId): string
    {
        return 'offer:'.$offerId;
    }

    public function isOfferLine(string|int $key): bool
    {
        return str_starts_with((string) $key, 'offer:');
    }

    public function parseOfferLineKey(string|int $key): int
    {
        return (int) substr((string) $key, strlen('offer:'));
    }

    /**
     * @return array{0: int, 1: StockUnit}
     */
    public function parseProductLineKey(string|int $key): array
    {
        $raw = (string) $key;

        if (str_starts_with($raw, 'product:')) {
            $raw = substr($raw, strlen('product:'));
        }

        if (! str_contains($raw, ':')) {
            return [(int) $raw, StockUnit::Kg];
        }

        [$id, $unit] = explode(':', $raw, 2);

        return [(int) $id, StockUnit::tryFrom($unit) ?? StockUnit::Kg];
    }

    /** @deprecated use parseProductLineKey */
    public function parseLineKey(string|int $key): array
    {
        return $this->parseProductLineKey($key);
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

        return (float) max(1, (int) round($qty));
    }

    /**
     * Máximo de unidades (enteras) comprables según el stock disponible y la unidad de venta.
     */
    public function maxPurchasableUnits(Product $product, StockUnit $saleUnit): int
    {
        $stock = (float) $product->stock;

        if ($stock <= 0) {
            return 0;
        }

        $stockPerUnit = $this->stockRequired($product, 1.0, $saleUnit);

        if ($stockPerUnit <= 0) {
            return 0;
        }

        return (int) floor(($stock / $stockPerUnit) + 1e-6);
    }

    public function unitPrice(Product $product, StockUnit $saleUnit, float $quantity = 1.0): float
    {
        return $this->bestPrice->bestUnitPrice($product, $saleUnit, $quantity);
    }

    public function priceQuote(Product $product, StockUnit $saleUnit, float $quantity): \App\DataTransferObjects\Store\ProductPriceQuote
    {
        return $this->bestPrice->quote($product, $saleUnit, $quantity);
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
            if ($this->isOfferLine($key)) {
                continue;
            }

            [$productId] = $this->parseProductLineKey($key);
            $ids[] = $productId;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $cart
     * @return list<int>
     */
    public function offerIds(array $cart): array
    {
        $ids = [];

        foreach (array_keys($cart) as $key) {
            if ($this->isOfferLine($key)) {
                $ids[] = $this->parseOfferLineKey($key);
            }
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
