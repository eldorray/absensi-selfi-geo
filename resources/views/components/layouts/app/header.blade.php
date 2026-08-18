<!-- Header -->
<header @class([
    'bg-white/85 dark:bg-gray-900/80 backdrop-blur-md z-20 border-b border-slate-100 dark:border-slate-800/80',
    'admin-header' => request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()),
])>
    <div class="flex items-center justify-between h-16 px-4">
        <!-- Left side: Logo and toggle -->
        <div class="flex items-center">
            <button @click="toggleSidebar"
                class="sidebar-toggle p-2 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none"
                aria-label="Buka atau tutup menu samping">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="app-brand ml-4 flex items-center gap-2 font-semibold text-xl text-blue-600 dark:text-blue-400">
                @if (($branding ?? null)?->logoUrl())
                    <img src="{{ $branding->logoUrl() }}" alt="" class="h-9 max-w-32 object-contain">
                @endif
                <span>{{ config('app.name') }}</span>
            </div>
            @if (request()->routeIs('admin.*'))
                <span class="admin-chip ml-3 hidden sm:inline-flex">Admin</span>
            @endif
        </div>

        <!-- Right side: appearance and profile -->
        <div class="flex items-center gap-2 sm:gap-3">
            @if (request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()))
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
            @endif

            <!-- Profile -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="profile-trigger flex items-center focus:outline-none">
                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-gray-200 text-black dark:bg-gray-700 dark:text-white">
                            {{ Auth::user()->initials() }}
                        </span>
                    </span>
                    <span class="ml-2 hidden md:block">{{ Auth::user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" :class="{ 'block': open, 'hidden': !open }"
                    @class([
                        'hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700',
                        'admin-glass-popover' => request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()),
                    ])>
                    <a href="{{ route('settings.profile.edit') }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </div>
                    </a>
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="block w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
