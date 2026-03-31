<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WilayahKemendagriSeeder extends Seeder
{
    private const SOURCE_URL = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        set_time_limit(0);

        $startTime = microtime(true);
        $this->info('Starting wilayah import from cahyadsn/wilayah ...');

        $this->truncateWilayahTables();
        $stats = $this->streamAndImport();

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("Done. Imported in {$elapsed}s");
        $this->info('provinsi: ' . $stats['provinsi']);
        $this->info('kabupaten: ' . $stats['kabupaten']);
        $this->info('kecamatan: ' . $stats['kecamatan']);
        $this->info('desa: ' . $stats['desa']);
    }

    private function streamAndImport(): array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'wilayah_');
        if ($tempFile === false) {
            throw new \RuntimeException('Failed creating temporary file for wilayah import.');
        }

        $response = Http::timeout(300)
            ->withOptions(['sink' => $tempFile])
            ->get(self::SOURCE_URL);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed downloading wilayah.sql: HTTP ' . $response->status());
        }

        $now = now();
        $buffers = [
            'provinsi' => [],
            'kabupaten' => [],
            'kecamatan' => [],
            'desa' => [],
        ];
        $counts = [
            'provinsi' => 0,
            'kabupaten' => 0,
            'kecamatan' => 0,
            'desa' => 0,
        ];

        $handle = fopen($tempFile, 'rb');
        if ($handle === false) {
            @unlink($tempFile);
            throw new \RuntimeException('Failed opening downloaded wilayah.sql.');
        }

        Schema::disableForeignKeyConstraints();

        try {
            while (($line = fgets($handle)) !== false) {
                if (!str_contains($line, "('")) {
                    continue;
                }

                preg_match_all("/\('([^']+)'\s*,\s*'((?:[^']|'{2})*)'\)/", $line, $matches, PREG_SET_ORDER);

                if (empty($matches)) {
                    continue;
                }

                foreach ($matches as $row) {
                    $mapped = $this->mapRow($row[1], str_replace("''", "'", $row[2]), $now);
                    if ($mapped === null) {
                        continue;
                    }

                    $table = $mapped['table'];
                    $buffers[$table][] = $mapped['row'];
                    $counts[$table]++;

                    if (count($buffers[$table]) >= self::CHUNK_SIZE) {
                        DB::table($table)->insert($buffers[$table]);
                        $buffers[$table] = [];
                    }
                }
            }

            foreach ($buffers as $table => $rows) {
                if (!empty($rows)) {
                    DB::table($table)->insert($rows);
                }
                Log::info('WilayahKemendagriSeeder inserted rows.', [
                    'table' => $table,
                    'count' => $counts[$table],
                ]);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
            fclose($handle);
            @unlink($tempFile);
        }

        return $counts;
    }

    private function mapRow(string $kode, string $nama, $now): ?array
    {
        $parts = explode('.', $kode);
        $id = (int) str_replace('.', '', $kode);
        $level = count($parts);

        if ($level === 1) {
            return [
                'table' => 'provinsi',
                'row' => [
                    'id' => $id,
                    'nama' => $nama,
                    'adm1' => $kode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
        }

        if ($level === 2) {
            return [
                'table' => 'kabupaten',
                'row' => [
                    'id' => $id,
                    'id_provinsi' => (int) $parts[0],
                    'nama' => $nama,
                    'adm2' => $kode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
        }

        if ($level === 3) {
            return [
                'table' => 'kecamatan',
                'row' => [
                    'id' => $id,
                    'id_kabupaten' => (int) ($parts[0] . $parts[1]),
                    'nama' => $nama,
                    'adm3' => $kode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
        }

        if ($level === 4) {
            return [
                'table' => 'desa',
                'row' => [
                    'id' => $id,
                    'id_kecamatan' => (int) ($parts[0] . $parts[1] . $parts[2]),
                    'nama' => $nama,
                    'adm4' => $kode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
        }

        return null;
    }

    private function truncateWilayahTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('desa')->truncate();
        DB::table('kecamatan')->truncate();
        DB::table('kabupaten')->truncate();
        DB::table('provinsi')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }

        Log::info($message);
    }
}
