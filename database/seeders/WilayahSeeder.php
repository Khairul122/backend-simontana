<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Services\WilayahApiService;
use App\Services\WilayahFallbackProvider;
use App\Services\WilayahDatabaseService;

class WilayahSeeder extends Seeder
{
    
    private string $dataSource = 'unknown';
    private bool $useFallback = false;

    
    private WilayahApiService $apiService;
    private WilayahFallbackProvider $fallbackProvider;
    private WilayahDatabaseService $databaseService;

    
    public function __construct()
    {
        $this->apiService = new WilayahApiService();
        $this->fallbackProvider = new WilayahFallbackProvider();
        $this->databaseService = new WilayahDatabaseService();
    }

    
    public function run(): void
    {
        set_time_limit(0); 

        $startTime = microtime(true);
        $this->logInfo('🌍 Starting Indonesian regional data seeding process...');

        try {
            
            $this->initializeDataSource();

            
            $this->cleanExistingData();

            
            $this->importHierarchicalData();

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->logSuccess("✅ Seeding completed successfully in {$executionTime} seconds");

            
            $this->showStatistics();

        } catch (\Exception $e) {
            $this->logError('❌ Fatal error during seeding: ' . $e->getMessage());
            Log::error('WilayahSeeder fatal error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; 
        }
    }

    
    private function initializeDataSource(): void
    {
        $this->logInfo('🔍 Testing API connectivity...');

        $testResult = $this->apiService->testConnectivity();

        if ($testResult['status'] === 'connected') {
            $this->dataSource = 'api';
            $this->useFallback = false;
            $this->logInfo('✅ Using API data source: Imigrasi.go.id');
        } else {
            $this->dataSource = 'fallback';
            $this->useFallback = true;
            $this->logWarning('⚠️ API unavailable, using fallback data source');
            $this->logWarning('Reason: ' . $testResult['message']);
        }
    }

    
    private function cleanExistingData(): void
    {
        $this->logInfo('🗑️ Cleaning existing regional data...');
        $this->databaseService->cleanTables();
        $this->logInfo('✅ Data cleaning completed');
    }

    
    private function importHierarchicalData(): void
    {
        $this->logInfo('📊 Starting hierarchical data import...');

        
        $provincesCount = $this->importProvinces();
        $this->logInfo("✅ Imported {$provincesCount} provinces");

        
        $regenciesCount = $this->importRegencies();
        $this->logInfo("✅ Imported {$regenciesCount} regencies");

        
        $districtsCount = $this->importDistricts();
        $this->logInfo("✅ Imported {$districtsCount} districts");

        
        $villagesCount = $this->importVillages();
        $this->logInfo("✅ Imported {$villagesCount} villages");

        $this->logSuccess('📈 Hierarchical data import completed');
    }

    
    private function importProvinces(): int
    {
        $this->logInfo('📍 Importing provinces...');

        try {
            $provinces = $this->useFallback
                ? $this->fallbackProvider->getProvinces()
                : $this->apiService->getProvinces();

            return $this->databaseService->insertProvinces($provinces);

        } catch (\Exception $e) {
            if (!$this->useFallback) {
                $this->logWarning('⚠️ API failed, switching to fallback for provinces');
                $this->useFallback = true;
                return $this->importProvinces(); 
            }
            throw new \RuntimeException('Failed to import provinces: ' . $e->getMessage());
        }
    }

    
    private function importRegencies(): int
    {
        $this->logInfo('🏛️ Importing regencies...');

        if ($this->useFallback) {
            
            $regencies = $this->fallbackProvider->getRegencies();
            return $this->databaseService->insertRegencies($regencies);
        }

        
        
        try {
            
            $allRegencies = [];
            $provinces = $this->fallbackProvider->getProvinces(); 

            foreach ($provinces as $province) {
                $this->logInfo("   📡 Fetching regencies for {$province['name']}...");
                $provinceRegencies = $this->apiService->getRegenciesByProvince($province['id']);
                $allRegencies = array_merge($allRegencies, $provinceRegencies);
            }

            return $this->databaseService->insertRegencies($allRegencies);

        } catch (\Exception $e) {
            $this->logWarning('⚠️ API mode failed for regencies, switching to fallback');
            $this->useFallback = true;
            $regencies = $this->fallbackProvider->getRegencies();
            return $this->databaseService->insertRegencies($regencies);
        }
    }

    
    private function importDistricts(): int
    {
        $this->logInfo('🏘️ Importing districts (sample data)...');

        
        
        $districts = $this->fallbackProvider->getDistricts();
        return $this->databaseService->insertDistricts($districts);
    }

    
    private function importVillages(): int
    {
        $this->logInfo('🏠 Importing villages (sample data)...');

        
        
        $villages = $this->fallbackProvider->getVillages();
        return $this->databaseService->insertVillages($villages);
    }

    
    private function showStatistics(): void
    {
        $this->logInfo('📊 === FINAL STATISTICS ===');

        $stats = $this->databaseService->getStatistics();
        $total = array_sum($stats);

        $this->logInfo("📍 Provinces: {$stats['provinsi']}");
        $this->logInfo("🏛️ Regencies: {$stats['kabupaten']}");
        $this->logInfo("🏘️ Districts: {$stats['kecamatan']}");
        $this->logInfo("🏠 Villages: {$stats['desa']}");
        $this->logInfo("📈 Total entries: {$total}");

        
        $issues = $this->databaseService->verifyIntegrity();
        if (empty($issues)) {
            $this->logSuccess('✅ Data integrity verified - no issues found');
        } else {
            $this->logWarning('⚠️ Data integrity issues:');
            foreach ($issues as $issue) {
                $this->logWarning("   - {$issue}");
            }
        }

        $this->logInfo("🔗 Data source: {$this->dataSource}");
        $this->logSuccess('🎉 Indonesian regional data is ready for use!');
    }

    
    public function testConnection(): void
    {
        $this->logInfo('🌐 Testing API connectivity...');

        $result = $this->apiService->testConnectivity();

        if ($result['status'] === 'connected') {
            $count = count($result['provinces'] ?? []);
            $this->logSuccess("✅ API Connected ({$count} provinces available)");
        } else {
            $this->logError("❌ API Failed: {$result['message']}");
        }
    }

    
    public function clean(): void
    {
        $this->logWarning('🗑️ Cleaning all regional data...');
        $this->databaseService->cleanTables();
        $this->logSuccess('✅ Regional data cleaned successfully');
    }

    
    private function logInfo(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
        Log::info($message);
    }

    
    private function logSuccess(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
        Log::info($message);
    }

    
    private function logWarning(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
        Log::warning($message);
    }

    
    private function logError(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        }
        Log::error($message);
    }
}