<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\WilayahSeeder;

class SeedWilayahCommand extends Command
{
    
    protected $signature = 'wilayah:seed {--test : Test connection ke GitHub CSV} {--size : Estimate file sizes} {--clean : Hapus semua data wilayah yang ada sebelum seeding}';

    
    protected $description = 'Seed SELURUH data wilayah Indonesia dari GitHub CSV dengan performa tinggi';

    
    public function handle()
    {
        $this->info('🌍 Starting Indonesian Regional Data Seeding...');
        $this->info('📊 Production-ready seeder with API and fallback support');

        try {
            $seeder = new WilayahSeeder();

            
            if ($this->option('test')) {
                $this->info('🧪 Testing API connectivity...');
                $seeder->testConnection();
                return 0;
            }

            
            if ($this->option('clean')) {
                $this->warn('🗑️  Cleaning existing regional data...');
                $seeder->clean();
                return 0;
            }

            
            $startTime = microtime(true);
            $seeder->run();
            $endTime = microtime(true);

            $executionTime = round($endTime - $startTime, 2);
            $this->info('✅ Regional data seeding completed successfully!');
            $this->info("⚡ Execution time: {$executionTime} seconds");
            $this->info('📈 Indonesian regional data is ready for SIMONTA BENCANA');

        } catch (\Exception $e) {
            $this->error('❌ Fatal error during regional data seeding: ' . $e->getMessage());
            $this->error('📍 Check logs for detailed error information');
            return 1;
        }

        return 0;
    }
}