<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum StockUnit: string
{
    case Kg = 'kg';
    case Lb = 'lb';

    public function label(): string
    {
        return match ($this) {
            self::Kg => 'Kilogramo',
            self::Lb => 'Libra',
        };
    }
}
