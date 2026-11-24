<?php

namespace Database\Seeders;

use App\Enums\Agency;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Jalan Rusak / Berlubang', 'jalan-rusak', 'construction', '#f97316', Agency::BinaMarga],
            ['Lampu Jalan Mati', 'lampu-jalan', 'lightbulb', '#eab308', Agency::Pln],
            ['Saluran Air Tersumbat', 'saluran-air', 'droplets', '#0ea5e9', Agency::PamJaya],
            ['Sampah Liar', 'sampah-liar', 'trash-2', '#16a34a', Agency::LingkunganHidup],
            ['Pohon Tumbang / Rawan', 'pohon', 'trees', '#15803d', Agency::PertamananHutanKota],
            ['Fasilitas Umum Rusak', 'fasilitas-umum', 'building-2', '#8b5cf6', Agency::Dishub],
            ['Lainnya', 'lainnya', 'ellipsis', '#64748b', null],
        ];

        foreach ($categories as [$name, $slug, $icon, $color, $agency]) {
            Category::updateOrCreate(['slug' => $slug], compact('name', 'icon', 'color', 'agency'));
        }

        $regions = [
            'Menteng', 'Tebet', 'Cempaka Putih', 'Kebon Jeruk',
            'Kemayoran', 'Pasar Minggu', 'Kebayoran Baru', 'Cilandak',
        ];

        foreach ($regions as $name) {
            Region::firstOrCreate(['name' => $name]);
        }
    }
}
