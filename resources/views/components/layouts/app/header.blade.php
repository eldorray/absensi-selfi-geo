@props(['title' => null])
@php
    $usesAdminMaterial = request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin());
    $pageTitle = trim(strip_tags((string) $title));
@endphp
{{-- Brand ada di sidebar; header hanya membawa lokasi halaman, tema, dan akun. --}}
<!-- Header -->
<header @class([
    'sticky top-0 z-20 border-b',
    'bg-white/85 dark:bg-gray-900/80 backdrop-blur-md border-slate-100 dark:border-slate-800/80' => ! $usesAdminMaterial,
    'admin-header' => $usesAdminMaterial,
])>
    <div class="flex h-16 items-center gap-3 px-4 lg:h-[4.5rem] lg:gap-4 lg:px-10">
        <button type="button" x-ref="sidebarToggle" @click="toggleSidebar()"
            class="sidebar-toggle shrink-0 rounded-md p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            aria-label="Menu samping" aria-controls="app-sidebar" :aria-expanded="sidebarOpen">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 5h16v14H4zM9 5v14" />
            </svg>
        </button>

        @if ($usesAdminMaterial)
            <nav aria-label="Breadcrumb" class="admin-breadcrumb">
                @php
                    $sections = [
                        'admin.dashboard' => 'Dashboard',
                        'admin.reports.daily*' => 'Rekap Harian',
                        'admin.reports.monthly*' => 'Rekap Bulanan',
                        'admin.attendances.*' => 'Detail Absensi',
                        'admin.leaves.*' => 'Perizinan',
                        'admin.kesiswaan.*' => 'Kesiswaan',
                        'admin.students.*' => 'Data Siswa',
                        'admin.school-classes.*' => 'Kelas',
                        'admin.homeroom-assignments.*' => 'Penugasan Wali Kelas',
                        'admin.bk-records.*' => 'Catatan BK',
                        'admin.bk-categories.*' => 'Kategori BK',
                        'admin.academic-years.*' => 'Tahun Ajaran',
                        'admin.offices.*' => 'Kelola Kantor',
                        'admin.users.*' => 'Kelola User',
                        'admin.roles.*' => 'Kelola Role',
                        'admin.work-schedules.*' => 'Jam Kerja',
                        'admin.announcements.*' => 'Informasi',
                        'admin.account-switches.*' => 'Riwayat Ganti Akun',
                        'settings.*' => 'Pengaturan',
                    ];
                    $crumb = collect($sections)->first(fn (string $label, string $route): bool => request()->routeIs($route))
                        ?? ($pageTitle !== '' ? $pageTitle : null);
                @endphp
                <a href="{{ route('admin.dashboard') }}" @class(['hidden sm:inline' => $crumb])>Admin</a>
                @if ($crumb)
                    <span aria-hidden="true" class="hidden sm:inline">/</span>
                    <span aria-current="page">{{ $crumb }}</span>
                @endif
            </nav>
        @else
            <div class="app-brand flex items-center gap-2 text-xl font-semibold text-blue-600 dark:text-blue-400">
                <span>{{ config('app.name') }}</span>
            </div>
        @endif

        <div class="flex-1"></div>

        <!-- Right side: appearance and profile -->
        <div class="flex items-center gap-2 sm:gap-3">
            @if ($usesAdminMaterial)
                <div class="admin-topbar-appearance hidden sm:flex" role="group" aria-label="Tema tampilan">
                    <button type="button" value="light" data-appearance="light" onclick="setAppearance('light')" class="admin-topbar-theme-option" aria-label="Gunakan tema terang" title="Tema terang">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                        </svg>
                    </button>
                    <button type="button" value="dark" data-appearance="dark" onclick="setAppearance('dark')" class="admin-topbar-theme-option" aria-label="Gunakan tema gelap" title="Tema gelap">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                        </svg>
                    </button>
                    <button type="button" value="system" data-appearance="system" onclick="setAppearance('system')" class="admin-topbar-theme-option" aria-label="Ikuti tema sistem" title="Tema sistem">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="13" rx="2"></rect>
                            <path stroke-linecap="round" d="M8 21h8m-4-4v4"></path>
                        </svg>
                    </button>
                </div>

                <div x-data="{ open: false }" class="relative sm:hidden" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open" class="admin-topbar-theme-trigger" aria-label="Pilih tema tampilan" :aria-expanded="open">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" class="admin-theme-popover admin-glass-popover absolute right-0 z-50 mt-2 w-44 p-2">
                        <button type="button" value="light" data-appearance="light" @click="setAppearance('light'); open = false" class="admin-theme-popover-option">
                            <span>Terang</span>
                        </button>
                        <button type="button" value="dark" data-appearance="dark" @click="setAppearance('dark'); open = false" class="admin-theme-popover-option">
                            <span>Gelap</span>
                        </button>
                        <button type="button" value="system" data-appearance="system" @click="setAppearance('system'); open = false" class="admin-theme-popover-option">
                            <span>Sistem</span>
                        </button>
                    </div>
                </div>

                <span class="hidden h-7 w-px sm:block" style="background: var(--admin-border)" aria-hidden="true"></span>
            @endif

            <!-- Profile -->
            <div x-data="{ open: false }" class="relative"
                @keydown.escape.window="if (open) { open = false; $refs.profileTrigger.focus(); }">
                <button type="button" x-ref="profileTrigger" @click="open = !open"
                    class="profile-trigger flex items-center gap-2.5 text-left"
                    aria-label="Menu akun {{ Auth::user()->name }}" aria-haspopup="true"
                    aria-controls="profile-menu" :aria-expanded="open">
                    @if ($usesAdminMaterial)
                        <span class="admin-profile-avatar">{{ Auth::user()->initials() }}</span>
                        <span class="hidden flex-col leading-tight md:flex">
                            <span class="text-sm font-bold">{{ Auth::user()->name }}</span>
                            <span class="admin-muted text-xs">{{ Auth::user()->role?->name ?? 'Admin' }}</span>
                        </span>
                    @else
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-200 text-black dark:bg-gray-700 dark:text-white">
                            {{ Auth::user()->initials() }}
                        </span>
                        <span class="hidden md:block">{{ Auth::user()->name }}</span>
                    @endif
                    <svg class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div id="profile-menu" x-show="open" x-cloak @click.away="open = false"
                    @class([
                        'absolute right-0 z-50 mt-2 w-52 p-1.5',
                        'rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800' => ! $usesAdminMaterial,
                        'admin-glass-popover' => $usesAdminMaterial,
                    ])>
                    <a href="{{ route('settings.profile.edit') }}"
                        class="flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Pengaturan
                    </a>
                    <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex min-h-11 w-full items-center gap-2 rounded-lg px-3 text-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
