<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Kelas {{ strtoupper($schoolLevel) }}" title="Edit Kelas"
            description="Perbarui data kelas dan wali kelas tahun ajaran aktif." />

        <form method="POST" action="{{ route('admin.school-classes.update', [$schoolLevel, $schoolClass]) }}"
            class="admin-glass-panel space-y-6 p-5 md:p-8">
            @csrf
            @method('PUT')

            @include('admin.school-classes._form', ['hideActions' => true])

            @if ($activeYear)
                <hr class="admin-divider">
                <section class="space-y-4">
                    <div>
                        <p class="admin-kicker">Pengaturan Wali Kelas</p>
                        <h2 class="mt-1 text-lg font-bold">Tahun Ajaran {{ $activeYear->name }}</h2>
                        <p class="admin-muted mt-1 text-sm">Hanya guru dari kantor berjenjang {{ strtoupper($schoolLevel) }} yang dapat dipilih.</p>
                    </div>
                    <div>
                        <label for="teacher_id" class="admin-label">Guru Wali</label>
                        <select id="teacher_id" name="teacher_id" class="admin-field p-3">
                            <option value="">Belum ditetapkan</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $assignment?->teacher_id) === $teacher->id)>
                                    {{ $teacher->name }} · {{ $teacher->office?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </section>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.school-classes.index', $schoolLevel) }}" class="admin-button-secondary px-5 py-3 text-sm">Batal</a>
                <button class="admin-button-primary px-5 py-3 text-sm">Simpan Kelas dan Wali Kelas</button>
            </div>
        </form>
    </div>
</x-layouts.app>
