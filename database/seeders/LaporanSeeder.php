<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laporan = [
            [
                'id_warga' => 4, // Warga Demo
                'id_kategori' => 1, // Banjir
                'tanggal_lapor' => '2024-12-15 10:30:00',
                'lokasi' => 'Jalan Merdeka No. 123, Kelurahan Lebak Gede, Kecamatan Coblong, Bandung',
                'deskripsi' => 'Terjadi banjir setelah hujan deras selama 3 jam. Air masuk ke rumah warga dengan ketinggian sekitar 50 cm. Beberapa kendaraan tergenang dan akses jalan tersumbat.',
                'foto' => 'uploads/laporan/banjir_bandung_1.jpg',
                'status_laporan' => 'Dilaporkan',
                'prioritas' => 'Tinggi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_warga' => 4, // Warga Demo
                'id_kategori' => 2, // Longsor
                'tanggal_lapor' => '2024-12-14 15:45:00',
                'lokasi' => 'Jalan Perbukitan Hijau RT 02/RW 05, Desa Sanur, Kecamatan Denpasar Selatan, Bali',
                'deskripsi' => 'Tanah longsor terjadi di tebing dekat permukiman warga. Tidak ada korban jiwa namun 1 rumah terancam dan akses jalan terputus. Material longsor menutupi badan jalan sepanjang 20 meter.',
                'foto' => 'uploads/laporan/longsor_sanur_1.jpg',
                'status_laporan' => 'Diverifikasi',
                'prioritas' => 'Sedang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_warga' => 4, // Warga Demo
                'id_kategori' => 3, // Kekeringan
                'tanggal_lapor' => '2024-12-13 08:15:00',
                'lokasi' => 'Dusun Mulyo Rejo, Desa Gondangdia, Kecamatan Menteng, Jakarta Pusat',
                'deskripsi' => 'Warga mengalami kesulitan air bersih selama 2 minggu terakhir. Sumur warga mengering dan sumber air mata air berkurang drastis. Kebutuhan air minum dan MCK terganggu.',
                'foto' => 'uploads/laporan/kekeringan_jakarta_1.jpg',
                'status_laporan' => 'Diterima',
                'prioritas' => 'Rendah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('laporan')->insert($laporan);
    }
}
