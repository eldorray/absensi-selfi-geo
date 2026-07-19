<x-layouts.app>
    <div class="mx-auto max-w-4xl space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Detail Absensi" description="Bukti selfie dan data lokasi absensi">
            <a href="{{ route('admin.attendances.index') }}"
                class="admin-button-secondary px-3 py-2 inline-flex items-center gap-1.5 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Selfie Image -->
            <div class="admin-glass-panel overflow-hidden">
                <div class="admin-panel-header">
                    <span class="admin-label">Foto Selfie</span>
                    <span
                        class="{{ $attendance->status->value === 'present' ? 'admin-status-success' : 'admin-status-warning' }} px-2.5 py-1 text-xs">
                        {{ $attendance->status->label() }}
                    </span>
                </div>
                <img src="{{ $attendance->image_url }}" alt="Selfie Absensi {{ $attendance->user->name }}"
                    class="aspect-square w-full object-cover">
            </div>

            <!-- Details -->
            <div class="admin-glass-panel p-6">
                <dl class="admin-dl">
                    <div>
                        <dt>Karyawan</dt>
                        <dd class="font-semibold">{{ $attendance->user->name }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ $attendance->user->email }}</dd>
                    </div>
                    <div>
                        <dt>Kantor</dt>
                        <dd>{{ $attendance->user->office?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Koordinat</dt>
                        <dd class="font-mono text-xs">
                            <a href="https://www.google.com/maps?q={{ $attendance->check_in_lat }},{{ $attendance->check_in_long }}"
                                target="_blank" rel="noopener" class="hover:underline">
                                {{ number_format($attendance->check_in_lat, 8) }},
                                {{ number_format($attendance->check_in_long, 8) }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt>Jarak dari Kantor</dt>
                        <dd>{{ number_format($attendance->distance_meters, 0) }} meter</dd>
                    </div>
                    <div>
                        <dt>Waktu Check-in</dt>
                        <dd>
                            <span
                                class="admin-chip admin-chip-time">{{ $attendance->created_at->format('H:i:s') }}</span>
                            <span
                                class="admin-muted ml-2 text-xs">{{ $attendance->created_at->format('d M Y') }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>
