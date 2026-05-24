<?php

declare(strict_types=1);

namespace App\Domain\Store;

enum OfferType: string
{
    case Bundle = 'bundle';
    case Volume = 'volume';

    public function label(): string
    {
        return match ($this) {
            self::Bundle => 'Pack (varios productos)',
            self::Volume => 'Oferta por cantidad',
        };
    }
}
