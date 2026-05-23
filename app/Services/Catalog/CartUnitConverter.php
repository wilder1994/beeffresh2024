<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;

/** Convierte cantidades de compra a la unidad de inventario del producto. */
final class CartUnitConverter
{
    /** Libra comercial del catálogo: 2 lb ≈ 1 kg (coherente con precio lb = kg ÷ 2). */
    private const LB_TO_KG = 0.5;

    public function toStockUnits(float $quantity, StockUnit $saleUnit, StockUnit $stockUnit): float
    {
        if ($saleUnit === $stockUnit) {
            return $quantity;
        }

        return match ([$saleUnit, $stockUnit]) {
            [StockUnit::Lb, StockUnit::Kg] => $quantity * self::LB_TO_KG,
            [StockUnit::Kg, StockUnit::Lb] => $quantity / self::LB_TO_KG,
            default => $quantity,
        };
    }
}
