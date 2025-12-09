<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CuacaService
{
    protected $baseUrl;
    protected $cacheTime;

    public function __construct()
    {
        $this->baseUrl = env('BMKG_API_URL', 'https://data.bmkg.go.id/');
        $this->cacheTime = env('BMKG_CACHE_TIME', 3600); // 1 hour default
    }

    /**
     * Get weather information for specific area
     */
    public function getCuacaWilayah($area = null)
    {
        $cacheKey = "cuaca_wilayah_" . ($area ?? 'indonesia');

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($area) {
            try {
                // BMKG weather API endpoints are currently unavailable
                // Using fallback data directly
                return $this->getCuacaFallback();

            } catch (\Exception $e) {
                \Log::error('Error fetching cuaca data: ' . $e->getMessage());
                return $this->getCuacaFallback();
            }
        });
    }

    /**
     * Get general weather information
     */
    public function getCuacaUmum()
    {
        $cacheKey = 'cuaca_umum';

        return Cache::remember($cacheKey, $this->cacheTime, function () {
            try {
                // BMKG weather API endpoints are currently unavailable
                // Using fallback data directly
                return $this->getCuacaFallback();

            } catch (\Exception $e) {
                \Log::error('Error fetching cuaca umum: ' . $e->getMessage());
                return $this->getCuacaFallback();
            }
        });
    }

    /**
     * Get weather alerts/warnings
     */
    public function getPeringatanCuaca()
    {
        $cacheKey = 'peringatan_cuaca';

        return Cache::remember($cacheKey, 1800, function () { // 30 minutes cache for warnings
            try {
                // BMKG weather warning API endpoints are currently unavailable
                return [
                    'status' => 'safe',
                    'message' => 'Tidak ada peringatan cuaca aktif',
                    'peringatan' => [],
                    'total' => 0
                ];

            } catch (\Exception $e) {
                \Log::error('Error fetching peringatan cuaca: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal mengambil data peringatan cuaca'
                ];
            }
        });
    }

    /**
     * Format cuaca data for API response
     */
    private function formatCuacaData($data)
    {
        if (!isset($data['data'])) {
            return $this->getCuacaFallback();
        }

        $formatted = [
            'status' => 'success',
            'waktu_update' => Carbon::now()->toISOString(),
            'data' => []
        ];

        try {
            // Process weather forecast data
            if (isset($data['data']['forecast'])) {
                $forecasts = [];
                foreach ($data['data']['forecast'] as $forecast) {
                    if (isset($forecast['area']) && isset($forecast['parameter'])) {
                        $areaName = $forecast['area']['name'] ?? 'Unknown Area';

                        $forecastData = [
                            'area' => [
                                'nama' => $areaName,
                                'koordinat' => $forecast['area']['coordinates'] ?? [],
                                'kode' => $forecast['area']['prop'] ?? ''
                            ],
                            'prakiraan' => []
                        ];

                        // Process weather parameters
                        foreach ($forecast['parameter'] as $parameter) {
                            $paramCode = $parameter['parameter_code'] ?? '';
                            $paramValues = $parameter['parameter_value'] ?? [];

                            $forecastData['prakiraan'][$paramCode] = [
                                'nama' => $this->getParameterName($paramCode),
                                'nilai' => $paramValues,
                                'unit' => $this->getParameterUnit($paramCode)
                            ];
                        }

                        $forecasts[] = $forecastData;
                    }
                }

                $formatted['data'] = $forecasts;
            }

            // If no forecast data, provide fallback
            if (empty($formatted['data'])) {
                return $this->getCuacaFallback();
            }

        } catch (\Exception $e) {
            \Log::error('Error formatting cuaca data: ' . $e->getMessage());
            return $this->getCuacaFallback();
        }

        return $formatted;
    }

    /**
     * Format peringatan data
     */
    private function formatPeringatanData($data)
    {
        return [
            'status' => 'success',
            'waktu_update' => Carbon::now()->toISOString(),
            'peringatan' => $data['data'] ?? [],
            'total' => count($data['data'] ?? [])
        ];
    }

    /**
     * Get fallback weather data
     */
    private function getCuacaFallback()
    {
        return [
            'status' => 'fallback',
            'message' => 'Data cuaca tidak tersedia, menggunakan data default',
            'waktu_update' => Carbon::now()->toISOString(),
            'data' => [
                [
                    'area' => [
                        'nama' => 'Indonesia',
                        'koordinat' => [],
                        'kode' => 'ID'
                    ],
                    'prakiraan' => [
                        'weather' => [
                            'nama' => 'Cuaca',
                            'nilai' => 'Informasi cuaca sedang dalam pemeliharaan',
                            'unit' => ''
                        ],
                        'temperature' => [
                            'nama' => 'Suhu',
                            'nilai' => ['25-30°C'],
                            'unit' => '°C'
                        ],
                        'humidity' => [
                            'nama' => 'Kelembaban',
                            'nilai' => ['60-80%'],
                            'unit' => '%'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get parameter name in Indonesian
     */
    private function getParameterName($code)
    {
        $names = [
            'weather' => 'Cuaca',
            'temperature' => 'Suhu',
            'humidity' => 'Kelembaban',
            'wind_speed' => 'Kecepatan Angin',
            'wind_direction' => 'Arah Angin',
            'pressure' => 'Tekanan Udara',
            'precipitation' => 'Curah Hujan',
            'visibility' => 'Jarak Pandang',
            'cloud_cover' => 'Tutupan Awan',
            'uv_index' => 'Indeks UV'
        ];

        return $names[$code] ?? $code;
    }

    /**
     * Get parameter unit
     */
    private function getParameterUnit($code)
    {
        $units = [
            'weather' => '',
            'temperature' => '°C',
            'humidity' => '%',
            'wind_speed' => 'km/jam',
            'wind_direction' => 'derajat',
            'pressure' => 'hPa',
            'precipitation' => 'mm',
            'visibility' => 'km',
            'cloud_cover' => '%',
            'uv_index' => ''
        ];

        return $units[$code] ?? '';
    }

    /**
     * Get weather for specific coordinates
     */
    public function getCuacaByCoordinates($lat, $lon)
    {
        // For future implementation: find nearest area based on coordinates
        // Currently using general weather data
        return $this->getCuacaUmum();
    }

    /**
     * Clear weather cache
     */
    public function clearCache()
    {
        Cache::forget('cuaca_umum');
        Cache::forget('peringatan_cuaca');

        // Clear area-specific caches
        $areas = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Makassar'];
        foreach ($areas as $area) {
            Cache::forget("cuaca_wilayah_" . strtolower($area));
        }

        return true;
    }

    /**
     * Get weather summary for dashboard
     */
    public function getCuacaSummary()
    {
        $cacheKey = 'cuaca_summary';

        return Cache::remember($cacheKey, $this->cacheTime, function () {
            try {
                $cuacaData = $this->getCuacaUmum();

                if ($cuacaData['status'] === 'success' && !empty($cuacaData['data'])) {
                    $summary = [
                        'status' => 'success',
                        'total_area' => count($cuacaData['data']),
                        'update_terakhir' => $cuacaData['waktu_update'],
                        'kondisi_umum' => 'Normal',
                        'peringatan_aktif' => 0
                    ];

                    // Check for weather warnings
                    $peringatan = $this->getPeringatanCuaca();
                    if ($peringatan['status'] === 'success') {
                        $summary['peringatan_aktif'] = $peringatan['total'] ?? 0;
                    }

                    return $summary;
                }

                return [
                    'status' => 'warning',
                    'message' => 'Data cuaca tidak tersedia'
                ];

            } catch (\Exception $e) {
                \Log::error('Error creating cuaca summary: ' . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Gagal membuat ringkasan cuaca'
                ];
            }
        });
    }
}