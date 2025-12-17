<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $log_activity = [
            [
                'user_id' => 4, // Warga Demo
                'role' => 'Warga',
                'aktivitas' => 'Pengguna warga_demo melaporkan bencana banjir di Lebak Gede',
                'endpoint' => '/api/laporan/create',
                'ip_address' => '192.168.1.100',
                'device_info' => 'Mozilla/5.0 (Android 12; Mobile) Safari/537.36',
                'created_at' => '2024-12-15 10:30:00',
            ],
            [
                'user_id' => 3, // Operator Desa Demo
                'role' => 'OperatorDesa',
                'aktivitas' => 'Operator operator_desa melakukan verifikasi laporan banjir di lapangan',
                'endpoint' => '/api/laporan/1/verify',
                'ip_address' => '192.168.1.101',
                'device_info' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                'created_at' => '2024-12-15 10:45:00',
            ],
            [
                'user_id' => 2, // Petugas BPBD Demo
                'role' => 'PetugasBPBD',
                'aktivitas' => 'Petugas petugas_bpbd memberikan tanggapan untuk laporan banjir',
                'endpoint' => '/api/tindaklanjut/create',
                'ip_address' => '192.168.1.102',
                'device_info' => 'Mozilla/5.0 (iPhone; iOS 17) Safari/605.1.15',
                'created_at' => '2024-12-15 11:00:00',
            ],
        ];

        \DB::table('log_activity')->insert($log_activity);
    }
}
