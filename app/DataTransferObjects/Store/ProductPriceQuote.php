<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Store;

use App\Domain\Catalog\StockUnit;

final class ProductPriceQuote
{
    /**
     * @param  'volume'|'promo'|'catalog'  $tier
     */
    public function __construct(
        public readonly float $unitPrice,
        public readonly string $tier,
        public readonly bool $volumeActive,
        public readonly StockUnit $saleUnit,
        public readonly float $quantity,
        public readonly ?string $feedbackMessage,
        public readonly ?string $volumeSummary,
        public readonly float $catalogUnitPrice,
        public readonly float $standardUnitPrice,
        public readonly ?float $volumeUnitPrice,
        public readonly ?float $remainingForVolume,
        public readonly ?StockUnit $remainingUnit,
    ) {}

    public function pricingLabel(): string
    {
        return match ($this->tier) {
            'volume' => 'Oferta por volumen aplicada',
            'promo' => 'Promoción activa',
            default => 'Precio de catálogo',
        };
    }
}
