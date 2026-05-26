import './bootstrap';

import { bootstrapBfRealtime } from './realtime';
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import registerAvatarEditor from './avatarEditor';
import registerImageCropEditor from './imageCropEditor';
import registerAddressPicker, { bootAddressPickerNodes } from './addressPicker';
import { registerBfFeedback } from './bfFeedback';
import { registerCartBadge } from './cartBadge';
import { registerStoreCart } from './storeCart';
import { registerProductPurchaseAlpine } from './volumeScalePricing';

window.Alpine = Alpine;
window.Livewire = Livewire;

registerCartBadge(window);
registerStoreCart();

/** Abre el modal de Mi perfil (tienda y panel); funciona aunque Alpine falle en el clic. */
window.bfOpenProfileModal = function () {
    window.dispatchEvent(
        new CustomEvent('open-modal', { detail: 'profile-account', bubbles: true })
    );
};

/** Modales de registro cliente (tienda / login). */
window.bfOpenRegisterConfirm = function () {
    window.dispatchEvent(
        new CustomEvent('open-modal', { detail: 'register-client-confirm', bubbles: true })
    );
};

window.bfOpenRegisterClient = function () {
    window.dispatchEvent(
        new CustomEvent('open-modal', { detail: 'register-client', bubbles: true })
    );
};

window.bfCloseRegisterConfirm = function () {
    window.dispatchEvent(
        new CustomEvent('close-modal', { detail: 'register-client-confirm', bubbles: true })
    );
};

document.addEventListener('alpine:init', () => {
    registerBfFeedback(Alpine);
    registerAvatarEditor(Alpine);
    registerImageCropEditor(Alpine);
    registerAddressPicker(Alpine);
    registerProductPurchaseAlpine(Alpine);

    Alpine.data('staffLayout', () => ({
        mobileMenuOpen: false,
        sidebarCollapsed: false,
        init() {
            try {
                this.sidebarCollapsed = localStorage.getItem('bf_staff_sidebar_collapsed') === '1';
            } catch {
                this.sidebarCollapsed = false;
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.mobileMenuOpen = false;
                }
            });
        },
        toggleDesktopSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            try {
                localStorage.setItem('bf_staff_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
            } catch {
                // ignore
            }
        },
        openMobileMenu() {
            this.mobileMenuOpen = true;
        },
        closeMobileMenu() {
            this.mobileMenuOpen = false;
        },
    }));
});

/** Restos de crop-dialog con x-teleport antiguo (huérfanos en body). */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('body > [aria-labelledby="bf-image-crop-title"], body > [aria-labelledby="bf-avatar-crop-title"]').forEach((el) => el.remove());
});

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.added', ({ el }) => bootAddressPickerNodes(el));
});

/**
 * Panel staff: @livewireScriptConfig evita el auto-start del ESM; arrancamos una sola vez aquí.
 * Tienda / guest: sin config, livewire.esm arranca solo en DOMContentLoaded.
 */
if (window.livewireScriptConfig !== undefined) {
    Livewire.start();
}

bootstrapBfRealtime();

import './notificationBell.js';
