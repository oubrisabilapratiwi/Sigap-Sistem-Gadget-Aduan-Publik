<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Menyajikan foto dari storage/app/public lewat route biasa, sehingga foto tetap
 * tampil walaupun `php artisan storage:link` gagal (umum terjadi di Windows tanpa
 * hak Administrator).
 */
class MediaController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        // Hanya folder foto aplikasi; tolak path traversal ("..") dan karakter aneh.
        abort_unless(
            preg_match('#^(reports|proofs|demo)/(thumbs/)?[A-Za-z0-9._-]+$#', $path) === 1,
            404
        );

        abort_if(str_contains($path, '..'), 404);

        $full = Storage::disk('public')->path($path);
        abort_unless(is_file($full), 404);

        return response()->file($full, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
