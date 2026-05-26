/**
 * @param {object} metrics
 */
export function bfPatchOperationsMetrics(metrics) {
    if (!metrics || typeof metrics !== 'object') {
        return;
    }

    const map = {
        pending: metrics.pending,
        preparing: metrics.preparing,
        ready: metrics.ready,
        in_delivery: metrics.in_transit,
        delivered: metrics.delivered_today,
        failed: metrics.failed,
    };

    Object.entries(map).forEach(([key, value]) => {
        if (typeof value !== 'number') {
            return;
        }

        document.querySelectorAll(`[data-ops-metric][data-metric-key="${key}"] [data-ops-metric-value]`).forEach((el) => {
            el.textContent = String(value);
        });
    });

    const couriersLine = document.querySelector('[data-ops-couriers-line]');
    if (couriersLine) {
        const available = metrics.available_couriers;
        const active = metrics.active_couriers;
        const revenue = metrics.revenue_today;

        if (typeof available === 'number' && typeof active === 'number') {
            const freeEl = couriersLine.querySelector('[data-ops-available-couriers]');
            const busyEl = couriersLine.querySelector('[data-ops-active-couriers]');
            if (freeEl) {
                freeEl.textContent = String(available);
            }
            if (busyEl) {
                busyEl.textContent = String(active);
            }
        }

        if (typeof revenue === 'number') {
            const revenueEl = couriersLine.querySelector('[data-ops-revenue-today]');
            if (revenueEl) {
                revenueEl.textContent = `$${Math.round(revenue).toLocaleString('es-CO')}`;
            }
        }
    }

    bfPatchDashboardMetrics(metrics);
}

/**
 * @param {object} metrics
 */
function bfPatchDashboardMetrics(metrics) {
    const dashboardMap = {
        pending: metrics.pending,
        orders_today: null,
    };

    if (typeof metrics.pending === 'number') {
        document
            .querySelectorAll('[data-dashboard-metric][data-metric-key="pending"] [data-dashboard-metric-value]')
            .forEach((el) => {
                el.textContent = String(metrics.pending);
            });
    }

    if (typeof metrics.low_stock_count === 'number') {
        document.querySelectorAll('[data-dashboard-low-stock-count]').forEach((el) => {
            el.textContent = String(metrics.low_stock_count);
        });
    }
}
