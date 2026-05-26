import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {import('laravel-echo').Echo} echo
 */
export function registerNotificationChannel(echo) {
    const userId = bfMetaContent('bf-user-id');

    if (!userId) {
        return;
    }

    echo.private(`App.Models.User.${userId}`).listen('.notification.created', (payload) => {
        bfRealtimeStore.recordEvent('notification', payload);
        bfRealtimeLog('info', 'Notification received', payload?.notification?.title);
        bfDispatchRealtimeEvent('bf:notification-created', payload);
    });
}
