/**
 * Parches DOM de seguimiento de pedido (cliente / staff).
 */

/**
 * @param {string} iso
 */
export function bfFormatTrackingDate(iso) {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleString('es-CO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'America/Bogota',
    });
}

/**
 * @param {Array<Record<string, unknown>>} entries
 * @param {HTMLElement|null} timeline
 */
export function bfPatchTrackingTimeline(entries, timeline) {
    if (!timeline || !Array.isArray(entries)) {
        return;
    }

    timeline.innerHTML = entries
        .map((entry) => {
            const isUpcoming = entry.state === 'upcoming';
            const label = entry.label ?? entry.to_status_label ?? entry.to_status ?? '';
            const dateMarkup = isUpcoming
                ? '<p class="text-xs text-[var(--bf-muted)]">Pendiente</p>'
                : entry.created_at
                  ? `<p class="text-xs text-[var(--bf-muted)]">${bfFormatTrackingDate(entry.created_at)}</p>`
                  : '';

            return `
                <li class="bf-ops-timeline__item${isUpcoming ? ' bf-ops-timeline__item--upcoming' : ''}">
                    <span class="bf-ops-timeline__dot"></span>
                    <div>
                        <p class="font-medium text-sm${isUpcoming ? ' text-[var(--bf-muted)]' : ''}">${label}</p>
                        ${dateMarkup}
                    </div>
                </li>`;
        })
        .join('');
}

/**
 * @param {Record<string, unknown>} tracking
 */
export function bfPatchTrackingPage(tracking) {
    const statusLabel = document.getElementById('tracking-status-label');
    const timeline = document.getElementById('tracking-timeline');
    const courierEl = document.getElementById('tracking-courier-name');
    const etaEl = document.getElementById('tracking-eta');

    if (statusLabel && tracking.status_label) {
        statusLabel.textContent = String(tracking.status_label);
    }

    if (Array.isArray(tracking.timeline)) {
        bfPatchTrackingTimeline(tracking.timeline, timeline);
    }

    if (courierEl && tracking.courier) {
        courierEl.textContent = tracking.courier.name ?? '—';
    }

    if (etaEl && tracking.eta) {
        etaEl.textContent = String(tracking.eta);
    }

    window.dispatchEvent(new CustomEvent('bf:tracking-map-patch', {
        detail: { location: tracking.courier_location ?? null },
        bubbles: true,
    }));
}

/**
 * @param {Record<string, unknown>} tracking
 */
export function bfPatchAdminOrderTracking(tracking) {
    const timeline = document.getElementById('admin-order-timeline');
    const courierBlock = document.getElementById('admin-order-courier');
    const badge = document.querySelector('[data-admin-order-status]');

    if (badge && tracking.status_label) {
        badge.textContent = String(tracking.status_label);
    }

    if (Array.isArray(tracking.timeline) && timeline) {
        bfPatchTrackingTimeline(tracking.timeline, timeline);
    }

    if (courierBlock && tracking.courier?.name) {
        courierBlock.textContent = String(tracking.courier.name);
    }
}
