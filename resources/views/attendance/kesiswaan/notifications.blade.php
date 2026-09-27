@php
    $statusLabels = ['new' => 'Rujukan baru', 'in_handling' => 'Sedang ditangani', 'completed' => 'Selesai ditangani', 'rejected' => 'Ditolak'];
    $unread = $notifications->whereNull('read_at')->count();
@endphp

<x-layouts.mobile title="Notifikasi" backUrl="{{ route('attendance.dashboard') }}">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    @if ($unread > 0)
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-guru-muted">{{ $unread }} belum dibaca</p>
            <form method="POST" action="{{ route('attendance.kesiswaan.notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <x-guru.button type="submit" variant="secondary" icon="check" class="g-btn--sm">Tandai semua dibaca</x-guru.button>
            </form>
        </div>
    @endif

    @if ($notifications->isEmpty())
        <x-guru.empty icon="bell" title="Belum ada notifikasi">Kabar rujukan siswa akan muncul di sini.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($notifications as $notification)
                <x-guru.list-item :href="route('attendance.kesiswaan.notifications.show', $notification)" icon="send"
                    :tone="$notification->read_at ? 'neutral' : 'primary'"
                    :title="$notification->data['student_name'] ?? 'Rujukan siswa'"
                    :desc="($statusLabels[$notification->data['status'] ?? 'new'] ?? 'Kabar rujukan').' · '.$notification->created_at->locale('id')->diffForHumans()">
                    @unless ($notification->read_at)
                        <x-slot:end>
                            <x-guru.chip tone="pending">Baru</x-guru.chip>
                        </x-slot:end>
                    @endunless
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($notifications->hasPages())
        <div class="g-pager">{{ $notifications->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
