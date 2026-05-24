<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Models\Offer;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use App\Services\Store\VolumeScaleService;

final class OfferAdminIndexPresenter
{
    public function __construct(
        private readonly OfferPricingService $pricing,
        private readonly OfferAvailabilityService $availability,
        private readonly VolumeScaleService $volumeScale,
        private readonly ProductPromotionResolver $promotionResolver,
    ) {}

    /**
     * @return array{
     *     offer: Offer,
     *     reference: float,
     *     offer_total: float,
     *     available: int|float,
     * }
     */
    public function bundleRow(Offer $offer): array
    {
        $reference = $this->pricing->referenceTotal($offer);
        $offerTotal = $this->pricing->offerTotal($offer);
        $available = $this->availability->availableUnits($offer);

        $discountPercent = null;
        if ($reference > 0 && $offerTotal > 0 && $offerTotal < $reference) {
            $discountPercent = (int) round((1 - ($offerTotal / $reference)) * 100);
        }

        $offer->loadMissing('items');

        return [
            'offer' => $offer,
            'reference' => $reference,
            'offer_total' => $offerTotal,
            'available' => $available,
            'discount_percent' => $discountPercent,
            'items_count' => $offer->items->count(),
            'stock_status' => $this->stockStatus($available),
            'image_url' => $offer->imageUrl(),
        ];
    }

    /**
     * @return array{
     *     offer: Offer,
     *     product_name: string,
     *     product_sku: string|null,
     *     product_image_url: string|null,
     *     offer_name: string,
     *     min_condition: string,
     *     primary_unit: string,
     *     scale_price: float,
     *     alternate_scale_price: float|null,
     *     alternate_unit: string|null,
     *     reference_price: float,
     *     reference_tier: 'promo'|'catalog',
     *     savings_percent: int|null,
     *     available: int|float,
     *     stock_status: 'ok'|'low'|'out',
     * }
     */
    public function volumeRow(Offer $offer): array
    {
        $product = $offer->product;
        $offerUnit = StockUnit::resolve($offer->volume_sale_unit);
        $minQty = (float) $offer->volume_min_quantity;
        $alternateUnit = $offerUnit === StockUnit::Kg ? StockUnit::Lb : StockUnit::Kg;

        $referenceInOfferUnit = $product !== null
            ? $this->volumeScale->standardUnitPrice($product, $offerUnit)
            : 0.0;
        $scaleInOfferUnit = $this->pricing->volumeOfferUnitPrice($offer, $offerUnit);
        $alternateScale = $this->pricing->volumeOfferUnitPrice($offer, $alternateUnit);

        $savingsPercent = null;
        if ($referenceInOfferUnit > 0 && $scaleInOfferUnit > 0 && $scaleInOfferUnit < $referenceInOfferUnit) {
            $savingsPercent = (int) round((1 - ($scaleInOfferUnit / $referenceInOfferUnit)) * 100);
        }

        return [
            'offer' => $offer,
            'offer_name' => $offer->name,
            'product_name' => $product?->name ?? '—',
            'product_sku' => $product?->sku,
            'product_image_url' => $product?->imageUrl(),
            'min_condition' => '≥ '.$this->formatMinQuantity($minQty, $offerUnit),
            'primary_unit' => $offerUnit->value,
            'scale_price' => $scaleInOfferUnit,
            'alternate_scale_price' => $alternateScale > 0 ? $alternateScale : null,
            'alternate_unit' => $alternateUnit->value,
            'reference_price' => $referenceInOfferUnit,
            'reference_tier' => $product !== null && $this->promotionResolver->isActive($product)
                ? 'promo'
                : 'catalog',
            'savings_percent' => $savingsPercent,
            'available' => $this->availability->availableUnits($offer),
            'stock_status' => $this->stockStatus($this->availability->availableUnits($offer)),
        ];
    }

    /**
     * @return 'ok'|'low'|'out'
     */
    private function stockStatus(int|float $available): string
    {
        if ($available <= 0) {
            return 'out';
        }

        if ($available <= 5) {
            return 'low';
        }

        return 'ok';
    }

    private function formatMinQuantity(float $minQty, StockUnit $unit): string
    {
        $display = fmod($minQty, 1.0) === 0.0
            ? (string) (int) $minQty
            : rtrim(rtrim(number_format($minQty, 1, ',', '.'), '0'), ',');

        return "{$display} {$unit->value}";
    }
}
