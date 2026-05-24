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
 * Avatar: previsualización + modal de recorte circular (rotar, zoom, arrastrar).
 */
export default function registerAvatarEditor(Alpine) {
    Alpine.data('avatarEditor', (config = {}) => ({
        preview: config.preview || null,
        initial: config.initial || '?',
        inputId: config.inputId || 'profile-avatar-input',
        useLivewire: Boolean(config.useLivewire),
        profile: resolveProfile('avatar', config.profile || {}),

        cropOpen: false,
        sourceUrl: null,
        image: null,
        rotation: 0,
        scaleMul: 1,
        offsetX: 0,
        offsetY: 0,
        baseScale: 1,
        viewportW: 280,
        viewportH: 280,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        applying: false,

        cropTitle: 'Ajustar foto',
        cropSubtitle: 'Arrastra para centrar · Gira o amplía dentro del círculo',
        cropVariant: 'circle',
        cropAspectStyle: '1/1',

        init() {
            this.$el.addEventListener('alpine:destroy', () => this.destroy());
        },

        async pickFile(event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

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
                window.alert('No se pudo cargar la imagen seleccionada.');
            }
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
                circular: true,
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
            this.dragging = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragOriginX = this.offsetX;
            this.dragOriginY = this.offsetY;
            event.currentTarget.setPointerCapture(event.pointerId);
        },

        pointerMove(event) {
            if (!this.dragging) {
                return;
            }
            this.offsetX = this.dragOriginX + (event.clientX - this.dragStartX);
            this.offsetY = this.dragOriginY + (event.clientY - this.dragStartY);
            this.drawViewport();
        },

        pointerUp(event) {
            if (!this.dragging) {
                return;
            }
            this.dragging = false;
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
                const file = await exportCroppedFile(this.cropState(), this.profile, 'avatar');

                if (this.useLivewire && this.$wire) {
                    await this.$wire.upload('avatar', file);
                } else {
                    assignFileToInput(document.getElementById(this.inputId), file);
                }

                this.revokePreviewBlob();
                this.preview = URL.createObjectURL(file);
                this.cancelCrop();
            } catch {
                window.alert('No se pudo procesar la imagen. Intenta de nuevo.');
            } finally {
                this.applying = false;
            }
        },

        destroy() {
            this.revokeSource();
            this.revokePreviewBlob();
        },
    }));
}
