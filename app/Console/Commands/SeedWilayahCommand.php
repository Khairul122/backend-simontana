<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\WilayahSeeder;

class SeedWilayahCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:seed {--test : Test connection ke GitHub CSV} {--size : Estimate file sizes} {--clean : Hapus semua data wilayah yang ada sebelum seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed SELURUH data wilayah Indonesia dari GitHub CSV dengan performa tinggi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 Starting Indonesian Regional Data Seeding...');
        $this->info('📊 Production-ready seeder with API and fallback support');

        try {
            $seeder = new WilayahSeeder();

            // Test connection jika ada flag --test
            if ($this->option('test')) {
                $this->info('🧪 Testing API connectivity...');
                $seeder->testConnection();
                return 0;
            }

            // Clean data jika ada flag --clean
            if ($this->option('clean')) {
                $this->warn('🗑️  Cleaning existing regional data...');
                $seeder->clean();
                return 0;
            }

            // Jalankan proses seeding
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