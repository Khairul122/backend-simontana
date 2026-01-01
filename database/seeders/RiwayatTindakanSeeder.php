<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatTindakanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch existing tindaklanjut IDs to ensure foreign key constraints are satisfied
        $tindakLanjutIds = DB::table('tindaklanjut')->pluck('id_tindaklanjut')->toArray();

        // Safety check: if no tindaklanjut records exist, skip seeding to prevent foreign key constraint errors
        if (empty($tindakLanjutIds)) {
            $this->command->warn('No tindaklanjut records found. Skipping RiwayatTindakan seeding to prevent foreign key constraint errors.');
            return;
        }

        // Create riwayat tindakan records using existing tindaklanjut IDs
        $riwayat_tindakan = [
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], // Random existing tindaklanjut ID
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Tim rescue telah diberangkatkan ke lokasi banjir. Membawa perahu karet dan peralatan evakuasi. Estimasi tiba di lokasi 30 menit.',
                'waktu_tindakan' => '2024-12-15 11:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], // Random existing tindaklanjut ID
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Pembersihan material longsor telah selesai. Jalan sudah bisa dilalui kendaraan. Tim dari Dinas PU membantu dengan alat berat.',
                'waktu_tindakan' => '2024-12-14 18:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], // Random existing tindaklanjut ID
                'id_petugas' => 2, // Petugas BPBD Demo
                'keterangan' => 'Koordinasi dengan PDAM untuk pengiriman air bersih menggunakan mobil tangki. Prioritas untuk warga lanjut usia dan anak-anak.',
                'waktu_tindakan' => '2024-12-13 10:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('riwayat_tindakan')->insert($riwayat_tindakan);
    }
}
