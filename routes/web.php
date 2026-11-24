<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Officer;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIGAP (Sistem Gadget Aduan Publik) — Routes
|--------------------------------------------------------------------------
| Hak akses dipisahkan per peran memakai permission Spatie (middleware "can:"):
|   warga   : report.create, report.vote
|   petugas : officer.access, report.update-progress
|   admin   : admin.access, report.validate, report.assign, officer.manage
*/

/* ---------- Publik ---------- */

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');

// Foto laporan & bukti (tidak butuh storage:link)
Route::get('/media/{path}', MediaController::class)->where('path', '.+')->name('media');

/* ---------- Autentikasi ---------- */

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------- Warga ---------- */

Route::middleware(['auth', 'can:report.create'])->group(function () {
    // Harus didaftarkan sebelum /laporan/{report}
    Route::get('/laporan/buat', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/laporan', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('reports.store');
    Route::get('/laporan-saya', [ReportController::class, 'mine'])->name('reports.mine');
    Route::delete('/laporan/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
});

Route::get('/laporan/{report}', [ReportController::class, 'show'])->name('reports.show');

Route::post('/laporan/{report}/dukung', VoteController::class)
    ->middleware(['auth', 'can:report.vote', 'throttle:60,1'])
    ->name('reports.vote');

/* ---------- Admin Pusat ---------- */

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin.access'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/{report}', [Admin\ReportController::class, 'show'])->name('reports.show');
    Route::post('/laporan/{report}/disposisi', [Admin\ReportController::class, 'dispose'])
        ->middleware('can:report.assign')->name('reports.dispose');
    Route::post('/laporan/{report}/alihkan', [Admin\ReportController::class, 'reassign'])
        ->middleware('can:report.assign')->name('reports.reassign');

    Route::middleware('can:officer.manage')->group(function () {
        Route::get('/petugas', [Admin\OfficerController::class, 'index'])->name('officers.index');
        Route::post('/petugas', [Admin\OfficerController::class, 'store'])->name('officers.store');
        Route::patch('/petugas/{user}/status', [Admin\OfficerController::class, 'toggle'])->name('officers.toggle');
    });
});

/* ---------- Petugas Lapangan ---------- */

Route::prefix('petugas')->name('officer.')->middleware(['auth', 'can:officer.access'])->group(function () {
    Route::get('/tugas', [Officer\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tugas/{report}', [Officer\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tugas/{report}', [Officer\TaskController::class, 'update'])
        ->middleware('can:report.update-progress')->name('tasks.update');
});
