<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Komunikasi" title="Tambah Informasi"
            description="Buat kartu informasi baru untuk guru" />

        <div class="admin-glass-panel p-6 md:p-8">
            <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @include('admin.announcements._form')

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.announcements.index') }}"
                        class="admin-button-secondary px-4 py-2 text-sm">Batal</a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
