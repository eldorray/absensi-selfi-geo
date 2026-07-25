<x-layouts.app>
    <div class="mx-auto max-w-3xl space-y-6">
        <x-admin.page-header kicker="Master Data · Tahun Ajaran {{ $activeYear->name }}" title="Edit Jadwal Kerja"
            description="{{ $user->name }} - {{ $user->office?->name ?? 'Tidak ada kantor' }}">
            <div class="flex flex-wrap items-center gap-2">
                @if ($previousYear)
                    <form method="POST" action="{{ route('admin.work-schedules.copy-previous', ['user' => $user] + request()->query()) }}" x-data="{}"
                        @submit.prevent="$dispatch('admin-confirm', {
                            title: 'Salin Jadwal',
                            message: 'Salin jadwal dari tahun ajaran {{ $previousYear->name }} ke {{ $activeYear->name }}? Jadwal {{ $activeYear->name }} yang ada akan ditimpa.',
                            confirmText: 'Salin',
                            variant: 'primary',
                            form: $el,
                        })">
                        @csrf
                        <button type="submit"
                            class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            Salin dari {{ $previousYear->name }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.work-schedules.index', request()->query()) }}"
                    class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </x-admin.page-header>

        <!-- Form -->
        <div class="admin-glass-panel p-6 md:p-8">
            <form method="POST" action="{{ route('admin.work-schedules.update', ['user' => $user] + request()->query()) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    @foreach ($days as $key => $label)
                        @php $schedule = $schedules[$key] ?? null; @endphp
                        <div class="flex items-center gap-4 rounded-2xl border p-4"
                            style="border-color: var(--admin-border-soft); background: var(--admin-glass-soft)">
                            <!-- Day Name -->
                            <div class="w-24 text-sm font-bold">{{ $label }}</div>

                            <!-- Check In Time -->
                            <div class="flex-1">
                                <label for="check-in-{{ $key }}" class="admin-label">Jam Masuk</label>
                                <input id="check-in-{{ $key }}" type="time"
                                    name="schedules[{{ $key }}][check_in_time]"
                                    value="{{ $schedule ? \Carbon\Carbon::parse($schedule->check_in_time)->format('H:i') : '07:00' }}"
                                    class="admin-field p-2">
                            </div>

                            <!-- Check Out Time -->
                            <div class="flex-1">
                                <label for="check-out-{{ $key }}" class="admin-label">Jam Pulang</label>
                                <input id="check-out-{{ $key }}" type="time"
                                    name="schedules[{{ $key }}][check_out_time]"
                                    value="{{ $schedule ? \Carbon\Carbon::parse($schedule->check_out_time)->format('H:i') : '16:00' }}"
                                    class="admin-field p-2">
                            </div>

                            <!-- Active Toggle -->
                            <div class="flex flex-col items-center">
                                <span class="admin-label">Aktif</span>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="schedules[{{ $key }}][is_active]"
                                        value="1" {{ !$schedule || $schedule->is_active ? 'checked' : '' }}
                                        class="sr-only peer admin-toggle">
                                    <div
                                        class="peer h-6 w-11 rounded-full after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full admin-toggle-track">
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($errors->any())
                    <div class="admin-alert-danger mt-6 rounded-2xl p-4">
                        <ul class="list-inside list-disc space-y-1 text-sm font-semibold">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Submit -->
                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.work-schedules.index', request()->query()) }}"
                        class="admin-button-secondary px-4 py-2 text-sm">
                        Batal
                    </a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
