@php
    $user = auth()->user();
    $hour = (int) now()->format('G');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi,',
        $hour < 15 => 'Selamat siang,',
        $hour < 18 => 'Selamat sore,',
        default => 'Selamat malam,',
    };
    $workDays = $data->monthlyWorkDays;
    $recorded = $data->monthlyRecorded();
    $bar = [
        ['is-ok', $data->monthlyOnTime()],
        ['is-late', $data->monthlyLate],
        ['is-izin', $data->monthlyLeaveDays],
        ['is-rest', max(0, $workDays - $recorded)],
    ];
    $isHeadmaster = $user->role?->slug === 'kepala-sekolah';
    $canBk = $user->canAccessBk() && ! $user->isAdmin();
    $isAffairs = $user->is_student_affairs_officer && in_array($user->office?->school_level, ['mi', 'smp'], true);
@endphp

<x-layouts.mobile title="Beranda" activeTab="beranda">
    <x-slot:header>
        <header class="g-header" data-region="top-app-bar">
            <a href="{{ route('attendance.profile') }}" class="g-greet" data-profile-link="teacher-identity" aria-label="Buka profil {{ $user->name }}">
                <span class="g-avatar">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="">
                    @else
                        {{ $user->initials() }}
                    @endif
                </span>
                <span>
                    <small>{{ $greeting }}</small>
                    <strong>{{ $user->name }}</strong>
                </span>
            </a>

            <div class="g-header__actions">
                @if ($linkedAccounts->isNotEmpty())
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" class="g-iconbtn" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu" aria-controls="menu-ganti-akun" aria-label="Ganti Akun">
                            <x-guru.icon name="swap" />
                        </button>
                        <div id="menu-ganti-akun" class="g-menu" role="menu" x-show="open" x-cloak @click.outside="open = false">
                            <p class="g-caps px-2.5 pb-1 pt-2">Ganti Akun</p>
                            @foreach ($linkedAccounts as $account)
                                <button type="button" role="menuitem" class="g-menu__item"
                                    @click="open = false; $refs.switchName.textContent = @js($account->name); $refs.switchTarget.value = @js($account->id); $refs.switchDialog.showModal()">
                                    <span class="g-avatar g-avatar--sm">{{ $account->initials() }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-bold">{{ $account->name }}</span>
                                        <span class="block truncate text-xs g-muted">{{ $account->office?->name ?? 'Tanpa kantor' }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <dialog x-ref="switchDialog" class="g-dialog" aria-labelledby="judul-ganti-akun">
                            <h2 id="judul-ganti-akun" class="g-display text-2xl font-semibold">Ganti akun?</h2>
                            <p class="mt-2 g-muted">Berpindah ke akun <strong class="text-guru-ink" x-ref="switchName"></strong>. Sesi akun ini akan diganti.</p>
                            <form method="POST" action="{{ route('account.switch') }}" class="mt-5 grid grid-cols-2 gap-2">
                                @csrf
                                <input type="hidden" name="target_id" x-ref="switchTarget">
                                <button type="button" class="g-btn g-btn--secondary" @click="$refs.switchDialog.close()">Batal</button>
                                <button type="submit" class="g-btn g-btn--primary">Ganti</button>
                            </form>
                        </dialog>
                    </div>
                @endif

                <a href="{{ route('attendance.kesiswaan.notifications.index') }}" class="g-iconbtn" aria-label="Notifikasi, {{ $data->unreadNotifications }} belum dibaca">
                    <x-guru.icon name="bell" />
                    @if ($data->unreadNotifications > 0)
                        <span class="g-iconbtn__dot"></span>
                    @endif
                </a>
            </div>
        </header>
    </x-slot:header>

    <div class="g-dateline">
        <h1>{{ now()->locale('id')->isoFormat('dddd, D MMMM') }}</h1>
        <p>{{ $user->office?->name ?? 'Sekolah' }}</p>
    </div>

    {{-- Kartu hero: status dan satu aksi yang relevan sekarang --}}
    <x-guru.card variant="hero" aria-label="Presensi hari ini" data-hero="today">
        <div class="flex items-center justify-between gap-3">
            <span class="g-hero__label">Presensi hari ini</span>
            <x-guru.chip :tone="$presence->late ? 'hero-late' : 'hero'">{{ $presence->status }}</x-guru.chip>
        </div>

        <div class="g-hero__times">
            <div class="g-hero__time">
                <span class="g-hero__time-label">Masuk</span>
                <span @class(['g-hero__time-value', 'is-empty' => ! $presence->checkIn])>{{ $presence->checkIn ?? '––.––' }}</span>
            </div>
            <div class="g-hero__time">
                <span class="g-hero__time-label">Pulang</span>
                <span @class(['g-hero__time-value', 'is-empty' => ! $presence->checkOut])>{{ $presence->checkOut ?? '––.––' }}</span>
            </div>
        </div>

        @if ($presence->scheduleStart)
            <div class="flex flex-col gap-2">
                <div class="g-progress" style="--p: {{ $presence->progress }}%" role="progressbar" aria-label="Waktu kerja hari ini"
                    aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $presence->progress }}">
                    <div class="g-progress__fill"></div>
                    <div class="g-progress__knob"></div>
                </div>
                <div class="g-progress__labels">
                    <span>{{ $presence->scheduleStart }}</span>
                    <span>{{ $presence->progressText }}</span>
                    <span>{{ $presence->scheduleEnd }}</span>
                </div>
            </div>
        @else
            <p class="g-hero__loc">{{ $presence->progressText }}</p>
        @endif

        <p class="g-hero__loc"><x-guru.icon name="pin" :size="18" />{{ $presence->locationText }}</p>

        @if ($presence->action)
            @if ($presence->action['disabled'])
                <span class="g-btn g-btn--hero" aria-disabled="true">
                    <x-guru.icon :name="$presence->action['icon']" />{{ $presence->action['label'] }}
                </span>
            @else
                <x-guru.button variant="hero" :href="$presence->action['href']" :icon="$presence->action['icon']">{{ $presence->action['label'] }}</x-guru.button>
            @endif
        @endif
    </x-guru.card>

    {{-- Rekap bulan ini --}}
    <x-guru.card aria-labelledby="judul-rekap">
        <div class="flex items-center justify-between gap-3">
            <h2 id="judul-rekap" class="g-h2">Rekap {{ now()->locale('id')->isoFormat('MMMM') }}</h2>
            <a href="{{ route('attendance.index') }}" class="flex min-h-11 items-center text-[13px] font-bold">Lihat riwayat</a>
        </div>
        <div class="g-stats">
            <x-guru.stat :value="$data->monthlyOnTime()" label="Tepat waktu" tone="primary" />
            <x-guru.stat :value="$data->monthlyLate" label="Terlambat" tone="late" />
            <x-guru.stat :value="$data->monthlyLeaveDays" label="Izin" tone="izin" />
        </div>
        @if ($workDays > 0)
            <div class="g-stackbar" aria-hidden="true">
                @foreach ($bar as [$segment, $count])
                    @if ($count > 0)
                        <span class="{{ $segment }}" style="flex-grow: {{ $count }}"></span>
                    @endif
                @endforeach
            </div>
            <p class="text-xs g-muted">{{ $recorded }} dari {{ $workDays }} hari kerja tercatat</p>
        @else
            <p class="text-xs g-muted">Belum ada hari kerja bulan ini</p>
        @endif
    </x-guru.card>

    {{-- Kelas wali --}}
    @if ($homeroomAssignment)
        <a href="{{ route('attendance.my-class.index') }}" class="g-card">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-1">
                    <span class="g-card__caps">Kelas wali · {{ $homeroomAssignment->academicYear->name }}</span>
                    <span class="g-card__title">{{ $homeroomAssignment->schoolClass->name }}</span>
                </div>
                <span class="g-list__icon size-11 rounded-[14px]"><x-guru.icon name="users" :size="22" /></span>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-guru.chip>{{ $homeroomStudentCount }} siswa aktif</x-guru.chip>
                @if ($homeroomViolationCount > 0)
                    <x-guru.chip tone="attn">{{ $homeroomViolationCount }} perlu perhatian</x-guru.chip>
                @endif
            </div>
        </a>
    @endif

    {{-- Layanan --}}
    <section aria-labelledby="judul-layanan" class="flex flex-col gap-2.5">
        <h2 id="judul-layanan" class="g-h2">Layanan</h2>
        <x-guru.list>
            @if ($isHeadmaster)
                <x-guru.list-item :href="route('approval.leaves.index')" icon="clipboard" title="Persetujuan Izin" desc="Setujui atau tolak pengajuan guru" />
            @endif
            <x-guru.list-item :href="route('attendance.leaves.index')" icon="doc" title="Pengajuan Izin" desc="Sakit, izin, cuti">
                @if ($data->pendingLeaves > 0)
                    <x-slot:end><x-guru.chip tone="pending">{{ $data->pendingLeaves }} menunggu</x-guru.chip></x-slot:end>
                @endif
            </x-guru.list-item>
            @if ($homeroomAssignment)
                <x-guru.list-item :href="route('attendance.referrals.mine')" icon="send" tone="attn" title="Rujukan Saya" desc="Rujukan siswa ke BK" />
            @endif
            @if ($canBk)
                <x-guru.list-item :href="route('attendance.bk.index')" icon="chat" tone="izin" title="Bimbingan Konseling" desc="Catatan dan tindak lanjut" />
                <x-guru.list-item :href="route('attendance.referrals.queue')" icon="inbox" tone="izin" title="Antrean Rujukan" desc="Rujukan yang menunggu ditangani" />
            @endif
            @if ($isAffairs)
                <x-guru.list-item :href="route('attendance.kesiswaan.index')" icon="school" title="Kesiswaan" desc="Direktori siswa" />
            @endif
            <x-guru.list-item :href="route('attendance.profile')" icon="lock" tone="neutral" title="Profil & Kata Sandi" desc="Foto, data diri, keamanan" />
        </x-guru.list>
    </section>

    {{-- Informasi --}}
    @if ($data->announcements->isNotEmpty())
        <section aria-labelledby="judul-informasi" class="flex flex-col gap-2.5">
            <h2 id="judul-informasi" class="g-h2">Informasi</h2>
            <div class="g-carousel">
                @foreach ($data->announcements as $info)
                    <a href="{{ route('attendance.information.show', $info) }}" class="g-info">
                        <div class="g-info__media">
                            @if ($info->image_url)
                                <img src="{{ $info->image_url }}" alt="" loading="lazy" decoding="async">
                            @endif
                            <span class="g-info__tag">Pengumuman</span>
                        </div>
                        <div class="g-info__body">
                            <span class="text-sm font-bold">{{ $info->title }}</span>
                            <span class="text-xs g-muted">{{ $info->summary ? $info->summary.' · ' : '' }}{{ $info->created_at->locale('id')->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Pasang PWA: muncul hanya bila browser menawarkan --}}
    <div id="pwa-install-banner" class="g-install" hidden>
        <x-guru.icon name="phone" :size="22" />
        <p><strong>Pasang absenKU</strong>Buka lebih cepat dari layar utama.</p>
        <button type="button" onclick="installPWA()" class="g-btn g-btn--hero g-btn--sm">Pasang</button>
        <button type="button" onclick="dismissInstallBanner()" class="g-iconbtn border-0 bg-transparent text-white" aria-label="Tutup banner instal">
            <x-guru.icon name="x" />
        </button>
    </div>

    <x-slot:scripts>
        <script>
            (function () {
                let deferredPrompt = null;
                const banner = () => document.getElementById('pwa-install-banner');
                // Banner fixed di atas nav: selama tampil, konten diberi ruang agar tidak tertutup.
                const showBanner = (visible) => {
                    banner().hidden = ! visible;
                    document.body.classList.toggle('g-has-install', visible);
                };
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    deferredPrompt = e;
                    let dismissed = false;
                    try { dismissed = localStorage.getItem('pwaInstallDismissed') === 'true'; } catch (err) {}
                    if (! dismissed) showBanner(true);
                });
                window.addEventListener('appinstalled', () => { showBanner(false); deferredPrompt = null; });
                window.installPWA = async function () {
                    if (! deferredPrompt) return;
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    showBanner(false);
                };
                window.dismissInstallBanner = function () {
                    showBanner(false);
                    try { localStorage.setItem('pwaInstallDismissed', 'true'); } catch (err) {}
                };

                // Kartu presensi dihitung di server; muat ulang saat PWA dibuka lagi setelah 5 menit.
                const loadedAt = Date.now();
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible' && Date.now() - loadedAt > 5 * 60 * 1000) {
                        location.reload();
                    }
                });
            })();
        </script>
    </x-slot:scripts>
</x-layouts.mobile>
