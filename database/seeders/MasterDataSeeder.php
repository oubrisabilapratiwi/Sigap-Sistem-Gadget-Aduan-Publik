<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Jalan Rusak / Berlubang', 'jalan-rusak', 'construction', '#f97316'],
            ['Lampu Jalan Mati', 'lampu-jalan', 'lightbulb', '#eab308'],
            ['Saluran Air Tersumbat', 'saluran-air', 'droplets', '#0ea5e9'],
            ['Sampah Liar', 'sampah-liar', 'trash-2', '#16a34a'],
            ['Pohon Tumbang / Rawan', 'pohon', 'trees', '#15803d'],
            ['Fasilitas Umum Rusak', 'fasilitas-umum', 'building-2', '#8b5cf6'],
            ['Lainnya', 'lainnya', 'ellipsis', '#64748b'],
        ];

        foreach ($categories as [$name, $slug, $icon, $color]) {
            Category::updateOrCreate(['slug' => $slug], compact('name', 'icon', 'color'));
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
