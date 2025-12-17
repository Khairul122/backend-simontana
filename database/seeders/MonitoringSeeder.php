<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monitoring = [
            [
                'id_laporan' => 1, // Laporan banjir
                'id_operator' => 3, // Operator Desa Demo
                'waktu_monitoring' => '2024-12-15 10:45:00',
                'hasil_monitoring' => 'Verifikasi lapangan: Banjir terjadi di 3 RT dengan ketinggian air 30-60 cm. 12 KK terdampak, 2 rumah rusak ringan. Warga sudah mengungsi ke lokasi yang lebih tinggi. Dibutuhkan bantuan makanan dan obat-obatan.',
                'koordinat_gps' => '-6.8915,107.6107',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_laporan' => 2, // Laporan longsor
                'id_operator' => 3, // Operator Desa Demo
                'waktu_monitoring' => '2024-12-14 16:00:00',
                'hasil_monitoring' => 'Verifikasi: Longsor menimbun badan jalan sepanjang 15 meter dengan volume material sekitar 50 meter kubik. Tidak ada korban jiwa. 1 rumah terancam dampak longsor susulan. Warga telah diungsikan sementara.',
                'koordinat_gps' => '-8.6745,115.2121',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_laporan' => 3, // Laporan kekeringan
                'id_operator' => 3, // Operator Desa Demo
                'waktu_monitoring' => '2024-12-13 08:45:00',
                'hasil_monitoring' => 'Verifikasi: 25 KK terdampak kekeringan. Sumur warga mengering, sumber air mata air debitnya sangat kecil. Kebutuhan air bersih sekitar 500 liter/hari. Warga menggunakan air dari PDAM tetapi sering mati.',
                'koordinat_gps' => '-6.1944,106.8229',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('monitoring')->insert($monitoring);
    }
}
