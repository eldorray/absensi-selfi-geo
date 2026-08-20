{{-- Shared Material 3 Expressive skin for every teacher attendance page. --}}
<style>
    body.pwa-m3 {
        --m3-primary: #9bd5b5;
        --m3-on-primary: #003822;
        --m3-primary-container: #145236;
        --m3-on-primary-container: #b7f2cd;
        --m3-secondary-container: #344b3e;
        --m3-on-secondary-container: #d0e8d8;
        --m3-tertiary-container: #5b4520;
        --m3-on-tertiary-container: #ffdea6;
        --m3-error: #ffb4ab;
        --m3-error-container: #93000a;
        --m3-on-error-container: #ffdad6;
        --m3-surface: #0f1712;
        --m3-surface-container-low: #141f18;
        --m3-surface-container: #19261e;
        --m3-surface-container-high: #223228;
        --m3-surface-container-highest: #2b3e32;
        --m3-on-surface: #e5efe7;
        --m3-on-surface-variant: #bdc9bf;
        --m3-outline: #899e8e;
        --m3-outline-variant: #3d4b40;
        --m3-card-outline: rgba(229, 239, 231, 0.07);
        --m3-card-highlight: rgba(229, 239, 231, 0.025);
        --m3-shadow: rgba(0, 0, 0, 0.38);
        --m3-stage: #0b100d;
        --m3-shell: #080d0a;
        --m3-success: #9bd5b5;
        --m3-warning: #efc174;

        --bg-color: var(--m3-stage);
        --screen-bg: var(--m3-surface);
        --text-main: var(--m3-on-surface);
        --text-muted: var(--m3-on-surface-variant);
        --glass-bg: var(--m3-surface-container-low);
        --glass-border: var(--m3-outline-variant);
        --glass-shadow: none;
        --glass-card-bg: var(--m3-surface-container-low);
        --glass-card-border: var(--m3-outline-variant);
        --glass-card-shadow: none;
        --menu-bg: var(--m3-surface-container-high);
        --menu-border: var(--m3-outline-variant);
        --phone-shell-bg: var(--m3-shell);
        --phone-shell-border: var(--m3-outline-variant);
        --footer-icon-color: var(--m3-on-surface-variant);
        --active-nav-color: var(--m3-primary);
        --input-bg: var(--m3-surface-container-high);
        --input-border: var(--m3-outline);
        --input-focus-bg: var(--m3-surface-container-highest);
        --btn-accent-bg: var(--m3-primary);
        --btn-accent-text: var(--m3-on-primary);
        --btn-accent-shadow: 0 8px 22px var(--m3-shadow);
        --nav-btn-inactive-bg: var(--m3-surface-container);
        --nav-btn-inactive-border: var(--m3-outline-variant);
        --nav-btn-inactive-text: var(--m3-on-surface-variant);
        --stats-card-border: var(--m3-outline-variant);
        --stats-present: var(--m3-success);
        --stats-late: var(--m3-warning);
        --stats-total: var(--m3-on-surface);
        --icon-riwayat-bg: var(--m3-secondary-container);
        --icon-profil-bg: var(--m3-secondary-container);
        --icon-password-bg: var(--m3-tertiary-container);
        --icon-perizinan-bg: var(--m3-primary-container);
        --status-ok-text: var(--m3-success);
        --status-ok-bg: var(--m3-primary-container);
        --status-ok-border: rgba(155, 213, 181, 0.3);
        --status-late-text: var(--m3-warning);
        --status-late-bg: var(--m3-tertiary-container);
        --status-late-border: rgba(239, 193, 116, 0.3);

        background: var(--m3-stage);
        color: var(--m3-on-surface);
    }

    body.pwa-m3.light-theme {
        --m3-primary: #176b43;
        --m3-on-primary: #ffffff;
        --m3-primary-container: #b7f2cd;
        --m3-on-primary-container: #002113;
        --m3-secondary-container: #d4e8d9;
        --m3-on-secondary-container: #102018;
        --m3-tertiary-container: #f8dfb2;
        --m3-on-tertiary-container: #241a05;
        --m3-error: #ba1a1a;
        --m3-error-container: #ffdad6;
        --m3-on-error-container: #410002;
        --m3-surface: #f7fbf5;
        --m3-surface-container-low: #eef5ee;
        --m3-surface-container: #e8f0e8;
        --m3-surface-container-high: #dfe9e0;
        --m3-surface-container-highest: #d4dfd6;
        --m3-on-surface: #171d19;
        --m3-on-surface-variant: #3e4a42;
        --m3-outline: #6f7a72;
        --m3-outline-variant: #bec9c0;
        --m3-card-outline: rgba(23, 107, 67, 0.065);
        --m3-card-highlight: rgba(255, 255, 255, 0.42);
        --m3-shadow: rgba(23, 107, 67, 0.18);
        --m3-stage: #e8f0e8;
        --m3-shell: #d4ded5;
        --m3-success: #075e38;
        --m3-warning: #824f00;
    }

    body.pwa-m3 .pwa-decoration,
    body.pwa-m3 .bg-grid-overlay,
    body.pwa-m3 .screen-content > .absolute.pointer-events-none,
    body.pwa-m3 .screen-content > .absolute[class*="blur-"] {
        display: none !important;
    }

    body.pwa-m3 .screen-content {
        background: var(--m3-surface);
    }

    body.pwa-m3 .phone-shell {
        background: var(--m3-shell);
        border-color: var(--m3-outline-variant);
        box-shadow: 0 28px 70px var(--m3-shadow);
    }

    body.pwa-m3 .glass-panel,
    body.pwa-m3 .glass-card {
        background: var(--m3-surface-container-low);
        border: 1px solid var(--m3-card-outline) !important;
        box-shadow: inset 0 1px 0 var(--m3-card-highlight);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        transition: transform 200ms cubic-bezier(0.2, 0, 0, 1), background-color 200ms ease, border-color 200ms ease;
    }

    body.pwa-m3 .glass-card.theme-border,
    body.pwa-m3 .glass-panel.theme-border {
        border-color: var(--m3-card-outline) !important;
    }

    body.pwa-m3 .glass-card .theme-border-t,
    body.pwa-m3 .glass-panel .theme-border-t {
        border-top-color: var(--m3-card-outline) !important;
    }

    body.pwa-m3 .solid-panel {
        background: var(--m3-surface-container-high);
        border: 1px solid var(--m3-outline-variant) !important;
        box-shadow: 0 12px 32px var(--m3-shadow);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    body.pwa-m3 .interactive-card { transition: transform 180ms cubic-bezier(0.2, 0, 0, 1), background-color 180ms ease; }
    body.pwa-m3 .interactive-card:active { transform: scale(0.98); }


    body.pwa-m3 .theme-toggle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 0 !important;
        background: var(--m3-surface-container-high);
        color: var(--m3-on-surface);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        transition: transform 150ms cubic-bezier(0.2, 0, 0, 1), background-color 180ms ease;
    }

    body.pwa-m3 .theme-toggle:hover { background: var(--m3-secondary-container); }
    body.pwa-m3 .theme-toggle:active { transform: scale(0.94); }

    body.pwa-m3 .theme-input {
        min-height: 52px;
        border: 1px solid transparent !important;
        border-bottom-color: var(--m3-outline) !important;
        border-radius: 16px 16px 5px 5px !important;
        background: var(--m3-surface-container-high);
        color: var(--m3-on-surface);
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        transition: border-color 180ms ease, background-color 180ms ease;
    }

    body.pwa-m3 .theme-input:focus {
        border: 2px solid var(--m3-primary) !important;
        outline: 0;
        background: var(--m3-surface-container-highest);
        box-shadow: none !important;
    }

    body.pwa-m3 .theme-btn-submit {
        min-height: 52px;
        border: 0;
        border-radius: 999px;
        background: var(--m3-primary) !important;
        color: var(--m3-on-primary) !important;
        box-shadow: var(--btn-accent-shadow) !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        letter-spacing: 0;
        transition: transform 150ms cubic-bezier(0.2, 0, 0, 1), box-shadow 180ms ease;
    }

    body.pwa-m3 .theme-btn-submit:active { transform: scale(0.98); }

    body.pwa-m3 :where(a, button, input, select, textarea):focus-visible {
        outline: 3px solid var(--m3-primary);
        outline-offset: 3px;
    }

    body.pwa-m3 .footer-nav {
        min-height: 76px;
        padding: 8px 8px max(8px, env(safe-area-inset-bottom));
        border-top: 0;
        border-radius: 24px 24px 0 0;
        transform: translateY(-6px);
    }

    body.pwa-m3 .footer-nav {
        background: var(--m3-surface-container-low);
        box-shadow: 0 -8px 24px var(--m3-shadow);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    body.pwa-m3 .nav-item {
        min-width: 0;
        min-height: 52px;
        justify-content: flex-end;
        transition: transform 150ms cubic-bezier(0.2, 0, 0, 1);
    }

    body.pwa-m3 .nav-item:active { transform: scale(0.94); }
    body.pwa-m3 .nav-pill { min-width: 64px; min-height: 32px; padding: 5px 18px; border-radius: 16px; transition: background-color 250ms cubic-bezier(0.2, 0, 0, 1), color 180ms ease, transform 180ms ease; }
    body.pwa-m3 .nav-item.is-active .nav-pill { background: var(--m3-primary-container); color: var(--m3-on-primary-container); box-shadow: none; }
    body.pwa-m3 .nav-label { color: var(--m3-on-surface-variant); font-family: 'Plus Jakarta Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0; }
    body.pwa-m3 .nav-item.is-active .nav-label { color: var(--m3-primary); }

    body.pwa-m3 .nav-fab {
        width: 52px;
        height: 52px;
        border: 3px solid var(--m3-surface-container-low);
        border-radius: 18px;
        transition: transform 180ms cubic-bezier(0.2, 0, 0, 1), box-shadow 180ms ease;
    }

    body.pwa-m3 .nav-fab-masuk { background: var(--m3-primary); color: var(--m3-on-primary); box-shadow: 0 8px 18px var(--m3-shadow); }
    body.pwa-m3 .nav-fab-pulang { background: #a24e38; color: #ffffff; box-shadow: 0 8px 18px var(--m3-shadow); }
    body.pwa-m3 .nav-fab:active { transform: scale(0.94); }
    body.pwa-m3 .pulse-glow-masuk { animation: none; }

    body.pwa-m3 .theme-status-ok-card { background: var(--m3-primary-container) !important; border-color: var(--status-ok-border) !important; }
    body.pwa-m3 .theme-status-ok-text { color: var(--m3-success); }
    body.pwa-m3 .theme-status-late-card { background: var(--m3-tertiary-container) !important; border-color: var(--status-late-border) !important; }
    body.pwa-m3 .theme-status-late-text { color: var(--m3-warning); }
    body.pwa-m3 .status-badge-glow { box-shadow: none; }

    body.pwa-m3 .theme-icon-riwayat,
    body.pwa-m3 .theme-icon-profil,
    body.pwa-m3 .theme-icon-perizinan { background: var(--m3-secondary-container); color: var(--m3-primary); }
    body.pwa-m3 .theme-icon-password { background: var(--m3-tertiary-container); color: var(--m3-warning); }

    body.pwa-m3 [class*="bg-gradient-to-r"],
    body.pwa-m3 [class*="bg-gradient-to-tr"] { background-image: none !important; }

    body.pwa-m3 [class*="from-emerald"],
    body.pwa-m3 [class*="from-green"] { background-color: var(--m3-primary) !important; color: var(--m3-on-primary); }
    body.pwa-m3 [class*="from-red"],
    body.pwa-m3 [class*="from-rose"] { background-color: var(--m3-error) !important; color: #410002; }
    body.pwa-m3 #pwa-install-banner { background: var(--m3-primary) !important; color: var(--m3-on-primary); box-shadow: 0 8px 22px var(--m3-shadow); }

    body.pwa-m3 .rounded-full[class*="bg-emerald"],
    body.pwa-m3 .rounded-full[class*="bg-green"] { box-shadow: none !important; }

    body.pwa-m3 .mobile-panel > .space-y-4,
    body.pwa-m3 .mobile-panel > .space-y-3 { display: grid; gap: 14px; }

    @keyframes m3-stagger-in {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body.pwa-m3 .animate-stagger {
        animation-name: m3-stagger-in;
        animation-duration: 300ms;
        animation-fill-mode: both;
        animation-timing-function: cubic-bezier(0.2, 0, 0, 1);
    }

    .stagger-0 { animation-delay: 0ms; }
    .stagger-40 { animation-delay: 40ms; }
    .stagger-80 { animation-delay: 80ms; }
    .stagger-120 { animation-delay: 120ms; }
    .stagger-160 { animation-delay: 160ms; }
    .stagger-200 { animation-delay: 200ms; }
    .stagger-240 { animation-delay: 240ms; }

    @keyframes sheet-slide-up-anim {
        from { opacity: 0; transform: translateY(32px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sheet-slide-up { animation: sheet-slide-up-anim 360ms cubic-bezier(0.2, 0, 0, 1) both; }
    .sheet-handle { width: 40px; height: 4px; margin: 0 auto 8px; border-radius: 999px; background: var(--m3-outline); }
    .sheet-overlay { background: rgba(0, 0, 0, 0.62); }
    .radio-card-check { transition: background-color 180ms ease, border-color 180ms ease, transform 150ms ease; }

    @supports (view-transition-name: page) {
        @view-transition { navigation: auto; }
        body.pwa-m3 { view-transition-name: page; }
        body.pwa-m3::view-transition-old(page) { animation: 220ms cubic-bezier(0.4, 0, 1, 1) both pwa-view-old; }
        body.pwa-m3::view-transition-new(page) { animation: 300ms cubic-bezier(0.2, 0, 0, 1) both pwa-view-new; }
    }

    @keyframes pwa-view-old {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-12px); }
    }

    @keyframes pwa-view-new {
        from { opacity: 0; transform: translateX(12px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @media (min-width: 640px) {
        body.pwa-m3 > main { width: 395px !important; height: min(780px, calc(100svh - 32px)) !important; }
    }

    @media (max-width: 359px) {
        body.pwa-m3 .mobile-panel { padding-inline: 14px; }
        body.pwa-m3 .nav-pill { min-width: 54px; padding-inline: 13px; }
    }

    @media (prefers-reduced-motion: reduce) {
        body.pwa-m3 *,
        body.pwa-m3 *::before,
        body.pwa-m3 *::after {
            scroll-behavior: auto !important;
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
