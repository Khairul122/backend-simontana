<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $desa = [
            // Desa di Menteng (id_kecamatan=1)
            ['nama' => 'Menteng', 'id_kecamatan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cikini', 'id_kecamatan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gondangdia', 'id_kecamatan' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Desa di Coblong (id_kecamatan=4)
            ['nama' => 'Lebak Gede', 'id_kecamatan' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sadang Serang', 'id_kecamatan' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Dago', 'id_kecamatan' => 4, 'created_at' => now(), 'updated_at' => now()],

            // Desa di Gubeng (id_kecamatan=10)
            ['nama' => 'Gubeng Pojok', 'id_kecamatan' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gubeng Kertajaya', 'id_kecamatan' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Airlangga', 'id_kecamatan' => 10, 'created_at' => now(), 'updated_at' => now()],

            // Desa di Denpasar Selatan (id_kecamatan=13)
            ['nama' => 'Sanur', 'id_kecamatan' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sesetan', 'id_kecamatan' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pedungan', 'id_kecamatan' => 13, 'created_at' => now(), 'updated_at' => now()],
        ];

        \DB::table('desa')->insert($desa);
    }
}
