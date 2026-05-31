const DASHBOARD_LOW_STOCK_MAX_ROWS = 12;

/**
 * @param {object} stock
 */
export function bfPatchInventoryRow(stock) {
    const productId = stock?.product_id;
    if (!productId) {
        return;
    }

    const row = document.querySelector(`[data-inventory-product-id="${productId}"]`);
    if (!row) {
        return;
    }

    const stockInput = row.querySelector('[data-inventory-stock-input]');
    if (stockInput && typeof stock.stock === 'number') {
        stockInput.value = String(stock.stock);
    }

    row.classList.toggle('bg-red-50/60', Boolean(stock.is_low_stock));
    row.dataset.inventoryLowStock = stock.is_low_stock ? '1' : '0';
    row.dataset.inventoryOutOfStock = stock.is_out_of_stock ? '1' : '0';

    bfSyncDashboardLowStock(stock);
}

/**
 * @param {object} stock
 */
function bfSyncDashboardLowStock(stock) {
    const productId = stock?.product_id;
    if (!productId) {
        return;
    }

    if (stock.is_low_stock) {
        bfEnsureDashboardLowStockRow(stock);
        bfUpdateLowStockCount();
        return;
    }

    bfRemoveDashboardLowStockRow(productId);
    bfUpdateLowStockCount();
}

/**
 * @param {object} stock
 */
function bfEnsureDashboardLowStockRow(stock) {
    const tbody = document.querySelector('[data-dashboard-low-stock-body]');
    if (!tbody) {
        return;
    }

    const existing = document.querySelector(`[data-dashboard-low-stock-product-id="${stock.product_id}"]`);
    if (existing) {
        bfPatchDashboardLowStockRow(stock);
        return;
    }

    const template = document.getElementById('bf-low-stock-row-tpl');
    if (!(template instanceof HTMLTemplateElement)) {
        return;
    }

    const rows = tbody.querySelectorAll('[data-dashboard-low-stock-product-id]');
    if (rows.length >= DASHBOARD_LOW_STOCK_MAX_ROWS) {
        return;
    }

    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    if (!row) {
        return;
    }

    row.dataset.dashboardLowStockProductId = String(stock.product_id);
    const nameCell = row.querySelector('[data-dashboard-low-stock-name]');
    const valueCell = row.querySelector('[data-dashboard-low-stock-value]');

    if (nameCell && stock.product_name) {
        nameCell.textContent = stock.product_name;
    }

    if (valueCell && typeof stock.stock === 'number') {
        valueCell.textContent = stock.stock.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
        valueCell.classList.toggle('text-red-600', Boolean(stock.is_low_stock));
        valueCell.classList.toggle('text-amber-700', !stock.is_low_stock);
    }

    const emptyRow = tbody.querySelector('[data-dashboard-low-stock-empty]');
    emptyRow?.remove();

    tbody.prepend(row);
}

/**
 * @param {number} productId
 */
function bfRemoveDashboardLowStockRow(productId) {
    const row = document.querySelector(`[data-dashboard-low-stock-product-id="${productId}"]`);
    if (!row) {
        return;
    }

    row.classList.add('opacity-0');
    window.setTimeout(() => row.remove(), 200);
}

/**
 * @param {object} stock
 */
export function bfPatchDashboardLowStockRow(stock) {
    const productId = stock?.product_id;
    if (!productId) {
        return;
    }

    const row = document.querySelector(`[data-dashboard-low-stock-product-id="${productId}"]`);
    if (!row) {
        return;
    }

    const cell = row.querySelector('[data-dashboard-low-stock-value]');
    if (cell && typeof stock.stock === 'number') {
        cell.textContent = stock.stock.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
        cell.classList.toggle('text-red-600', Boolean(stock.is_low_stock));
        cell.classList.toggle('text-amber-700', !stock.is_low_stock);
    }
}

function bfUpdateLowStockCount() {
    const tbody = document.querySelector('[data-dashboard-low-stock-body]');
    const counter = document.querySelector('[data-dashboard-low-stock-count]');
    if (!tbody || !counter) {
        return;
    }

    const count = tbody.querySelectorAll('[data-dashboard-low-stock-product-id]').length;
    counter.textContent = String(count);
}

/**
 * Actualiza la fila del catálogo admin (Catálogo › Productos) en tiempo real.
 * @param {object} stock
 */
export function bfPatchCatalogStockRow(stock) {
    const productId = stock?.product_id;
    if (!productId) {
        return;
    }

    const row = document.querySelector(`[data-catalog-product-id="${productId}"]`);
    if (!row) {
        return;
    }

    const cell = row.querySelector('[data-catalog-stock-cell]');
    const valueEl = row.querySelector('[data-catalog-stock-value]');

    if (valueEl && typeof stock.stock === 'number') {
        valueEl.textContent = stock.stock.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    }

    cell?.classList.toggle('text-red-700', Boolean(stock.is_low_stock) || Boolean(stock.is_out_of_stock));
    cell?.classList.toggle('font-semibold', Boolean(stock.is_low_stock) || Boolean(stock.is_out_of_stock));

    const outLabel = row.querySelector('[data-catalog-out-label]');
    outLabel?.classList.toggle('hidden', !stock.is_out_of_stock);
}

/**
 * @param {object} availability
 */
export function bfPatchStoreProductAvailability(availability) {
    const productId = availability?.product_id;
    if (!productId) {
        return;
    }

    document.querySelectorAll(`[data-store-product-id="${productId}"]`).forEach((root) => {
        const labelEl = root.querySelector('[data-store-availability-label]');
        if (labelEl && availability.availability_label) {
            labelEl.textContent = availability.availability_label;
            labelEl.classList.toggle('hidden', false);
        }

        root.classList.toggle('bf-store-product--out', Boolean(availability.is_out_of_stock));
        root.classList.toggle('bf-store-product--low', Boolean(availability.is_low_stock));

        const cartBtn = root.querySelector('.agregar-carrito');
        if (cartBtn instanceof HTMLButtonElement) {
            cartBtn.disabled = !availability.can_purchase;
            cartBtn.classList.toggle('opacity-50', !availability.can_purchase);
            cartBtn.classList.toggle('cursor-not-allowed', !availability.can_purchase);
        }

        const unavailableMsg = root.querySelector('[data-store-unavailable-msg]');
        if (unavailableMsg) {
            unavailableMsg.classList.toggle('hidden', Boolean(availability.can_purchase));
            if (!availability.can_purchase && availability.availability_label) {
                unavailableMsg.textContent = availability.availability_label;
            }
        }

        if (!availability.can_purchase) {
            root.querySelectorAll('[data-product-purchase] input, [data-product-purchase] button').forEach((el) => {
                if (el instanceof HTMLInputElement || el instanceof HTMLButtonElement) {
                    el.disabled = true;
                }
            });
        }
    });
}
