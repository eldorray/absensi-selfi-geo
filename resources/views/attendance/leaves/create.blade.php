<x-layouts.mobile title="Ajukan Izin" backUrl="{{ route('attendance.leaves.index') }}" activeTab="izin">
    <form action="{{ route('attendance.leaves.store') }}" method="POST" enctype="multipart/form-data" class="g-card"
        x-data="{
            sending: false,
            imagePreview: null,
            pick(event) {
                const file = event.target.files[0];
                if (! file) { this.imagePreview = null; return; }
                const reader = new FileReader();
                reader.onload = (loaded) => { this.imagePreview = loaded.target.result; };
                reader.readAsDataURL(file);
            },
            clearFile() { this.imagePreview = null; this.$refs.file.value = ''; },
        }"
        @submit="sending = true" @pageshow.window="sending = false">
        @csrf

        @if ($errors->any())
            <x-guru.notice tone="error" title="Periksa kembali isian Anda" role="alert">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-guru.notice>
        @endif

        <fieldset>
            <legend class="g-label">Jenis Perizinan <span class="g-req" aria-hidden="true">*</span></legend>
            <div class="g-choices">
                @foreach (['izin' => ['Izin', 'doc'], 'cuti' => ['Cuti', 'briefcase'], 'sakit' => ['Sakit', 'medical']] as $value => [$label, $icon])
                    <label class="g-choice">
                        <input type="radio" name="type" value="{{ $value }}" @checked(old('type', 'izin') === $value) required>
                        <x-guru.icon :name="$icon" :size="22" />
                        {{ $label }}
                        <span class="g-choice__check" aria-hidden="true"><x-guru.icon name="check" :size="14" /></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="grid grid-cols-2 gap-3">
            <x-guru.field label="Tanggal mulai" for="start_date" error="start_date" :required="true">
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="g-input" required
                    @error('start_date') aria-invalid="true" aria-describedby="start_date-error" @enderror>
            </x-guru.field>
            <x-guru.field label="Tanggal selesai" for="end_date" error="end_date" :required="true">
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', date('Y-m-d')) }}" class="g-input" required
                    @error('end_date') aria-invalid="true" aria-describedby="end_date-error" @enderror>
            </x-guru.field>
        </div>

        <x-guru.field label="Alasan pengajuan" for="reason" error="reason" :required="true">
            <textarea name="reason" id="reason" rows="3" class="g-input" placeholder="Tuliskan alasan dengan jelas" required
                @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>{{ old('reason') }}</textarea>
        </x-guru.field>

        <div class="g-field">
            <span class="g-label">Dokumen pendukung <span class="font-medium text-guru-muted">(opsional)</span></span>
            <label for="attachment" class="g-drop">
                <input type="file" name="attachment" id="attachment" accept="image/*" class="sr-only" x-ref="file" @change="pick($event)" aria-describedby="attachment-hint">
                <template x-if="imagePreview">
                    <img :src="imagePreview" alt="Pratinjau lampiran" class="g-drop__preview">
                </template>
                <span x-show="! imagePreview" class="g-list__icon g-list__icon--izin"><x-guru.icon name="image" /></span>
                <span class="font-bold" x-text="imagePreview ? 'Ganti foto' : 'Pilih foto bukti atau surat'">Pilih foto bukti atau surat</span>
                <span id="attachment-hint" class="text-xs text-guru-muted">JPG atau PNG, maksimal 5 MB</span>
            </label>
            <button type="button" x-show="imagePreview" x-cloak @click="clearFile()" class="g-btn g-btn--secondary g-btn--sm mt-2 self-start">
                <x-guru.icon name="x" /> Hapus lampiran
            </button>
            @error('attachment')
                <p id="attachment-error" class="g-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="g-btn g-btn--primary g-btn--block" :disabled="sending" :aria-busy="sending.toString()">
            <x-guru.icon name="send" />
            <span x-text="sending ? 'Mengirim…' : 'Kirim Pengajuan Izin'">Kirim Pengajuan Izin</span>
        </button>
    </form>
</x-layouts.mobile>
