<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** slug kategori => nilai App\Enums\Agency */
    private array $map = [
        'jalan-rusak' => 'bina_marga',
        'lampu-jalan' => 'pln',
        'saluran-air' => 'pam_jaya',
        'sampah-liar' => 'lingkungan_hidup',
        'pohon' => 'pertamanan_hutan_kota',
        'fasilitas-umum' => 'dishub',
    ];

    public function up(): void
    {
        // Instansi yang bertanggung jawab menangani kategori kerusakan ini
        Schema::table('categories', function (Blueprint $table) {
            $table->string('agency', 40)->nullable()->after('color');
        });

        foreach ($this->map as $slug => $agency) {
            DB::table('categories')->where('slug', $slug)->update(['agency' => $agency]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('agency');
        });
    }
};
