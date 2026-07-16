@props(['active' => false, 'title' => '', 'icon' => 'fas-list'])

<li x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button @click="
        if (sidebarOpen) {
            open = !open;
        } else {
            temporarilyOpenSidebar();
            open = true;
        }
    " @class([
        'flex items-center w-full px-4 py-2.5 text-xs rounded-xl transition-all duration-200 font-semibold',
        'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' => $active,
        'hover:bg-slate-500/5 hover:text-slate-800 dark:hover:text-slate-200 text-slate-600 dark:text-slate-400' => !$active,
        'admin-nav-link' => request()->routeIs('admin.*'),
        'admin-nav-active' => request()->routeIs('admin.*') && $active,
    ])
    :class="{ 'justify-center': !sidebarOpen, 'justify-between': sidebarOpen }">
        <div class="flex items-center" :class="{ 'justify-center': !sidebarOpen }">
            @svg($icon, $active ? 'w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400' : 'w-4.5 h-4.5 text-slate-400 dark:text-slate-500')
            <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300" x-transition:enter-start="opacity-0 transform -translate-x-2"
                x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition-all duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-2"
                class="ml-3 whitespace-nowrap">{{ $title }}</span>
        </div>
        <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform"
            :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    <!-- Level 2 submenu -->
    <div x-show="open && sidebarOpen" class="mt-1 ml-4 space-y-1">
        {{ $slot }}
    </div>
</li>
