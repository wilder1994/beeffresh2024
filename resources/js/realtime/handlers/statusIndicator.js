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
        nodes.forEach((node) => {
            const dot = node.querySelector('[data-bf-realtime-dot]');
            const label = node.querySelector('[data-bf-realtime-label]');

            node.dataset.state = status.reconnecting
                ? 'reconnecting'
                : status.connected
                  ? 'connected'
                  : status.echoEnabled
                    ? 'disconnected'
                    : 'fallback';

            if (label) {
                if (status.reconnecting) {
                    label.textContent = 'Reconectando…';
                } else if (status.connected) {
                    label.textContent = 'Conectado en tiempo real';
                } else if (!status.echoEnabled) {
                    label.textContent = 'Sin conexión realtime (modo fallback)';
                } else {
                    label.textContent = 'Sin conexión realtime (modo fallback)';
                }
            }

            if (dot) {
                dot.classList.toggle('bf-realtime-dot--live', status.connected);
                dot.classList.toggle('bf-realtime-dot--warn', status.reconnecting);
                dot.classList.toggle('bf-realtime-dot--off', !status.connected && !status.reconnecting);
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
