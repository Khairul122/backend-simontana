<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatTindakanSeeder extends Seeder
{
    
    public function run(): void
    {
        
        $tindakLanjutIds = DB::table('tindaklanjut')->pluck('id_tindaklanjut')->toArray();

        
        if (empty($tindakLanjutIds)) {
            $this->command->warn('No tindaklanjut records found. Skipping RiwayatTindakan seeding to prevent foreign key constraint errors.');
            return;
        }

        
        $riwayat_tindakan = [
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], 
                'id_petugas' => 2, 
                'keterangan' => 'Tim rescue telah diberangkatkan ke lokasi banjir. Membawa perahu karet dan peralatan evakuasi. Estimasi tiba di lokasi 30 menit.',
                'waktu_tindakan' => '2024-12-15 11:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], 
                'id_petugas' => 2, 
                'keterangan' => 'Pembersihan material longsor telah selesai. Jalan sudah bisa dilalui kendaraan. Tim dari Dinas PU membantu dengan alat berat.',
                'waktu_tindakan' => '2024-12-14 18:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tindaklanjut_id' => $tindakLanjutIds[array_rand($tindakLanjutIds)], 
                'id_petugas' => 2, 
                'keterangan' => 'Koordinasi dengan PDAM untuk pengiriman air bersih menggunakan mobil tangki. Prioritas untuk warga lanjut usia dan anak-anak.',
                'waktu_tindakan' => '2024-12-13 10:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('riwayat_tindakan')->insert($riwayat_tindakan);
    }
}
