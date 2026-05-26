/**
 * @returns {string|null}
 */
export function bfMetaContent(name) {
    return document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') ?? null;
}

/**
 * @param {string} selector
 * @returns {string|null}
 */
export function bfDataAttr(selector, attr) {
    return document.querySelector(selector)?.getAttribute(attr) ?? null;
}

/**
 * @param {string} eventName
 * @param {unknown} detail
 */
export function bfDispatchRealtimeEvent(eventName, detail) {
    window.dispatchEvent(new CustomEvent(eventName, { detail, bubbles: true }));
}
