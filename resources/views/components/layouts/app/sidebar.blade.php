            <aside :class="{
                    'w-full md:w-64 app-sidebar-expanded': sidebarOpen,
                    'w-0 md:w-16 hidden md:block app-sidebar-collapsed': !sidebarOpen,
                }"
                @class([
                    'bg-white/70 dark:bg-gray-950/40 backdrop-blur-md border-r border-slate-100 dark:border-slate-900/80 sidebar-transition overflow-hidden',
                    'admin-sidebar' => request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin()),
                ]) data-layout-sidebar>
                <!-- Sidebar Content -->
                <div class="h-full flex flex-col">
                    <!-- Sidebar Menu -->
                    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                        <ul class="admin-nav-list space-y-1 px-2">
                            @if (auth()->user()->isAdmin())
                                <!-- Admin Menu -->
                                <x-layouts.sidebar-link href="{{ route('admin.dashboard') }}" icon='fas-house'
                                    :active="request()->routeIs('admin.dashboard')">Dashboard</x-layouts.sidebar-link>

                                @php
                                    $masterActive = request()->routeIs('admin.academic-years.*', 'admin.offices.*', 'admin.users.*', 'admin.roles.*', 'admin.work-schedules.*');
                                    $studentActive = request()->routeIs('admin.students.*', 'admin.school-classes.*');
                                    $hadirActive = request()->routeIs('admin.reports.*', 'admin.attendances.*', 'admin.leaves.*');
                                    $infoActive = request()->routeIs('admin.announcements.*', 'admin.account-switches.*');
                                @endphp

                                {{-- Master Data --}}
                                <x-layouts.sidebar-dropdown label="Master Data" icon="fas-database" :active="$masterActive">
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
                                </x-layouts.sidebar-dropdown>

                                <x-layouts.sidebar-dropdown label="Bimbingan Konseling" icon="fas-user-shield" :active="request()->routeIs('admin.bk-*')">
                                    <x-layouts.sidebar-link href="{{ route('admin.bk-records.index') }}" icon='fas-notes-medical' :active="request()->routeIs('admin.bk-records.*')">Catatan BK</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.bk-categories.index') }}" icon='fas-tags' :active="request()->routeIs('admin.bk-categories.*')">Kategori BK</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Data Siswa --}}
                                <x-layouts.sidebar-dropdown label="Data Siswa" icon="fas-user-graduate" :active="$studentActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.students.index', 'mi') }}" icon='fas-users' :active="request()->routeIs('admin.students.*') && request()->route('schoolLevel') === 'mi'">Data Siswa MI</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.students.index', 'smp') }}" icon='fas-users' :active="request()->routeIs('admin.students.*') && request()->route('schoolLevel') === 'smp'">Data Siswa SMP</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.school-classes.index', 'mi') }}" icon='fas-chalkboard' :active="request()->routeIs('admin.school-classes.*') && request()->route('schoolLevel') === 'mi'">Kelas MI</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.school-classes.index', 'smp') }}" icon='fas-chalkboard' :active="request()->routeIs('admin.school-classes.*') && request()->route('schoolLevel') === 'smp'">Kelas SMP</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Kehadiran --}}
                                <x-layouts.sidebar-dropdown label="Kehadiran" icon="fas-calendar-check" :active="$hadirActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.reports.daily') }}"
                                        icon='fas-calendar-day' :active="request()->routeIs('admin.reports.daily')">Rekap Harian</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.reports.monthly') }}"
                                        icon='fas-calendar-alt' :active="request()->routeIs('admin.reports.monthly')">Rekap Bulanan</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.attendances.index') }}"
                                        icon='fas-clipboard-list' :active="request()->routeIs('admin.attendances.*')">Detail Absensi</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.leaves.index') }}" icon='fas-file-alt'
                                        :active="request()->routeIs('admin.leaves.*')">Perizinan</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Informasi --}}
                                <x-layouts.sidebar-dropdown label="Informasi" icon="fas-bell" :active="$infoActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.announcements.index') }}"
                                        icon='fas-bullhorn' :active="request()->routeIs('admin.announcements.*')">Informasi</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.account-switches.index') }}"
                                        icon='fas-right-left' :active="request()->routeIs('admin.account-switches.*')">Riwayat Ganti Akun</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>
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
