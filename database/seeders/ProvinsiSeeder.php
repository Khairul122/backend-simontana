<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinsi = [
            ['nama' => 'Aceh', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sumatera Utara', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sumatera Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Riau', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jambi', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sumatera Selatan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bengkulu', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lampung', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kepulauan Bangka Belitung', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kepulauan Riau', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'DKI Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jawa Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jawa Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jawa Timur', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Banten', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bali', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Nusa Tenggara Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Nusa Tenggara Timur', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kalimantan Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kalimantan Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kalimantan Selatan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kalimantan Timur', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kalimantan Utara', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sulawesi Utara', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sulawesi Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sulawesi Selatan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sulawesi Tenggara', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gorontalo', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sulawesi Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Maluku', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Maluku Utara', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua Barat', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua Selatan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua Pegunungan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Papua Barat Daya', 'created_at' => now(), 'updated_at' => now()],
        ];

        \DB::table('provinsi')->insert($provinsi);
    }
}
