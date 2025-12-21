<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BmkgService
{
    private const CACHE_DURATION = 60; // Cache duration in minutes

    /**
     * Get latest earthquake data from BMKG
     *
     * @return array|null
     */
    public function getGempaTerbaru(): ?array
    {
        return Cache::remember('bmkg.gempa_terbaru', now()->addMinutes(self::CACHE_DURATION), function () {
            try {
                $response = Http::timeout(10)->get('https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch gempa terbaru', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $data = $response->json();

                // Validate response structure
                if (!isset($data['Infogempa']) || !isset($data['Infogempa']['gempa'])) {
                    Log::error('BMKG API Error: Invalid response structure for gempa terbaru');
                    return null;
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch gempa terbaru', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Get list of recent earthquakes from BMKG
     *
     * @return array|null
     */
    public function getDaftarGempa(): ?array
    {
        return Cache::remember('bmkg.daftar_gempa', now()->addMinutes(self::CACHE_DURATION), function () {
            try {
                $response = Http::timeout(10)->get('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch daftar gempa', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $data = $response->json();

                // Validate response structure
                if (!isset($data['Infogempa']) || !isset($data['Infogempa']['gempa'])) {
                    Log::error('BMKG API Error: Invalid response structure for daftar gempa');
                    return null;
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch daftar gempa', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Get earthquake felt data from BMKG
     *
     * @return array|null
     */
    public function getGempaDirasakan(): ?array
    {
        return Cache::remember('bmkg.gempa_dirasakan', now()->addMinutes(self::CACHE_DURATION), function () {
            try {
                $response = Http::timeout(10)->get('https://data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.json');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch gempa dirasakan', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $data = $response->json();

                // Validate response structure
                if (!isset($data['Infogempa']) || !isset($data['Infogempa']['gempa'])) {
                    Log::error('BMKG API Error: Invalid response structure for gempa dirasakan');
                    return null;
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch gempa dirasakan', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Get weather forecast data from BMKG
     *
     * @param string $wilayahId
     * @return array|null
     */
    public function getPrakiraanCuaca(string $wilayahId): ?array
    {
        $cacheKey = "bmkg.prakiraan_cuaca.{$wilayahId}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($wilayahId) {
            try {
                $url = "https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-{$wilayahId}.xml";
                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch prakiraan cuaca', [
                        'wilayah_id' => $wilayahId,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                // Parse XML to array (you might need to implement XML parsing)
                // For now, return raw response
                return [
                    'xml_data' => $response->body(),
                    'wilayah_id' => $wilayahId
                ];

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch prakiraan cuaca', [
                    'wilayah_id' => $wilayahId,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Get tsunami warning data from BMKG
     *
     * @return array|null
     */
    public function getPeringatanTsunami(): ?array
    {
        return Cache::remember('bmkg.peringatan_tsunami', now()->addMinutes(5), function () {
            try {
                $response = Http::timeout(10)->get('https://data.bmkg.go.id/DataMKG/TEWS/ipmap.txt');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch peringatan tsunami', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                // Parse text data to array
                $lines = explode("\n", trim($response->body()));
                $tsunamiData = [];

                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        $tsunamiData[] = trim($line);
                    }
                }

                return [
                    'raw_data' => $response->body(),
                    'parsed_data' => $tsunamiData,
                    'updated_at' => now()
                ];

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch peringatan tsunami', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Clear all BMKG cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            'bmkg.gempa_terbaru',
            'bmkg.daftar_gempa',
            'bmkg.gempa_dirasakan',
            'bmkg.peringatan_tsunami'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear weather forecast cache patterns
        $weatherCacheKeys = Cache::getRedis()?->keys('bmkg.prakiraan_cuaca.*');
        if ($weatherCacheKeys) {
            foreach ($weatherCacheKeys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Get cache status and information
     *
     * @return array
     */
    public function getCacheStatus(): array
    {
        return [
            'cache_duration_minutes' => self::CACHE_DURATION,
            'gempa_terbaru_cached' => Cache::has('bmkg.gempa_terbaru'),
            'daftar_gempa_cached' => Cache::has('bmkg.daftar_gempa'),
            'gempa_dirasakan_cached' => Cache::has('bmkg.gempa_dirasakan'),
            'peringatan_tsunami_cached' => Cache::has('bmkg.peringatan_tsunami'),
        ];
    }
}