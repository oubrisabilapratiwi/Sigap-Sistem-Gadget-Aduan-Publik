<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Instansi / asal dinas petugas lapangan (lihat App\Enums\Agency)
        Schema::table('users', function (Blueprint $table) {
            $table->string('agency', 40)->nullable()->after('region_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('agency');
        });
    }
};
