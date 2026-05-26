const DEBUG = import.meta.env.DEV || import.meta.env.VITE_BF_REALTIME_DEBUG === 'true';

/**
 * @param {'debug'|'info'|'warn'|'error'} level
 * @param {string} message
 * @param {unknown} [detail]
 */
export function bfRealtimeLog(level, message, detail = undefined) {
    if (!DEBUG && level === 'debug') {
        return;
    }

    const prefix = '[BF-Realtime]';
    const payload = detail === undefined ? message : [message, detail];

    switch (level) {
        case 'error':
            console.error(prefix, payload);
            break;
        case 'warn':
            console.warn(prefix, payload);
            break;
        case 'debug':
            console.debug(prefix, payload);
            break;
        default:
            console.info(prefix, payload);
    }
}
