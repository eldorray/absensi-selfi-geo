<x-layouts.app>
    @php
        $presentOnTime = max($todayAttendances - $todayLate, 0);
        $notYet = max($totalEmployees - $todayAttendances, 0);
        $presentPct = $totalEmployees > 0 ? (int) round(($todayAttendances / $totalEmployees) * 100) : 0;
        $onTimePct = $totalEmployees > 0 ? ($presentOnTime / $totalEmployees) * 100 : 0;
        $latePct = $totalEmployees > 0 ? ($todayLate / $totalEmployees) * 100 : 0;
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };
    @endphp

    <div class="space-y-6">
        <!-- Hero: greeting + presence meter -->
        <div class="admin-glass-panel p-6 md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="admin-page-header min-w-0">
                    <span class="admin-kicker">Dashboard</span>
                    <h1 class="mt-1.5">{{ $greeting }}, {{ auth()->user()->name }}</h1>
                    <p class="mt-1.5 text-sm">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }} &middot; Ringkasan kehadiran hari ini
                    </p>
                </div>
                <div class="w-full lg:max-w-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <span class="admin-label">Kehadiran hari ini</span>
                        <span class="admin-chip admin-chip-time">{{ $todayAttendances }}/{{ $totalEmployees }} &middot; {{ $presentPct }}%</span>
                    </div>
                    <div class="admin-meter mt-1" role="img"
                        aria-label="{{ $presentOnTime }} hadir tepat waktu, {{ $todayLate }} terlambat, {{ $notYet }} belum absen">
                        <div class="admin-meter-seg admin-meter-success" style="width: {{ $onTimePct }}%"></div>
                        <div class="admin-meter-seg admin-meter-warning" style="width: {{ $latePct }}%"></div>
                    </div>
                    <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                        <span class="admin-muted inline-flex items-center gap-1.5">
                            <span class="admin-meter-dot admin-meter-success"></span>Tepat waktu {{ $presentOnTime }}
                        </span>
                        <span class="admin-muted inline-flex items-center gap-1.5">
                            <span class="admin-meter-dot admin-meter-warning"></span>Terlambat {{ $todayLate }}
                        </span>
                        <span class="admin-muted inline-flex items-center gap-1.5">
                            <span class="admin-meter-dot admin-meter-idle"></span>Belum absen {{ $notYet }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <x-admin.stat-card tone="indigo" label="Total Karyawan" :value="$totalEmployees">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card tone="violet" label="Total Kantor" :value="$totalOffices">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card tone="emerald" label="Absensi Hari Ini" :value="$todayAttendances">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card tone="amber" label="Terlambat Hari Ini" :value="$todayLate">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-admin.stat-card>
        </div>

        <!-- Quick links -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <a href="{{ route('admin.offices.index') }}"
                class="admin-glass-panel group flex items-center gap-4 p-6 transition-transform duration-300 hover:-translate-y-0.5">
                <span class="admin-stat-icon admin-tone-sky" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold">Kelola Kantor</span>
                    <span class="admin-muted mt-1 block text-xs">Tambah, edit, dan hapus lokasi kantor</span>
                </span>
                <svg class="admin-muted h-4 w-4 flex-none transition-transform group-hover:translate-x-0.5" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="admin-glass-panel group flex items-center gap-4 p-6 transition-transform duration-300 hover:-translate-y-0.5">
                <span class="admin-stat-icon admin-tone-emerald" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold">Kelola User</span>
                    <span class="admin-muted mt-1 block text-xs">Manajemen akun karyawan dan admin</span>
                </span>
                <svg class="admin-muted h-4 w-4 flex-none transition-transform group-hover:translate-x-0.5" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('admin.attendances.index') }}"
                class="admin-glass-panel group flex items-center gap-4 p-6 transition-transform duration-300 hover:-translate-y-0.5">
                <span class="admin-stat-icon admin-tone-amber" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold">Laporan Absensi</span>
                    <span class="admin-muted mt-1 block text-xs">Lihat semua rekap absensi</span>
                </span>
                <svg class="admin-muted h-4 w-4 flex-none transition-transform group-hover:translate-x-0.5" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <!-- Recent attendance -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-4">
                <span class="admin-label" style="margin-bottom: 0">Absensi Terbaru</span>
                <a href="{{ route('admin.attendances.index') }}"
                    class="admin-muted text-xs font-semibold hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3.5 text-left">Karyawan</th>
                            <th class="px-6 py-3.5 text-left">Kantor</th>
                            <th class="px-6 py-3.5 text-left">Status</th>
                            <th class="px-6 py-3.5 text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="text-left">
                        @forelse ($recentAttendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-avatar">
                                            <img src="{{ $attendance->image_url }}" alt="Selfie {{ $attendance->user->name }}">
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold">{{ $attendance->user->name }}</span>
                                            <span class="admin-muted block text-xs">{{ $attendance->user->email }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $attendance->user->office?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="@if ($attendance->status->value === 'present') admin-status-success @elseif ($attendance->status->value === 'late') admin-status-warning @else admin-status-neutral @endif px-2.5 py-1 text-[10px] uppercase tracking-wider">
                                        {{ $attendance->status->label() }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="admin-chip admin-chip-time">{{ $attendance->created_at->format('H:i') }} WIB</span>
                                    <span class="admin-muted ml-2 text-xs">{{ $attendance->created_at->format('d M Y') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-admin.empty-state icon="fas-clipboard-list" title="Belum ada absensi hari ini"
                                        hint="Absensi karyawan yang masuk hari ini akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
