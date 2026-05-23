<?php

declare(strict_types=1);

namespace App\Services\Catalog;

final class ProductPriceCalculator
{
    public function lbFromKg(float $pricePerKg): float
    {
        return round($pricePerKg / 2, 2);
    }

    public function kgFromLb(float $pricePerLb): float
    {
        return round($pricePerLb * 2, 2);
    }
}
