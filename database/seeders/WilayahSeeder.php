<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Services\WilayahApiService;
use App\Services\WilayahFallbackProvider;
use App\Services\WilayahDatabaseService;

class WilayahSeeder extends Seeder
{
    /**
     * Data source mode
     */
    private string $dataSource = 'unknown';
    private bool $useFallback = false;

    /**
     * Services
     */
    private WilayahApiService $apiService;
    private WilayahFallbackProvider $fallbackProvider;
    private WilayahDatabaseService $databaseService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiService = new WilayahApiService();
        $this->fallbackProvider = new WilayahFallbackProvider();
        $this->databaseService = new WilayahDatabaseService();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        set_time_limit(0); // Disable time limit for large imports

        $startTime = microtime(true);
        $this->logInfo('🌍 Starting Indonesian regional data seeding process...');

        try {
            // Initialize data source
            $this->initializeDataSource();

            // Clean existing data
            $this->cleanExistingData();

            // Import hierarchical data
            $this->importHierarchicalData();

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->logSuccess("✅ Seeding completed successfully in {$executionTime} seconds");

            // Show final statistics
            $this->showStatistics();

        } catch (\Exception $e) {
            $this->logError('❌ Fatal error during seeding: ' . $e->getMessage());
            Log::error('WilayahSeeder fatal error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to fail the seeder clearly
        }
    }

    /**
     * Initialize and test data source
     */
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

    /**
     * Clean existing data
     */
    private function cleanExistingData(): void
    {
        $this->logInfo('🗑️ Cleaning existing regional data...');
        $this->databaseService->cleanTables();
        $this->logInfo('✅ Data cleaning completed');
    }

    /**
     * Import hierarchical data
     */
    private function importHierarchicalData(): void
    {
        $this->logInfo('📊 Starting hierarchical data import...');

        // Import provinces
        $provincesCount = $this->importProvinces();
        $this->logInfo("✅ Imported {$provincesCount} provinces");

        // Import regencies
        $regenciesCount = $this->importRegencies();
        $this->logInfo("✅ Imported {$regenciesCount} regencies");

        // Import districts (sample for performance)
        $districtsCount = $this->importDistricts();
        $this->logInfo("✅ Imported {$districtsCount} districts");

        // Import villages (sample for performance)
        $villagesCount = $this->importVillages();
        $this->logInfo("✅ Imported {$villagesCount} villages");

        $this->logSuccess('📈 Hierarchical data import completed');
    }

    /**
     * Import provinces
     */
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
                return $this->importProvinces(); // Retry with fallback
            }
            throw new \RuntimeException('Failed to import provinces: ' . $e->getMessage());
        }
    }

    /**
     * Import regencies for all provinces
     */
    private function importRegencies(): int
    {
        $this->logInfo('🏛️ Importing regencies...');

        if ($this->useFallback) {
            // Use comprehensive fallback data
            $regencies = $this->fallbackProvider->getRegencies();
            return $this->databaseService->insertRegencies($regencies);
        }

        // For API mode, we'll get all regencies in one go to avoid multiple requests
        // This is more efficient than per-province requests
        try {
            // Try to get comprehensive regencies data first
            $allRegencies = [];
            $provinces = $this->fallbackProvider->getProvinces(); // Use provinces list for iteration

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

    /**
     * Import districts (sample data for performance)
     */
    private function importDistricts(): int
    {
        $this->logInfo('🏘️ Importing districts (sample data)...');

        // Always use sample districts for performance reasons
        // Real districts data would require thousands of API requests
        $districts = $this->fallbackProvider->getDistricts();
        return $this->databaseService->insertDistricts($districts);
    }

    /**
     * Import villages (sample data for performance)
     */
    private function importVillages(): int
    {
        $this->logInfo('🏠 Importing villages (sample data)...');

        // Always use sample villages for performance reasons
        // Real villages data would require tens of thousands of API requests
        $villages = $this->fallbackProvider->getVillages();
        return $this->databaseService->insertVillages($villages);
    }

    /**
     * Show final statistics
     */
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

        // Verify data integrity
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

    /**
     * Test API connectivity (public method for command)
     */
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

    /**
     * Clean data (public method for command)
     */
    public function clean(): void
    {
        $this->logWarning('🗑️ Cleaning all regional data...');
        $this->databaseService->cleanTables();
        $this->logSuccess('✅ Regional data cleaned successfully');
    }

    /**
     * Log info message
     */
    private function logInfo(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
        Log::info($message);
    }

    /**
     * Log success message
     */
    private function logSuccess(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
        Log::info($message);
    }

    /**
     * Log warning message
     */
    private function logWarning(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
        Log::warning($message);
    }

    /**
     * Log error message
     */
    private function logError(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        }
        Log::error($message);
    }
}