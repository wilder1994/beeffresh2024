/**
 * Lógica compartida de recorte (canvas): catálogo, logo y avatares.
 */

export const DEFAULT_PROFILES = {
    catalog: {
        aspectW: 4,
        aspectH: 3,
        outputW: 1200,
        outputH: 900,
        quality: 0.85,
        mime: 'image/jpeg',
        ext: 'jpg',
        maxEditPx: 2048,
        circular: false,
    },
    logo: {
        aspectW: 1,
        aspectH: 1,
        outputW: 512,
        outputH: 512,
        quality: 0.88,
        mime: 'image/jpeg',
        ext: 'jpg',
        maxEditPx: 2048,
        circular: false,
    },
    avatar: {
        aspectW: 1,
        aspectH: 1,
        outputW: 512,
        outputH: 512,
        quality: 0.9,
        mime: 'image/jpeg',
        ext: 'jpg',
        maxEditPx: 2048,
        circular: true,
    },
};

export function resolveProfile(name, overrides = {}) {
    const base = DEFAULT_PROFILES[name] ?? DEFAULT_PROFILES.catalog;

    return { ...base, ...overrides };
}

export function viewportDimensions(profile, maxWidth = 320) {
    const ratio = profile.aspectW / profile.aspectH;
    const width = maxWidth;
    const height = Math.round(width / ratio);

    return { width, height };
}

export function computeBaseScale(image, viewportW, viewportH) {
    return Math.max(viewportW / image.width, viewportH / image.height) * 1.05;
}

export function paintCrop(ctx, width, height, state) {
    const {
        image,
        rotation,
        scaleMul,
        offsetX,
        offsetY,
        baseScale,
        circular,
    } = state;

    if (!image || !ctx) {
        return;
    }

    const ratio = width / state.viewportW;

    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#f5f5f4';
    ctx.fillRect(0, 0, width, height);
    ctx.save();

    if (circular) {
        ctx.beginPath();
        ctx.arc(width / 2, height / 2, width / 2, 0, Math.PI * 2);
        ctx.clip();
    } else {
        ctx.beginPath();
        ctx.rect(0, 0, width, height);
        ctx.clip();
    }

    ctx.fillStyle = '#f5f5f4';
    ctx.fillRect(0, 0, width, height);
    ctx.translate(width / 2 + offsetX * ratio, height / 2 + offsetY * ratio);
    ctx.rotate((rotation * Math.PI) / 180);
    const scale = baseScale * scaleMul * ratio;
    ctx.scale(scale, scale);
    ctx.drawImage(image, -image.width / 2, -image.height / 2);
    ctx.restore();

    if (!circular) {
        ctx.save();
        ctx.strokeStyle = 'rgba(255,255,255,0.9)';
        ctx.lineWidth = Math.max(2, width * 0.006);
        ctx.strokeRect(1, 1, width - 2, height - 2);
        ctx.restore();
    }
}

export async function loadImageElement(file, maxEditPx) {
    const bitmap = await createImageBitmap(file);
    let { width, height } = bitmap;
    const longest = Math.max(width, height);

    if (longest > maxEditPx) {
        const scale = maxEditPx / longest;
        width = Math.round(width * scale);
        height = Math.round(height * scale);
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(bitmap, 0, 0, width, height);
        bitmap.close?.();

        return loadFromCanvas(canvas);
    }

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(bitmap, 0, 0);
    bitmap.close?.();

    return loadFromCanvas(canvas);
}

function loadFromCanvas(canvas) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error('image load failed'));
        img.src = canvas.toDataURL('image/jpeg', 0.92);
    });
}

export async function exportCroppedFile(state, profile, filenameBase = 'image') {
    const canvas = document.createElement('canvas');
    canvas.width = profile.outputW;
    canvas.height = profile.outputH;

    paintCrop(canvas.getContext('2d'), profile.outputW, profile.outputH, {
        ...state,
        viewportW: state.viewportW,
        viewportH: state.viewportH,
    });

    const blob = await new Promise((resolve) => {
        canvas.toBlob((b) => resolve(b), profile.mime, profile.quality);
    });

    if (!blob) {
        throw new Error('export failed');
    }

    const name = `${filenameBase}.${profile.ext}`;

    return new File([blob], name, { type: profile.mime });
}

export function assignFileToInput(inputOrId, file, { notify = false } = {}) {
    const input = typeof inputOrId === 'string' ? document.getElementById(inputOrId) : inputOrId;
    if (!input) {
        return false;
    }

    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;

    if (notify) {
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    return true;
}
