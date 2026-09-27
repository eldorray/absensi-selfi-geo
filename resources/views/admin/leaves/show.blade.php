<x-layouts.app title="Detail Pengajuan">
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Detail Pengajuan"
            description="Tinjau dan proses pengajuan perizinan">
            <a href="{{ route('admin.leaves.index') }}" class="admin-button-secondary px-4 text-sm">
                <x-admin.icon name="chevron-left" size="16" />
                Kembali
            </a>
        </x-admin.page-header>

        <section aria-labelledby="detail-pengajuan-judul" class="admin-glass-panel max-w-4xl p-6 md:p-8">
            @include('admin.leaves._detail')
        </section>
    </div>
</x-layouts.app>
