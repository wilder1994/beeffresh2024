import { registerInventoryChannels } from './channels/inventory.js';
import { registerCourierChannels } from './channels/couriers.js';
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
import { bfInitNotificationSoundUnlock } from './utils/notificationSound.js';
import { bfInitStockRealtimeHandler } from './handlers/stockHandler.js';
import { bfInitTrackingRealtimeHandler } from './handlers/trackingHandler.js';
import { bfInitCourierLocationHandler } from './handlers/courierLocationHandler.js';
import { bfInitOperationsMapHandler } from './handlers/operationsMapHandler.js';
import { bfInitCourierPresenceHandler } from './handlers/courierPresenceHandler.js';
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
        registerCourierChannels(echo);
        bfRealtimeLog('info', 'Listeners realtime registrados');
    }

    bfInitNotificationRealtimeHandler();
    bfInitNotificationSoundUnlock();
    bfInitMetricsRealtimeHandler();
    bfInitStockRealtimeHandler();
    bfInitAvailabilityRealtimeHandler();
    bfInitRealtimeStatusIndicator();
    bfInitTrackingRealtimeHandler();
    bfInitCourierLocationHandler();
    bfInitOperationsMapHandler();
    bfInitCourierPresenceHandler();
    bfStartRealtimeHealthMonitor();

    return echo;
}

export { getBfEcho } from './echo.js';
export { bfRealtimeStore } from './stores/realtimeStore.js';
export { bfConnectionStore } from './stores/realtimeStore.js';
