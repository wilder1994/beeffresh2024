<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum SaleType: string
{
    case FixedWeight = 'fixed_weight';
    case VariableWeight = 'variable_weight';

    public function label(): string
    {
        return match ($this) {
            self::FixedWeight => 'Peso fijo',
            self::VariableWeight => 'Peso variable',
        };
    }
}
