<x-layouts.app>
    <div class="space-y-6" data-student-module="material-you-3">
        <x-admin.page-header kicker="Master Akademik" title="Data Siswa {{ strtoupper($schoolLevel) }}" description="Kelola siswa manual dan sinkronkan dari Data Induk." :count="$students->total().' siswa'">
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.students.sync', $schoolLevel) }}" x-data="{}" @submit.prevent="$dispatch('admin-confirm', { title: 'Sinkronisasi Data Siswa', message: 'Data API akan memperbarui field identitas dan akademik siswa yang cocok. Lanjutkan?', confirmText: 'Sinkronkan', variant: 'primary', form: $el })">@csrf<button class="admin-button-secondary px-4 py-2 text-sm"><i class="fas fa-rotate mr-2"></i>Sync Data Induk</button></form>
                <a href="{{ route('admin.students.create', $schoolLevel) }}" class="admin-button-primary px-4 py-2 text-sm"><i class="fas fa-plus mr-2"></i>Tambah Siswa</a>
            </div>
        </x-admin.page-header>
        @if(session('success'))<div class="admin-alert-success rounded-2xl p-4 text-sm font-semibold">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="admin-alert-danger rounded-2xl p-4 text-sm font-semibold">{{ session('error') }}</div>@endif
        <form class="admin-glass-panel grid gap-3 p-4 md:grid-cols-[1fr_220px_auto]" method="GET">
            <input name="search" value="{{ request('search') }}" class="admin-field p-3" placeholder="Cari nama, NISN, atau NIK">
            <select name="school_class_id" class="admin-field p-3"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('school_class_id') == $class->id)>{{ $class->name }}</option>@endforeach</select>
            <button class="admin-button-secondary px-5 py-3 text-sm">Terapkan</button>
        </form>
        <div class="admin-glass-panel overflow-hidden"><div class="overflow-x-auto"><table class="admin-table w-full"><thead><tr><th class="px-5 py-4 text-left">Siswa</th><th class="px-5 py-4 text-left">Identitas</th><th class="px-5 py-4 text-left">Kelas</th><th class="px-5 py-4 text-left">Status</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead><tbody>
        @forelse($students as $student)<tr><td class="px-5 py-4"><div class="font-bold">{{ $student->nama_lengkap }}</div><div class="admin-muted mt-1 text-xs">{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : ($student->jenis_kelamin === 'P' ? 'Perempuan' : 'Belum diisi') }} · {{ strtoupper($student->source) }}</div></td><td class="px-5 py-4 text-sm"><div>NISN: {{ $student->nisn ?: '—' }}</div><div class="admin-muted">NIK: {{ $student->nik ?: '—' }}</div></td><td class="px-5 py-4 text-sm">{{ $student->schoolClass?->name ?: ($student->tingkat_rombel ?: '—') }}</td><td class="px-5 py-4"><span class="admin-status-info px-3 py-1 text-xs">{{ $student->status }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.students.edit', [$schoolLevel, $student]) }}" class="admin-row-action admin-row-action-edit" title="Edit siswa" aria-label="Edit siswa {{ $student->nama_lengkap }}"><i class="fas fa-pen" aria-hidden="true"></i></a><form method="POST" action="{{ route('admin.students.destroy', [$schoolLevel, $student]) }}" x-data="{}" @submit.prevent="$dispatch('admin-confirm', { title: 'Hapus Siswa', message: 'Data siswa akan dihapus permanen.', confirmText: 'Hapus', variant: 'danger', form: $el })">@csrf @method('DELETE')<button class="admin-row-action admin-row-action-delete" title="Hapus siswa" aria-label="Hapus siswa {{ $student->nama_lengkap }}"><i class="fas fa-trash" aria-hidden="true"></i></button></form></div></td></tr>
        @empty<tr><td colspan="5"><x-admin.empty-state icon="fas-user-graduate" title="Belum ada siswa" hint="Tambahkan siswa manual atau sinkronkan dari Data Induk." /></td></tr>@endforelse
        </tbody></table></div>@if($students->hasPages())<div class="admin-panel-footer">{{ $students->links() }}</div>@endif</div>
    </div>
</x-layouts.app>
