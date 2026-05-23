<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\MeatCut;
use App\Models\MeatType;
use App\Models\Product;
use Illuminate\Support\Str;

final class ProductSkuGenerator
{
    public function generate(MeatType $meatType, MeatCut $meatCut): string
    {
        $typeCode = strtoupper(Str::substr(Str::slug($meatType->slug, ''), 0, 3));
        $cutCode = strtoupper(Str::substr(Str::slug($meatCut->slug, ''), 0, 3));
        $prefix = $typeCode.'-'.$cutCode;

        $sequence = Product::query()
            ->where('meat_type_id', $meatType->id)
            ->where('meat_cut_id', $meatCut->id)
            ->count() + 1;

        do {
            $sku = sprintf('%s-%04d', $prefix, $sequence);
            $sequence++;
        } while (Product::query()->where('sku', $sku)->exists());

        return $sku;
    }
}
