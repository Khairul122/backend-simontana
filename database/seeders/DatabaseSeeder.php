<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    
    public function run(): void
    {
        $this->call([
            
            WilayahSeeder::class, 
            KategoriBencanaSeeder::class,

            
            PenggunaSeeder::class,

            
            LaporanSeeder::class,
            TindaklanjutSeeder::class,

            
            MonitoringSeeder::class,
            RiwayatTindakanSeeder::class,
        ]);
    }
}
