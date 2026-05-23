<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Catalog\StockUnit;
use App\Services\Catalog\CartUnitConverter;
use PHPUnit\Framework\TestCase;

class CartUnitConverterTest extends TestCase
{
    public function test_converts_purchase_pounds_to_kilograms_for_stock(): void
    {
        $converter = new CartUnitConverter();

        $this->assertSame(1.5, $converter->toStockUnits(3, StockUnit::Lb, StockUnit::Kg));
        $this->assertSame(3.0, $converter->toStockUnits(3, StockUnit::Kg, StockUnit::Kg));
    }
}
