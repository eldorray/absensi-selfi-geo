@php
    $selectedLinkedIds = array_map('intval', old('linked_accounts', $linkedIds));
    $linkedAccountOptions = $linkableUsers->map(fn ($candidate) => [
        'id' => $candidate->id,
        'name' => $candidate->name,
        'email' => $candidate->email,
        'office' => $candidate->office?->name ?? 'Tanpa kantor',
        'initials' => $candidate->initials(),
    ])->values();
@endphp

<div x-data="{
    open: false,
    search: '',
    office: 'Semua kantor',
    selectedIds: @js($selectedLinkedIds),
    accounts: @js($linkedAccountOptions),
    init() {
        this.selectedIds = this.selectedIds.map(Number);
    },
    get selectedAccounts() {
        return this.accounts.filter(account => this.selectedIds.includes(Number(account.id)));
    },
    get offices() {
        return ['Semua kantor', ...new Set(this.accounts.map(account => account.office))];
    },
    get filteredAccounts() {
        const keyword = this.search.trim().toLocaleLowerCase('id');

        return this.accounts.filter(account => {
            const matchesOffice = this.office === 'Semua kantor' || account.office === this.office;
            const matchesSearch = keyword === '' || `${account.name} ${account.email}`.toLocaleLowerCase('id').includes(keyword);

            return matchesOffice && matchesSearch;
        });
    },
    toggle(id) {
        id = Number(id);
        this.selectedIds = this.selectedIds.includes(id)
            ? this.selectedIds.filter(selectedId => selectedId !== id)
            : [...this.selectedIds, id];
    },
    remove(id) {
        this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== Number(id));
    },
    close() {
        this.open = false;
        this.search = '';
        this.office = 'Semua kantor';
    }
}" @keydown.escape.window="open && close()" class="admin-linked-account-picker">
    <template x-for="id in selectedIds" :key="id">
        <input type="hidden" name="linked_accounts[]" :value="id">
    </template>

    <div class="admin-linked-account-heading">
        <span class="admin-linked-account-icon" aria-hidden="true">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3" />
            </svg>
        </span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-bold">Ganti Akun Cepat</span>
            <span class="admin-hint mt-1 block">Izinkan pengguna berpindah ke akun lain tanpa login ulang.</span>
        </span>
        <span class="admin-chip" x-text="`${selectedIds.length} tertaut`"></span>
    </div>

    <div class="admin-divider my-4"></div>

    <div x-show="selectedAccounts.length > 0" class="space-y-2">
        <p class="admin-label">Akun yang sudah tertaut</p>
        <template x-for="account in selectedAccounts" :key="account.id">
            <div class="admin-linked-account-row">
                <span class="admin-linked-account-avatar" x-text="account.initials"></span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold" x-text="account.name"></span>
                    <span class="admin-muted block truncate text-xs">
                        <span x-text="account.office"></span><span aria-hidden="true"> · </span><span x-text="account.email"></span>
                    </span>
                </span>
                <button type="button" class="admin-linked-account-remove" @click="remove(account.id)"
                    :aria-label="`Hapus tautan ${account.name}`">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <div x-show="selectedAccounts.length === 0" class="admin-linked-account-empty">
        <span class="font-semibold">Belum ada akun tertaut</span>
        <span class="admin-muted text-xs">Pilih akun agar pengguna dapat berpindah tanpa login ulang.</span>
    </div>

    <button type="button" class="admin-button-primary mt-3 w-full px-4 py-3 text-sm" @click="open = true; $nextTick(() => $refs.search?.focus())">
        Kelola akun tertaut
    </button>

    <p class="admin-hint mt-3 flex items-start gap-2">
        <svg class="mt-0.5 h-4 w-4 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9h.01M11 12h1v4h1m8-4a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Relasi berlaku dua arah dan diterapkan setelah formulir diperbarui.
    </p>

    @error('linked_accounts.0')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror

    <template x-teleport="body">
        <div x-show="open" x-cloak class="admin-modal-overlay fixed inset-0 z-[70] flex items-center justify-center p-4"
            @click.self="close()" role="dialog" aria-modal="true" aria-labelledby="linked-account-title"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <section class="admin-linked-account-dialog admin-glass-panel" @click.stop>
                <header class="admin-linked-account-dialog-header">
                    <div>
                        <h2 id="linked-account-title" class="text-lg font-bold">Pilih akun terkait</h2>
                        <p class="admin-hint">Cari berdasarkan nama atau email, lalu filter berdasarkan kantor.</p>
                    </div>
                    <button type="button" class="admin-linked-account-remove" @click="close()" aria-label="Tutup pemilih akun">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </header>

                <div class="admin-linked-account-tools">
                    <label class="relative block">
                        <span class="sr-only">Cari nama atau email</span>
                        <svg class="admin-linked-account-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-4-4"></path>
                        </svg>
                        <input x-ref="search" type="search" x-model.debounce.150ms="search" class="admin-field py-3 pl-11 pr-4"
                            placeholder="Cari nama atau email">
                    </label>
                    <div class="admin-linked-account-filters" aria-label="Filter kantor">
                        <template x-for="officeName in offices" :key="officeName">
                            <button type="button" class="admin-linked-account-filter" :class="office === officeName && 'is-active'"
                                @click="office = officeName" x-text="officeName"></button>
                        </template>
                    </div>
                </div>

                <div class="admin-linked-account-results">
                    <template x-for="account in filteredAccounts" :key="account.id">
                        <button type="button" class="admin-linked-account-option" @click="toggle(account.id)"
                            :aria-pressed="selectedIds.includes(Number(account.id))">
                            <span class="admin-linked-account-avatar" x-text="account.initials"></span>
                            <span class="min-w-0 flex-1 text-left">
                                <span class="block truncate text-sm font-bold" x-text="account.name"></span>
                                <span class="admin-muted block truncate text-xs">
                                    <span x-text="account.office"></span><span aria-hidden="true"> · </span><span x-text="account.email"></span>
                                </span>
                            </span>
                            <span class="admin-linked-account-check" :class="selectedIds.includes(Number(account.id)) && 'is-checked'" aria-hidden="true">✓</span>
                        </button>
                    </template>
                    <div x-show="filteredAccounts.length === 0" class="admin-linked-account-empty m-3">
                        <span class="font-semibold">Akun tidak ditemukan</span>
                        <span class="admin-muted text-xs">Coba kata kunci atau filter kantor lain.</span>
                    </div>
                </div>

                <footer class="admin-linked-account-dialog-footer">
                    <span class="admin-muted text-xs" x-text="`${selectedIds.length} akun dipilih`"></span>
                    <button type="button" class="admin-button-primary px-5 py-2.5 text-sm" @click="close()">Terapkan</button>
                </footer>
            </section>
        </div>
    </template>
</div>
