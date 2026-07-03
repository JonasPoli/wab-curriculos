import './bootstrap.js';

/*
 * WAB Ninjas — Symfony Starter Kit
 * Main application entry point.
 *
 * This file is loaded via importmap() in base templates.
 * - Registers all Stimulus controllers (via bootstrap.js)
 * - Initialises the dark/light colour scheme
 */

// ── Dark / Light Mode ──────────────────────────────────────────────────────
/**
 * Applies the current theme from localStorage (or system preference)
 * by toggling classes on <html>.
 *
 * Classes managed:
 *   dark      — Tailwind dark mode selector
 *   wa-dark   — Web Awesome dark mode
 *   wa-light  — Web Awesome light mode
 */
function applyColorTheme() {
    const stored    = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark    = stored === 'dark' || (!stored && prefersDark);
    const html      = document.documentElement;

    if (isDark) {
        html.classList.add('dark', 'wa-dark');
        html.classList.remove('wa-light');
    } else {
        html.classList.remove('dark', 'wa-dark');
        html.classList.add('wa-light');
    }
}

/** Switch to dark mode and persist preference. */
window.darkMode = () => {
    localStorage.setItem('theme', 'dark');
    applyColorTheme();
};

/** Switch to light mode and persist preference. */
window.lightMode = () => {
    localStorage.setItem('theme', 'light');
    applyColorTheme();
};

// Run on page load (Turbo-compatible — fires after every navigation)
applyColorTheme();

/** Update sun/moon icons on theme toggle buttons (FA6 <i> elements). */
window.updateThemeIcons = () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('#theme-icon-sidebar, #theme-icon-topbar').forEach(el => {
        if (isDark) {
            el.className = el.className.replace('fa-moon', 'fa-sun');
        } else {
            el.className = el.className.replace('fa-sun', 'fa-moon');
        }
    });
};

// Re-apply after Turbo page transitions
document.addEventListener('turbo:render', () => { applyColorTheme(); updateThemeIcons(); });

// Run on initial load
document.addEventListener('DOMContentLoaded', updateThemeIcons);

// ── System preference changes (live) ──────────────────────────────────────
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    // Only respond if no explicit preference is stored
    if (!localStorage.getItem('theme')) {
        applyColorTheme();
    }
});