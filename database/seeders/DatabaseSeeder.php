<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Master Data (Parent)
            WilayahSeeder::class, // Includes Provinsi, Kabupaten, Kecamatan, Desa
            KategoriBencanaSeeder::class,

            // Users (Parent for transactional data)
            PenggunaSeeder::class,

            // Transactional Data (Child)
            LaporanSeeder::class,
            TindaklanjutSeeder::class,

            // Additional Transactional Data
            MonitoringSeeder::class,
            RiwayatTindakanSeeder::class,
        ]);
    }
}
