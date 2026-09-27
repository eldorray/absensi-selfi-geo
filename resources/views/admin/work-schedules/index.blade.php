<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Jam Kerja"
            description="Pengaturan jadwal kerja dan toleransi absensi" />

        @if (!$activeYear)
            <div class="admin-alert-danger flex items-center gap-3 rounded-2xl p-4">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold">Belum ada tahun ajaran aktif. Aktifkan tahun ajaran dulu untuk mengatur jam kerja.</span>
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
                            <label for="before_check_in" class="admin-label">Sebelum Masuk (Menit) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                            <input id="before_check_in" type="number" name="before_check_in"
                                value="{{ $settings->before_check_in }}" class="admin-field p-2.5" @error('before_check_in') aria-invalid="true" aria-describedby="before_check_in-error" @enderror required>
                            @error('before_check_in')
                                <p id="before_check_in-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="after_check_in" class="admin-label">Sesudah Masuk (Menit) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                            <input id="after_check_in" type="number" name="after_check_in"
                                value="{{ $settings->after_check_in }}" class="admin-field p-2.5" @error('after_check_in') aria-invalid="true" aria-describedby="after_check_in-error" @enderror required>
                            @error('after_check_in')
                                <p id="after_check_in-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="late_limit" class="admin-label">Limit Sesudah Masuk (Menit) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                            <input id="late_limit" type="number" name="late_limit" value="{{ $settings->late_limit }}"
                                class="admin-field p-2.5" @error('late_limit') aria-invalid="true" aria-describedby="late_limit-error" @enderror required>
                            @error('late_limit')
                                <p id="late_limit-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="before_check_out" class="admin-label">Sebelum Pulang (Menit) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                            <input id="before_check_out" type="number" name="before_check_out"
                                value="{{ $settings->before_check_out }}" class="admin-field p-2.5" @error('before_check_out') aria-invalid="true" aria-describedby="before_check_out-error" @enderror required>
                            @error('before_check_out')
                                <p id="before_check_out-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="admin-divider">

                    <div class="mb-6">
                        <h3 class="text-sm font-bold">Denda Keterlambatan</h3>
                        <p class="admin-muted admin-hint mb-4">Denda dihitung dari menit telat setelah batas toleransi
                            (Sesudah Masuk). Hanya untuk status Terlambat.</p>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="fine_tier1_amount" class="admin-label">Denda Tier 1 (Rp) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                                <input id="fine_tier1_amount" type="number" name="fine_tier1_amount" min="0"
                                    value="{{ $settings->fine_tier1_amount }}" class="admin-field p-2.5" @error('fine_tier1_amount') aria-invalid="true" aria-describedby="fine_tier1_amount-error" @enderror required>
                                @error('fine_tier1_amount')
                                    <p id="fine_tier1_amount-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                                @enderror
                                <p class="admin-hint">Telat 1 s/d batas menit di bawah.</p>
                            </div>
                            <div>
                                <label for="fine_tier1_max_minutes" class="admin-label">Batas Menit Tier 1 <span aria-hidden="true" class="admin-text-danger">*</span></label>
                                <input id="fine_tier1_max_minutes" type="number" name="fine_tier1_max_minutes"
                                    min="1" value="{{ $settings->fine_tier1_max_minutes }}"
                                    class="admin-field p-2.5" @error('fine_tier1_max_minutes') aria-invalid="true" aria-describedby="fine_tier1_max_minutes-error" @enderror required>
                                @error('fine_tier1_max_minutes')
                                    <p id="fine_tier1_max_minutes-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                                @enderror
                                <p class="admin-hint">Telat &le; menit ini kena Tier 1, di atasnya Tier 2.</p>
                            </div>
                            <div>
                                <label for="fine_tier2_amount" class="admin-label">Denda Tier 2 (Rp) <span aria-hidden="true" class="admin-text-danger">*</span></label>
                                <input id="fine_tier2_amount" type="number" name="fine_tier2_amount" min="0"
                                    value="{{ $settings->fine_tier2_amount }}" class="admin-field p-2.5" @error('fine_tier2_amount') aria-invalid="true" aria-describedby="fine_tier2_amount-error" @enderror required>
                                @error('fine_tier2_amount')
                                    <p id="fine_tier2_amount-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                                @enderror
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
        <div class="admin-glass-panel overflow-hidden" x-data="{ expandedRow: null }">
            <div class="admin-panel-header flex-wrap gap-3">
                <span class="flex items-center gap-2">
                    <span class="admin-label">List Data Jam Kerja</span>
                    @if ($activeYear)
                        <span class="admin-chip">TA {{ $activeYear->name }}</span>
                    @endif
                    @if ($selectedOffice)
                        <span class="admin-chip">{{ $selectedOffice->name }}</span>
                    @endif
                </span>
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <svg class="admin-muted pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                        <label for="work-schedule-search" class="sr-only">Cari karyawan</label>
                        <input id="work-schedule-search" type="search" name="search" value="{{ $search }}"
                            placeholder="Cari nama / email..." class="admin-field !w-auto py-2.5 pl-9 pr-3 text-sm">
                    </div>
                    <label for="work-schedule-office-filter" class="sr-only">Filter kantor</label>
                    <select id="work-schedule-office-filter" name="office_id" class="admin-field !w-auto p-2.5 text-sm" onchange="this.form.submit()">
                        <option value="">Semua Kantor</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}" @selected($officeId == $office->id)>{{ $office->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="admin-button-secondary px-4 py-2.5 text-sm">Cari</button>
                </form>
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
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr class="cursor-pointer"
                                @click="expandedRow = expandedRow === {{ $user->id }} ? null : {{ $user->id }}">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <button type="button" class="mr-2 rounded"
                                            @click.stop="expandedRow = expandedRow === {{ $user->id }} ? null : {{ $user->id }}"
                                            :aria-expanded="(expandedRow === {{ $user->id }}).toString()"
                                            aria-controls="schedule-detail-{{ $user->id }}"
                                            aria-label="Tampilkan detail jadwal {{ $user->name }}">
                                            <svg class="admin-muted h-4 w-4 transition-transform"
                                                :class="{ 'rotate-90': expandedRow === {{ $user->id }} }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                        {{ $users->firstItem() + $loop->index }}
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
                                    <a href="{{ route('admin.work-schedules.edit', ['user' => $user] + request()->query()) }}"
                                        class="admin-row-action admin-row-action-edit" title="Edit jadwal kerja"
                                        aria-label="Edit jadwal kerja {{ $user->name }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
</svg></a>
                                </td>
                            </tr>
                            <!-- Expanded Row - Schedule Details -->
                            <tr id="schedule-detail-{{ $user->id }}" x-show="expandedRow === {{ $user->id }}" x-cloak x-transition.opacity>
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
                                                                    class="admin-status-success px-2.5 py-1 text-xs">Aktif</span>
                                                            @else
                                                                <span
                                                                    class="admin-status-neutral px-2.5 py-1 text-xs">Nonaktif</span>
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
                                    @if ($search !== '')
                                        <x-admin.empty-state icon="fas-magnifying-glass" title="Tidak ditemukan"
                                            hint="Tidak ada karyawan yang cocok dengan pencarian." />
                                    @else
                                        <x-admin.empty-state icon="fas-clock" title="Belum ada data karyawan"
                                            hint="Karyawan yang terdaftar akan muncul di sini beserta jadwal kerjanya." />
                                    @endif
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
