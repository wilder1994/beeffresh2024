import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
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
