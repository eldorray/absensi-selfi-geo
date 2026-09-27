{{-- Reusable glass confirmation dialog for admin actions.
     Mounted once per admin page; triggered by dispatching the
     `admin-confirm` window event with { title, message, confirmText, variant, form }. --}}
<div x-data="{
    open: false,
    title: 'Konfirmasi',
    message: '',
    confirmText: 'Ya',
    variant: 'primary',
    form: null,
    returnFocus: null,
    confirmClass() {
        return this.variant === 'danger' ?
            'admin-button-danger' :
            this.variant === 'success' ?
            'admin-button-success' :
            'admin-button-primary';
    },
    proceed() {
        const target = this.form;
        this.close();
        if (target) {
            target.submit();
        }
    },
    cancel() {
        this.close();
    },
    close() {
        this.open = false;
        this.form = null;
        const el = this.returnFocus;
        this.returnFocus = null;
        if (el && typeof el.focus === 'function' && document.contains(el)) {
            this.$nextTick(() => el.focus());
        }
    },
    trapFocus(e) {
        const items = [...this.$refs.dialog.querySelectorAll('button:not([disabled])')];
        if (!items.length) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        } else if (!this.$refs.dialog.contains(document.activeElement)) {
            e.preventDefault();
            first.focus();
        }
    },
}" x-id="['confirm-title', 'confirm-message']"
    @admin-confirm.window="
        title = $event.detail.title || 'Konfirmasi';
        message = $event.detail.message || '';
        confirmText = $event.detail.confirmText || 'Ya';
        variant = $event.detail.variant || 'primary';
        form = $event.detail.form || null;
        if (!open) returnFocus = document.activeElement;
        open = true;
        $nextTick(() => $refs.confirmBtn && $refs.confirmBtn.focus());
    "
    @keydown.escape.window="open && cancel()" @keydown.tab.window="open && trapFocus($event)" x-show="open" x-cloak
    class="admin-modal-overlay fixed inset-0 z-[60] flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="cancel()" role="dialog"
    aria-modal="true" :aria-labelledby="$id('confirm-title')" :aria-describedby="$id('confirm-message')">

    <div x-ref="dialog" class="admin-glass-modal w-full max-w-sm p-6 text-left" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95">

        <h2 :id="$id('confirm-title')" x-text="title" class="font-display text-base font-bold"></h2>
        <p :id="$id('confirm-message')" class="admin-muted mt-2 text-sm leading-relaxed" x-text="message"></p>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" @click="cancel()" class="admin-button-secondary px-4 py-2 text-sm">Batal</button>
            <button type="button" x-ref="confirmBtn" @click="proceed()" :class="confirmClass()"
                class="px-4 py-2 text-sm" x-text="confirmText"></button>
        </div>
    </div>
</div>
