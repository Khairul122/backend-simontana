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
            ProvinsiSeeder::class,
            KabupatenSeeder::class,
            KecamatanSeeder::class,
            DesaSeeder::class,
            KategoriBencanaSeeder::class,
            PenggunaSeeder::class,
            LaporanSeeder::class,
            TindaklanjutSeeder::class,
            RiwayatTindakanSeeder::class,
            MonitoringSeeder::class,
            LogActivitySeeder::class,
        ]);
    }
}
