<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class WilayahDatabaseService
{
    private const CHUNK_SIZE = 1000;

    /**
     * Clean all wilayah tables
     */
    public function cleanTables(): void
    {
        $tables = ['desa', 'kecamatan', 'kabupaten', 'provinsi'];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
                Log::info("Table {$table} truncated successfully");
            } catch (\Exception $e) {
                Log::error("Failed to truncate table {$table}: " . $e->getMessage());
                throw new \RuntimeException("Failed to clean table {$table}: " . $e->getMessage());
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Insert provinces data
     */
    public function insertProvinces(array $provinces): int
    {
        return $this->insertBatch('provinsi', $this->mapProvincesData($provinces));
    }

    /**
     * Insert regencies data
     */
    public function insertRegencies(array $regencies): int
    {
        return $this->insertBatch('kabupaten', $this->mapRegenciesData($regencies));
    }

    /**
     * Insert districts data
     */
    public function insertDistricts(array $districts): int
    {
        return $this->insertBatch('kecamatan', $this->mapDistrictsData($districts));
    }

    /**
     * Insert villages data
     */
    public function insertVillages(array $villages): int
    {
        return $this->insertBatch('desa', $this->mapVillagesData($villages));
    }

    /**
     * Generic batch insert method
     */
    private function insertBatch(string $table, array $data): int
    {
        if (empty($data)) {
            Log::warning("No data to insert into {$table}");
            return 0;
        }

        $chunks = array_chunk($data, self::CHUNK_SIZE);
        $totalInserted = 0;

        Schema::disableForeignKeyConstraints();

        foreach ($chunks as $index => $chunk) {
            try {
                DB::table($table)->insert($chunk);
                $chunkCount = count($chunk);
                $totalInserted += $chunkCount;

                Log::info("Inserted chunk " . ($index + 1) . " into {$table}: {$chunkCount} records");
            } catch (\Exception $e) {
                Log::error("Failed to insert chunk " . ($index + 1) . " into {$table}: " . $e->getMessage());
                throw new \RuntimeException("Failed to insert data into {$table}: " . $e->getMessage());
            }
        }

        Schema::enableForeignKeyConstraints();

        Log::info("Successfully inserted {$totalInserted} records into {$table}");
        return $totalInserted;
    }

    /**
     * Map provinces data for database insertion
     */
    private function mapProvincesData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);
    }

    /**
     * Map regencies data for database insertion
     */
    private function mapRegenciesData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'id_provinsi' => $item['province_id'],
                'nama' => $item['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);
    }

    /**
     * Map districts data for database insertion
     */
    private function mapDistrictsData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'id_kabupaten' => $item['regency_id'],
                'nama' => $item['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);
    }

    /**
     * Map villages data for database insertion
     */
    private function mapVillagesData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'id_kecamatan' => $item['district_id'],
                'nama' => $item['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);
    }

    /**
     * Get statistics for verification
     */
    public function getStatistics(): array
    {
        return [
            'provinsi' => DB::table('provinsi')->count(),
            'kabupaten' => DB::table('kabupaten')->count(),
            'kecamatan' => DB::table('kecamatan')->count(),
            'desa' => DB::table('desa')->count(),
        ];
    }

    /**
     * Verify data integrity
     */
    public function verifyIntegrity(): array
    {
        $issues = [];

        // Check if provinces have regencies
        $provincesWithoutRegencies = DB::table('provinsi')
            ->leftJoin('kabupaten', 'provinsi.id', '=', 'kabupaten.id_provinsi')
            ->whereNull('kabupaten.id')
            ->count();

        if ($provincesWithoutRegencies > 0) {
            $issues[] = "{$provincesWithoutRegencies} provinces without regencies";
        }

        // Check if regencies have districts
        $regenciesWithoutDistricts = DB::table('kabupaten')
            ->leftJoin('kecamatan', 'kabupaten.id', '=', 'kecamatan.id_kabupaten')
            ->whereNull('kecamatan.id')
            ->count();

        if ($regenciesWithoutDistricts > 0) {
            $issues[] = "{$regenciesWithoutDistricts} regencies without districts";
        }

        // Check if districts have villages
        $districtsWithoutVillages = DB::table('kecamatan')
            ->leftJoin('desa', 'kecamatan.id', '=', 'desa.id_kecamatan')
            ->whereNull('desa.id')
            ->count();

        if ($districtsWithoutVillages > 0) {
            $issues[] = "{$districtsWithoutVillages} districts without villages";
        }

        return $issues;
    }
}