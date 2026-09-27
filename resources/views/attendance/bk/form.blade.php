<x-layouts.mobile :title="$record->exists ? 'Edit Catatan BK' : 'Catatan BK Baru'"
    :backUrl="$record->exists ? route('attendance.bk.show', $record) : route('attendance.bk.index')">
    <form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route('attendance.bk.update', $record) : route('attendance.bk.store') }}" class="flex flex-col gap-4">
        @csrf
        @if ($record->exists)
            @method('PUT')
        @endif
        @if (! $record->exists && $record->student_referral_id)
            <input type="hidden" name="student_referral_id" value="{{ $record->student_referral_id }}">
        @endif

        @if ($errors->any())
            <x-guru.notice tone="error" title="Catatan belum tersimpan" role="alert">{{ $errors->first() }}</x-guru.notice>
        @endif

        <x-guru.card as="div">
            <h2 class="g-h2">Siswa</h2>
            @if (! $record->exists && $record->student_referral_id)
                <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                <x-guru.notice tone="ok">Siswa dari rujukan: <strong class="inline">{{ $referral->student->nama_lengkap }}</strong>. Siswa dan rujukan dikunci oleh server.</x-guru.notice>
            @else
                @include('attendance.bk.partials.student-combobox')
            @endif
            @include('attendance.bk.partials.related-students-combobox')
        </x-guru.card>

        <x-guru.card as="div">
            <h2 class="g-h2">Catatan</h2>
            <x-guru.field label="Jenis" for="record_type">
                <select id="record_type" name="record_type" class="g-input">
                    <option value="violation" @selected(old('record_type', $record->record_type) === 'violation')>Pelanggaran</option>
                    <option value="counseling" @selected(old('record_type', $record->record_type) === 'counseling')>Konseling</option>
                </select>
            </x-guru.field>
            <x-guru.field label="Kategori" for="category_id">
                <select id="category_id" name="category_id" class="g-input">
                    <option value="">Lainnya</option>
                    @foreach ($categories as $c)
                        <option data-type="{{ $c->record_type }}" data-severity="{{ $c->default_severity }}" value="{{ $c->id }}" @selected(old('category_id', $record->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Topik lainnya" for="custom_topic">
                <input id="custom_topic" name="custom_topic" value="{{ old('custom_topic', $record->custom_topic) }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Waktu" for="occurred_at" :required="true">
                <input id="occurred_at" type="datetime-local" required name="occurred_at" value="{{ old('occurred_at', $record->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="g-input">
            </x-guru.field>

            <div id="violation" class="flex flex-col gap-4">
                <x-guru.field label="Tingkat" for="severity">
                    <select id="severity" name="severity" class="g-input">
                        @foreach (['light' => 'Ringan', 'medium' => 'Sedang', 'heavy' => 'Berat'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('severity', $record->severity) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </x-guru.field>
                <x-guru.field label="Kronologi" for="chronology">
                    <textarea id="chronology" name="chronology" class="g-input">{{ old('chronology', $record->chronology) }}</textarea>
                </x-guru.field>
                <x-guru.field label="Tindakan" for="action_taken">
                    <textarea id="action_taken" name="action_taken" class="g-input">{{ old('action_taken', $record->action_taken) }}</textarea>
                </x-guru.field>
            </div>

            <div id="counseling" class="flex flex-col gap-4">
                <x-guru.field label="Isi konseling" for="counseling_content">
                    <textarea id="counseling_content" name="counseling_content" class="g-input">{{ old('counseling_content', $record->counseling_content) }}</textarea>
                </x-guru.field>
                <x-guru.field label="Hasil" for="counseling_result">
                    <textarea id="counseling_result" name="counseling_result" class="g-input">{{ old('counseling_result', $record->counseling_result) }}</textarea>
                </x-guru.field>
            </div>

            <x-guru.field label="Rencana tindak lanjut" for="follow_up_plan">
                <textarea id="follow_up_plan" name="follow_up_plan" class="g-input">{{ old('follow_up_plan', $record->follow_up_plan) }}</textarea>
            </x-guru.field>
            <x-guru.field label="Status" for="status">
                <select id="status" name="status" class="g-input">
                    @foreach (['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $record->status ?? 'new') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Lampiran privat (maks. 5)" for="attachments">
                <input id="attachments" type="file" multiple name="attachments[]" class="g-input">
            </x-guru.field>
        </x-guru.card>

        <x-guru.button type="submit" class="g-btn--block">Simpan</x-guru.button>
    </form>

    <x-slot:scripts>
        <script>
            (function () {
                const type = document.querySelector('#record_type');
                const violation = document.querySelector('#violation');
                const counseling = document.querySelector('#counseling');
                const category = document.querySelector('#category_id');
                function sync() {
                    violation.hidden = type.value !== 'violation';
                    counseling.hidden = type.value !== 'counseling';
                    [...category.options].forEach((option) => { option.hidden = option.dataset.type && option.dataset.type !== type.value; });
                }
                type.onchange = sync;
                category.onchange = () => {
                    const option = category.selectedOptions[0];
                    if (option.dataset.severity) document.querySelector('#severity').value = option.dataset.severity;
                };
                sync();
            })();
        </script>
    </x-slot:scripts>
</x-layouts.mobile>
