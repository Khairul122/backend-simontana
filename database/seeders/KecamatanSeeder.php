<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecamatan = [
            // Kecamatan di Jakarta Pusat (id_kabupaten=1)
            ['nama' => 'Menteng', 'id_kabupaten' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Tanah Abang', 'id_kabupaten' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cempaka Putih', 'id_kabupaten' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Kecamatan di Bandung (id_kabupaten=7)
            ['nama' => 'Coblong', 'id_kabupaten' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cidadap', 'id_kabupaten' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sukajadi', 'id_kabupaten' => 7, 'created_at' => now(), 'updated_at' => now()],

            // Kecamatan di Bogor (id_kabupaten=8)
            ['nama' => 'Bogor Tengah', 'id_kabupaten' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bogor Utara', 'id_kabupaten' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bogor Selatan', 'id_kabupaten' => 8, 'created_at' => now(), 'updated_at' => now()],

            // Kecamatan di Surabaya (id_kabupaten=22)
            ['nama' => 'Gubeng', 'id_kabupaten' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sukolilo', 'id_kabupaten' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kenjeran', 'id_kabupaten' => 22, 'created_at' => now(), 'updated_at' => now()],

            // Kecamatan di Denpasar (id_kabupaten=31)
            ['nama' => 'Denpasar Selatan', 'id_kabupaten' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Denpasar Utara', 'id_kabupaten' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Denpasar Timur', 'id_kabupaten' => 31, 'created_at' => now(), 'updated_at' => now()],
        ];

        \DB::table('kecamatan')->insert($kecamatan);
    }
}
