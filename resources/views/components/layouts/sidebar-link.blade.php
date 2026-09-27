@props(['active' => false, 'href' => '#', 'icon' => null, 'badge' => null])
@php($usesAdminMaterial = request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()))
<li>
    <a href="{{ $href }}" @class([
        'relative flex items-center gap-3 text-sm rounded-xl px-4 py-2.5 transition-colors duration-200 font-semibold',
        'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 font-bold' => $active && ! $usesAdminMaterial,
        'hover:bg-slate-500/5 text-slate-600 dark:text-slate-400' => ! $active && ! $usesAdminMaterial,
        'admin-nav-link' => $usesAdminMaterial,
        'admin-nav-active' => $usesAdminMaterial && $active,
    ])
    @if ($active) aria-current="page" @endif
    @click="closeSidebarOnMobile()"
    :class="{ 'justify-center': !sidebarOpen, 'justify-start': sidebarOpen }">
        @if ($icon)
            <x-admin.icon :name="$icon" class="admin-nav-icon h-[1.125rem] w-[1.125rem] shrink-0" />
        @endif
        <span x-show="sidebarOpen" class="min-w-0 flex-1 truncate">{{ $slot }}</span>
        {{-- Keep an accessible name when the collapsed sidebar hides the visible label. --}}
        <span x-show="!sidebarOpen" class="sr-only">{{ $slot }}</span>
        @if ($badge)
            <span class="admin-badge" aria-label="{{ $badge }} perlu ditindaklanjuti">{{ $badge }}</span>
        @endif
    </a>
</li>
