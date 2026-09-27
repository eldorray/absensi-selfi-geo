@props(['label', 'icon' => null, 'active' => false, 'badge' => null])
@php($usesAdminMaterial = request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()))

{{-- Collapsible sidebar group. Opens automatically when one of its child
     routes is active. Reads `sidebarOpen` from the parent Alpine scope. --}}
<li x-data="{ open: @js($active) }" class="admin-nav-group">
    <button type="button" @click="
        if (sidebarOpen) {
            open = !open;
        } else {
            temporarilyOpenSidebar();
            open = true;
        }
    " @class([
        'relative flex w-full items-center gap-3 text-sm rounded-xl px-4 py-2.5 transition-colors duration-200 font-semibold',
        'hover:bg-slate-500/5 text-slate-600 dark:text-slate-400' => ! $usesAdminMaterial,
        'admin-nav-link' => $usesAdminMaterial,
        'admin-nav-active' => $active,
    ]) :class="{ 'justify-center': !sidebarOpen }"
        :aria-expanded="open">
        @if ($icon)
            <x-admin.icon :name="$icon" class="admin-nav-icon h-[1.125rem] w-[1.125rem] shrink-0" />
        @endif
        <span x-show="sidebarOpen" class="min-w-0 flex-1 truncate">{{ $label }}</span>
        <span x-show="!sidebarOpen" class="sr-only">{{ $label }}</span>
        @if ($badge)
            {{-- Hitungan anak ikut naik ke tombol grup selama grupnya tertutup. --}}
            <span x-show="!open || !sidebarOpen" class="admin-badge" aria-label="{{ $badge }} perlu ditindaklanjuti">{{ $badge }}</span>
        @endif
        <span x-show="sidebarOpen" :class="{ 'rotate-90': open }" class="admin-nav-chevron inline-flex">
            <x-admin.icon name="chevron-right" size="12" />
        </span>
    </button>

    <ul x-show="open && sidebarOpen" x-transition.opacity class="mt-0.5 space-y-0.5 pl-3">
        {{ $slot }}
    </ul>
</li>
