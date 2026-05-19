import './bootstrap';

import Alpine from 'alpinejs';
import registerAvatarEditor from './avatarEditor';

window.Alpine = Alpine;

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
    registerAvatarEditor(Alpine);

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

Alpine.start();
