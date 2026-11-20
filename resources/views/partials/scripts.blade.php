<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

@include('partials.confirm-dialog')

{{-- Lightbox foto (dipanggil dengan: $dispatch('lightbox', url)) --}}
<div x-data="{ open: false, src: '' }"
     x-on:lightbox.window="src = $event.detail; open = true"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak x-transition.opacity
     @click="open = false"
     class="fixed inset-0 z-[100] flex cursor-zoom-out items-center justify-center bg-ink/90 p-4 backdrop-blur-sm">
    <img :src="src" alt="Foto" class="max-h-full max-w-full rounded-2xl shadow-2xl">
    <button type="button" class="absolute right-5 top-5 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button>
</div>

<script>
    // Buka foto di lightbox dari elemen mana pun (tanpa perlu scope Alpine)
    window.openLightbox = (url) => window.dispatchEvent(new CustomEvent('lightbox', { detail: url }));

    // Ikon Lucide
    const renderIcons = () => window.lucide && window.lucide.createIcons();
    window.renderIcons = renderIcons;
    document.addEventListener('DOMContentLoaded', renderIcons);
    window.addEventListener('load', renderIcons);

    // Upvote tanpa reload halaman (progressive enhancement: tanpa JS tetap jalan lewat form biasa)
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-vote]');
        if (!form) return;
        e.preventDefault();

        const id = form.dataset.vote;
        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            if (res.status === 401 || res.status === 419) { window.location.href = @json(route('login')); return; }

            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Gagal memproses dukungan.'); return; }

            document.querySelectorAll(`[data-vote-count="${id}"]`).forEach((el) => el.textContent = data.count);
            document.querySelectorAll(`form[data-vote="${id}"] button`).forEach((b) => b.dataset.voted = data.voted ? 'true' : 'false');
            document.querySelectorAll(`[data-vote-label="${id}"]`).forEach((el) => el.textContent = data.voted ? 'Didukung' : 'Dukung');
        } catch (err) {
            form.submit();
        } finally {
            button.disabled = false;
        }
    });
</script>
