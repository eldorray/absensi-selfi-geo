            {{-- Mobile (< lg) backdrop: closes the off-canvas sidebar. --}}
            <div x-show="sidebarOpen && !isDesktop" x-cloak x-transition.opacity @click="setSidebar(false)"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden" aria-hidden="true"></div>

            @php
                $usesAdminMaterial = request()->routeIs('admin.*') || (request()->routeIs('settings.*') && auth()->user()?->isAdmin());
                $isAdminUser = auth()->user()->isAdmin();
                $sidebarBranding = \App\Models\ApplicationSetting::current();
            @endphp
            <aside id="app-sidebar" aria-label="Menu samping"
                :class="{
                    'app-sidebar-expanded translate-x-0! lg:w-64': sidebarOpen,
                    'app-sidebar-collapsed lg:w-[4.5rem]': !sidebarOpen,
                }"
                :inert="!isDesktop && !sidebarOpen"
                @class([
                    'fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] shrink-0 -translate-x-full shadow-xl lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:max-w-none lg:translate-x-0 lg:shadow-none',
                    'overflow-hidden transition-[width,translate] duration-300 ease-in-out motion-reduce:transition-none',
                    'bg-white dark:bg-gray-950 border-r border-slate-100 dark:border-slate-900/80' => ! $usesAdminMaterial,
                    'admin-sidebar' => $usesAdminMaterial,
                ]) data-layout-sidebar>
                <!-- Sidebar Content -->
                <div class="flex h-full flex-col gap-4 px-3 pb-4 pt-4">
                    <div class="flex items-center gap-2 px-1">
                        <a href="{{ $isAdminUser ? route('admin.dashboard') : route('attendance.dashboard') }}"
                            class="admin-brand min-w-0 flex-1" :class="{ 'justify-center': !sidebarOpen }">
                            <span class="admin-brand-mark" aria-hidden="true">
                                @if ($sidebarBranding->logoUrl())
                                    <img src="{{ $sidebarBranding->logoUrl() }}" alt="">
                                @else
                                    {{ mb_substr(config('app.name'), 0, 1) }}
                                @endif
                            </span>
                            <span class="admin-brand-text flex min-w-0 flex-col" x-show="sidebarOpen">
                                <span class="admin-brand-name truncate">{{ config('app.name') }}</span>
                                @if ($isAdminUser)
                                    <span class="admin-brand-sub">Panel admin yayasan</span>
                                @endif
                            </span>
                        </a>
                        <button type="button" x-ref="sidebarClose" @click="setSidebar(false)"
                            class="sidebar-close inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-500 lg:hidden"
                            aria-label="Tutup menu samping">
                            <x-admin.icon name="x" size="20" />
                        </button>
                    </div>
                    <!-- Sidebar Menu -->
                    <nav class="custom-scrollbar -mx-1 flex-1 overflow-y-auto px-1">
                        <ul class="admin-nav-list space-y-0.5">
                            @if ($isAdminUser)
                                @php
                                    $masterActive = request()->routeIs('admin.academic-years.*', 'admin.offices.*', 'admin.users.*', 'admin.roles.*', 'admin.work-schedules.*');
                                    $infoActive = request()->routeIs('admin.announcements.*', 'admin.account-switches.*');
                                    $hadirActive = request()->routeIs('admin.reports.*', 'admin.attendances.*', 'admin.leaves.*');
                                    $studentActive = request()->routeIs('admin.kesiswaan.*', 'admin.students.*', 'admin.school-classes.*', 'admin.homeroom-assignments.*');
                                    $pendingLeaveCount = \App\Models\Leave::pending()->count();
                                @endphp
                                <!-- Admin Menu -->
                                <x-layouts.sidebar-link href="{{ route('admin.dashboard') }}" icon="grid"
                                    :active="request()->routeIs('admin.dashboard')">Dashboard</x-layouts.sidebar-link>

                                {{-- Kehadiran --}}
                                <x-layouts.sidebar-dropdown label="Kehadiran" icon="clipboard" :active="$hadirActive" :badge="$pendingLeaveCount ?: null">
                                    <x-layouts.sidebar-link href="{{ route('admin.reports.daily') }}" :active="request()->routeIs('admin.reports.daily')">Rekap Harian</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.reports.monthly') }}" :active="request()->routeIs('admin.reports.monthly')">Rekap Bulanan</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.attendances.index') }}" :active="request()->routeIs('admin.attendances.*')">Detail Absensi</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.leaves.index') }}" :badge="$pendingLeaveCount ?: null" :active="request()->routeIs('admin.leaves.*')">Perizinan</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Siswa --}}
                                <x-layouts.sidebar-dropdown label="Siswa" icon="users" :active="$studentActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.kesiswaan.index') }}" :active="request()->routeIs('admin.kesiswaan.*')">Kesiswaan</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.students.index', 'mi') }}" :active="request()->routeIs('admin.students.*') && request()->route('schoolLevel') === 'mi'">Data Siswa MI</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.students.index', 'smp') }}" :active="request()->routeIs('admin.students.*') && request()->route('schoolLevel') === 'smp'">Data Siswa SMP</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.school-classes.index', 'mi') }}" :active="request()->routeIs('admin.school-classes.*') && request()->route('schoolLevel') === 'mi'">Kelas MI</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.school-classes.index', 'smp') }}" :active="request()->routeIs('admin.school-classes.*') && request()->route('schoolLevel') === 'smp'">Kelas SMP</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.homeroom-assignments.index') }}" :active="request()->routeIs('admin.homeroom-assignments.*')">Penugasan Wali Kelas</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                <x-layouts.sidebar-dropdown label="Bimbingan Konseling" icon="counsel" :active="request()->routeIs('admin.bk-*')">
                                    <x-layouts.sidebar-link href="{{ route('admin.bk-records.index') }}" :active="request()->routeIs('admin.bk-records.*')">Catatan BK</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.bk-categories.index') }}" :active="request()->routeIs('admin.bk-categories.*')">Kategori BK</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Master Data --}}
                                <x-layouts.sidebar-dropdown label="Master Data" icon="database" :active="$masterActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.academic-years.index') }}" :active="request()->routeIs('admin.academic-years.*')">Tahun Ajaran</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.offices.index') }}" :active="request()->routeIs('admin.offices.*')">Kelola Kantor</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">Kelola User</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.roles.index') }}" :active="request()->routeIs('admin.roles.*')">Kelola Role</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.work-schedules.index') }}" :active="request()->routeIs('admin.work-schedules.*')">Jam Kerja</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>

                                {{-- Informasi --}}
                                <x-layouts.sidebar-dropdown label="Informasi" icon="megaphone" :active="$infoActive">
                                    <x-layouts.sidebar-link href="{{ route('admin.announcements.index') }}" :active="request()->routeIs('admin.announcements.*')">Informasi</x-layouts.sidebar-link>
                                    <x-layouts.sidebar-link href="{{ route('admin.account-switches.index') }}" :active="request()->routeIs('admin.account-switches.*')">Riwayat Ganti Akun</x-layouts.sidebar-link>
                                </x-layouts.sidebar-dropdown>
                            @else
                                <!-- Employee Menu -->
                                <x-layouts.sidebar-link href="{{ route('attendance.dashboard') }}" icon="home"
                                    :active="request()->routeIs('attendance.dashboard')">Beranda</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('attendance.selfie') }}" icon="camera"
                                    :active="request()->routeIs('attendance.selfie')">Absensi Selfie</x-layouts.sidebar-link>

                                <x-layouts.sidebar-link href="{{ route('attendance.index') }}" icon="list"
                                    :active="request()->routeIs('attendance.index')">Riwayat Absensi</x-layouts.sidebar-link>

                                @if (auth()->user()->activeHomeroomAssignment())
                                    <x-layouts.sidebar-link href="{{ route('attendance.my-class.index') }}" icon="users"
                                        :active="request()->routeIs('attendance.my-class.*')">Kelas Saya</x-layouts.sidebar-link>
                                @endif
                                @if (auth()->user()->activeHomeroomAssignment())
                                    <x-layouts.sidebar-link href="{{ route('attendance.referrals.mine') }}" icon="send"
                                        :active="request()->routeIs('attendance.referrals.mine')">Rujukan Saya</x-layouts.sidebar-link>
                                @endif
                                @if (auth()->user()->is_bk_counselor && in_array(auth()->user()->office?->school_level, ['mi', 'smp'], true))
                                    <x-layouts.sidebar-link href="{{ route('attendance.referrals.queue') }}" icon="inbox"
                                        :active="request()->routeIs('attendance.referrals.queue')">Antrean Rujukan</x-layouts.sidebar-link>
                                @endif
                                @if (auth()->user()->is_student_affairs_officer && in_array(auth()->user()->office?->school_level, ['mi', 'smp'], true))
                                    <x-layouts.sidebar-link href="{{ route('attendance.kesiswaan.index') }}" icon="users"
                                        :active="request()->routeIs('attendance.kesiswaan.*')">Kesiswaan @if(auth()->user()->unreadNotifications()->where('type','like','%StudentReferral%')->count()) ({{ auth()->user()->unreadNotifications()->where('type','like','%StudentReferral%')->count() }}) @endif</x-layouts.sidebar-link>
                                @endif
                            @endif
                        </ul>
                    </nav>
                    @if ($isAdminUser && ($sidebarYear = \App\Models\AcademicYear::getActive()))
                        <div class="admin-sidebar-footer" x-show="sidebarOpen">
                            <span class="admin-sidebar-footer-label">Tahun ajaran aktif</span>
                            <span class="admin-sidebar-footer-value">{{ $sidebarYear->name }}</span>
                        </div>
                    @endif
                </div>
            </aside>
