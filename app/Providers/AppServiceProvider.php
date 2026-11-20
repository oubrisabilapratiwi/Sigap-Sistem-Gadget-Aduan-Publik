<?php

namespace App\Providers;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Zona waktu & bahasa Indonesia
        $tz = config('sigap.timezone', 'Asia/Jakarta');
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
        Carbon::setLocale('id');
        app()->setLocale('id');

        Schema::defaultStringLength(191);
        Paginator::defaultView('vendor.pagination.sigap');

        // Badge jumlah laporan yang menunggu validasi di sidebar panel
        View::composer('layouts.panel', function ($view) {
            $view->with('pendingCount', Report::status(ReportStatus::Submitted)->count());
        });
    }
}
