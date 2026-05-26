/** @type {string} */
export const BF_NOTIFICATION_SOUND_MUTE_KEY = 'bf:notifications:sound-muted';

const SOUND_URL = '/sounds/notification.wav';

/** @type {HTMLAudioElement|null} */
let htmlAudio = null;
/** @type {AudioContext|null} */
let audioContext = null;
/** @type {boolean} */
let soundUnlocked = false;
/** @type {boolean} */
let unlockListenersBound = false;

/**
 * @param {number|string} id
 * @returns {number|string}
 */
export function bfNormalizeNotificationId(id) {
    const numeric = Number(id);

    return Number.isFinite(numeric) && numeric > 0 ? numeric : id;
}

/**
 * @returns {boolean}
 */
export function bfIsNotificationSoundMuted() {
    try {
        return localStorage.getItem(BF_NOTIFICATION_SOUND_MUTE_KEY) === '1';
    } catch {
        return false;
    }
}

/**
 * @param {boolean} muted
 */
export function bfSetNotificationSoundMuted(muted) {
    try {
        if (muted) {
            localStorage.setItem(BF_NOTIFICATION_SOUND_MUTE_KEY, '1');
        } else {
            localStorage.removeItem(BF_NOTIFICATION_SOUND_MUTE_KEY);
        }
    } catch {
        // storage bloqueado
    }

    window.dispatchEvent(
        new CustomEvent('bf:notification-sound-muted-changed', {
            detail: { muted },
            bubbles: true,
        }),
    );
}

/**
 * @returns {boolean}
 */
export function bfToggleNotificationSoundMuted() {
    const next = !bfIsNotificationSoundMuted();
    bfSetNotificationSoundMuted(next);

    return next;
}

/**
 * @returns {AudioContext}
 */
function bfGetAudioContext() {
    if (!audioContext) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        audioContext = new Ctx();
    }

    return audioContext;
}

/**
 * @returns {HTMLAudioElement}
 */
function bfGetHtmlAudio() {
    if (!htmlAudio) {
        htmlAudio = new Audio(SOUND_URL);
        htmlAudio.preload = 'auto';
        htmlAudio.volume = 1;
    }

    return htmlAudio;
}

/** Parciales de campana (misma base que scripts/generate-notification-sound.php). */
const BF_BELL_FUNDAMENTAL = 830.61;
const BF_BELL_DURATION_SEC = 0.55;
const BF_BELL_PARTIALS = [
    { ratio: 1.0, amp: 0.55 },
    { ratio: 2.0, amp: 0.35 },
    { ratio: 2.41, amp: 0.28 },
    { ratio: 3.04, amp: 0.2 },
    { ratio: 4.16, amp: 0.12 },
    { ratio: 5.43, amp: 0.07 },
];

/**
 * Campana sintética vía Web Audio (respaldo si falla el .wav).
 */
function bfPlayWebAudioBell() {
    const ctx = bfGetAudioContext();

    const play = () => {
        const start = ctx.currentTime;
        const master = ctx.createGain();
        master.connect(ctx.destination);
        master.gain.setValueAtTime(0.0001, start);
        master.gain.exponentialRampToValueAtTime(0.42, start + 0.01);
        master.gain.exponentialRampToValueAtTime(0.0001, start + BF_BELL_DURATION_SEC);

        BF_BELL_PARTIALS.forEach(({ ratio, amp }) => {
            const osc = ctx.createOscillator();
            const partialGain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = BF_BELL_FUNDAMENTAL * ratio;
            partialGain.gain.value = amp;
            osc.connect(partialGain);
            partialGain.connect(master);
            osc.start(start);
            osc.stop(start + BF_BELL_DURATION_SEC + 0.05);
        });
    };

    if (ctx.state === 'suspended') {
        return ctx.resume().then(play);
    }

    play();

    return Promise.resolve();
}

/**
 * Desbloquea audio tras interacción (autoplay policy).
 * @returns {Promise<void>}
 */
export function bfUnlockNotificationSound() {
    if (soundUnlocked) {
        return Promise.resolve();
    }

    const ctx = bfGetAudioContext();
    const audio = bfGetHtmlAudio();

    const unlockHtml = audio
        .play()
        .then(() => {
            audio.pause();
            audio.currentTime = 0;
            audio.volume = 1;
        })
        .catch(() => undefined);

    return Promise.all([ctx.resume(), unlockHtml])
        .then(() => {
            soundUnlocked = true;
        })
        .catch(() => undefined);
}

/**
 * Reproduce el tono (volumen del sistema; HTMLAudio a volumen 1).
 * @returns {Promise<void>}
 */
export function bfPlayNotificationSound() {
    if (bfIsNotificationSoundMuted()) {
        return Promise.resolve();
    }

    const audio = bfGetHtmlAudio();

    const playHtml = () => {
        audio.currentTime = 0;
        audio.volume = 1;

        return audio.play();
    };

    return playHtml()
        .catch(() => bfPlayWebAudioBell())
        .catch(() => {
            if (!soundUnlocked) {
                return undefined;
            }

            return bfPlayWebAudioBell();
        });
}

/**
 * @param {HTMLElement} root
 */
export function bfBindNotificationSoundToggle(root) {
    const control = root.querySelector('[data-notification-sound-toggle]');
    if (!control || control.dataset.bound === '1') {
        return;
    }

    control.dataset.bound = '1';

    const sync = () => {
        const muted = bfIsNotificationSoundMuted();
        control.setAttribute('aria-pressed', muted ? 'true' : 'false');
        if (control instanceof HTMLInputElement && control.type === 'checkbox') {
            control.checked = !muted;
        }
        const label = root.querySelector('[data-notification-sound-label]');
        if (label) {
            label.textContent = muted ? 'Sonido silenciado' : 'Sonido activo';
        }
    };

    if (control instanceof HTMLInputElement && control.type === 'checkbox') {
        control.addEventListener('change', () => {
            bfSetNotificationSoundMuted(!control.checked);
            if (control.checked) {
                bfUnlockNotificationSound();
            }
            sync();
        });
    } else {
        control.addEventListener('click', (event) => {
            event.preventDefault();
            bfToggleNotificationSoundMuted();
            sync();
        });
    }

    window.addEventListener('storage', (event) => {
        if (event.key === BF_NOTIFICATION_SOUND_MUTE_KEY) {
            sync();
        }
    });

    window.addEventListener('bf:notification-sound-muted-changed', sync);
    sync();
}

/**
 * @param {ParentNode} [scope]
 */
export function bfInitNotificationSoundToggles(scope = document) {
    scope.querySelectorAll('[data-notification-bell]').forEach((root) => {
        if (root instanceof HTMLElement) {
            bfBindNotificationSoundToggle(root);
            const summary = root.querySelector('.bf-notification-bell');
            summary?.addEventListener('click', () => {
                bfUnlockNotificationSound();
            });
        }
    });

    scope.querySelectorAll('[data-notification-sound-prefs]').forEach((root) => {
        if (root instanceof HTMLElement) {
            bfBindNotificationSoundToggle(root);
        }
    });
}

/**
 * Desbloquea reproducción tras la primera interacción.
 */
export function bfInitNotificationSoundUnlock() {
    if (unlockListenersBound) {
        return;
    }

    unlockListenersBound = true;

    const onGesture = () => {
        bfUnlockNotificationSound();
    };

    document.addEventListener('pointerdown', onGesture, { capture: true, passive: true });
    document.addEventListener('keydown', onGesture, { capture: true, passive: true });
}
