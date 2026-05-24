<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;
use Illuminate\Support\Collection;

final class ProductBestPriceResolver
{
    public function __construct(
        private readonly ProductPromotionResolver $promotionResolver,
        private readonly OfferPricingService $offerPricing,
        private readonly OfferAvailabilityService $availability,
    ) {}

    public function bestUnitPrice(Product $product, StockUnit $saleUnit, float $quantity): float
    {
        $candidates = [
            $this->offerPricing->referenceUnitPrice($product, $saleUnit),
        ];

        if ($this->promotionResolver->isActive($product)) {
            $candidates[] = $this->promotionResolver->effectivePrice($product, $saleUnit);
        }

        $volumeOffer = $this->activeVolumeOfferForProduct($product, $quantity);
        if ($volumeOffer !== null) {
            $candidates[] = $this->offerPricing->volumeOfferUnitPrice($volumeOffer, $saleUnit);
        }

        return min($candidates);
    }

    public function activeVolumeOfferForProduct(Product $product, float $quantity): ?Offer
    {
        /** @var Collection<int, Offer> $offers */
        $offers = Offer::query()
            ->where('type', OfferType::Volume)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($offers as $offer) {
            if ($this->availability->availableUnits($offer) <= 0) {
                continue;
            }

            $minQty = (float) $offer->volume_min_quantity;
            if ($quantity >= $minQty) {
                return $offer;
            }
        }

        return null;
    }
}
