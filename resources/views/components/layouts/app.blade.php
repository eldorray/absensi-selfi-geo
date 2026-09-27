@props(['title' => null])
<!DOCTYPE html>
<html lang="id">

<head>
    @php
        $branding = \App\Models\ApplicationSetting::current();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#176b43">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Absensi">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="{{ $branding->iconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $branding->iconUrl() }}">
    @php
        $pageTitle = trim(strip_tags((string) $title));
    @endphp
    <title>{{ $pageTitle !== '' ? $pageTitle.' · '.config('app.name') : config('app.name') }}</title>
    <script>
        window.setAppearance = function(appearance) {
            let setDark = () => document.documentElement.classList.add('dark')
            let setLight = () => document.documentElement.classList.remove('dark')
            let setButtons = (appearance) => {
                document.querySelectorAll('button[data-appearance]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(appearance === button.value))
                })
            }
            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')
                window.localStorage.removeItem('appearance')
                media.matches ? setDark() : setLight()
            } else if (appearance === 'dark') {
                window.localStorage.setItem('appearance', 'dark')
                setDark()
            } else if (appearance === 'light') {
                window.localStorage.setItem('appearance', 'light')
                setLight()
            }
            if (document.readyState === 'complete') {
                setButtons(appearance)
            } else {
                document.addEventListener("DOMContentLoaded", () => setButtons(appearance))
            }
        }
        window.setAppearance(window.localStorage.getItem('appearance') || 'system')

        // "Sistem" (no stored preference) follows OS theme changes live.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
            if (!window.localStorage.getItem('appearance')) {
                document.documentElement.classList.toggle('dark', event.matches)
            }
        })
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Service Worker Registration & PWA Install -->
    <script>
        // PWA Install prompt
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show install banner
            showInstallBanner();
        });

        window.addEventListener('appinstalled', () => {
            // Hide the banner when installed
            hideInstallBanner();
            deferredPrompt = null;
        });

        function showInstallBanner() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner && !localStorage.getItem('pwaInstallDismissed')) {
                banner.classList.remove('hidden');
            }
        }

        function hideInstallBanner() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner) {
                banner.classList.add('hidden');
            }
        }

        window.installPWA = async function() {
            if (!deferredPrompt) return;

            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond
            const {
                outcome
            } = await deferredPrompt.userChoice;
            console.log(`User response: ${outcome}`);

            // Clear the deferredPrompt
            deferredPrompt = null;
            hideInstallBanner();
        }

        window.dismissInstallBanner = function() {
            hideInstallBanner();
            localStorage.setItem('pwaInstallDismissed', 'true');
        }

        // Service Worker registration handled in partials/pwa-update.blade.php
    </script>

    <!-- Global submit loading: blocks double submits on POST forms. -->
    <script>
        (() => {
            const busyLabel = 'Memproses…';
            const originals = new Map();

            const submitButtons = (form) => Array.from(form.elements)
                .filter((el) => el.type === 'submit' && ['BUTTON', 'INPUT'].includes(el.tagName));

            const skips = (form, submitter = null) => {
                if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) return true;
                const method = submitter && submitter.hasAttribute('formmethod') ? submitter.formMethod : form.method;
                const target = (submitter && submitter.getAttribute('formtarget')) || form.getAttribute('target') || '_self';

                return method !== 'post' || target !== '_self';
            };

            window.markFormBusy = function(form) {
                if (skips(form) || form.getAttribute('aria-busy') === 'true') return;
                form.setAttribute('aria-busy', 'true');

                submitButtons(form).forEach((button) => {
                    if (originals.has(button)) return;
                    const isInput = button.tagName === 'INPUT';
                    originals.set(button, {
                        content: isInput ? button.value : button.innerHTML,
                        disabled: button.disabled,
                        minWidth: button.style.minWidth,
                    });
                    button.style.minWidth = button.offsetWidth + 'px';
                    button.disabled = true;

                    // Icon-only buttons keep their icon and aria-label; only labelled buttons change text.
                    const text = (isInput ? button.value : button.textContent).trim();
                    if (button.hasAttribute('aria-label') || text === '') return;
                    if (isInput) {
                        button.value = busyLabel;
                    } else {
                        button.innerHTML = '<span class="inline-flex items-center justify-center gap-2">' +
                            '<svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                            '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>' +
                            '<path d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>' +
                            '</svg><span>' + busyLabel + '</span></span>';
                    }
                });
            };

            const restoreAll = () => {
                originals.forEach((original, button) => {
                    if (button.tagName === 'INPUT') {
                        button.value = original.content;
                    } else {
                        button.innerHTML = original.content;
                    }
                    button.disabled = original.disabled;
                    button.style.minWidth = original.minWidth;
                });
                originals.clear();
                document.querySelectorAll('form[aria-busy="true"]').forEach((form) => form.removeAttribute('aria-busy'));
            };

            document.addEventListener('submit', (event) => {
                // Forms using @submit.prevent (e.g. to open the confirm modal) are skipped;
                // the confirm modal calls markFormBusy() itself before form.submit().
                if (event.defaultPrevented || skips(event.target, event.submitter)) return;
                // Defer so the submitter's name/value is still part of the submitted data.
                setTimeout(() => {
                    if (!event.defaultPrevented) window.markFormBusy(event.target);
                }, 0);
            });

            // Back/forward cache restores the page as it was left: re-enable buttons.
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) restoreAll();
            });
        })();
    </script>
</head>

@php
    $isAdminRoute = request()->routeIs('admin.*');
    $usesAdminMaterial = $isAdminRoute || (request()->routeIs('settings.*') && auth()->user()?->isAdmin());

    $flashMessages = collect([
        ['type' => 'success', 'message' => session('status')],
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
    ])
        ->filter(fn (array $flash): bool => is_string($flash['message']) && trim($flash['message']) !== '')
        ->unique('message');
@endphp

{{-- Sidebar uses one breakpoint (lg = 1024px): below it the sidebar is an off-canvas
     overlay that always starts closed (not persisted); at lg+ it collapses between
     256px and 64px and that preference is persisted in localStorage. --}}
<body @class([
    'antialiased',
    'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200' => ! $usesAdminMaterial,
    'admin-shell' => $usesAdminMaterial,
]) data-admin-ui="{{ $usesAdminMaterial ? 'absenku' : '' }}" x-data="{
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,
    sidebarOpen: window.matchMedia('(min-width: 1024px)').matches && localStorage.getItem('sidebarOpen') !== 'false',
    init() {
        const media = window.matchMedia('(min-width: 1024px)');
        media.addEventListener('change', () => {
            this.isDesktop = media.matches;
            this.sidebarOpen = media.matches && localStorage.getItem('sidebarOpen') !== 'false';
        });
    },
    setSidebar(open) {
        this.sidebarOpen = open;
        if (this.isDesktop) {
            localStorage.setItem('sidebarOpen', open);
        } else if (open) {
            this.$nextTick(() => this.$refs.sidebarClose && this.$refs.sidebarClose.focus());
        } else {
            this.$nextTick(() => this.$refs.sidebarToggle && this.$refs.sidebarToggle.focus());
        }
    },
    toggleSidebar() {
        this.setSidebar(!this.sidebarOpen);
    },
    temporarilyOpenSidebar() {
        if (!this.sidebarOpen) {
            this.setSidebar(true);
        }
    },
    closeSidebarOnMobile() {
        if (!this.isDesktop) {
            this.sidebarOpen = false;
        }
    },
}" @keydown.escape.window="if (!isDesktop && sidebarOpen) setSidebar(false)">

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner"
        class="hidden fixed bottom-0 left-0 right-0 z-50 p-4 bg-[#176b43] shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-white">
                    <p class="font-semibold text-sm sm:text-base">Pasang Aplikasi Absensi</p>
                    <p class="text-xs sm:text-sm text-white/80">Akses lebih cepat & bekerja offline</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="installPWA()"
                    class="px-4 py-2 bg-white text-[#176b43] font-semibold text-sm rounded-lg hover:bg-gray-100 transition-colors">
                    Pasang
                </button>
                <button type="button" onclick="dismissInstallBanner()" aria-label="Tutup banner instal" class="p-2 text-white/80 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Container: sidebar setinggi layar, header hanya di atas konten. -->
    <div class="flex min-h-screen">

        <x-layouts.app.sidebar />

        <div class="flex min-w-0 flex-1 flex-col">

            <x-layouts.app.header :title="$title" />

            <!-- Main Content -->
            <main @class([
                'flex-1 content-transition',
                'bg-gray-100 dark:bg-gray-900' => ! $usesAdminMaterial,
                'admin-main' => $usesAdminMaterial,
            ])>
                <div @class(['p-6', 'lg:px-10 lg:py-8' => $usesAdminMaterial])>
                    {{-- Flash messages: rendered once here for every page. --}}
                    @foreach ($flashMessages as $flash)
                        @php($isError = $flash['type'] === 'error')
                        <div x-data="{ show: true }" x-show="show"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            role="{{ $isError ? 'alert' : 'status' }}" data-flash="{{ $flash['type'] }}"
                            @class([
                                'mb-6 flex items-start gap-3 rounded-2xl border p-4 text-sm font-medium',
                                'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950/60 dark:text-green-100' => ! $isError,
                                'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/60 dark:text-red-100' => $isError,
                                'admin-alert-success' => $usesAdminMaterial && ! $isError,
                                'admin-alert-danger' => $usesAdminMaterial && $isError,
                            ])>
                            <svg class="mt-0.5 h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                @if ($isError)
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                @else
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                @endif
                            </svg>
                            <p class="flex-1">{{ $flash['message'] }}</p>
                            <button type="button" @click="show = false"
                                class="-m-1.5 inline-flex shrink-0 rounded-lg p-1.5 opacity-80 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current">
                                <span class="sr-only">Tutup pesan</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    @endforeach

                    {{ $slot }}

                </div>
            </main>
        </div>
    </div>

    @if ($usesAdminMaterial)
        <x-admin.confirm-modal />
    @endif

    @include('partials.pwa-update')
</body>

</html>
