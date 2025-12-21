<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TindaklanjutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tindaklanjut = [
            [
                'laporan_id' => 1, // Banjir di Perumahan Sukajadi
                'id_petugas' => 2, // Petugas BPBD
                'tanggal_tanggapan' => '2024-12-15 11:00:00',
                'status' => 'Menuju Lokasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'laporan_id' => 3, // Kebakaran Hutan Kecil
                'id_petugas' => 2, // Petugas BPBD
                'tanggal_tanggapan' => '2024-12-13 17:00:00',
                'status' => 'Selesai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('tindaklanjut')->insert($tindaklanjut);
    }
}
