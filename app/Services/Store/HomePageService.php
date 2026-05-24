<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\StockUnit;
use App\Domain\Catalog\TaxonomyStatus;
use App\Domain\Store\OfferType;
use App\Models\MeatCut;
use App\Models\Offer;
use App\Models\Product;
use App\Models\VideoReceta;
use App\Services\Catalog\ProductPromotionResolver;
use App\Support\YoutubeEmbedUrl;
use Illuminate\Support\Collection;

final class HomePageService
{
    public function __construct(
        private readonly CintaMerchandisingService $cintaMerchandising,
        private readonly OfferAvailabilityService $offerAvailability,
        private readonly OfferPricingService $offerPricing,
        private readonly ProductPromotionResolver $promotionResolver,
        private readonly ProductBestPriceResolver $bestPrice,
    ) {}

    /**
     * @return array{
     *     cintaTiles: Collection,
     *     promoProducts: Collection<int, array{product: Product, unit: StockUnit, unit_price: float, reference_price: float}>,
     *     offers: Collection<int, array{offer: Offer, reference_price: float, offer_price: float, unit_suffix: string|null, volume_summary: string|null, available: int, label: string}>,
     *     featuredProducts: Collection<int, array{product: Product, unit: StockUnit, unit_price: float}>,
     *     meatCuts: Collection,
     *     videos: Collection
     * }
     */
    public function data(): array
    {
        return [
            'cintaTiles' => $this->cintaMerchandising->tiles(),
            'promoProducts' => $this->promoProducts(),
            'offers' => $this->homeOffers(),
            'featuredProducts' => $this->featuredProducts(),
            'meatCuts' => $this->meatCuts(),
            'videos' => $this->videos(),
        ];
    }

    /**
     * @return Collection<int, array{product: Product, unit: StockUnit, unit_price: float, reference_price: float}>
     */
    private function promoProducts(): Collection
    {
        return Product::query()
            ->with(['meatType', 'meatCut'])
            ->where('status', ProductStatus::Available)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->where('stock', '>', 0)
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $this->promotionResolver->isActive($product))
            ->take(8)
            ->map(function (Product $product): array {
                $unit = $product->stock_unit ?? StockUnit::Kg;

                return [
                    'product' => $product,
                    'unit' => $unit,
                    'unit_price' => $this->promotionResolver->effectivePrice($product, $unit),
                    'reference_price' => $this->offerPricing->referenceUnitPrice($product, $unit),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array{offer: Offer, reference_price: float, offer_price: float, unit_suffix: string|null, volume_summary: string|null, available: int, label: string}>
     */
    private function homeOffers(): Collection
    {
        return Offer::query()
            ->where('show_on_home', true)
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['product', 'items.product'])
            ->get()
            ->filter(fn (Offer $offer) => $this->offerAvailability->isVisibleOnStorefront($offer))
            ->take(8)
            ->map(function (Offer $offer): array {
                $prices = $this->offerPricing->storefrontCardPrices($offer);

                return [
                    'offer' => $offer,
                    'reference_price' => $prices['reference'],
                    'offer_price' => $prices['offer'],
                    'unit_suffix' => $prices['unit_suffix'],
                    'volume_summary' => $prices['volume_summary'],
                    'available' => $this->offerAvailability->availableUnits($offer),
                    'label' => $this->offerAvailability->availabilityLabel($offer),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array{product: Product, unit: StockUnit, unit_price: float}>
     */
    private function featuredProducts(): Collection
    {
        $promoIds = $this->promoProducts()->pluck('product.id');

        return Product::query()
            ->with(['meatType', 'meatCut'])
            ->where('featured', true)
            ->where('status', ProductStatus::Available)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->where('stock', '>', 0)
            ->whereNotIn('id', $promoIds)
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function (Product $product): array {
                $unit = $product->stock_unit ?? StockUnit::Kg;

                return [
                    'product' => $product,
                    'unit' => $unit,
                    'unit_price' => $this->bestPrice->bestUnitPrice($product, $unit, 1.0),
                ];
            });
    }

    private function meatCuts(): Collection
    {
        $cuts = MeatCut::query()
            ->with(['meatType'])
            ->where('status', TaxonomyStatus::Active)
            ->withCount([
                'products as available_products_count' => static function ($query): void {
                    $query->where('status', ProductStatus::Available);
                },
            ])
            ->having('available_products_count', '>', 0)
            ->orderBy('name')
            ->limit(8)
            ->get();

        return $cuts->map(function (MeatCut $cut): array {
            $coverProduct = $cut->products()
                ->where('status', ProductStatus::Available)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->orderByDesc('featured')
                ->orderBy('name')
                ->first();

            return [
                'cut' => $cut,
                'image_url' => $coverProduct?->imageUrl(),
                'products_count' => (int) $cut->available_products_count,
                'catalog_url' => route('products.public.index', ['meat_cut_id' => $cut->id]),
            ];
        });
    }

    private function videos(): Collection
    {
        return VideoReceta::query()
            ->latest()
            ->limit(3)
            ->get()
            ->map(function (VideoReceta $video): array {
                $isYoutube = $video->tipo === 'youtube';
                $embedUrl = $isYoutube ? YoutubeEmbedUrl::resolve((string) $video->url) : null;
                $thumbnailUrl = $isYoutube
                    ? YoutubeEmbedUrl::thumbnailUrl((string) $video->url, 'hqdefault')
                    : null;
                $fileUrl = ! $isYoutube && filled($video->archivo)
                    ? asset('storage/videos/'.$video->archivo)
                    : null;

                return [
                    'video' => $video,
                    'embed_url' => $embedUrl,
                    'thumbnail_url' => $thumbnailUrl,
                    'file_url' => $fileUrl,
                    'is_youtube' => $isYoutube,
                ];
            });
    }
}
