<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Data Siswa" title="Penugasan Wali Kelas" description="Satu guru untuk satu kelas pada setiap tahun ajaran" />

        @if (session('success')) <div class="admin-alert-success p-4">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="admin-alert-danger p-4">{{ session('error') }}</div> @endif

        <div class="admin-glass-panel p-5">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                <div><label class="admin-label">Tahun Ajaran</label><select name="academic_year_id" class="admin-field p-2.5">@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected($selectedYear?->id === $year->id)>{{ $year->name }}</option>@endforeach</select></div>
                <div><label class="admin-label">Jenjang</label><select name="school_level" class="admin-field p-2.5"><option value="mi" @selected($schoolLevel === 'mi')>MI</option><option value="smp" @selected($schoolLevel === 'smp')>SMP</option></select></div>
                <button class="admin-button-primary px-5 py-2.5">Tampilkan</button>
            </form>
        </div>

        @if($selectedYear && $previousYear)
            <div class="admin-glass-panel flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between">
                <div><p class="font-bold">Salin dari {{ $previousYear->name }}</p><p class="admin-muted text-sm">Tinjau penugasan valid dan konflik sebelum menerapkannya.</p></div>
                <a href="{{ route('admin.homeroom-assignments.index', ['academic_year_id' => $selectedYear->id, 'school_level' => $schoolLevel, 'preview_copy' => 1]) }}" class="admin-button-secondary px-4 py-2.5 text-sm">Pratinjau penyalinan</a>
            </div>
        @endif

        @if($copyPreview->isNotEmpty())
            <section class="admin-glass-panel overflow-hidden">
                <div class="admin-panel-header"><div><p class="admin-label">Pratinjau Penyalinan</p><p class="admin-muted mt-1 text-sm">{{ $copyPreview->where('valid', true)->count() }} siap disalin · {{ $copyPreview->where('valid', false)->count() }} perlu diperbaiki</p></div></div>
                <div class="divide-y admin-divider">@foreach($copyPreview as $item)<div class="flex items-center justify-between gap-4 px-5 py-3"><div><p class="text-sm font-bold">{{ $item['assignment']->schoolClass->name }}</p><p class="admin-muted text-xs">{{ $item['assignment']->teacher->name }}</p></div><span class="{{ $item['valid'] ? 'admin-status-success' : 'admin-status-warning' }} px-2.5 py-1 text-xs">{{ $item['valid'] ? 'Siap disalin' : $item['reason'] }}</span></div>@endforeach</div>
                @if($copyPreview->where('valid', true)->isNotEmpty())<form method="POST" action="{{ route('admin.homeroom-assignments.copy-previous') }}" class="flex justify-end border-t admin-divider p-4">@csrf<input type="hidden" name="academic_year_id" value="{{ $selectedYear->id }}"><button class="admin-button-primary px-5 py-2.5 text-sm">Terapkan penugasan valid</button></form>@endif
            </section>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse($classes as $class)
                @php($assignment = $class->homeroomAssignments->first())
                <section class="admin-glass-panel p-5">
                    <div class="flex items-start justify-between gap-4"><div><span class="admin-kicker">{{ strtoupper($schoolLevel) }} · Kelas</span><h2 class="mt-1 text-lg font-bold">{{ $class->name }}</h2><p class="admin-muted mt-1 text-sm">{{ $class->students_count }} siswa aktif</p></div><span class="admin-chip">{{ $assignment ? 'Sudah ditetapkan' : 'Belum ada wali' }}</span></div>
                    <form method="POST" action="{{ $assignment ? route('admin.homeroom-assignments.update', $assignment) : route('admin.homeroom-assignments.store') }}" class="mt-4 space-y-3">@csrf @if($assignment) @method('PUT') @endif
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYear?->id }}"><input type="hidden" name="school_class_id" value="{{ $class->id }}">
                        <div><label class="admin-label">Wali Kelas</label><select name="teacher_id" class="admin-field p-2.5" required><option value="">Pilih guru sesuai jenjang</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($assignment?->teacher_id === $teacher->id)>{{ $teacher->name }} · {{ $teacher->office?->name }}</option>@endforeach</select></div>
                        <div class="flex justify-end gap-2"><button class="admin-button-primary px-4 py-2 text-sm">{{ $assignment ? 'Perbarui' : 'Tetapkan' }}</button></div>
                    </form>
                    @if($assignment)<form method="POST" action="{{ route('admin.homeroom-assignments.destroy', $assignment) }}" class="mt-2 text-right">@csrf @method('DELETE')<button class="admin-text-danger text-sm font-semibold">Hapus penugasan</button></form>@endif
                </section>
            @empty
                <div class="admin-glass-panel p-8 text-center lg:col-span-2"><p class="font-bold">Belum ada kelas</p><p class="admin-muted text-sm">Tambahkan kelas pada jenjang ini terlebih dahulu.</p></div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
