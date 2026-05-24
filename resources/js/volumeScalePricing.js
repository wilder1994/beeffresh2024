/**
 * Espejo client-side de VolumeScaleService (conversiones 1 kg = 2 lb).
 */
export const KG_TO_LB = 2;
export const LB_TO_KG = 0.5;

export function quantityInUnit(quantity, fromUnit, toUnit) {
    if (fromUnit === toUnit) {
        return quantity;
    }
    if (fromUnit === 'lb' && toUnit === 'kg') {
        return quantity * LB_TO_KG;
    }
    if (fromUnit === 'kg' && toUnit === 'lb') {
        return quantity * KG_TO_LB;
    }
    return quantity;
}

export function formatQuantityDisplay(value) {
    const n = Number(value);
    if (!Number.isFinite(n) || n <= 0) {
        return '0';
    }
    if (Math.abs(n - Math.round(n)) < 0.001) {
        return String(Math.round(n));
    }
    return n.toFixed(1).replace('.', ',').replace(/,?0+$/, '');
}

export function formatMoney(value) {
    return Math.round(Number(value)).toLocaleString('es-CO');
}

/**
 * @param {object} config - purchaseConfig from server
 * @param {'kg'|'lb'} saleUnit
 * @param {number} quantity
 * @param {object} prices - { catalogKg, catalogLb, standardKg, standardLb, onPromo }
 */
export function computePurchaseQuote(config, saleUnit, quantity, prices) {
    const qty = Math.max(1, Number(quantity) || 1);
    const unit = saleUnit === 'lb' ? 'lb' : 'kg';
    const catalogUnitPrice = unit === 'kg' ? Number(prices.catalogKg) : Number(prices.catalogLb);
    const standardUnitPrice = unit === 'kg' ? Number(prices.standardKg) : Number(prices.standardLb);

    if (!config) {
        const tier = prices.onPromo ? 'promo' : 'catalog';
        return {
            unitPrice: standardUnitPrice,
            tier,
            volumeActive: false,
            feedbackMessage: null,
            volumeSummary: null,
            catalogUnitPrice,
            standardUnitPrice,
            volumeUnitPrice: null,
            pricingLabel: tier === 'promo' ? 'Promoción activa' : 'Precio de catálogo',
            showStrikethrough: prices.onPromo || false,
            strikethroughPrice: catalogUnitPrice,
        };
    }

    const minUnit = config.min_unit === 'lb' ? 'lb' : 'kg';
    const minQty = Number(config.min_qty) || 0;
    const volumeUnitPrice = unit === 'kg' ? Number(config.volume_price_kg) : Number(config.volume_price_lb);
    const qtyInMinUnit = quantityInUnit(qty, unit, minUnit);
    const remainingInMinUnit = Math.max(0, minQty - qtyInMinUnit);

    if (remainingInMinUnit > 0.0001) {
        const remainingInSaleUnit = quantityInUnit(remainingInMinUnit, minUnit, unit);
        const tier = prices.onPromo ? 'promo' : 'catalog';
        return {
            unitPrice: standardUnitPrice,
            tier,
            volumeActive: false,
            feedbackMessage: `Te faltan ${formatQuantityDisplay(remainingInSaleUnit)} ${unit} para activar el precio por volumen.`,
            volumeSummary: config.summary ?? null,
            catalogUnitPrice,
            standardUnitPrice,
            volumeUnitPrice,
            pricingLabel: tier === 'promo' ? 'Promoción activa' : 'Precio de catálogo',
            showStrikethrough: prices.onPromo || false,
            strikethroughPrice: catalogUnitPrice,
        };
    }

    return {
        unitPrice: volumeUnitPrice,
        tier: 'volume',
        volumeActive: true,
        feedbackMessage: 'Oferta por volumen aplicada',
        volumeSummary: config.summary ?? null,
        catalogUnitPrice,
        standardUnitPrice,
        volumeUnitPrice,
        pricingLabel: 'Oferta por volumen aplicada',
        showStrikethrough: true,
        strikethroughPrice: standardUnitPrice,
    };
}

export function registerProductPurchaseAlpine(Alpine) {
    Alpine.data('productPurchase', (initial) => ({
        unit: initial.defaultUnit ?? 'kg',
        qty: initial.defaultQty ?? 1,
        volumeConfig: initial.volumeConfig ?? null,
        catalogKg: Number(initial.catalogKg),
        catalogLb: Number(initial.catalogLb),
        standardKg: Number(initial.standardKg),
        standardLb: Number(initial.standardLb),
        onPromo: Boolean(initial.onPromo),

        get unitLabel() {
            return this.unit === 'kg' ? 'kg' : 'lb';
        },

        get quote() {
            return computePurchaseQuote(
                this.volumeConfig,
                this.unit,
                this.qty,
                {
                    catalogKg: this.catalogKg,
                    catalogLb: this.catalogLb,
                    standardKg: this.standardKg,
                    standardLb: this.standardLb,
                    onPromo: this.onPromo,
                },
            );
        },

        formatMoney(value) {
            return formatMoney(value);
        },
    }));
}
