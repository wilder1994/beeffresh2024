<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum StockUnit: string
{
    case Kg = 'kg';
    case Lb = 'lb';
    case Pack = 'pack';

    public function label(): string
    {
        return match ($this) {
            self::Kg => 'Kilogramo',
            self::Lb => 'Libra',
            self::Pack => 'Pack',
        };
    }
}
