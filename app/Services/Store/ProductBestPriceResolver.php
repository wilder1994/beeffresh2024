<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\DataTransferObjects\Store\ProductPriceQuote;
use App\Domain\Catalog\StockUnit;
use App\Models\Product;

final class ProductBestPriceResolver
{
    public function __construct(
        private readonly VolumeScaleService $volumeScale,
    ) {}

    public function bestUnitPrice(Product $product, StockUnit $saleUnit, float $quantity): float
    {
        return $this->volumeScale->quote($product, $saleUnit, $quantity)->unitPrice;
    }

    public function quote(Product $product, StockUnit $saleUnit, float $quantity): ProductPriceQuote
    {
        return $this->volumeScale->quote($product, $saleUnit, $quantity);
    }
}
