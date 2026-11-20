# SIGAP (Sistem Gadget Aduan Publik)

Aplikasi web pelaporan infrastruktur dan fasilitas umum berbasis wilayah. Warga dapat melaporkan masalah seperti jalan berlubang, lampu jalan mati, saluran air tersumbat, dan sampah liar. Laporan kemudian divalidasi admin dan ditindaklanjuti petugas lapangan.

Dibangun dengan **Laravel 10** dan **MySQL**.

## Fitur

- **Warga**: membuat laporan dengan foto dan lokasi, memantau status laporan, dan memberi dukungan (upvote) pada laporan lain.
- **Admin Pusat**: memvalidasi laporan, menyaring spam, dan menugaskan laporan ke petugas lapangan.
- **Petugas Lapangan**: memperbarui status pengerjaan dan wajib mengunggah foto bukti saat laporan selesai.
- **Alur status laporan**: Diajukan → Diverifikasi → Sedang Dikerjakan → Selesai.
- **Role & Permission** memakai Spatie Laravel Permission.
- **Kompresi foto** otomatis memakai Intervention Image.
- Laporan dapat diurutkan berdasarkan jumlah dukungan terbanyak.

## Teknologi

| Komponen | Keterangan |
|---|---|
| Framework | Laravel 10 (PHP 8.1+) |
| Database | MySQL |
| Hak akses | spatie/laravel-permission |
| Pengolah gambar | intervention/image |

## Prasyarat

- PHP 8.1 atau lebih baru, dengan ekstensi **gd** aktif
- Composer
- MySQL (misalnya lewat XAMPP atau Laragon)

## Cara Menjalankan

1. **Clone repository**
   ```bash
   git clone https://github.com/oubrisabilapratiwi/Sigap-Sistem-Gadget-Aduan-Publik.git
   cd Sigap-Sistem-Gadget-Aduan-Publik
   ```

2. **Install dependensi**
   ```bash
   composer install
   ```

3. **Siapkan file environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Di Windows CMD, gunakan `copy .env.example .env`.

4. **Buat database** bernama `db_sigap` (collation `utf8mb4_unicode_ci`) lewat phpMyAdmin atau MySQL, lalu sesuaikan `.env`:
   ```env
   APP_NAME=SIGAP
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_sigap
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan migrasi dan data demo**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Nyalakan server**
   ```bash
   php artisan serve
   ```
   Buka http://localhost:8000.

## Akun Demo

| Peran | Email | Kata sandi |
|---|---|---|
| Admin | admin.sigap@gmail.com | `AdminSigap#2026` |
| Petugas Lapangan | slamet.riyadi@gmail.com, dewi.anggraini@gmail.com, bambang.hartono@gmail.com | `PetugasSigap#2026` |
| Warga | siti.nurhaliza@gmail.com, agus.prasetyo@gmail.com, rina.wulandari@gmail.com, dimas.pratama@gmail.com, maya.kusuma@gmail.com, fajar.nugroho@gmail.com, lestari.putri@gmail.com, hendra.gunawan@gmail.com, ayu.permata@gmail.com | `WargaSigap#2026` |

## Catatan

- Foto yang diunggah saat membuat laporan ditampilkan lewat alamat `/media/...`, jadi **tidak perlu** menjalankan `php artisan storage:link`.
- Foto contoh untuk data demo ada di `database/seeders/photos/`.

## Mengatasi Error

| Pesan error | Solusi |
|---|---|
| `Access denied` / `Unknown database` | Nama database, username, atau password di `.env` belum cocok |
| `Class "Intervention\Image\..." not found` | Jalankan `composer install` |
| `Class "Spatie\Permission\..." not found` | Jalankan `composer install` |
| Ekstensi `gd` belum aktif | Di `php.ini`, hapus tanda `;` di depan `extension=gd`, lalu restart Apache |

## Lisensi

Dikembangkan untuk keperluan pembelajaran.
