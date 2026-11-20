{{--
    Dialog konfirmasi bertema SIGAP (pengganti confirm() bawaan browser).
    Pakai pada <form> apa pun:
        <form data-confirm="Pesan penjelasan"
              data-confirm-title="Judul"            (opsional)
              data-confirm-ok="Ya, lanjutkan"       (opsional, teks tombol)
              data-confirm-tone="danger|primary"    (opsional, bawaan danger)
              data-confirm-icon="reject|delete|disable|enable|question" (opsional)
              data-confirm-detail-from="#id-field"  (opsional, tampilkan isi sebuah input)>
--}}
<div x-data="confirmDialog()"
     x-on:confirm-dialog.window="ask($event.detail)"
     x-on:keydown.escape.window="close()"
     x-show="open" x-cloak
     class="fixed inset-0 z-[120] flex items-end justify-center p-4 sm:items-center"
     role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">

    <div x-show="open" x-transition.opacity.duration.200ms @click="close()" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
         class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 sm:p-8">

        <div class="flex items-start gap-4">
            {{-- Ikon (nada bahaya) --}}
            <span x-show="tone === 'danger'" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 ring-8 ring-rose-50">
                <span x-show="icon === 'reject'"><i data-lucide="shield-x" class="h-6 w-6"></i></span>
                <span x-show="icon === 'delete'"><i data-lucide="trash-2" class="h-6 w-6"></i></span>
                <span x-show="icon === 'disable'"><i data-lucide="user-x" class="h-6 w-6"></i></span>
                <span x-show="!['reject','delete','disable'].includes(icon)"><i data-lucide="triangle-alert" class="h-6 w-6"></i></span>
            </span>
            {{-- Ikon (nada biasa) --}}
            <span x-show="tone !== 'danger'" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 ring-8 ring-brand-50">
                <span x-show="icon === 'enable'"><i data-lucide="user-check" class="h-6 w-6"></i></span>
                <span x-show="icon !== 'enable'"><i data-lucide="circle-help" class="h-6 w-6"></i></span>
            </span>

            <div class="min-w-0 flex-1">
                <h3 id="confirm-title" class="text-lg font-extrabold tracking-tight text-ink" x-text="title"></h3>
                <p id="confirm-message" class="mt-1.5 text-sm leading-relaxed text-slate-500" x-text="message"></p>

                <div x-show="detail" x-cloak class="mt-4 rounded-xl bg-slate-50 p-3.5 ring-1 ring-slate-200">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Alasan yang Anda tulis</p>
                    <p class="mt-1 line-clamp-4 break-words text-sm text-slate-700" x-text="detail"></p>
                </div>
            </div>
        </div>

        <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" x-ref="cancel" @click="close()" class="btn-outline sm:min-w-[110px]" x-text="cancelLabel"></button>
            <button type="button" x-show="tone === 'danger'" @click="accept()" class="btn-danger sm:min-w-[140px]" x-text="okLabel"></button>
            <button type="button" x-show="tone !== 'danger'" @click="accept()" class="btn-primary sm:min-w-[140px]" x-text="okLabel"></button>
        </div>
    </div>
</div>

<script>
    // Didefinisikan sebelum Alpine berjalan (Alpine dimuat dengan "defer").
    function confirmDialog() {
        return {
            open: false, form: null,
            title: '', message: '', detail: '', okLabel: 'Ya, lanjutkan', cancelLabel: 'Batal',
            tone: 'danger', icon: 'question',

            ask(d) {
                this.form = d.form;
                this.title = d.title || 'Apakah Anda yakin?';
                this.message = d.message || '';
                this.detail = d.detail || '';
                this.okLabel = d.ok || 'Ya, lanjutkan';
                this.tone = d.tone || 'danger';
                this.icon = d.icon || 'question';
                this.open = true;
                // Fokus awal ke "Batal" agar tindakan berbahaya tidak terpicu tak sengaja
                this.$nextTick(() => this.$refs.cancel && this.$refs.cancel.focus());
                window.renderIcons && window.renderIcons();
            },
            close() { this.open = false; },
            accept() {
                const f = this.form;
                this.open = false;
                if (!f) return;
                f.dataset.confirmed = '1';
                f.submit();
            },
        };
    }

    // Cegat submit form ber-atribut data-confirm, tampilkan dialog dulu.
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === '1') return;

        e.preventDefault();
        const from = form.dataset.confirmDetailFrom;
        const field = from ? form.querySelector(from) : null;

        window.dispatchEvent(new CustomEvent('confirm-dialog', {
            detail: {
                form,
                message: form.dataset.confirm,
                title: form.dataset.confirmTitle,
                ok: form.dataset.confirmOk,
                tone: form.dataset.confirmTone,
                icon: form.dataset.confirmIcon,
                detail: field ? field.value.trim() : '',
            },
        }));
    });
</script>
