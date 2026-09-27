@php
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $methods = ['phone' => 'Telepon', 'whatsapp' => 'WhatsApp', 'meeting' => 'Pertemuan', 'letter' => 'Surat', 'other' => 'Lainnya'];
@endphp

<x-layouts.mobile title="Detail BK" backUrl="{{ route('attendance.bk.index') }}">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.bk.edit', $record)" variant="secondary" class="g-btn--sm">Edit</x-guru.button>
    </x-slot:headerAction>

    <x-guru.card>
        <div class="flex flex-wrap items-center gap-2">
            <x-guru.chip :tone="$record->record_type === 'violation' ? 'attn' : 'izin'">{{ $record->record_type === 'violation' ? 'Pelanggaran' : 'Konseling' }}</x-guru.chip>
            <x-guru.chip>{{ $statusLabels[$record->status] ?? $record->status }}</x-guru.chip>
            @if ($record->archived_at)
                <x-guru.chip tone="pending">Diarsipkan</x-guru.chip>
            @endif
        </div>
        <h2 class="g-card__title">{{ $record->student->nama_lengkap }}</h2>
        <p class="text-sm text-guru-muted">{{ $record->category?->name ?? $record->custom_topic }} · {{ $record->occurred_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</p>
    </x-guru.card>

    <x-guru.card as="div">
        <dl class="g-dl">
            @foreach (['chronology' => 'Kronologi', 'action_taken' => 'Tindakan', 'counseling_content' => 'Isi konseling', 'counseling_result' => 'Hasil', 'follow_up_plan' => 'Rencana'] as $field => $label)
                @if ($record->$field)
                    <div class="flex-col !items-start">
                        <dt>{{ $label }}</dt>
                        <dd class="!max-w-none whitespace-pre-line !text-left !font-normal">{{ $record->$field }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-guru.card>

    @if ($record->attachments->isNotEmpty())
        <section class="flex flex-col gap-2.5" aria-labelledby="judul-lampiran">
            <h2 id="judul-lampiran" class="g-h2">Lampiran privat</h2>
            <x-guru.list>
                @foreach ($record->attachments as $attachment)
                    <x-guru.list-item :href="route('attendance.bk.attachments.show', $attachment)" icon="paperclip" tone="neutral" :title="$attachment->original_name" />
                @endforeach
            </x-guru.list>
        </section>
    @endif

    <x-guru.card as="section" aria-labelledby="judul-tindak-lanjut">
        <h2 id="judul-tindak-lanjut" class="g-h2">Timeline tindak lanjut</h2>
        @foreach ($record->followUps->sortByDesc('followed_up_at') as $followUp)
            <div class="flex flex-col gap-1 border-t border-guru-divider pt-3 text-sm">
                <b class="g-num">{{ $followUp->followed_up_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</b>
                <p>{{ $followUp->progress_notes }}</p>
                @if ($followUp->result)
                    <p class="text-guru-muted">{{ $followUp->result }}</p>
                @endif
            </div>
        @endforeach
        <form method="POST" action="{{ route('attendance.bk.follow-ups.store', $record) }}" class="flex flex-col gap-3 border-t border-guru-divider pt-4">
            @csrf
            <x-guru.field label="Waktu" for="followed_up_at" :required="true">
                <input id="followed_up_at" type="datetime-local" name="followed_up_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Catatan progres" for="progress_notes" :required="true">
                <textarea id="progress_notes" name="progress_notes" required class="g-input"></textarea>
            </x-guru.field>
            <x-guru.field label="Hasil" for="result">
                <textarea id="result" name="result" class="g-input"></textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="secondary" icon="plus" class="g-btn--block">Tambah tindak lanjut</x-guru.button>
        </form>
    </x-guru.card>

    <x-guru.card as="section" aria-labelledby="judul-kontak">
        <h2 id="judul-kontak" class="g-h2">Kontak orang tua</h2>
        @foreach ($record->parentContacts->sortByDesc('contacted_at') as $contact)
            <div class="flex flex-col gap-1 border-t border-guru-divider pt-3 text-sm">
                <b>{{ $contact->contact_name }} · {{ $methods[$contact->method] ?? $contact->method }}</b>
                <p>{{ $contact->summary }}</p>
            </div>
        @endforeach
        <form method="POST" action="{{ route('attendance.bk.parent-contacts.store', $record) }}" class="flex flex-col gap-3 border-t border-guru-divider pt-4">
            @csrf
            <x-guru.field label="Waktu" for="contacted_at" :required="true">
                <input id="contacted_at" type="datetime-local" name="contacted_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Cara" for="method">
                <select id="method" name="method" class="g-input">
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Nama orang tua/wali" for="contact_name" :required="true">
                <input id="contact_name" name="contact_name" required class="g-input">
            </x-guru.field>
            <x-guru.field label="Ringkasan" for="summary" :required="true">
                <textarea id="summary" name="summary" required class="g-input"></textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="secondary" icon="plus" class="g-btn--block">Tambah kontak</x-guru.button>
        </form>
    </x-guru.card>

    <form method="POST" action="{{ route($record->archived_at ? 'attendance.bk.restore' : 'attendance.bk.archive', $record) }}">
        @csrf
        @method('PATCH')
        <x-guru.button type="submit" variant="secondary" class="g-btn--block">{{ $record->archived_at ? 'Pulihkan' : 'Arsipkan' }}</x-guru.button>
    </form>
</x-layouts.mobile>
