<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GempaService
{
    protected $baseUrl;
    protected $cacheTime;

    public function __construct()
    {
        $this->baseUrl = env('BMKG_API_URL', 'https://data.bmkg.go.id/');
        $this->cacheTime = env('BMKG_CACHE_TIME', 300); // 5 minutes for earthquake data
    }

    /**
     * Get latest earthquake information
     */
    public function getGempaTerbaru()
    {
        $cacheKey = 'gempa_terbaru';

        return Cache::remember($cacheKey, $this->cacheTime, function () {
            try {
                $response = Http::timeout(30)
                    ->get($this->baseUrl . 'DataMKG/TEWS/autogempa.json');

                if ($response->successful()) {
                    return $this->formatGempaData($response->json());
                }

                return $this->getGempaFallback('Terjadi kesalahan saat mengambil data gempa terbaru');

            } catch (\Exception $e) {
                \Log::error('Error fetching gempa terbaru: ' . $e->getMessage());
                return $this->getGempaFallback('Gagal mengambil data gempa terbaru: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get recent earthquake information (last 24 hours)
     */
    public function getGempa24Jam()
    {
        $cacheKey = 'gempa_24_jam';

        return Cache::remember($cacheKey, $this->cacheTime, function () {
            try {
                $response = Http::timeout(30)
                    ->get($this->baseUrl . 'DataMKG/TEWS/gempaterkini.json');

                if ($response->successful()) {
                    return $this->formatGempa24JamData($response->json());
                }

                return [
                    'status' => 'error',
                    'message' => 'Data gempa 24 jam tidak tersedia',
                    'data' => []
                ];

            } catch (\Exception $e) {
                \Log::error('Error fetching gempa 24 jam: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengambil data gempa 24 jam: ' . $e->getMessage(),
                    'data' => []
                ];
            }
        });
    }

    /**
     * Get earthquake with tsunami potential
     */
    public function getGempaTsunami()
    {
        $cacheKey = 'gempa_tsunami';

        return Cache::remember($cacheKey, 600, function () { // 10 minutes cache for tsunami warnings
            try {
                $response = Http::timeout(30)
                    ->get($this->baseUrl . 'DataMKG/TEWS/autogempa.json');

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['Infogempa']['gempa'])) {
                        $gempa = $data['Infogempa']['gempa'];

                        // Check for tsunami potential
                        if (isset($gempa['Potensi']) &&
                            (strpos(strtolower($gempa['Potensi']), 'tsunami') !== false ||
                             strpos(strtolower($gempa['Potensi']), 'berpotensi') !== false)) {

                            return [
                                'status' => 'warning',
                                'message' => 'Terdapat gempa dengan potensi tsunami',
                                'data' => $this->formatSingleGempa($gempa)
                            ];
                        }
                    }
                }

                return [
                    'status' => 'safe',
                    'message' => 'Tidak ada gempa dengan potensi tsunami',
                    'data' => null
                ];

            } catch (\Exception $e) {
                \Log::error('Error checking gempa tsunami: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal memeriksa data gempa tsunami',
                    'data' => null
                ];
            }
        });
    }

    /**
     * Get earthquake history for specific period
     */
    public function getRiwayatGempa($startDate = null, $endDate = null)
    {
        $cacheKey = 'riwayat_gempa_' . ($startDate ?? 'default') . '_' . ($endDate ?? 'default');

        return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            try {
                // For now, return recent earthquakes
                // In the future, this could be extended to use historical data APIs
                $recentQuakes = $this->getGempa24Jam();

                return [
                    'status' => 'success',
                    'periode' => [
                        'mulai' => $startDate ?: Carbon::now()->subDays(7)->format('Y-m-d'),
                        'selesai' => $endDate ?: Carbon::now()->format('Y-m-d')
                    ],
                    'total_gempa' => $recentQuakes['status'] === 'success' ? count($recentQuakes['data']) : 0,
                    'data' => $recentQuakes['status'] === 'success' ? $recentQuakes['data'] : []
                ];

            } catch (\Exception $e) {
                \Log::error('Error fetching riwayat gempa: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengambil riwayat gempa',
                    'data' => []
                ];
            }
        });
    }

    /**
     * Get earthquake statistics
     */
    public function getStatistikGempa()
    {
        $cacheKey = 'statistik_gempa';

        return Cache::remember($cacheKey, 1800, function () {
            try {
                $gempa24Jam = $this->getGempa24Jam();

                $stats = [
                    'status' => 'success',
                    'update_terakhir' => Carbon::now()->toISOString(),
                    'periode' => '24 Jam Terakhir',
                    'total_gempa' => 0,
                    'magnitud_rerata' => 0,
                    'kedalaman_rerata' => 0,
                    'dengan_tsunami' => 0,
                    'magnitud_distribusi' => [
                        'kecil' => 0,    // < 4.0
                        'sedang' => 0,    // 4.0 - 5.9
                        'besar' => 0,     // 6.0 - 6.9
                        'sangat_besar' => 0 // >= 7.0
                    ],
                    'daerah_terbanyak' => []
                ];

                if ($gempa24Jam['status'] === 'success' && !empty($gempa24Jam['data'])) {
                    $earthquakes = $gempa24Jam['data'];
                    $stats['total_gempa'] = count($earthquakes);

                    if ($stats['total_gempa'] > 0) {
                        $totalMagnitude = 0;
                        $totalDepth = 0;
                        $areas = [];

                        foreach ($earthquakes as $quake) {
                            // Calculate magnitude
                            if (isset($quake['magnitudo'])) {
                                $mag = floatval($quake['magnitudo']);
                                $totalMagnitude += $mag;

                                // Distribute by magnitude
                                if ($mag < 4.0) {
                                    $stats['magnitud_distribusi']['kecil']++;
                                } elseif ($mag < 6.0) {
                                    $stats['magnitud_distribusi']['sedang']++;
                                } elseif ($mag < 7.0) {
                                    $stats['magnitud_distribusi']['besar']++;
                                } else {
                                    $stats['magnitud_distribusi']['sangat_besar']++;
                                }
                            }

                            // Calculate depth
                            if (isset($quake['kedalaman'])) {
                                $totalDepth += floatval($quake['kedalaman']);
                            }

                            // Track areas
                            if (isset($quake['wilayah'])) {
                                $area = $quake['wilayah'];
                                if (!isset($areas[$area])) {
                                    $areas[$area] = 0;
                                }
                                $areas[$area]++;
                            }

                            // Check for tsunami potential
                            if (isset($quake['potensi']) &&
                                strpos(strtolower($quake['potensi']), 'tsunami') !== false) {
                                $stats['dengan_tsunami']++;
                            }
                        }

                        $stats['magnitud_rerata'] = round($totalMagnitude / $stats['total_gempa'], 2);
                        $stats['kedalaman_rerata'] = round($totalDepth / $stats['total_gempa'], 2);

                        // Get top areas
                        arsort($areas);
                        $stats['daerah_terbanyak'] = array_slice($areas, 0, 5, true);
                    }
                }

                return $stats;

            } catch (\Exception $e) {
                \Log::error('Error fetching statistik gempa: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengambil statistik gempa'
                ];
            }
        });
    }

    /**
     * Format earthquake data from API response
     */
    private function formatGempaData($data)
    {
        if (!isset($data['Infogempa']['gempa'])) {
            return $this->getGempaFallback('Data gempa tidak tersedia');
        }

        return [
            'status' => 'success',
            'update_terakhir' => Carbon::now()->toISOString(),
            'data' => $this->formatSingleGempa($data['Infogempa']['gempa'])
        ];
    }

    /**
     * Format single earthquake data
     */
    private function formatSingleGempa($gempa)
    {
        return [
            'tanggal' => $gempa['Tanggal'] ?? null,
            'jam' => $gempa['Jam'] ?? null,
            'datetime' => $gempa['DateTime'] ?? null,
            'koordinat' => [
                'lintang' => $gempa['Lintang'] ?? null,
                'bujur' => $gempa['Bujur'] ?? null,
                'coordinates' => $gempa['Coordinates'] ?? null
            ],
            'magnitudo' => $gempa['Magnitude'] ?? null,
            'kedalaman' => $gempa['Kedalaman'] ?? null,
            'wilayah' => $gempa['Wilayah'] ?? null,
            'potensi' => $gempa['Potensi'] ?? null,
            'dirasakan' => $gempa['Dirasakan'] ?? null,
            'shakemap' => $gempa['Shakemap'] ?? null,
            'signifikan' => $this->getSignifikanGempa($gempa)
        ];
    }

    /**
     * Format 24-hour earthquake data
     */
    private function formatGempa24JamData($data)
    {
        if (!isset($data['Infogempa']['gempa'])) {
            return [
                'status' => 'error',
                'message' => 'Data gempa 24 jam tidak tersedia',
                'data' => []
            ];
        }

        $earthquakes = $data['Infogempa']['gempa'];

        // Handle single earthquake or array of earthquakes
        if (!isset($earthquakes[0])) {
            $earthquakes = [$earthquakes];
        }

        $formattedData = [];
        foreach ($earthquakes as $quake) {
            $formattedData[] = $this->formatSingleGempa($quake);
        }

        return [
            'status' => 'success',
            'update_terakhir' => Carbon::now()->toISOString(),
            'periode' => '24 Jam Terakhir',
            'total' => count($formattedData),
            'data' => $formattedData
        ];
    }

    /**
     * Get earthquake significance level
     */
    private function getSignifikanGempa($gempa)
    {
        $magnitude = floatval($gempa['Magnitude'] ?? 0);
        $depth = floatval($gempa['Kedalaman'] ?? 0);
        $potensi = strtolower($gempa['Potensi'] ?? '');

        if ($magnitude >= 7.0) {
            return 'Sangat Berbahaya';
        } elseif ($magnitude >= 6.0) {
            return 'Berbahaya';
        } elseif ($magnitude >= 5.0) {
            return 'Waspada';
        } elseif ($magnitude >= 4.0) {
            return 'Berpotensi';
        } else {
            return 'Ringan';
        }
    }

    /**
     * Get fallback earthquake data
     */
    private function getGempaFallback($message)
    {
        return [
            'status' => 'error',
            'message' => $message,
            'update_terakhir' => Carbon::now()->toISOString(),
            'data' => [
                'tanggal' => Carbon::now()->format('d M Y'),
                'jam' => Carbon::now()->format('H:i:s'),
                'datetime' => Carbon::now()->toISOString(),
                'koordinat' => [
                    'lintang' => null,
                    'bujur' => null,
                    'coordinates' => null
                ],
                'magnitudo' => null,
                'kedalaman' => null,
                'wilayah' => 'Data tidak tersedia',
                'potensi' => 'Tidak diketahui',
                'dirasakan' => '-',
                'shakemap' => null,
                'signifikan' => 'Tidak diketahui'
            ]
        ];
    }

    /**
     * Clear earthquake cache
     */
    public function clearCache()
    {
        Cache::forget('gempa_terbaru');
        Cache::forget('gempa_24_jam');
        Cache::forget('gempa_tsunami');
        Cache::forget('riwayat_gempa_default_default_default');
        Cache::forget('statistik_gempa');

        return true;
    }

    /**
     * Check earthquake by coordinates
     */
    public function checkGempaByCoordinates($lat, $lon, $radius = 50)
    {
        try {
            $gempa24Jam = $this->getGempa24Jam();

            if ($gempa24Jam['status'] !== 'success' || empty($gempa24Jam['data'])) {
                return [
                    'status' => 'error',
                    'message' => 'Data gempa tidak tersedia',
                    'ditemukan' => 0,
                    'data' => []
                ];
            }

            $nearbyQuakes = [];
            $targetLat = floatval($lat);
            $targetLon = floatval($lon);

            foreach ($gempa24Jam['data'] as $quake) {
                if (isset($quake['koordinat']['coordinates'])) {
                    $coords = explode(',', $quake['koordinat']['coordinates']);
                    if (count($coords) >= 2) {
                        $quakeLat = floatval(trim($coords[0]));
                        $quakeLon = floatval(trim($coords[1]));

                        // Calculate distance (simplified)
                        $distance = $this->calculateDistance($targetLat, $targetLon, $quakeLat, $quakeLon);

                        if ($distance <= $radius) {
                            $quake['jarak'] = round($distance, 2);
                            $nearbyQuakes[] = $quake;
                        }
                    }
                }
            }

            return [
                'status' => 'success',
                'koordinat_pencarian' => [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'radius' => $radius
                ],
                'ditemukan' => count($nearbyQuakes),
                'data' => $nearbyQuakes
            ];

        } catch (\Exception $e) {
            \Log::error('Error checking earthquake by coordinates: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal memeriksa gempa berdasarkan koordinat',
                'ditemukan' => 0,
                'data' => []
            ];
        }
    }

    /**
     * Calculate distance between two coordinates (in km)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}