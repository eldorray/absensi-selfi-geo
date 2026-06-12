<x-layouts.app>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white font-display">Dashboard Admin</h1>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 font-semibold font-outfit uppercase tracking-wider">
                    Selamat datang kembali, <span class="text-indigo-500">{{ auth()->user()->name }}</span>!
                </p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Employees -->
            <div class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-850 hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01]">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Total Karyawan</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white font-display mt-1.5">{{ $totalEmployees }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Offices -->
            <div class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-855 hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01]">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Total Kantor</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white font-display mt-1.5">{{ $totalOffices }}</p>
                    </div>
                </div>
            </div>

            <!-- Today Attendances -->
            <div class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-860 hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01]">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Absensi Hari Ini</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white font-display mt-1.5">{{ $todayAttendances }}</p>
                    </div>
                </div>
            </div>

            <!-- Today Late -->
            <div class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-865 hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01]">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Terlambat Hari Ini</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white font-display mt-1.5">{{ $todayLate }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('admin.offices.index') }}" class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-800/80 hover:shadow-xl hover:scale-[1.01] transition-all duration-300 group">
                <div class="flex items-center">
                    <div class="p-3.5 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-500/20 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="font-bold text-slate-800 dark:text-white font-display text-sm">Kelola Kantor</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold font-outfit uppercase mt-1.5">Tambah, edit, hapus lokasi kantor</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.users.index') }}" class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-800/80 hover:shadow-xl hover:scale-[1.01] transition-all duration-300 group">
                <div class="flex items-center">
                    <div class="p-3.5 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500/20 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="font-bold text-slate-800 dark:text-white font-display text-sm">Kelola User</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold font-outfit uppercase mt-1.5">Manajemen karyawan & admin</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.attendances.index') }}" class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg p-6 border border-slate-100 dark:border-slate-800/80 hover:shadow-xl hover:scale-[1.01] transition-all duration-300 group">
                <div class="flex items-center">
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:bg-amber-500/20 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 text-left leading-none">
                        <p class="font-bold text-slate-800 dark:text-white font-display text-sm">Laporan Absensi</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold font-outfit uppercase mt-1.5">Lihat semua rekap absensi</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Attendances -->
        <div class="bg-white dark:bg-gray-800 rounded-[24px] shadow-lg overflow-hidden border border-slate-100 dark:border-slate-800/80">
            <div class="px-6 py-4.5 border-b border-gray-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-gray-900/10 text-left">
                <h2 class="text-xs font-bold text-slate-800 dark:text-white font-display uppercase tracking-wider">Absensi Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4.5 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Karyawan</th>
                            <th class="px-6 py-4.5 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Kantor</th>
                            <th class="px-6 py-4.5 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Status</th>
                            <th class="px-6 py-4.5 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-left">
                        @forelse($recentAttendances as $attendance)
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <td class="px-6 py-4.5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full border border-slate-100 dark:border-slate-800 p-[1px] shadow-sm flex-none">
                                            <img src="{{ $attendance->image_url }}" alt="Selfie" class="w-full h-full rounded-full object-cover">
                                        </div>
                                        <div class="ml-3.5 leading-none">
                                            <p class="text-xs font-bold text-slate-800 dark:text-white font-display">{{ $attendance->user->name }}</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-semibold font-outfit">{{ $attendance->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 font-semibold font-outfit">
                                    {{ $attendance->user->office?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-full @if($attendance->status->value === 'present') theme-status-ok-card theme-status-ok-text @elseif($attendance->status->value === 'late') theme-status-late-card theme-status-late-text @else bg-slate-400/10 text-slate-500 border border-slate-500/20 @endif">
                                        {{ $attendance->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-xs text-slate-400 dark:text-slate-500 font-bold font-outfit uppercase">
                                    {{ $attendance->created_at->format('d M Y, H:i') }} WIB
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-xs text-slate-400 dark:text-slate-500 font-bold font-outfit">
                                    Belum ada data absensi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
