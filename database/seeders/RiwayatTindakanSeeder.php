<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RiwayatTindakanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $riwayat_tindakan = [
            [
                'tindaklanjut_id' => 1, // Tindaklanjut banjir
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Tim rescue telah diberangkatkan ke lokasi banjir. Membawa perahu karet dan peralatan evakuasi. Estimasi tiba di lokasi 30 menit.',
                'waktu_tindakan' => '2024-12-15 11:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => 2, // Tindaklanjut longsor
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Pembersihan material longsor telah selesai. Jalan sudah bisa dilalui kendaraan. Tim dari Dinas PU membantu dengan alat berat.',
                'waktu_tindakan' => '2024-12-14 18:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => 3, // Tindaklanjut kekeringan
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Koordinasi dengan PDAM untuk pengiriman air bersih menggunakan mobil tangki. Prioritas untuk warga lanjut usia dan anak-anak.',
                'waktu_tindakan' => '2024-12-13 10:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('riwayat_tindakan')->insert($riwayat_tindakan);
    }
}
