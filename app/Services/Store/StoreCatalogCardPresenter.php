<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Domain\Catalog\StockUnit;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;
use App\Support\Realtime\ProductStockPayload;

/** Datos de tarjeta teaser para catálogo público (opción A: sin compra en grid). */
final class StoreCatalogCardPresenter
{
    public function __construct(
        private readonly ProductBestPriceResolver $bestPrice,
        private readonly OfferPricingService $offerPricing,
        private readonly ProductPromotionResolver $promotionResolver,
    ) {}

    /**
     * @return array{
     *     unit_price: float,
     *     reference_price: float,
     *     badge: string|null,
     *     availability_label: string|null,
     *     meta: string,
     *     image_url: string,
     * }
     */
    public function forProduct(Product $product): array
    {
        $unit = $product->stock_unit ?? StockUnit::Kg;
        $unitPrice = $this->bestPrice->bestUnitPrice($product, $unit, 1.0);
        $referencePrice = $this->offerPricing->referenceUnitPrice($product, $unit);

        $badge = null;
        if ($this->promotionResolver->isActive($product)) {
            $badge = 'Promo';
        } elseif ($product->featured) {
            $badge = 'Destacado';
        }

        $availabilityLabel = null;
        if (! $product->isPurchasable()) {
            $availabilityLabel = ProductStockPayload::availabilityLabel($product);
        } elseif ($product->isLowStock()) {
            $availabilityLabel = 'Stock bajo';
        }

        return [
            'unit_price' => $unitPrice,
            'reference_price' => $referencePrice,
            'badge' => $badge,
            'availability_label' => $availabilityLabel,
            'meta' => trim(($product->meatType?->name ?? '').' · '.($product->meatCut?->name ?? '')),
            'image_url' => $product->imageUrl() ?? asset('logos/logo.jpeg'),
        ];
    }
}
