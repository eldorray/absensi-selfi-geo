@props(['active' => false, 'href' => '#', 'icon' => null])
<li>
    <a href="{{ $href }}" @class([
        'flex items-center text-xs rounded-xl px-4 py-2.5 justify-center transition-all duration-200 font-semibold',
        'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' => $active,
        'hover:bg-slate-500/5 hover:text-slate-800 dark:hover:text-slate-200 text-slate-600 dark:text-slate-400' => !$active,
    ])
    :class="{ 'justify-center': !sidebarOpen, 'justify-start': sidebarOpen }">
        @svg($icon, $active ? 'w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400' : 'w-4.5 h-4.5 text-slate-400 dark:text-slate-500')
        <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300" x-transition:enter-start="opacity-0 transform -translate-x-2"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition-all duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-2"
            class="ml-3 whitespace-nowrap">{{ $slot }}</span>
    </a>
</li>
