<x-layouts.mobile title="Buat Rujukan" backUrl="{{ route('attendance.my-class.show', $student) }}" activeTab="kelas">
    <form method="POST" enctype="multipart/form-data" action="{{ route('attendance.kesiswaan.referrals.store', $student) }}" class="flex flex-col gap-4">
        @csrf

        @if ($errors->any())
            <x-guru.notice tone="error" title="Rujukan belum terkirim" role="alert">{{ $errors->first() }}</x-guru.notice>
        @endif

        <x-guru.notice>Rujukan untuk <strong class="inline">{{ $student->nama_lengkap }}</strong> dikirim ke Guru BK unit sekolahnya.</x-guru.notice>

        <x-guru.card as="div">
            <x-guru.field label="Alasan rujukan" for="reason" error="reason" :required="true">
                <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" required class="g-input" placeholder="Mis. sering melamun di kelas"
                    @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>
            </x-guru.field>
            <x-guru.field label="Ringkasan pengamatan" for="observation" error="observation" :required="true">
                <textarea id="observation" name="observation" rows="4" required class="g-input" placeholder="Apa yang Anda lihat, kapan, dan seberapa sering"
                    @error('observation') aria-invalid="true" aria-describedby="observation-error" @enderror>{{ old('observation') }}</textarea>
            </x-guru.field>
            <x-guru.field label="Tanggal pengamatan" for="observed_at" error="observed_at" :required="true">
                <input id="observed_at" type="date" name="observed_at" value="{{ old('observed_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="g-input"
                    @error('observed_at') aria-invalid="true" aria-describedby="observed_at-error" @enderror>
            </x-guru.field>
            <x-guru.field label="Urgensi" for="urgency" error="urgency" :required="true">
                <select id="urgency" name="urgency" required class="g-input">
                    @foreach (['normal' => 'Biasa', 'important' => 'Penting', 'urgent' => 'Mendesak'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('urgency', 'normal') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Lampiran" for="attachments" hint="Opsional. Maks. 3 file JPG, PNG, atau PDF, masing-masing 5 MB.">
                <input id="attachments" type="file" name="attachments[]" multiple accept="image/jpeg,image/png,application/pdf" class="g-input" aria-describedby="attachments-hint">
            </x-guru.field>
        </x-guru.card>

        <x-guru.button type="submit" icon="send" class="g-btn--block">Kirim rujukan</x-guru.button>
    </form>
</x-layouts.mobile>
