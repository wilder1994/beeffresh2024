import { registerInventoryChannels } from './channels/inventory.js';
import { registerMapsChannels } from './channels/maps.js';
import { registerNotificationChannel } from './channels/notifications.js';
import { registerOperationsChannels } from './channels/operations.js';
import { registerPaymentChannels } from './channels/payments.js';
import { registerStoreCatalogChannel } from './channels/storeCatalog.js';
import { registerTrackingChannels } from './channels/tracking.js';
import { bfInitAvailabilityRealtimeHandler } from './handlers/availabilityHandler.js';
import { bfInitMetricsRealtimeHandler } from './handlers/metricsHandler.js';
import { bfInitNotificationRealtimeHandler } from './handlers/notificationsHandler.js';
import { bfInitRealtimeStatusIndicator } from './handlers/statusIndicator.js';
import { bfInitStockRealtimeHandler } from './handlers/stockHandler.js';
import { initBfEcho } from './echo.js';
import { bfStartRealtimeHealthMonitor } from './healthMonitor.js';
import { bfRealtimeLog } from './utils/logger.js';

/**
 * Bootstrap BF-Realtime (Fase 1.5).
 * @returns {import('laravel-echo').Echo|null}
 */
export function bootstrapBfRealtime() {
    const echo = initBfEcho();

    if (echo) {
        registerNotificationChannel(echo);
        registerOperationsChannels(echo);
        registerInventoryChannels(echo);
        registerStoreCatalogChannel(echo);
        registerTrackingChannels(echo);
        registerPaymentChannels(echo);
        registerMapsChannels(echo);
        bfRealtimeLog('info', 'Listeners realtime registrados');
    }

    bfInitNotificationRealtimeHandler();
    bfInitMetricsRealtimeHandler();
    bfInitStockRealtimeHandler();
    bfInitAvailabilityRealtimeHandler();
    bfInitRealtimeStatusIndicator();
    bfStartRealtimeHealthMonitor();

    return echo;
}

export { getBfEcho } from './echo.js';
export { bfRealtimeStore } from './stores/realtimeStore.js';
export { bfConnectionStore } from './stores/realtimeStore.js';
