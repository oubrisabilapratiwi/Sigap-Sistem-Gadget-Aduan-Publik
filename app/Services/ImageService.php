<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Mengompresi foto dari kamera HP (bisa 5-12 MB) menjadi ~100-300 KB:
 *  - koreksi orientasi EXIF (foto tidak miring)
 *  - perkecil sisi terpanjang tanpa memperbesar foto kecil
 *  - simpan sebagai JPEG berkualitas 75%
 *  - buat thumbnail untuk daftar laporan
 */
class ImageService
{
    protected string $disk = 'public';

    /**
     * Simpan foto & thumbnail. Mengembalikan path relatif di disk "public".
     */
    public function store(UploadedFile $file, string $directory = 'reports'): string
    {
        // Foto kamera resolusi tinggi butuh memori GD yang cukup besar.
        @ini_set('memory_limit', '512M');

        $cfg = config('sigap.image');
        $name = Str::uuid()->toString() . '.jpg';
        $path = trim($directory, '/') . '/' . $name;

        $main = $this->prepare($file->getRealPath(), $cfg['max_width'], $cfg['max_width']);
        Storage::disk($this->disk)->put($path, (string) $main->encode('jpg', $cfg['quality']));
        $main->destroy();

        $thumb = $this->prepare($file->getRealPath(), $cfg['thumb_width'], $cfg['thumb_height'], crop: true);
        Storage::disk($this->disk)->put(static::thumbPath($path), (string) $thumb->encode('jpg', $cfg['thumb_quality']));
        $thumb->destroy();

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk($this->disk)->delete([$path, static::thumbPath($path)]);
    }

    /** URL publik sebuah foto (disajikan oleh MediaController, tanpa symlink). */
    public static function url(string $path): string
    {
        return route('media', ['path' => $path]);
    }

    /** reports/abc.jpg  =>  reports/thumbs/abc.jpg */
    public static function thumbPath(string $path): string
    {
        return dirname($path) . '/thumbs/' . basename($path);
    }

    protected function prepare(string $realPath, int $width, int $height, bool $crop = false)
    {
        $img = Image::make($realPath)->orientate();

        if ($crop) {
            $img->fit($width, $height);
        } else {
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // PNG transparan -> JPEG: beri latar putih agar tidak jadi hitam.
        $canvas = Image::canvas($img->width(), $img->height(), '#ffffff');
        $canvas->insert($img, 'top-left');
        $img->destroy();

        return $canvas;
    }
}
