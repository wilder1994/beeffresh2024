<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\DataTransferObjects\Store\ProductPriceQuote;
use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Catalog\ProductPromotionResolver;
use App\Support\VolumeOfferConstraints;

final class VolumeScaleService
{
    public function __construct(
        private readonly OfferAvailabilityService $availability,
        private readonly OfferPricingService $offerPricing,
        private readonly ProductPromotionResolver $promotionResolver,
    ) {}

    public function activeVolumeOffer(Product $product): ?Offer
    {
        $offer = Offer::query()
            ->where('type', OfferType::Volume)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($offer === null || $this->availability->availableUnits($offer) <= 0) {
            return null;
        }

        return $offer;
    }

    public function offerUnit(Offer $offer): StockUnit
    {
        return StockUnit::resolve($offer->volume_sale_unit);
    }

    public function quantityInUnit(float $quantity, StockUnit $from, StockUnit $to): float
    {
        if ($from === $to) {
            return $quantity;
        }

        return match ([$from, $to]) {
            [StockUnit::Lb, StockUnit::Kg] => $quantity * VolumeOfferConstraints::LB_TO_KG,
            [StockUnit::Kg, StockUnit::Lb] => $quantity * VolumeOfferConstraints::KG_TO_LB,
            default => $quantity,
        };
    }

    public function meetsMinimum(Offer $offer, float $quantity, StockUnit $saleUnit): bool
    {
        $minQty = (float) $offer->volume_min_quantity;
        if ($minQty <= 0) {
            return false;
        }

        $offerUnit = $this->offerUnit($offer);
        $quantityInOfferUnit = $this->quantityInUnit($quantity, $saleUnit, $offerUnit);

        return $quantityInOfferUnit >= $minQty - 0.0001;
    }

    public function volumeUnitPriceInSaleUnit(Offer $offer, StockUnit $saleUnit): float
    {
        $offerUnit = $this->offerUnit($offer);
        $priceInOfferUnit = $this->offerPricing->volumeOfferUnitPrice($offer, $offerUnit);

        if ($saleUnit === $offerUnit) {
            return $priceInOfferUnit;
        }

        return match ([$offerUnit, $saleUnit]) {
            [StockUnit::Lb, StockUnit::Kg] => $priceInOfferUnit / VolumeOfferConstraints::LB_TO_KG,
            [StockUnit::Kg, StockUnit::Lb] => $priceInOfferUnit / VolumeOfferConstraints::KG_TO_LB,
            default => $priceInOfferUnit,
        };
    }

    public function standardUnitPrice(Product $product, StockUnit $saleUnit): float
    {
        if ($this->promotionResolver->isActive($product)) {
            return $this->promotionResolver->effectivePrice($product, $saleUnit);
        }

        return $this->offerPricing->referenceUnitPrice($product, $saleUnit);
    }

    public function baselineUnitPriceForAdmin(Product $product, StockUnit $unit): float
    {
        return $this->standardUnitPrice($product, $unit);
    }

    /**
     * @return string|null Mensaje de error; null si es válido.
     */
    public function validateScaleUnitPrice(Product $product, StockUnit $unit, float $scalePrice): ?string
    {
        if ($scalePrice <= 0) {
            return 'El precio por escala debe ser mayor a cero.';
        }

        $baseline = $this->baselineUnitPriceForAdmin($product, $unit);

        if ($scalePrice >= $baseline) {
            $label = $this->promotionResolver->isActive($product)
                ? 'promoción individual activa'
                : 'precio normal del catálogo';

            return "El precio por escala debe ser menor que la {$label} ($".number_format($baseline, 0, ',', '.')."/{$unit->value}).";
        }

        return null;
    }

    public function quote(Product $product, StockUnit $saleUnit, float $quantity): ProductPriceQuote
    {
        $quantity = max(0.01, round($quantity, 2));
        $catalogUnitPrice = $this->offerPricing->referenceUnitPrice($product, $saleUnit);
        $standardUnitPrice = $this->standardUnitPrice($product, $saleUnit);
        $volumeOffer = $this->activeVolumeOffer($product);

        $volumeUnitPrice = null;
        $volumeSummary = null;
        $volumeActive = false;
        $remainingForVolume = null;
        $remainingUnit = null;
        $feedbackMessage = null;

        if ($volumeOffer !== null) {
            $offerUnit = $this->offerUnit($volumeOffer);
            $volumeUnitPrice = $this->volumeUnitPriceInSaleUnit($volumeOffer, $saleUnit);
            $volumeSummary = $this->offerPricing->volumeStorefrontSummary($volumeOffer);
            $minQty = (float) $volumeOffer->volume_min_quantity;
            $quantityInOfferUnit = $this->quantityInUnit($quantity, $saleUnit, $offerUnit);
            $remainingInOfferUnit = max(0.0, $minQty - $quantityInOfferUnit);

            if ($remainingInOfferUnit > 0.0001) {
                $remainingForVolume = fmod($remainingInOfferUnit, 1.0) === 0.0
                    ? (float) (int) $remainingInOfferUnit
                    : round($remainingInOfferUnit, 1);
                $remainingUnit = $offerUnit;
                $remainingInSaleUnit = $this->quantityInUnit($remainingInOfferUnit, $offerUnit, $saleUnit);
                $remainingDisplay = fmod($remainingInSaleUnit, 1.0) === 0.0
                    ? (string) (int) $remainingInSaleUnit
                    : rtrim(rtrim(number_format($remainingInSaleUnit, 1, ',', '.'), '0'), ',');
                $feedbackMessage = "Te faltan {$remainingDisplay} {$saleUnit->value} para activar el precio por volumen.";
            } else {
                $volumeActive = true;
                $feedbackMessage = 'Oferta por volumen aplicada';
            }
        }

        if ($volumeActive && $volumeUnitPrice !== null) {
            return new ProductPriceQuote(
                unitPrice: $volumeUnitPrice,
                tier: 'volume',
                volumeActive: true,
                saleUnit: $saleUnit,
                quantity: $quantity,
                feedbackMessage: $feedbackMessage,
                volumeSummary: $volumeSummary,
                catalogUnitPrice: $catalogUnitPrice,
                standardUnitPrice: $standardUnitPrice,
                volumeUnitPrice: $volumeUnitPrice,
                remainingForVolume: null,
                remainingUnit: null,
            );
        }

        $tier = $this->promotionResolver->isActive($product) ? 'promo' : 'catalog';

        return new ProductPriceQuote(
            unitPrice: $standardUnitPrice,
            tier: $tier,
            volumeActive: false,
            saleUnit: $saleUnit,
            quantity: $quantity,
            feedbackMessage: $feedbackMessage,
            volumeSummary: $volumeSummary,
            catalogUnitPrice: $catalogUnitPrice,
            standardUnitPrice: $standardUnitPrice,
            volumeUnitPrice: $volumeUnitPrice,
            remainingForVolume: $remainingForVolume,
            remainingUnit: $remainingUnit,
        );
    }

    /**
     * Configuración para Alpine / API de compra en tienda.
     *
     * @return array<string, mixed>|null
     */
    public function purchaseConfig(Product $product): ?array
    {
        $offer = $this->activeVolumeOffer($product);
        if ($offer === null) {
            return null;
        }

        $offerUnit = $this->offerUnit($offer);
        $priceInOfferUnit = $this->offerPricing->volumeOfferUnitPrice($offer, $offerUnit);

        return [
            'min_qty' => (float) $offer->volume_min_quantity,
            'min_unit' => $offerUnit->value,
            'volume_price_kg' => $this->volumeUnitPriceInSaleUnit($offer, StockUnit::Kg),
            'volume_price_lb' => $this->volumeUnitPriceInSaleUnit($offer, StockUnit::Lb),
            'volume_price_offer_unit' => $priceInOfferUnit,
            'summary' => $this->offerPricing->volumeStorefrontSummary($offer),
            'lb_per_kg' => VolumeOfferConstraints::KG_TO_LB,
            'kg_per_lb' => VolumeOfferConstraints::LB_TO_KG,
        ];
    }
}
