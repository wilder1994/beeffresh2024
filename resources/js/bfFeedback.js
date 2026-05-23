/**
 * Toast + confirmación de eliminación (sin diálogos nativos del navegador).
 */
export function registerBfFeedback(Alpine) {
    Alpine.store('bfToast', {
        visible: false,
        type: 'success',
        message: '',
        _timer: null,
        show(type, message, duration = 4000) {
            clearTimeout(this._timer);
            this.type = type === 'error' ? 'error' : 'success';
            this.message = message;
            this.visible = true;
            this._timer = setTimeout(() => {
                this.visible = false;
            }, duration);
        },
        hide() {
            clearTimeout(this._timer);
            this.visible = false;
        },
    });

    Alpine.store('bfConfirm', {
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Eliminar',
        _form: null,
        ask({ title, message = '', form, confirmLabel = 'Eliminar' }) {
            this.title = title;
            this.message = message;
            this.confirmLabel = confirmLabel;
            this._form = form;
            this.open = true;
        },
        confirm() {
            const form = this._form;
            this.cancel();
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        },
        cancel() {
            this.open = false;
            this._form = null;
        },
    });

    window.addEventListener('bf-toast', (event) => {
        const detail = event.detail ?? {};
        Alpine.store('bfToast').show(detail.type ?? 'success', detail.message ?? '', detail.duration);
    });

    Alpine.data('bfDeleteAction', (config) => ({
        blockWhenCount: config.blockWhenCount ?? 0,
        blockedMessage: config.blockedMessage ?? 'No se puede eliminar.',
        confirmTitle: config.confirmTitle ?? '¿Confirmar eliminación?',
        confirmMessage: config.confirmMessage ?? '',
        confirmLabel: config.confirmLabel ?? 'Eliminar',
        click() {
            if (this.blockWhenCount > 0) {
                Alpine.store('bfToast').show('error', this.blockedMessage, 4500);
                return;
            }
            Alpine.store('bfConfirm').ask({
                title: this.confirmTitle,
                message: this.confirmMessage,
                form: this.$refs.form,
                confirmLabel: this.confirmLabel,
            });
        },
    }));
}
