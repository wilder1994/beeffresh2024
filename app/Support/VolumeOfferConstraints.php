<?php

declare(strict_types=1);

namespace App\Support;

final class VolumeOfferConstraints
{
    /** Mínimo comercial: 3 lb (1 kg del catálogo = 2 lb). */
    public const MIN_POUNDS = 3.0;

    public const POUNDS_PER_KG = 2.0;

    public static function equivalentPounds(float $quantity, string $unit): float
    {
        return $unit === 'lb' ? $quantity : $quantity * self::POUNDS_PER_KG;
    }

    public static function minimumQuantity(string $unit): float
    {
        return $unit === 'lb' ? self::MIN_POUNDS : self::MIN_POUNDS / self::POUNDS_PER_KG;
    }

    public static function meetsMinimum(float $quantity, string $unit): bool
    {
        return self::equivalentPounds($quantity, $unit) >= self::MIN_POUNDS;
    }

    /**
     * @return array{volume_offer_price_kg: ?float, volume_offer_price_lb: ?float}
     */
    public static function splitUnitPrice(string $unit, float $price): array
    {
        return [
            'volume_offer_price_kg' => $unit === 'kg' ? $price : null,
            'volume_offer_price_lb' => $unit === 'lb' ? $price : null,
        ];
    }
}
