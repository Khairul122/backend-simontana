<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CuacaService;
use App\Services\GempaService;

class BMKGController extends Controller
{
    protected $cuacaService;
    protected $gempaService;

    public function __construct(CuacaService $cuacaService, GempaService $gempaService)
    {
        $this->cuacaService = $cuacaService;
        $this->gempaService = $gempaService;
    }

    /**
     * Get weather information for dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $cuacaData = $this->cuacaService->getCuacaSummary();
            $gempaData = $this->gempaService->getStatistikGempa();
            $gempaTerbaru = $this->gempaService->getGempaTerbaru();
            $peringatanCuaca = $this->cuacaService->getPeringatanCuaca();

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard BMKG berhasil diambil',
                'data' => [
                    'cuaca' => $cuacaData,
                    'gempa' => [
                        'statistik' => $gempaData,
                        'terbaru' => $gempaTerbaru
                    ],
                    'peringatan' => [
                        'cuaca' => $peringatanCuaca,
                        'tsunami' => $this->gempaService->getGempaTsunami()
                    ],
                    'update_terakhir' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weather information
     */
    public function cuaca(Request $request)
    {
        try {
            $validated = $request->validate([
                'area' => 'nullable|string|max:100',
                'coordinates' => 'nullable|string'
            ]);

            // BMKG weather API endpoints are currently unavailable
            // Return fallback data directly
            $cuacaData = [
                'status' => 'fallback',
                'message' => 'Data cuaca BMKG sedang dalam pemeliharaan. API cuaca tidak tersedia saat ini.',
                'waktu_update' => now()->toISOString(),
                'data' => [
                    [
                        'area' => [
                            'nama' => $validated['area'] ?? 'Indonesia',
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

            return response()->json([
                'success' => true,
                'message' => 'Data cuaca fallback berhasil diambil',
                'data' => $cuacaData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weather warnings/alerts
     */
    public function peringatanCuaca(Request $request)
    {
        try {
            $peringatanData = $this->cuacaService->getPeringatanCuaca();

            return response()->json([
                'success' => true,
                'message' => 'Data peringatan cuaca berhasil diambil',
                'data' => $peringatanData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peringatan cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest earthquake information
     */
    public function gempaTerbaru(Request $request)
    {
        try {
            $gempaData = $this->gempaService->getGempaTerbaru();

            return response()->json([
                'success' => true,
                'message' => 'Data gempa terbaru berhasil diambil',
                'data' => $gempaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gempa terbaru: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earthquake information for last 24 hours
     */
    public function gempa24Jam(Request $request)
    {
        try {
            $gempaData = $this->gempaService->getGempa24Jam();

            return response()->json([
                'success' => true,
                'message' => 'Data gempa 24 jam terakhir berhasil diambil',
                'data' => $gempaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gempa 24 jam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earthquake history
     */
    public function riwayatGempa(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ], [
                'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai'
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $riwayatData = $this->gempaService->getRiwayatGempa($startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat gempa berhasil diambil',
                'data' => $riwayatData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data riwayat gempa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earthquake statistics
     */
    public function statistikGempa(Request $request)
    {
        try {
            $statistikData = $this->gempaService->getStatistikGempa();

            return response()->json([
                'success' => true,
                'message' => 'Statistik gempa berhasil diambil',
                'data' => $statistikData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik gempa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check earthquakes by coordinates
     */
    public function cekGempaKoordinat(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:1|max:500'
            ], [
                'latitude.required' => 'Latitude wajib diisi',
                'longitude.required' => 'Longitude wajib diisi',
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'radius.min' => 'Radius minimal 1 km',
                'radius.max' => 'Radius maksimal 500 km'
            ]);

            $lat = $request->input('latitude');
            $lon = $request->input('longitude');
            $radius = $request->input('radius', 50); // default 50 km

            $gempaData = $this->gempaService->checkGempaByCoordinates($lat, $lon, $radius);

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan gempa berdasarkan koordinat berhasil',
                'data' => $gempaData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa gempa berdasarkan koordinat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tsunami warnings
     */
    public function peringatanTsunami(Request $request)
    {
        try {
            $tsunamiData = $this->gempaService->getGempaTsunami();

            return response()->json([
                'success' => true,
                'message' => 'Data peringatan tsunami berhasil diambil',
                'data' => $tsunamiData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peringatan tsunami: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear BMKG cache
     */
    public function clearCache(Request $request)
    {
        try {
            $user = $request->user();

            // Only admin can clear cache
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat membersihkan cache.'
                ], 403);
            }

            $cuacaCleared = $this->cuacaService->clearCache();
            $gempaCleared = $this->gempaService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache BMKG berhasil dibersihkan',
                'data' => [
                    'cuaca_cache' => $cuacaCleared,
                    'gempa_cache' => $gempaCleared,
                    'cleaned_by' => $user->nama,
                    'waktu_clean' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get BMKG API status
     */
    public function status(Request $request)
    {
        try {
            $baseUrl = env('BMKG_API_URL', 'https://data.bmkg.go.id/');

            // Test API connectivity
            $response = \Http::timeout(10)->get($baseUrl);

            $status = [
                'api_url' => $baseUrl,
                'status' => $response->successful() ? 'online' : 'offline',
                'response_time' => $response->successful() ? $response->handlerStats()['total_time'] ?? 'unknown' : 'timeout',
                'last_check' => now()->toISOString(),
                'cache_status' => [
                    'cuaca' => 'active',
                    'gempa' => 'active'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status BMKG API berhasil diambil',
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status BMKG API: ' . $e->getMessage()
            ], 500);
        }
    }
}
