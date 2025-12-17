<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kabupaten = [
            // Provinsi DKI Jakarta (id=11)
            ['nama' => 'Jakarta Pusat', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jakarta Utara', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jakarta Barat', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jakarta Selatan', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jakarta Timur', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kepulauan Seribu', 'id_provinsi' => 11, 'created_at' => now(), 'updated_at' => now()],

            // Provinsi Jawa Barat (id=12)
            ['nama' => 'Bandung', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bogor', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cirebon', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sukabumi', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Depok', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bekasi', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Karawang', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Garut', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Tasikmalaya', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Ciamis', 'id_provinsi' => 12, 'created_at' => now(), 'updated_at' => now()],

            // Provinsi Jawa Tengah (id=13)
            ['nama' => 'Semarang', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Surakarta', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pekalongan', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Tegal', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Salatiga', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Magelang', 'id_provinsi' => 13, 'created_at' => now(), 'updated_at' => now()],

            // Provinsi Jawa Timur (id=15)
            ['nama' => 'Surabaya', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Malang', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kediri', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Blitar', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Madiun', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jember', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Probolinggo', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pasuruan', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Mojokerto', 'id_provinsi' => 15, 'created_at' => now(), 'updated_at' => now()],

            // Provinsi Bali (id=17)
            ['nama' => 'Denpasar', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Badung', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gianyar', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Tabanan', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Klungkung', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bangli', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Karangasem', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Buleleng', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Jembrana', 'id_provinsi' => 17, 'created_at' => now(), 'updated_at' => now()],
        ];

        \DB::table('kabupaten')->insert($kabupaten);
    }
}
