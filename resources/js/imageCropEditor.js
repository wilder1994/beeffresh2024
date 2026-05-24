import {
    assignFileToInput,
    computeBaseScale,
    exportCroppedFile,
    loadImageElement,
    paintCrop,
    resolveProfile,
    viewportDimensions,
} from './imageCropCore';

/**
 * Upload con recorte rectangular (catálogo, logo) vía x-bf.image-upload-zone.
 */
export default function registerImageCropEditor(Alpine) {
    Alpine.data('imageCropUpload', (config = {}) => ({
        inputId: config.inputId || 'bf-image-upload',
        profileName: config.profileName || 'catalog',
        profile: resolveProfile(config.profileName || 'catalog', config.profile || {}),
        enableCrop: config.enableCrop !== false,
        initialUrl: config.currentUrl || null,
        preview: config.currentUrl || null,
        fileName: '',
        dragging: false,

        cropOpen: false,
        image: null,
        sourceUrl: null,
        rotation: 0,
        scaleMul: 1,
        offsetX: 0,
        offsetY: 0,
        baseScale: 1,
        viewportW: 320,
        viewportH: 240,
        draggingCrop: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        applying: false,

        cropTitle: config.cropTitle || 'Ajustar imagen',
        cropSubtitle: config.cropSubtitle || 'Arrastra, haz zoom o gira · lo que ves es lo que se publicará',

        init() {
            this.syncViewport();
            this.$el.addEventListener('alpine:destroy', () => this.destroy());
        },

        syncViewport() {
            const dims = viewportDimensions(this.profile, 320);
            this.viewportW = dims.width;
            this.viewportH = dims.height;
        },

        get cropAspectStyle() {
            return `${this.profile.aspectW}/${this.profile.aspectH}`;
        },

        get cropVariant() {
            return this.profile.circular ? 'circle' : 'rect';
        },

        onPick(event) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                return;
            }

            if (this.enableCrop) {
                event.target.value = '';
                this.openCrop(file);
            } else {
                this.applyFile(file);
            }
        },

        onDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer?.files?.[0];
            if (!file || !this.$refs.input) {
                return;
            }

            if (this.enableCrop) {
                this.openCrop(file);
            } else {
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.input.files = dt.files;
                this.applyFile(file);
            }
        },

        async openCrop(file) {
            try {
                this.revokeSource();
                this.image = await loadImageElement(file, this.profile.maxEditPx);
                this.rotation = 0;
                this.scaleMul = 1;
                this.offsetX = 0;
                this.offsetY = 0;
                this.baseScale = computeBaseScale(this.image, this.viewportW, this.viewportH);
                this.cropOpen = true;
                this.$nextTick(() => this.drawViewport());
            } catch {
                window.alert('No se pudo cargar la imagen. Prueba con JPG, PNG o WebP.');
            }
        },

        applyFile(file) {
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.revokePreviewBlob();
                this.preview = event.target?.result ?? this.initialUrl;
            };
            reader.readAsDataURL(file);
        },

        revokeSource() {
            if (this.sourceUrl) {
                URL.revokeObjectURL(this.sourceUrl);
                this.sourceUrl = null;
            }
        },

        revokePreviewBlob() {
            if (this.preview && String(this.preview).startsWith('blob:')) {
                URL.revokeObjectURL(this.preview);
            }
        },

        cropState() {
            return {
                image: this.image,
                rotation: this.rotation,
                scaleMul: this.scaleMul,
                offsetX: this.offsetX,
                offsetY: this.offsetY,
                baseScale: this.baseScale,
                circular: Boolean(this.profile.circular),
                viewportW: this.viewportW,
                viewportH: this.viewportH,
            };
        },

        drawViewport() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.image) {
                return;
            }

            canvas.width = this.viewportW;
            canvas.height = this.viewportH;
            paintCrop(canvas.getContext('2d'), this.viewportW, this.viewportH, this.cropState());
        },

        rotateLeft() {
            this.rotation = (this.rotation - 90 + 360) % 360;
            this.drawViewport();
        },

        rotateRight() {
            this.rotation = (this.rotation + 90) % 360;
            this.drawViewport();
        },

        zoomIn() {
            this.scaleMul = Math.min(3, +(this.scaleMul + 0.12).toFixed(2));
            this.drawViewport();
        },

        zoomOut() {
            this.scaleMul = Math.max(0.5, +(this.scaleMul - 0.12).toFixed(2));
            this.drawViewport();
        },

        onZoomInput(event) {
            this.scaleMul = parseFloat(event.target.value);
            this.drawViewport();
        },

        pointerDown(event) {
            if (!this.cropOpen) {
                return;
            }
            this.draggingCrop = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragOriginX = this.offsetX;
            this.dragOriginY = this.offsetY;
            event.currentTarget.setPointerCapture(event.pointerId);
        },

        pointerMove(event) {
            if (!this.draggingCrop) {
                return;
            }
            this.offsetX = this.dragOriginX + (event.clientX - this.dragStartX);
            this.offsetY = this.dragOriginY + (event.clientY - this.dragStartY);
            this.drawViewport();
        },

        pointerUp(event) {
            if (!this.draggingCrop) {
                return;
            }
            this.draggingCrop = false;
            try {
                event.currentTarget.releasePointerCapture(event.pointerId);
            } catch {
                // ignore
            }
        },

        cancelCrop() {
            this.cropOpen = false;
            this.image = null;
            this.rotation = 0;
            this.scaleMul = 1;
            this.offsetX = 0;
            this.offsetY = 0;
            this.revokeSource();
        },

        async applyCrop() {
            if (!this.image || this.applying) {
                return;
            }

            this.applying = true;

            try {
                const file = await exportCroppedFile(this.cropState(), this.profile, 'image');
                assignFileToInput(this.$refs.input ?? document.getElementById(this.inputId), file);
                this.fileName = file.name;
                this.revokePreviewBlob();
                this.preview = URL.createObjectURL(file);
                this.cropOpen = false;
                this.image = null;
                this.rotation = 0;
                this.scaleMul = 1;
                this.offsetX = 0;
                this.offsetY = 0;
            } catch (error) {
                console.error('applyCrop failed', error);
                window.alert('No se pudo procesar la imagen. Intenta de nuevo.');
            } finally {
                this.applying = false;
            }
        },

        clearPick() {
            this.preview = this.initialUrl;
            this.fileName = '';
            if (this.$refs.input) {
                this.$refs.input.value = '';
            }
        },

        destroy() {
            this.revokeSource();
            this.revokePreviewBlob();
        },
    }));

    Alpine.data('logoCropUpload', (config = {}) => ({
        submitUrl: config.submitUrl,
        csrfToken: config.csrfToken,
        profile: resolveProfile('logo', config.profile || {}),
        uploading: false,

        cropOpen: false,
        image: null,
        rotation: 0,
        scaleMul: 1,
        offsetX: 0,
        offsetY: 0,
        baseScale: 1,
        viewportW: 280,
        viewportH: 280,
        draggingCrop: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        applying: false,

        cropTitle: 'Ajustar logo',
        cropSubtitle: 'Arrastra y amplía · se verá circular en el menú',
        cropVariant: 'rect',
        cropAspectStyle: '1/1',

        init() {
            this.$el.addEventListener('alpine:destroy', () => this.destroy());
        },

        cropState() {
            return {
                image: this.image,
                rotation: this.rotation,
                scaleMul: this.scaleMul,
                offsetX: this.offsetX,
                offsetY: this.offsetY,
                baseScale: this.baseScale,
                circular: false,
                viewportW: this.viewportW,
                viewportH: this.viewportH,
            };
        },

        async pickFile(event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            try {
                this.image = await loadImageElement(file, this.profile.maxEditPx);
                this.rotation = 0;
                this.scaleMul = 1;
                this.offsetX = 0;
                this.offsetY = 0;
                this.baseScale = computeBaseScale(this.image, this.viewportW, this.viewportH);
                this.cropOpen = true;
                this.$nextTick(() => this.drawViewport());
            } catch {
                window.alert('No se pudo cargar la imagen.');
            }
        },

        drawViewport() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.image) {
                return;
            }
            canvas.width = this.viewportW;
            canvas.height = this.viewportH;
            paintCrop(canvas.getContext('2d'), this.viewportW, this.viewportH, this.cropState());
        },

        rotateLeft() {
            this.rotation = (this.rotation - 90 + 360) % 360;
            this.drawViewport();
        },

        rotateRight() {
            this.rotation = (this.rotation + 90) % 360;
            this.drawViewport();
        },

        zoomIn() {
            this.scaleMul = Math.min(3, +(this.scaleMul + 0.12).toFixed(2));
            this.drawViewport();
        },

        zoomOut() {
            this.scaleMul = Math.max(0.5, +(this.scaleMul - 0.12).toFixed(2));
            this.drawViewport();
        },

        onZoomInput(event) {
            this.scaleMul = parseFloat(event.target.value);
            this.drawViewport();
        },

        pointerDown(event) {
            if (!this.cropOpen) {
                return;
            }
            this.draggingCrop = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragOriginX = this.offsetX;
            this.dragOriginY = this.offsetY;
            event.currentTarget.setPointerCapture(event.pointerId);
        },

        pointerMove(event) {
            if (!this.draggingCrop) {
                return;
            }
            this.offsetX = this.dragOriginX + (event.clientX - this.dragStartX);
            this.offsetY = this.dragOriginY + (event.clientY - this.dragStartY);
            this.drawViewport();
        },

        pointerUp(event) {
            if (!this.draggingCrop) {
                return;
            }
            this.draggingCrop = false;
            try {
                event.currentTarget.releasePointerCapture(event.pointerId);
            } catch {
                // ignore
            }
        },

        cancelCrop() {
            this.cropOpen = false;
            this.image = null;
        },

        async applyCrop() {
            if (!this.image || this.applying || !this.submitUrl) {
                return;
            }

            this.applying = true;

            try {
                const file = await exportCroppedFile(this.cropState(), this.profile, 'logo');
                const body = new FormData();
                body.append('_token', this.csrfToken);
                body.append('imagen', file);

                const response = await fetch(this.submitUrl, {
                    method: 'POST',
                    body,
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('upload failed');
                }

                window.location.reload();
            } catch {
                window.alert('No se pudo guardar el logo. Intenta de nuevo.');
            } finally {
                this.applying = false;
                this.cancelCrop();
            }
        },

        destroy() {
            this.image = null;
        },
    }));
}
