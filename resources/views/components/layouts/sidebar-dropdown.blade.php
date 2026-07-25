@props(['label', 'icon' => null, 'active' => false])

{{-- Collapsible sidebar group. Opens automatically when one of its child
     routes is active. Reads `sidebarOpen` from the parent Alpine scope. --}}
<li x-data="{ open: @js($active) }" class="admin-nav-group">
    <button type="button" @click="open = !open" @class([
        'flex w-full items-center text-xs rounded-xl px-4 py-2.5 transition-all duration-200 font-semibold',
        'hover:bg-slate-500/5 hover:text-slate-800 dark:hover:text-slate-200 text-slate-600 dark:text-slate-400',
        'admin-nav-link' => request()->routeIs('admin.*'),
        'admin-nav-active' => $active,
    ]) :class="{ 'justify-center': !sidebarOpen, 'justify-between': sidebarOpen }"
        :aria-expanded="open">
        <span class="flex items-center">
            @svg($icon, $active ? 'w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400' : 'w-4.5 h-4.5 text-slate-400 dark:text-slate-500')
            <span x-show="sidebarOpen" class="ml-3 whitespace-nowrap">{{ $label }}</span>
        </span>
        <span x-show="sidebarOpen" :class="{ 'rotate-90': open }"
            class="transition-transform duration-200">
            @svg('fas-chevron-right', 'w-3 h-3 text-slate-400 dark:text-slate-500')
        </span>
    </button>

    <ul x-show="open" x-collapse class="mt-1 space-y-1" :class="{ 'pl-3': sidebarOpen }">
        {{ $slot }}
    </ul>
</li>
