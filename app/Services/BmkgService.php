<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BmkgService
{
    private const CACHE_DURATION = 60; 
    private const WEATHER_CACHE_REGISTRY_KEY = 'bmkg.prakiraan_cuaca.keys';
    private const HTTP_TIMEOUT_SECONDS = 4;
    private const HTTP_RETRY_ATTEMPTS = 1;
    private const HTTP_RETRY_SLEEP_MS = 150;

    private function fetchJson(string $url): ?array
    {
        try {
            $response = Http::retry(self::HTTP_RETRY_ATTEMPTS, self::HTTP_RETRY_SLEEP_MS)
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                Log::error('BMKG API Error: HTTP request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('BMKG API Exception: HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function rememberWeatherCacheKey(string $cacheKey): void
    {
        $existing = Cache::get(self::WEATHER_CACHE_REGISTRY_KEY, []);

        if (!is_array($existing)) {
            $existing = [];
        }

        if (!in_array($cacheKey, $existing, true)) {
            $existing[] = $cacheKey;
        }

        Cache::put(self::WEATHER_CACHE_REGISTRY_KEY, $existing, now()->addDays(7));
    }

    
    public function getGempaTerbaru(): ?array
    {
        return Cache::remember('bmkg.gempa_terbaru', now()->addMinutes(self::CACHE_DURATION), function () {
            $data = $this->fetchJson('https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json');

            if (!isset($data['Infogempa']['gempa'])) {
                Log::error('BMKG API Error: Invalid response structure for gempa terbaru');
                return null;
            }

            return $data;
        });
    }

    
    public function getDaftarGempa(): ?array
    {
        return Cache::remember('bmkg.daftar_gempa', now()->addMinutes(self::CACHE_DURATION), function () {
            $data = $this->fetchJson('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json');

            if (!isset($data['Infogempa']['gempa'])) {
                Log::error('BMKG API Error: Invalid response structure for daftar gempa');
                return null;
            }

            return $data;
        });
    }

    
    public function getGempaDirasakan(): ?array
    {
        return Cache::remember('bmkg.gempa_dirasakan', now()->addMinutes(self::CACHE_DURATION), function () {
            $data = $this->fetchJson('https://data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.json');

            if (!isset($data['Infogempa']['gempa'])) {
                Log::error('BMKG API Error: Invalid response structure for gempa dirasakan');
                return null;
            }

            return $data;
        });
    }

    
    public function getPrakiraanCuaca(string $wilayahId): ?array
    {
        $cacheKey = "bmkg.prakiraan_cuaca.{$wilayahId}";

        $this->rememberWeatherCacheKey($cacheKey);

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($wilayahId) {
            try {
                $url = "https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-{$wilayahId}.xml";
                $response = Http::retry(self::HTTP_RETRY_ATTEMPTS, self::HTTP_RETRY_SLEEP_MS)
                    ->timeout(self::HTTP_TIMEOUT_SECONDS)
                    ->get($url);

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch prakiraan cuaca', [
                        'wilayah_id' => $wilayahId,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                
                
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

    
    public function getPeringatanTsunami(): ?array
    {
        return Cache::remember('bmkg.peringatan_tsunami', now()->addMinutes(5), function () {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)->get('https://data.bmkg.go.id/DataMKG/TEWS/ipmap.txt');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch peringatan tsunami', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                
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

    
    public function clearCache(): void
    {
        $cacheKeys = [
            'bmkg.gempa_terbaru',
            'bmkg.daftar_gempa',
            'bmkg.gempa_dirasakan',
            'bmkg.peringatan_tsunami',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        $weatherCacheKeys = Cache::get(self::WEATHER_CACHE_REGISTRY_KEY, []);
        if (is_array($weatherCacheKeys)) {
            foreach ($weatherCacheKeys as $key) {
                Cache::forget((string) $key);
            }
        }

        Cache::forget(self::WEATHER_CACHE_REGISTRY_KEY);
    }

    
    public function getCacheStatus(): array
    {
        return [
            'cache_duration_minutes' => self::CACHE_DURATION,
            'gempa_terbaru_cached' => Cache::has('bmkg.gempa_terbaru'),
            'daftar_gempa_cached' => Cache::has('bmkg.daftar_gempa'),
            'gempa_dirasakan_cached' => Cache::has('bmkg.gempa_dirasakan'),
            'peringatan_tsunami_cached' => Cache::has('bmkg.peringatan_tsunami'),
            'prakiraan_cuaca_keys_count' => count((array) Cache::get(self::WEATHER_CACHE_REGISTRY_KEY, [])),
        ];
    }
}
