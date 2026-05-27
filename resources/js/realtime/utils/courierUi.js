/**
 * Parches UI de presencia domiciliario (mapa / listas).
 */

/**
 * @param {Record<string, unknown>} presence
 */
export function bfPatchCourierPresence(presence) {
    const courierId = presence.courier_id;
    if (courierId == null) {
        return;
    }

    const row = document.querySelector(`[data-courier-presence-id="${courierId}"]`);
    if (!row) {
        return;
    }

    const statusEl = row.querySelector('[data-courier-presence-status]');
    const seenEl = row.querySelector('[data-courier-presence-seen]');

    if (statusEl) {
        const online = presence.online !== false;
        const available = presence.available === true;
        let label = 'Desconectado';

        if (online && available) {
            label = 'Disponible';
        } else if (online) {
            label = 'Ocupado';
        }

        statusEl.textContent = label;
    }

    if (seenEl && presence.last_seen_at) {
        seenEl.textContent = new Date(String(presence.last_seen_at)).toLocaleString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'America/Bogota',
        });
    }
}
