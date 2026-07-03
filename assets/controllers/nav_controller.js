import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sidebar', 'overlay'];

    toggleSidebar() {
        this.sidebarTarget.classList.toggle('is-open');
        if (this.hasOverlayTarget) {
            this.overlayTarget.classList.toggle('is-visible');
        }

        const isOpen = this.sidebarTarget.classList.contains('is-open');
        const menuBtn = this.element.querySelector('.topbar-menu-btn');
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', String(isOpen));
        }
    }

    closeSidebar() {
        this.sidebarTarget.classList.remove('is-open');
        if (this.hasOverlayTarget) {
            this.overlayTarget.classList.remove('is-visible');
        }

        const menuBtn = this.element.querySelector('.topbar-menu-btn');
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', 'false');
        }
    }
}

