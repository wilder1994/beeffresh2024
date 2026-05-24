<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\DataTransferObjects\Store\CintaTile;
use App\Domain\Catalog\ProductStatus;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;
use Illuminate\Support\Collection;

final class CintaMerchandisingService
{
    public function __construct(
        private readonly OfferAvailabilityService $availability,
        private readonly OfferPricingService $offerPricing,
        private readonly ProductPromotionResolver $promotionResolver,
        private readonly ProductBestPriceResolver $bestPrice,
    ) {}

    /**
     * @return Collection<int, CintaTile>
     */
    public function tiles(): Collection
    {
        $tiles = collect();

        $products = Product::query()
            ->where('show_on_cinta', true)
            ->where('status', ProductStatus::Available)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->where('stock', '>', 0)
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            $badge = $this->promotionResolver->isActive($product)
                ? 'Promo'
                : ($product->featured ? 'Destacado' : 'Producto');

            $unit = $product->stock_unit ?? \App\Domain\Catalog\StockUnit::Kg;
            $price = $this->bestPrice->bestUnitPrice($product, $unit, 1.0);

            $tiles->push(new CintaTile(
                url: route('products.public.show', $product),
                imageUrl: (string) $product->imageUrl(),
                title: $product->name,
                badge: $badge,
                priceLabel: '$'.number_format($price, 0, ',', '.').'/'.$unit->value,
            ));
        }

        $offers = Offer::query()
            ->where('show_on_cinta', true)
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['product', 'items.product'])
            ->get();

        foreach ($offers as $offer) {
            if (! $this->availability->isVisibleOnStorefront($offer, forCinta: true)) {
                continue;
            }

            $tiles->push(new CintaTile(
                url: $offer->isVolume() && $offer->product
                    ? route('products.public.show', $offer->product)
                    : route('offers.public.show', $offer),
                imageUrl: $offer->imageUrl(),
                title: $offer->name,
                badge: $offer->isVolume() ? 'Por cantidad' : 'Pack',
                priceLabel: $this->offerPricing->storefrontPriceLabel($offer),
                availabilityLabel: $this->availability->availabilityLabel($offer),
            ));
        }

        return $tiles->values();
    }
}
