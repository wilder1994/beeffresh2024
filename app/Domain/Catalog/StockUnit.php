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

    public static function resolve(mixed $value, self $default = self::Kg): self
    {
        if ($value instanceof self) {
            return $value === self::Pack ? $default : $value;
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return self::tryFrom((string) $value) ?? $default;
    }
}
