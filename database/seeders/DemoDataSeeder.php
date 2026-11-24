<?php

namespace Database\Seeders;

use App\Enums\Agency;
use App\Enums\ReportStatus;
use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const REMOTE_PHOTOS = [
        'jalan-rusak' => 'Potholes_at_Bus_Terminus_Northumberland_Park_N17.jpg',
        'sampah-liar' => 'Rubbish_dumped_in_Moorefield_Road._Tottenham_N17.jpg',
        'saluran-air' => 'Trash_From_Flooding_(13244660065).jpg',
    ];

    /** @var array<string, string[]> slug => daftar path file sumber */
    private array $sourceCache = [];

    /** @var array<string, int> penghitung agar foto dibagikan bergantian */
    private array $cursor = [];

    private int $photoCount = 0;

    public function run(): void
    {
        $regions = Region::pluck('id', 'name');
        $categories = Category::pluck('id', 'slug');
        
        $passwordAdmin = Hash::make('AdminSigap#2026');
        $passwordPetugas = Hash::make('PetugasSigap#2026');
        $passwordWarga = Hash::make('WargaSigap#2026');

        /* ---------- Akun ---------- */

        $admin = User::updateOrCreate(['email' => 'admin.sigap@gmail.com'], [
            'name' => 'Admin Pusat SIGAP', 'password' => $passwordAdmin,
            'phone' => '081200000001', 'is_active' => true,
        ]);
        $admin->syncRoles('admin');

        $officers = collect([
            // [nama, email, wilayah, instansi, no. HP]
            ['Slamet Riyadi', 'slamet.riyadi@gmail.com', 'Menteng', Agency::BinaMarga, '081200000101'],
            ['Dewi Anggraini', 'dewi.anggraini@gmail.com', 'Tebet', Agency::LingkunganHidup, '081200000102'],
            ['Bambang Hartono', 'bambang.hartono@gmail.com', 'Kebon Jeruk', Agency::Pln, '081200000103'],
        ])->map(function ($o) use ($passwordPetugas, $regions) {
            $user = User::updateOrCreate(['email' => $o[1]], [
                'name' => $o[0], 'password' => $passwordPetugas,
                'phone' => $o[4], 'region_id' => $regions[$o[2]], 'agency' => $o[3], 'is_active' => true,
            ]);
            $user->syncRoles('petugas');

            return $user;
        });

        $citizenNames = [
            'Budi Santoso', 'Siti Nurhaliza', 'Agus Prasetyo', 'Rina Wulandari', 'Dimas Pratama',
            'Maya Kusuma', 'Fajar Nugroho', 'Lestari Putri', 'Hendra Gunawan', 'Ayu Permata',
        ];
        $citizens = collect($citizenNames)->map(function ($name, $i) use ($passwordWarga, $regions) {
            $user = User::updateOrCreate(['email' => Str::slug($name, '.') . '@gmail.com'], [
                'name' => $name, 'password' => $passwordWarga,
                'phone' => '0813000000' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'region_id' => $regions->values()[$i % $regions->count()], 'is_active' => true,
            ]);
            $user->syncRoles('warga');

            return $user;
        });

        /* ---------- Laporan contoh ---------- */

        if (Report::exists()) {
            return;
        }

        // created_at/updated_at diisi manual agar riwayat demo terlihat realistis
        Model::unguard();

        // Format tiap baris:
        // [kategori, judul, alamat, wilayah, lat, lng, status, JUMLAH DUKUNGAN, umur laporan (hari lalu)]
        // Jumlah dukungan ditulis apa adanya ke kolom upvotes_count (>= 10 = "Prioritas").
        $samples = [
            ['jalan-rusak', 'Lubang besar di depan SDN 03 membahayakan pengendara', 'Jl. Melati Raya No. 12, depan SDN 03', 'Menteng', -6.1953, 106.8312, 'in_progress', 9, 6],
            ['lampu-jalan', 'Lampu penerangan jalan mati sepanjang 300 meter', 'Jl. Kenanga Timur, dekat Pos Ronda RW 05', 'Tebet', -6.2261, 106.8537, 'verified', 8, 4],
            ['saluran-air', 'Selokan tersumbat sampah, air meluap saat hujan', 'Gg. Mawar III, RT 04/RW 02', 'Cempaka Putih', -6.1776, 106.8715, 'submitted', 7, 2],
            ['sampah-liar', 'Tumpukan sampah liar di pinggir kali sudah dua minggu', 'Jl. Kali Baru Barat, samping jembatan', 'Kemayoran', -6.1662, 106.8466, 'in_progress', 6, 5],
            ['pohon', 'Pohon besar miring dan hampir menimpa kabel listrik', 'Jl. Cendana No. 7, depan taman lingkungan', 'Menteng', -6.2012, 106.8379, 'done', 5, 8],
            ['jalan-rusak', 'Aspal mengelupas dan bergelombang di tikungan', 'Jl. Ragunan Raya, dekat halte', 'Pasar Minggu', -6.2891, 106.8203, 'submitted', 4, 1],
            ['fasilitas-umum', 'Pagar taman bermain anak patah dan berkarat', 'Taman RW 09, Jl. Anggrek', 'Kebon Jeruk', -6.1927, 106.7699, 'verified', 3, 3],
            ['lampu-jalan', 'Lampu perempatan mati, rawan kecelakaan malam hari', 'Perempatan Jl. Wijaya & Jl. Senopati', 'Kebayoran Baru', -6.2434, 106.8001, 'done', 6, 9],
            ['saluran-air', 'Gorong-gorong amblas, ada lubang di tengah jalan', 'Jl. Pondok Labu Raya No. 21', 'Cilandak', -6.3043, 106.7943, 'in_progress', 5, 4],
            ['sampah-liar', 'Pembuangan sampah sembarangan di lahan kosong', 'Lahan kosong Jl. Tebet Barat Dalam', 'Tebet', -6.2349, 106.8506, 'rejected', 0, 2],
            ['jalan-rusak', 'Trotoar retak dan membahayakan pejalan kaki', 'Jl. Salemba Raya, depan ruko', 'Cempaka Putih', -6.1894, 106.8534, 'submitted', 2, 0],
            ['fasilitas-umum', 'Halte bus atap bocor, penumpang kehujanan', 'Halte Kebon Jeruk Raya', 'Kebon Jeruk', -6.1883, 106.7745, 'verified', 4, 2],
        ];

        foreach ($samples as $i => [$cat, $title, $address, $region, $lat, $lng, $status, $upvotes, $daysAgo]) {
            $owner = $citizens[$i % $citizens->count()];
            $createdAt = now()->subDays($daysAgo)->subHours(rand(1, 10));
            $officer = $officers->firstWhere('region_id', $regions[$region]) ?? $officers->random();

            $report = Report::create([
                'photo' => $this->photoFor($cat),
                'user_id' => $owner->id,
                'category_id' => $categories[$cat],
                'region_id' => $regions[$region],
                'title' => $title,
                'description' => $title . '. Kondisi ini sudah berlangsung beberapa hari dan mengganggu aktivitas warga sekitar. '
                    . 'Mohon segera ditindaklanjuti oleh dinas terkait.',
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => ReportStatus::Submitted,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->log($report, $owner, ReportStatus::Submitted, 'Laporan diajukan oleh warga dan menunggu disposisi admin.', $createdAt);

            if ($status === 'rejected') {
                $this->log($report, $admin, ReportStatus::Submitted, "Laporan didisposisikan ke {$officer->name} ({$officer->agency->label()}) untuk dicek ke lokasi.", $createdAt->copy()->addHours(2));
                $report->update([
                    'status' => ReportStatus::Rejected, 'assigned_to' => $officer->id, 'verified_by' => $officer->id,
                    'rejection_reason' => 'Foto dan lokasi tidak sesuai dengan deskripsi (terindikasi laporan duplikat).',
                ]);
                $this->log($report, $officer, ReportStatus::Rejected, 'Laporan ditolak setelah dicek di lokasi: foto dan lokasi tidak sesuai dengan deskripsi.', $createdAt->copy()->addHours(5));
                continue;
            }

            if (in_array($status, ['verified', 'in_progress', 'done'], true)) {
                $at = $createdAt->copy()->addHours(6);
                $report->update([
                    'status' => ReportStatus::Verified, 'assigned_to' => $officer->id,
                    'verified_by' => $officer->id, 'verified_at' => $at,
                ]);
                $this->log($report, $admin, ReportStatus::Submitted, "Laporan didisposisikan ke {$officer->name} ({$officer->agency->label()}) untuk dicek ke lokasi.", $createdAt->copy()->addHours(2));
                $this->log($report, $officer, ReportStatus::Verified, 'Laporan terbukti valid setelah dicek di lokasi.', $at);
            }

            if (in_array($status, ['in_progress', 'done'], true)) {
                $at = $createdAt->copy()->addDay();
                $report->update(['status' => ReportStatus::InProgress]);
                $this->log($report, $officer, ReportStatus::InProgress, 'Tim sudah di lokasi dan mulai melakukan perbaikan.', $at);
            }

            if ($status === 'done') {
                $at = $createdAt->copy()->addDays(2);
                $report->update(['status' => ReportStatus::Done, 'completed_at' => $at]);
                $this->log($report, $officer, ReportStatus::Done, 'Perbaikan selesai dikerjakan. Mohon dikonfirmasi bila masih ada kendala.', $at);
            }

            // Jumlah dukungan sesuai angka yang Anda tulis di $samples
            $report->update(['upvotes_count' => $upvotes]);
        }

        Model::reguard();

        $this->command?->info("Laporan contoh dibuat; {$this->photoCount} di antaranya memakai foto.");
    }

    /** Simpan satu salinan foto (terkompres) untuk sebuah laporan; null bila tidak ada sumbernya. */
    private function photoFor(string $slug): ?string
    {
        $sources = $this->sourceFiles($slug);
        if (! $sources) {
            return null;
        }

        // Laporan ke-1 dapat foto ke-1, laporan ke-2 foto ke-2, dst. (berputar bila foto kurang)
        $index = $this->cursor[$slug] = ($this->cursor[$slug] ?? -1) + 1;
        $source = $sources[$index % count($sources)];

        try {
            $path = app(ImageService::class)->store(new UploadedFile($source, basename($source), null, null, true), 'demo');
            $this->photoCount++;

            return $path;
        } catch (\Throwable $e) {
            $this->command?->warn("Foto contoh '{$slug}' dilewati: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Foto lokal: database/seeders/photos/<slug>.jpg, <slug>-1.jpg, <slug>-2.jpg, ...
     * Bila tidak ada, dan kategori punya foto unduhan, pakai itu.
     *
     * @return string[]
     */
    private function sourceFiles(string $slug): array
    {
        if (array_key_exists($slug, $this->sourceCache)) {
            return $this->sourceCache[$slug];
        }

        $found = [];
        $dir = database_path('seeders/photos');

        foreach (is_dir($dir) ? (scandir($dir) ?: []) : [] as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $name = pathinfo($file, PATHINFO_FILENAME);

            // "jalan-rusak", "jalan-rusak-2", "jalan-rusak_3", "jalan-rusak3" (tetapi bukan slug lain)
            $isMatch = $name === $slug || preg_match('/^' . preg_quote($slug, '/') . '[-_ ]?\d+$/i', $name) === 1;

            if ($isMatch && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $found[] = $dir . DIRECTORY_SEPARATOR . $file;
            }
        }

        natsort($found);
        $found = array_values($found);

        if (! $found && isset(self::REMOTE_PHOTOS[$slug])) {
            try {
                $url = 'https://commons.wikimedia.org/wiki/Special:FilePath/'
                    . rawurlencode(self::REMOTE_PHOTOS[$slug]) . '?width=1280';

                $res = Http::timeout(30)
                    ->withHeaders(['User-Agent' => 'SIGAP-demo/1.0 (student project; Laravel seeder)'])
                    ->get($url);

                if ($res->successful() && str_starts_with((string) $res->header('Content-Type'), 'image/')) {
                    $tmp = tempnam(sys_get_temp_dir(), 'sigap');
                    file_put_contents($tmp, $res->body());
                    $found[] = $tmp;
                }
            } catch (\Throwable $e) {
                // Tidak ada internet / diblokir: lanjut tanpa foto (kartu memakai ikon kategori).
            }

            if (! $found) {
                $this->command?->warn("Foto contoh '{$slug}' tidak bisa diunduh; memakai ikon kategori.");
            }
        }

        return $this->sourceCache[$slug] = $found;
    }

    private function log(Report $report, User $user, ReportStatus $status, string $note, $at): void
    {
        $report->updates()->create([
            'user_id' => $user->id, 'status' => $status, 'note' => $note,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }
}