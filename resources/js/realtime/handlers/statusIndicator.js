import { bfRealtimeStore } from '../stores/realtimeStore.js';

/** @type {(() => void)|null} */
let boundStatusHandler = null;

export function bfInitRealtimeStatusIndicator() {
    const nodes = document.querySelectorAll('[data-bf-realtime-status]');
    if (nodes.length === 0) {
        return;
    }

    const render = () => {
        const status = bfRealtimeStore.getStatus();
        const mode = status.mode ?? 'fallback';

        nodes.forEach((node) => {
            const dot = node.querySelector('[data-bf-realtime-dot]');
            const label = node.querySelector('[data-bf-realtime-label]');

            node.dataset.state = mode;

            if (label) {
                if (status.reconnecting) {
                    label.textContent = 'Reconectando…';
                } else if (mode === 'live') {
                    label.textContent = 'Operación en tiempo real';
                } else if (mode === 'degraded') {
                    label.textContent = 'Sincronización diferida (cola ocupada)';
                } else if (!status.echoEnabled) {
                    label.textContent = 'Modo respaldo (polling)';
                } else {
                    label.textContent = 'Modo respaldo (polling)';
                }
            }

            if (dot) {
                dot.classList.toggle('bf-realtime-dot--live', mode === 'live');
                dot.classList.toggle('bf-realtime-dot--warn', mode === 'degraded' || status.reconnecting);
                dot.classList.toggle('bf-realtime-dot--off', mode === 'fallback');
            }
        });
    };

    if (boundStatusHandler) {
        render();
        return;
    }

    boundStatusHandler = render;
    ['bf:realtime-connected', 'bf:realtime-disconnected', 'bf:realtime-reconnecting', 'bf:realtime-status'].forEach(
        (eventName) => window.addEventListener(eventName, render),
    );

    render();
}
