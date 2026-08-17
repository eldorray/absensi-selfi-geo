{{--
    Material 3 Expressive & Fluid Motion Skin for Employee PWA.
    Combines institutional Material 3 tokens with lively, organic liquid background,
    fluid spring physics, responsive micro-interactions, and native bottom sheet transitions.
--}}
<style>
    body.pwa-m3 {
        /* Colors - Dark Theme (Material 3 Emerald / Forest Palette) */
        --m3-primary: #9bd5b5;
        --m3-primary-rgb: 155, 213, 181;
        --m3-on-primary: #003822;
        --m3-primary-container: #145236;
        --m3-on-primary-container: #b7f1ce;
        --m3-secondary-container: #344b3e;
        --m3-on-secondary-container: #d0e8d8;
        --m3-tertiary-container: #5b4520;
        --m3-on-tertiary-container: #ffdea6;
        --m3-error-container: #8c1d18;
        --m3-on-error-container: #ffdad6;
        --m3-surface: #0f1712;
        --m3-surface-container-low: #141f18;
        --m3-surface-container: #18261e;
        --m3-surface-container-high: #213227;
        --m3-surface-container-highest: #2b3e32;
        --m3-on-surface: #e5efe7;
        --m3-on-surface-variant: #a2b7a6;
        --m3-outline: #899e8e;
        --m3-outline-variant: rgba(255, 255, 255, 0.12);
        --m3-shadow: rgba(0, 0, 0, 0.45);

        --bg-color: #0c140f;
        --screen-bg: #111c15;
        --text-main: var(--m3-on-surface);
        --text-muted: var(--m3-on-surface-variant);
        --glass-bg: rgba(24, 38, 30, 0.75);
        --glass-border: rgba(155, 213, 181, 0.16);
        --glass-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.35);
        --glass-card-bg: rgba(24, 38, 30, 0.72);
        --glass-card-border: rgba(155, 213, 181, 0.14);
        --glass-card-shadow: 0 8px 24px -2px rgba(0, 0, 0, 0.28);
        --menu-bg: #18261e;
        --menu-border: rgba(155, 213, 181, 0.16);
        --phone-shell-bg: #111c15;
        --phone-shell-border: rgba(155, 213, 181, 0.18);
        --footer-icon-color: var(--m3-on-surface-variant);
        --active-nav-color: var(--m3-primary);
        --input-bg: rgba(43, 62, 50, 0.65);
        --input-border: rgba(155, 213, 181, 0.2);
        --input-focus-bg: rgba(43, 62, 50, 0.9);
        --btn-accent-bg: var(--m3-primary);
        --btn-accent-text: var(--m3-on-primary);
        --btn-accent-shadow: 0 10px 25px -3px rgba(155, 213, 181, 0.3);
        --nav-btn-inactive-bg: rgba(255, 255, 255, 0.04);
        --nav-btn-inactive-border: rgba(255, 255, 255, 0.08);
        --nav-btn-inactive-text: var(--m3-on-surface-variant);
        --stats-card-border: rgba(155, 213, 181, 0.16);
        --stats-present: var(--m3-primary);
        --stats-late: #efc174;
        --stats-total: #b7ccb9;
        --icon-riwayat-bg: rgba(52, 75, 62, 0.7);
        --icon-profil-bg: rgba(52, 75, 62, 0.7);
        --icon-password-bg: rgba(91, 69, 32, 0.7);
        --icon-perizinan-bg: rgba(20, 82, 54, 0.75);
        --status-ok-text: var(--m3-primary);
        --status-ok-bg: rgba(20, 82, 54, 0.55);
        --status-ok-border: rgba(155, 213, 181, 0.3);
        --status-late-text: #efc174;
        --status-late-bg: rgba(91, 69, 32, 0.55);
        --status-late-border: rgba(239, 193, 116, 0.3);
        --blob-opacity: 0.35;
        --grid-line-color: rgba(155, 213, 181, 0.035);

        background: var(--bg-color);
        color: var(--text-main);
    }

    body.pwa-m3.light-theme {
        /* Colors - Light Theme (Material 3 Emerald Fresh Palette) */
        --m3-primary: #12633e;
        --m3-primary-rgb: 18, 99, 62;
        --m3-on-primary: #ffffff;
        --m3-primary-container: #b8f2cd;
        --m3-on-primary-container: #002113;
        --m3-secondary-container: #d2e8d9;
        --m3-on-secondary-container: #0e1f16;
        --m3-tertiary-container: #f8dfb2;
        --m3-on-tertiary-container: #241a05;
        --m3-error-container: #ffdad6;
        --m3-on-error-container: #410002;
        --m3-surface: #f4f8f4;
        --m3-surface-container-low: #ecf3ed;
        --m3-surface-container: #e4eee6;
        --m3-surface-container-high: #dbe7dd;
        --m3-surface-container-highest: #d2dfd5;
        --m3-on-surface: #141e17;
        --m3-on-surface-variant: #3e5344;
        --m3-outline: #6a8270;
        --m3-outline-variant: rgba(18, 99, 62, 0.12);
        --m3-shadow: rgba(18, 99, 62, 0.14);

        --bg-color: #eaf2eb;
        --screen-bg: #f4f8f4;
        --text-main: var(--m3-on-surface);
        --text-muted: var(--m3-on-surface-variant);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(18, 99, 62, 0.12);
        --glass-shadow: 0 12px 30px -4px rgba(18, 99, 62, 0.08);
        --glass-card-bg: rgba(255, 255, 255, 0.88);
        --glass-card-border: rgba(18, 99, 62, 0.12);
        --glass-card-shadow: 0 8px 20px -2px rgba(18, 99, 62, 0.06);
        --menu-bg: #ffffff;
        --menu-border: rgba(18, 99, 62, 0.12);
        --phone-shell-bg: #ecf3ed;
        --phone-shell-border: rgba(18, 99, 62, 0.14);
        --footer-icon-color: var(--m3-on-surface-variant);
        --active-nav-color: var(--m3-primary);
        --input-bg: rgba(255, 255, 255, 0.9);
        --input-border: rgba(18, 99, 62, 0.18);
        --input-focus-bg: #ffffff;
        --btn-accent-bg: var(--m3-primary);
        --btn-accent-text: var(--m3-on-primary);
        --btn-accent-shadow: 0 10px 24px -3px rgba(18, 99, 62, 0.28);
        --nav-btn-inactive-bg: rgba(18, 99, 62, 0.04);
        --nav-btn-inactive-border: rgba(18, 99, 62, 0.08);
        --nav-btn-inactive-text: var(--m3-on-surface-variant);
        --stats-card-border: rgba(18, 99, 62, 0.12);
        --stats-present: var(--m3-primary);
        --stats-late: #9c6000;
        --stats-total: #31583d;
        --icon-riwayat-bg: rgba(210, 232, 217, 0.85);
        --icon-profil-bg: rgba(210, 232, 217, 0.85);
        --icon-password-bg: rgba(248, 223, 178, 0.85);
        --icon-perizinan-bg: rgba(184, 242, 205, 0.85);
        --status-ok-text: #0b5e38;
        --status-ok-bg: rgba(184, 242, 205, 0.7);
        --status-ok-border: rgba(11, 94, 56, 0.25);
        --status-late-text: #824f00;
        --status-late-bg: rgba(248, 223, 178, 0.7);
        --status-late-border: rgba(130, 79, 0, 0.25);
        --blob-opacity: 0.45;
        --grid-line-color: rgba(18, 99, 62, 0.04);
    }

    /* Ambient Floating Liquid Lights (Preserved & Enhanced for Alive Feeling) */
    body.pwa-m3 .bg-grid-overlay {
        display: block !important;
        opacity: 0.75;
    }
    body.pwa-m3 .pwa-decoration {
        display: block !important;
    }

    body.pwa-m3 .phone-shell {
        box-shadow: 0 24px 60px -10px var(--m3-shadow);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
    }

    /* Fluid Glassmorphic Cards & Elevated Panels */
    body.pwa-m3 .glass-panel,
    body.pwa-m3 .glass-card,
    body.pwa-m3 .solid-panel {
        background: var(--glass-card-bg);
        border: 1px solid var(--glass-card-border) !important;
        box-shadow: var(--glass-card-shadow);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        transition: transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1),
                    background-color 0.25s ease,
                    border-color 0.25s ease,
                    box-shadow 0.25s ease;
    }

    body.pwa-m3 .glass-card:hover {
        border-color: rgba(155, 213, 181, 0.3) !important;
    }

    /* Spring micro-interactions on interactive cards & tiles */
    .interactive-card {
        cursor: pointer;
        transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.22s ease;
    }
    .interactive-card:active {
        transform: scale(0.955);
    }

    /* Form Inputs with Smooth Focus Glow */
    body.pwa-m3 .theme-input {
        background-color: var(--input-bg);
        border: 1.5px solid var(--input-border) !important;
        border-radius: 16px !important;
        color: var(--text-main);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: border-color 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    background-color 0.2s ease;
    }

    body.pwa-m3 .theme-input:focus {
        background-color: var(--input-focus-bg);
        border-color: var(--m3-primary) !important;
        box-shadow: 0 0 0 3.5px rgba(155, 213, 181, 0.22) !important;
        outline: none;
    }

    body.pwa-m3.light-theme .theme-input:focus {
        border-color: var(--m3-primary) !important;
        box-shadow: 0 0 0 3.5px rgba(18, 99, 62, 0.18) !important;
    }

    /* Expressive Primary Buttons */
    body.pwa-m3 .theme-btn-submit {
        background: linear-gradient(135deg, var(--m3-primary), #68bb8d);
        color: var(--m3-on-primary);
        box-shadow: var(--btn-accent-shadow);
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        letter-spacing: 0.02em;
        transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1),
                    box-shadow 0.22s ease,
                    filter 0.2s ease;
    }
    body.pwa-m3.light-theme .theme-btn-submit {
        background: linear-gradient(135deg, #12633e, #1e8756);
        color: #ffffff;
    }
    body.pwa-m3 .theme-btn-submit:hover {
        filter: brightness(1.05);
    }
    body.pwa-m3 .theme-btn-submit:active {
        transform: scale(0.96);
    }

    /* Theme Toggle */
    body.pwa-m3 .theme-toggle {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--glass-card-bg);
        border: 1px solid var(--glass-card-border) !important;
        color: var(--text-main);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s ease;
    }
    body.pwa-m3 .theme-toggle:active {
        transform: scale(0.92);
    }

    /* Fluid Footer Navigation */
    body.pwa-m3 .footer-nav {
        background: rgba(18, 30, 23, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid var(--glass-border);
        border-radius: 26px 26px 0 0;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.35);
        padding: 8px 10px max(10px, env(safe-area-inset-bottom));
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    body.pwa-m3.light-theme .footer-nav {
        background: rgba(255, 255, 255, 0.92);
        border-top: 1px solid rgba(18, 99, 62, 0.12);
        box-shadow: 0 -8px 24px rgba(18, 99, 62, 0.08);
    }

    body.pwa-m3 .nav-item {
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    body.pwa-m3 .nav-item:active {
        transform: scale(0.92);
    }

    body.pwa-m3 .nav-pill {
        border-radius: 16px;
        transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }
    body.pwa-m3 .nav-item.is-active .nav-pill {
        background: rgba(155, 213, 181, 0.18);
        color: var(--m3-primary);
        box-shadow: 0 2px 10px rgba(155, 213, 181, 0.15);
    }
    body.pwa-m3.light-theme .nav-item.is-active .nav-pill {
        background: rgba(18, 99, 62, 0.12);
        color: var(--m3-primary);
        box-shadow: 0 2px 8px rgba(18, 99, 62, 0.1);
    }

    /* Floating Action Buttons with Breathing Glow */
    body.pwa-m3 .nav-fab {
        position: relative;
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    }
    body.pwa-m3 .nav-fab:active {
        transform: scale(0.92);
    }

    @keyframes pulse-fab-glow {
        0%, 100% { box-shadow: 0 8px 22px rgba(102, 187, 106, 0.45); }
        50% { box-shadow: 0 12px 28px rgba(102, 187, 106, 0.7), 0 0 0 6px rgba(102, 187, 106, 0.2); }
    }
    .pulse-glow-masuk {
        animation: pulse-fab-glow 3s infinite ease-in-out;
    }

    /* ════════ Staggered Entrance Animations (Alive Cascades) ════════ */
    @keyframes fluid-stagger-in {
        0% {
            opacity: 0;
            transform: translateY(18px) scale(0.975);
        }
        60% {
            opacity: 0.95;
            transform: translateY(-2px) scale(1.005);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    body.pwa-m3 .animate-stagger {
        animation-name: fluid-stagger-in;
        animation-duration: 420ms;
        animation-fill-mode: both;
        animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
    }
    .stagger-0 { animation-delay: 0ms; }
    .stagger-40 { animation-delay: 45ms; }
    .stagger-80 { animation-delay: 90ms; }
    .stagger-120 { animation-delay: 135ms; }
    .stagger-160 { animation-delay: 180ms; }
    .stagger-200 { animation-delay: 225ms; }
    .stagger-240 { animation-delay: 270ms; }

    /* ════════ Bottom Sheet Fluid Motion System ════════ */
    @keyframes sheet-slide-up-anim {
        0% {
            opacity: 0;
            transform: translateY(100%);
        }
        70% {
            opacity: 1;
            transform: translateY(-4px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes sheet-slide-down-anim {
        0% {
            opacity: 1;
            transform: translateY(0);
        }
        100% {
            opacity: 0;
            transform: translateY(100%);
        }
    }

    .sheet-slide-up {
        animation: sheet-slide-up-anim 460ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .sheet-handle {
        width: 44px;
        height: 5px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.28);
        margin: 0 auto 12px;
        transition: background-color 0.2s ease, width 0.2s ease;
    }
    body.pwa-m3.light-theme .sheet-handle {
        background: rgba(18, 99, 62, 0.22);
    }
    .sheet-handle:hover {
        width: 52px;
        background: rgba(255, 255, 255, 0.45);
    }

    /* Native Bottom Sheet Container overlay */
    .sheet-overlay {
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* Status Badges with Subtle Glow */
    .status-badge-glow {
        box-shadow: 0 0 12px currentColor;
    }

    /* Segmented pill radio transitions */
    .radio-card-check {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Cross Document View Transitions */
    @supports (view-transition-name: page) {
        @view-transition {
            navigation: auto;
        }

        body.pwa-m3 {
            view-transition-name: page;
        }

        body.pwa-m3::view-transition-old(page) {
            animation: 250ms cubic-bezier(0.4, 0, 1, 1) both pwa-view-old;
        }

        body.pwa-m3::view-transition-new(page) {
            animation: 320ms cubic-bezier(0.16, 1, 0.3, 1) both pwa-view-new;
        }
    }

    @keyframes pwa-view-old {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.97); }
    }

    @keyframes pwa-view-new {
        from { opacity: 0; transform: translateY(16px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
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
