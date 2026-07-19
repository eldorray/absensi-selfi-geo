<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Jam Kerja"
            description="Pengaturan jadwal kerja dan toleransi absensi" />

        <!-- Messages -->
        @if (session('success'))
            <div class="admin-alert-success flex items-center gap-3 rounded-2xl p-4">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tolerance Settings Card -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header">
                <span class="admin-label">Toleransi Jam Kerja</span>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.work-schedules.settings') }}">
                    @csrf
                    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                        <div>
                            <label for="before_check_in" class="admin-label">Sebelum Masuk (Menit)</label>
                            <input id="before_check_in" type="number" name="before_check_in"
                                value="{{ $settings->before_check_in }}" class="admin-field p-2.5">
                        </div>
                        <div>
                            <label for="after_check_in" class="admin-label">Sesudah Masuk (Menit)</label>
                            <input id="after_check_in" type="number" name="after_check_in"
                                value="{{ $settings->after_check_in }}" class="admin-field p-2.5">
                        </div>
                        <div>
                            <label for="late_limit" class="admin-label">Limit Sesudah Masuk (Menit)</label>
                            <input id="late_limit" type="number" name="late_limit" value="{{ $settings->late_limit }}"
                                class="admin-field p-2.5">
                        </div>
                        <div>
                            <label for="before_check_out" class="admin-label">Sebelum Pulang (Menit)</label>
                            <input id="before_check_out" type="number" name="before_check_out"
                                value="{{ $settings->before_check_out }}" class="admin-field p-2.5">
                        </div>
                    </div>

                    <hr class="admin-divider">

                    <div class="mb-6">
                        <h3 class="text-sm font-bold">Denda Keterlambatan</h3>
                        <p class="admin-muted admin-hint mb-4">Denda dihitung dari menit telat setelah batas toleransi
                            (Sesudah Masuk). Hanya untuk status Terlambat.</p>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="fine_tier1_amount" class="admin-label">Denda Tier 1 (Rp)</label>
                                <input id="fine_tier1_amount" type="number" name="fine_tier1_amount" min="0"
                                    value="{{ $settings->fine_tier1_amount }}" class="admin-field p-2.5">
                                <p class="admin-hint">Telat 1 s/d batas menit di bawah.</p>
                            </div>
                            <div>
                                <label for="fine_tier1_max_minutes" class="admin-label">Batas Menit Tier 1</label>
                                <input id="fine_tier1_max_minutes" type="number" name="fine_tier1_max_minutes"
                                    min="1" value="{{ $settings->fine_tier1_max_minutes }}"
                                    class="admin-field p-2.5">
                                <p class="admin-hint">Telat &le; menit ini kena Tier 1, di atasnya Tier 2.</p>
                            </div>
                            <div>
                                <label for="fine_tier2_amount" class="admin-label">Denda Tier 2 (Rp)</label>
                                <input id="fine_tier2_amount" type="number" name="fine_tier2_amount" min="0"
                                    value="{{ $settings->fine_tier2_amount }}" class="admin-field p-2.5">
                                <p class="admin-hint">Telat di atas batas menit Tier 1.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="require_check_in" value="1"
                                {{ $settings->require_check_in ? 'checked' : '' }} class="admin-checkbox rounded">
                            <span class="admin-muted ml-2 text-sm">
                                Wajib Absen Masuk - Jika dicentang, maka absen pulang harus absen masuk terlebih dahulu.
                            </span>
                        </label>
                        <button type="submit" class="admin-button-success px-4 py-2 text-sm">
                            Update Toleransi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Data Jam Kerja -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header">
                <span class="admin-label">List Data Jam Kerja</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">Kantor</th>
                            <th class="px-6 py-4 text-left">Jadwal Aktif</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ expandedRow: null }">
                        @forelse ($users as $index => $user)
                            <tr class="cursor-pointer"
                                @click="expandedRow = expandedRow === {{ $user->id }} ? null : {{ $user->id }}">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <svg class="admin-muted mr-2 h-4 w-4 transition-transform"
                                            :class="{ 'rotate-90': expandedRow === {{ $user->id }} }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="text-sm font-bold">{{ $user->name }}</p>
                                    <p class="admin-muted text-xs">{{ $user->email }}</p>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $user->office?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="admin-status-info px-2.5 py-1 text-xs">
                                        {{ $user->workSchedules->where('is_active', true)->count() }} Hari
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right" @click.stop>
                                    <a href="{{ route('admin.work-schedules.edit', $user) }}"
                                        class="admin-button-primary admin-icon-action inline-flex items-center gap-1.5 px-3 text-xs">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
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
                            <tr x-show="expandedRow === {{ $user->id }}" x-collapse>
                                <td colspan="5" class="px-6 py-4">
                                    <div class="overflow-x-auto">
                                        <table class="admin-table w-full text-sm">
                                            <thead>
                                                <tr class="text-left">
                                                    <th class="pb-2">Hari</th>
                                                    <th class="pb-2">Jam Masuk</th>
                                                    <th class="pb-2">Jam Pulang</th>
                                                    <th class="pb-2">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $day)
                                                    @php $schedule = $user->workSchedules->firstWhere('day', $day); @endphp
                                                    <tr>
                                                        <td class="py-2 text-sm font-semibold">{{ ucfirst($day) }}
                                                        </td>
                                                        <td class="py-2">
                                                            <span class="admin-chip admin-chip-time">
                                                                {{ $schedule ? \Carbon\Carbon::parse($schedule->check_in_time)->format('H:i') : '07:00' }}
                                                            </span>
                                                        </td>
                                                        <td class="py-2">
                                                            <span class="admin-chip admin-chip-time">
                                                                {{ $schedule ? \Carbon\Carbon::parse($schedule->check_out_time)->format('H:i') : '16:00' }}
                                                            </span>
                                                        </td>
                                                        <td class="py-2">
                                                            @if ($schedule && $schedule->is_active)
                                                                <span
                                                                    class="admin-status-success px-2.5 py-1 text-xs">Active</span>
                                                            @else
                                                                <span
                                                                    class="admin-status-neutral px-2.5 py-1 text-xs">Inactive</span>
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
                                <td colspan="5">
                                    <x-admin.empty-state icon="fas-clock" title="Belum ada data karyawan"
                                        hint="Karyawan yang terdaftar akan muncul di sini beserta jadwal kerjanya." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="admin-panel-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
