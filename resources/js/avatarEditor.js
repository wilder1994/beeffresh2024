/**
 * Avatar: previsualización + modal de recorte circular (rotar, zoom, arrastrar).
 */
export default function registerAvatarEditor(Alpine) {
    Alpine.data('avatarEditor', (config = {}) => ({
        preview: config.preview || null,
        initial: config.initial || '?',
        inputId: config.inputId || 'profile-avatar-input',
        useLivewire: Boolean(config.useLivewire),
        outputSize: 512,
        viewportSize: 280,

        cropOpen: false,
        sourceUrl: null,
        image: null,
        rotation: 0,
        scaleMul: 1,
        offsetX: 0,
        offsetY: 0,
        baseScale: 1,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        applying: false,

        init() {
            this.$el.addEventListener('alpine:destroy', () => this.destroy());
        },

        pickFile(event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            this.revokeSource();
            this.sourceUrl = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                this.image = img;
                this.rotation = 0;
                this.scaleMul = 1;
                this.offsetX = 0;
                this.offsetY = 0;
                const vs = this.viewportSize;
                this.baseScale = (Math.max(vs / img.width, vs / img.height) * 1.05);
                this.cropOpen = true;
                this.$nextTick(() => this.drawViewport());
            };
            img.onerror = () => {
                this.revokeSource();
                window.alert('No se pudo cargar la imagen seleccionada.');
            };
            img.src = this.sourceUrl;
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

        drawViewport() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.image) {
                return;
            }
            const size = this.viewportSize;
            canvas.width = size;
            canvas.height = size;
            this.paint(canvas.getContext('2d'), size);
        },

        paint(ctx, size) {
            const img = this.image;
            if (!img || !ctx) {
                return;
            }

            const ratio = size / this.viewportSize;

            ctx.clearRect(0, 0, size, size);
            ctx.fillStyle = '#f5f5f4';
            ctx.fillRect(0, 0, size, size);
            ctx.save();
            ctx.beginPath();
            ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
            ctx.clip();
            ctx.fillStyle = '#f5f5f4';
            ctx.fillRect(0, 0, size, size);
            ctx.translate(
                size / 2 + this.offsetX * ratio,
                size / 2 + this.offsetY * ratio
            );
            ctx.rotate((this.rotation * Math.PI) / 180);
            const s = this.baseScale * this.scaleMul * ratio;
            ctx.scale(s, s);
            ctx.drawImage(img, -img.width / 2, -img.height / 2);
            ctx.restore();
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
                const out = document.createElement('canvas');
                out.width = this.outputSize;
                out.height = this.outputSize;
                this.paint(out.getContext('2d'), this.outputSize);

                const blob = await new Promise((resolve) => {
                    out.toBlob((b) => resolve(b), 'image/jpeg', 0.9);
                });

                if (!blob) {
                    throw new Error('export failed');
                }

                const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });

                if (this.useLivewire && this.$wire) {
                    await this.$wire.upload('avatar', file);
                } else {
                    const input = document.getElementById(this.inputId);
                    if (input) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                    }
                }

                this.revokePreviewBlob();
                this.preview = URL.createObjectURL(blob);
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
