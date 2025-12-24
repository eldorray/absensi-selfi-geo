            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">
                <!-- Sidebar Content -->
                <div class="h-full flex flex-col">
                    <!-- Sidebar Menu -->
                    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                        <ul class="space-y-1 px-2">
                            @if (auth()->user()->isAdmin())
                                <!-- Admin Menu -->
                                <x-layouts.sidebar-link href="{{ route('admin.dashboard') }}" icon='fas-house'
                                    :active="request()->routeIs('admin.dashboard')">Dashboard</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('admin.academic-years.index') }}"
                                    icon='fas-calendar' :active="request()->routeIs('admin.academic-years.*')">Tahun Ajaran</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('admin.offices.index') }}" icon='fas-building'
                                    :active="request()->routeIs('admin.offices.*')">Kelola Kantor</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('admin.users.index') }}" icon='fas-users'
                                    :active="request()->routeIs('admin.users.*')">Kelola User</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('admin.roles.index') }}" icon='fas-user-tag'
                                    :active="request()->routeIs('admin.roles.*')">Kelola Role</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('admin.work-schedules.index') }}"
                                    icon='fas-clock' :active="request()->routeIs('admin.work-schedules.*')">Jam Kerja</x-layouts.sidebar-link>

                                <!-- Laporan Dropdown -->
                                <x-layouts.sidebar-two-level-link-parent title="Laporan" icon="fas-chart-bar"
                                    :active="request()->routeIs('admin.reports.*') ||
                                        request()->routeIs('admin.attendances.*')">
                                    <x-layouts.sidebar-two-level-link href="{{ route('admin.reports.daily') }}"
                                        icon='fas-calendar-day' :active="request()->routeIs('admin.reports.daily')">Rekap
                                        Harian</x-layouts.sidebar-two-level-link>
                                    <x-layouts.sidebar-two-level-link href="{{ route('admin.reports.monthly') }}"
                                        icon='fas-calendar-alt' :active="request()->routeIs('admin.reports.monthly')">Rekap
                                        Bulanan</x-layouts.sidebar-two-level-link>
                                    <x-layouts.sidebar-two-level-link href="{{ route('admin.attendances.index') }}"
                                        icon='fas-clipboard-list' :active="request()->routeIs('admin.attendances.*')">Detail
                                        Absensi</x-layouts.sidebar-two-level-link>
                                </x-layouts.sidebar-two-level-link-parent>

                                <x-layouts.sidebar-link href="{{ route('admin.leaves.index') }}" icon='fas-file-alt'
                                    :active="request()->routeIs('admin.leaves.*')">Perizinan</x-layouts.sidebar-link>
                            @else
                                <!-- Employee Menu -->
                                <x-layouts.sidebar-link href="{{ route('attendance.dashboard') }}" icon='fas-house'
                                    :active="request()->routeIs('attendance.dashboard')">Beranda</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('attendance.selfie') }}" icon='fas-camera'
                                    :active="request()->routeIs('attendance.selfie')">Absensi Selfie</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('attendance.index') }}" icon='fas-list'
                                    :active="request()->routeIs('attendance.index')">Riwayat Absensi</x-layouts.sidebar-link>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>
