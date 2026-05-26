<?php

declare(strict_types=1);

namespace App\Support\Realtime;

use App\Domain\Catalog\ProductStatus;
use App\Models\Product;

/** Payload unificado para stock realtime (Fase 1.5). */
final class ProductStockPayload
{
    /** @return array<string, mixed> */
    public static function stockPayload(Product $product): array
    {
        $product->refresh();

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => (float) $product->stock,
            'availability_label' => self::availabilityLabel($product),
            'is_low_stock' => $product->isLowStock(),
            'is_out_of_stock' => self::isOutOfStock($product),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public static function availabilityPayload(Product $product): array
    {
        $product->refresh();

        return [
            'product_id' => $product->id,
            'availability_label' => self::availabilityLabel($product),
            'is_low_stock' => $product->isLowStock(),
            'is_out_of_stock' => self::isOutOfStock($product),
            'can_purchase' => $product->isPurchasable(),
        ];
    }

    public static function availabilityLabel(Product $product): string
    {
        if (self::isOutOfStock($product)) {
            return 'Agotado';
        }

        if ($product->isLowStock()) {
            return 'Stock bajo';
        }

        return 'Disponible';
    }

    public static function isOutOfStock(Product $product): bool
    {
        return $product->status !== ProductStatus::Available || (float) $product->stock <= 0;
    }
}
