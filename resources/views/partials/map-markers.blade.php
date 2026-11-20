{{-- Peta Leaflet + marker laporan. Variabel: $mapId, $markers, $height (opsional) --}}
@push('head')
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endonce
@endpush

@push('scripts')
    @once
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endonce
    <script>
        (function () {
            const markers = @json($markers);
            const center = @json(config('sigap.map_center'));
            const map = L.map(@json($mapId), { scrollWheelZoom: false }).setView(center, 12);
            window.sigapMaps = window.sigapMaps || {};
            window.sigapMaps[@json($mapId)] = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            const group = [];
            markers.forEach((m) => {
                const radius = Math.min(16, 8 + Math.sqrt(m.votes) * 1.6);
                const circle = L.circleMarker([m.lat, m.lng], {
                    radius, color: '#fff', weight: 2, fillColor: m.color, fillOpacity: .92,
                }).addTo(map);
                const safe = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                circle.bindPopup(
                    `<div style="min-width:180px">
                        <div style="font-weight:800;color:#0b1437;line-height:1.3">${safe(m.title)}</div>
                        <div style="margin:6px 0;font-size:12px;color:#64748b">${safe(m.status)} &middot; ${m.votes} dukungan</div>
                        <a href="${m.url}" style="font-weight:700;color:#1d4ed8;font-size:13px">Lihat detail &rarr;</a>
                    </div>`
                );
                group.push([m.lat, m.lng]);
            });

            const fit = () => {
                if (group.length > 1) map.fitBounds(group, { padding: [40, 40], maxZoom: 15 });
                else if (group.length === 1) map.setView(group[0], 15);
            };
            fit();

            // Dipanggil saat peta yang semula tersembunyi (tab) ditampilkan
            window.showSigapMap = window.showSigapMap || ((id) => {
                const m = window.sigapMaps[id];
                if (!m) return;
                m.invalidateSize();
                m._fit && m._fit();
            });
            map._fit = fit;

            // Aktifkan scroll-zoom hanya setelah peta diklik (agar tidak mengganggu scroll halaman)
            map.on('click', () => map.scrollWheelZoom.enable());
            map.on('mouseout', () => map.scrollWheelZoom.disable());
        })();
    </script>
@endpush
