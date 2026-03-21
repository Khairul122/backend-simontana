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

            if (isset($data['Infogempa']['gempa']['Shakemap'])) {
                $data['Infogempa']['gempa']['Shakemap'] = 'https://static.bmkg.go.id/' . $data['Infogempa']['gempa']['Shakemap'];
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
            $url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$wilayahId}";
            $data = $this->fetchJson($url);

            if (!$data) {
                return null;
            }

            return $data;
        });
    }

    
    public function getPeringatanDiniCuaca(): ?array
    {
        return Cache::remember('bmkg.peringatan_dini_cuaca', now()->addMinutes(15), function () {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)->get('https://www.bmkg.go.id/alerts/nowcast/id');

                if (!$response->successful()) {
                    Log::error('BMKG API Error: Failed to fetch peringatan dini cuaca', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                if (!$xml || !isset($xml->channel->item)) {
                    Log::error('BMKG API Error: Invalid RSS XML format for peringatan dini cuaca');
                    return null;
                }

                $alerts = [];
                foreach ($xml->channel->item as $item) {
                    $alerts[] = [
                        'title' => (string) $item->title,
                        'link' => (string) $item->link,
                        'description' => strip_tags((string) $item->description),
                        'author' => (string) $item->author,
                        'pubDate' => (string) $item->pubDate,
                        'lastBuildDate' => isset($xml->channel->lastBuildDate) ? (string) $xml->channel->lastBuildDate : null,
                    ];
                }

                return [
                    'alerts' => $alerts,
                    'updated_at' => now()->toIso8601String()
                ];

            } catch (\Exception $e) {
                Log::error('BMKG API Exception: Failed to fetch peringatan dini cuaca', [
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
            'bmkg.peringatan_dini_cuaca',
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
            'peringatan_dini_cuaca_cached' => Cache::has('bmkg.peringatan_dini_cuaca'),
            'prakiraan_cuaca_keys_count' => count((array) Cache::get(self::WEATHER_CACHE_REGISTRY_KEY, [])),
        ];
    }
}
