<x-layouts.app :title="'Riwayat Ganti Akun'">
    <div class="space-y-6">
        <x-admin.page-header kicker="Keamanan" title="Riwayat Ganti Akun"
            description="Catatan setiap perpindahan akun cepat" :count="$logs->total() . ' entri'" />

        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Waktu</th>
                            <th class="px-6 py-4 text-left">Dari Akun</th>
                            <th class="px-6 py-4 text-left">Ke Akun</th>
                            <th class="px-6 py-4 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $log->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold">{{ $log->fromUser?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold">{{ $log->toUser?->name ?? '-' }}</td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 font-mono text-xs">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-admin.empty-state icon="fas-right-left" title="Belum ada riwayat"
                                        hint="Perpindahan akun cepat akan tercatat di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="admin-panel-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
