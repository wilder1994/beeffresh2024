<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum ProductStatus: string
{
    case Available = 'available';
    case OutOfStock = 'out_of_stock';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::OutOfStock => 'Agotado',
            self::Hidden => 'Oculto',
        };
    }
}
