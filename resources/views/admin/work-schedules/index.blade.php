<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Jam Kerja</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pengaturan jadwal kerja dan toleransi absensi
                </p>
            </div>
        </div>

        <!-- Messages -->
        @if (session('success'))
            <div
                class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl p-4 flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-green-700 dark:text-green-300">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tolerance Settings Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-sky-50 dark:bg-sky-900/30">
                <h2 class="text-lg font-semibold text-sky-700 dark:text-sky-300">Toleransi Jam Kerja</h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.work-schedules.settings') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sebelum Masuk (Menit)
                            </label>
                            <input type="number" name="before_check_in" value="{{ $settings->before_check_in }}"
                                class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sesudah Masuk (Menit)
                            </label>
                            <input type="number" name="after_check_in" value="{{ $settings->after_check_in }}"
                                class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Limit Sesudah Masuk (Menit)
                            </label>
                            <input type="number" name="late_limit" value="{{ $settings->late_limit }}"
                                class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sebelum Pulang (Menit)
                            </label>
                            <input type="number" name="before_check_out" value="{{ $settings->before_check_out }}"
                                class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="require_check_in" value="1"
                                {{ $settings->require_check_in ? 'checked' : '' }}
                                class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Wajib Absen Masuk - Jika dicentang, maka absen pulang harus absen masuk terlebih dahulu.
                            </span>
                        </label>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">
                            Update Toleransi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Data Jam Kerja -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">List Data Jam Kerja</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                No.</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Nama</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Kantor</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Jadwal Aktif</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-data="{ expandedRow: null }">
                        @forelse($users as $index => $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
                                @click="expandedRow = expandedRow === {{ $user->id }} ? null : {{ $user->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 transition-transform"
                                            :class="{ 'rotate-90': expandedRow === {{ $user->id }} }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->office?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $user->workSchedules->where('is_active', true)->count() }} Hari
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right" @click.stop>
                                    <a href="{{ route('admin.work-schedules.edit', $user) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-sky-100 hover:bg-sky-200 text-sky-700 font-medium rounded-lg text-sm transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            <!-- Expanded Row - Schedule Details -->
                            <tr x-show="expandedRow === {{ $user->id }}" x-collapse
                                class="bg-gray-50 dark:bg-gray-700/30">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="text-left text-gray-500 dark:text-gray-400">
                                                    <th class="pb-2 font-semibold">Hari</th>
                                                    <th class="pb-2 font-semibold">Jam Masuk</th>
                                                    <th class="pb-2 font-semibold">Jam Pulang</th>
                                                    <th class="pb-2 font-semibold">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $day)
                                                    @php $schedule = $user->workSchedules->firstWhere('day', $day); @endphp
                                                    <tr>
                                                        <td class="py-2 font-medium text-gray-900 dark:text-white">
                                                            {{ ucfirst($day) }}</td>
                                                        <td class="py-2 text-gray-600 dark:text-gray-300">
                                                            {{ $schedule ? \Carbon\Carbon::parse($schedule->check_in_time)->format('H:i') : '07:00' }}
                                                        </td>
                                                        <td class="py-2 text-gray-600 dark:text-gray-300">
                                                            {{ $schedule ? \Carbon\Carbon::parse($schedule->check_out_time)->format('H:i') : '16:00' }}
                                                        </td>
                                                        <td class="py-2">
                                                            @if ($schedule && $schedule->is_active)
                                                                <span
                                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
                                                            @else
                                                                <span
                                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Inactive</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
