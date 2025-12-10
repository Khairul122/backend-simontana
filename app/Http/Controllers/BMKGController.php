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
     * @group BMKG Integration
     *
     * Dashboard BMKG Lengkap
     *
     * Endpoint untuk mendapatkan data lengkap dari BMKG untuk dashboard.
     * Data mencakup informasi cuaca, gempa terkini, dan peringatan dini.
     *
     * @authenticated
     *
     * @response {
     *   "success": true,
     *   "message": "Data dashboard BMKG berhasil diambil",
     *   "data": {
     *     "cuaca": {
     *       "status": "available",
     *       "suhu": "28°C",
     *       "kelembaban": "75%",
     *       "cuaca": "Cerah Berawan"
     *     },
     *     "gempa": {
     *       "statistik": {
     *         "total_24_jam": 3,
     *         "magnitudo_max": "5.4",
     *         "terbanyak": "Laut Banda"
     *       },
     *       "terbaru": {
     *         "tanggal": "07 Des 2025",
     *         "jam": "11:55:55 WIB",
     *         "magnitudo": "5.4",
     *         "kedalaman": "103 km",
     *         "wilayah": "150 km BaratLaut TANIMBAR",
     *         "potensi": "Tidak berpotensi tsunami"
     *       }
     *     },
     *     "peringatan": {
     *       "cuaca": "Waspada hujan lebat di beberapa wilayah",
     *       "tsunami": "Tidak ada peringatan tsunami aktif"
     *     },
     *     "update_terakhir": "2023-12-10T01:23:45.678Z"
     *   }
     * }
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
     * @OA\Get(
     *      path="/api/bmkg/cuaca",
     *      tags={"BMKG Integration"},
     *      summary="Get Weather Information",
     *      description="Endpoint untuk mendapatkan informasi cuaca dari BMKG.",
     *      operationId="getWeatherInfo",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="area",
     *          in="query",
     *          description="Nama area/wilayah",
     *          required=false,
     *          @OA\Schema(type="string", example="Jakarta")
     *      ),
     *      @OA\Parameter(
     *          name="coordinates",
     *          in="query",
     *          description="Koordinat lokasi",
     *          required=false,
     *          @OA\Schema(type="string", example="-6.200000,106.816666")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Data cuaca berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data cuaca fallback berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="status", type="string", example="fallback"),
     *                  @OA\Property(property="message", type="string", example="Data cuaca BMKG sedang dalam pemeliharaan. API cuaca tidak tersedia saat ini."),
     *                  @OA\Property(property="waktu_update", type="string", example="2024-12-10T10:30:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    
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
     * @OA\Get(
     *      path="/api/bmkg/cuaca/peringatan",
     *      tags={"BMKG Integration"},
     *      summary="Get Weather Warnings/Alerts",
     *      description="Endpoint untuk mendapatkan data peringatan cuaca dari BMKG.",
     *      operationId="getWeatherWarnings",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Data peringatan cuaca berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data peringatan cuaca berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="peringatan", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="jenis", type="string", example="Hujan Lebat Disertai Petir"),
     *                          @OA\Property(property="tingkat", type="string", example="Waspada"),
     *                          @OA\Property(property="daerah", type="string", example="DKI Jakarta, Jawa Barat"),
     *                          @OA\Property(property="waktu_berlaku", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="keterangan", type="string", example="Waspada hujan lebat disertai petir dan angin kencang")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */

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
     * @group BMKG Integration
     *
     * Gempa Terbaru
     *
     * Endpoint untuk mendapatkan informasi gempa bumi terbaru dari BMKG.
     * Data real-time langsung dari BMKG.
     *
     * @authenticated
     *
     * @response {
     *   "success": true,
     *   "message": "Data gempa terbaru berhasil diambil",
     *   "data": {
     *     "tanggal": "07 Des 2025",
     *     "jam": "11:55:55 WIB",
     *     "magnitudo": "5.4",
     *     "kedalaman": "103 km",
     *     "wilayah": "150 km BaratLaut TANIMBAR",
     *     "potensi": "Tidak berpotensi tsunami",
     *     "dirasakan": "II-III Saumlaki, II-III Tanimbar Selatan",
     *     "shakemap": "https://data.bmkg.go.id/eqmap.gif",
     *     "coordinates": {
     *       "latitude": -7.89,
     *       "longitude": 131.45
     *     }
     *   }
     * }
     */
    /**
     * @OA\Get(
     *      path="/api/bmkg/gempa/terbaru",
     *      tags={"BMKG Integration"},
     *      summary="Get Latest Earthquake Information",
     *      description="Endpoint untuk mendapatkan informasi gempa terbaru dari BMKG.",
     *      operationId="getLatestEarthquake",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Data gempa terbaru berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data gempa terbaru berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="tanggal", type="string", example="07 Des 2025"),
     *                  @OA\Property(property="jam", type="string", example="11:55:55 WIB"),
     *                  @OA\Property(property="magnitudo", type="string", example="5.4"),
     *                  @OA\Property(property="kedalaman", type="string", example="103 km"),
     *                  @OA\Property(property="wilayah", type="string", example="150 km BaratLaut TANIMBAR"),
     *                  @OA\Property(property="potensi", type="string", example="Tidak berpotensi tsunami")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
    /**
     * @OA\Get(
     *      path="/api/bmkg/gempa/24-jam",
     *      tags={"BMKG Integration"},
     *      summary="Get Earthquake Information for Last 24 Hours",
     *      description="Endpoint untuk mendapatkan informasi gempa bumi dalam periode 24 jam terakhir dari BMKG.",
     *      operationId="getEarthquakes24Hours",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Data gempa 24 jam berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data gempa 24 jam terakhir berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="tanggal", type="string", example="07 Des 2025"),
     *                      @OA\Property(property="jam", type="string", example="11:55:55 WIB"),
     *                      @OA\Property(property="magnitudo", type="string", example="5.4"),
     *                      @OA\Property(property="kedalaman", type="string", example="103 km"),
     *                      @OA\Property(property="wilayah", type="string", example="150 km BaratLaut TANIMBAR"),
     *                      @OA\Property(property="potensi", type="string", example="Tidak berpotensi tsunami")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
    /**
     * @OA\Get(
     *      path="/api/bmkg/gempa/riwayat",
     *      tags={"BMKG Integration"},
     *      summary="Get Earthquake History",
     *      description="Endpoint untuk mendapatkan riwayat gempa bumi dengan filter tanggal.",
     *      operationId="getEarthquakeHistory",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="Tanggal mulai (format: YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(type="string", format="date", example="2024-01-01")
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="Tanggal akhir (format: YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(type="string", format="date", example="2024-12-31")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Riwayat gempa berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data riwayat gempa berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="tanggal", type="string", example="07 Des 2025"),
     *                      @OA\Property(property="jam", type="string", example="11:55:55 WIB"),
     *                      @OA\Property(property="magnitudo", type="string", example="5.4"),
     *                      @OA\Property(property="kedalaman", type="string", example="103 km"),
     *                      @OA\Property(property="wilayah", type="string", example="150 km BaratLaut TANIMBAR"),
     *                      @OA\Property(property="potensi", type="string", example="Tidak berpotensi tsunami")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
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
    /**
     * @OA\Get(
     *      path="/api/bmkg/gempa/statistik",
     *      tags={"BMKG Integration"},
     *      summary="Get Earthquake Statistics",
     *      description="Endpoint untuk mendapatkan statistik gempa bumi dari BMKG.",
     *      operationId="getEarthquakeStats",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Statistik gempa berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Statistik gempa berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total_gempa", type="integer", example=15),
     *                  @OA\Property(property="gempa_terbesar", type="object",
     *                      @OA\Property(property="magnitudo", type="string", example="6.4"),
     *                      @OA\Property(property="tanggal", type="string", example="2024-12-10"),
     *                      @OA\Property(property="lokasi", type="string", example="150 km BaratLaut TANIMBAR")
     *                  ),
     *                  @OA\Property(property="distribusi_magnitudo", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="range", type="string", example="5.0-6.0"),
     *                          @OA\Property(property="jumlah", type="integer", example=8)
     *                      )
     *                  ),
     *                  @OA\Property(property="gempa_terbanyak", type="string", example="Laut Banda")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Get(
     *      path="/api/bmkg/gempa/cek-koordinat",
     *      tags={"BMKG Integration"},
     *      summary="Check Earthquakes by Coordinates",
     *      description="Endpoint untuk mengecek gempa di sekitar koordinat tertentu.",
     *      operationId="checkEarthquakesByCoordinates",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="Latitude lokasi (antara -90 dan 90)",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=-6.200000)
     *      ),
     *      @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="Longitude lokasi (antara -180 dan 180)",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=106.816666)
     *      ),
     *      @OA\Parameter(
     *          name="radius",
     *          in="query",
     *          description="Radius pencarian dalam kilometer (1-500 km)",
     *          required=false,
     *          @OA\Schema(type="integer", example=50)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Pemeriksaan gempa berdasarkan koordinat berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Pemeriksaan gempa berdasarkan koordinat berhasil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="tanggal", type="string", example="07 Des 2025"),
     *                      @OA\Property(property="jam", type="string", example="11:55:55 WIB"),
     *                      @OA\Property(property="magnitudo", type="string", example="5.4"),
     *                      @OA\Property(property="kedalaman", type="string", example="103 km"),
     *                      @OA\Property(property="lokasi", type="string", example="150 km BaratLaut TANIMBAR"),
     *                      @OA\Property(property="jarak", type="number", format="double", example=45.5)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */

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
    /**
     * @OA\Get(
     *      path="/api/bmkg/gempa/peringatan-tsunami",
     *      tags={"BMKG Integration"},
     *      summary="Get Tsunami Warning Information",
     *      description="Endpoint untuk mendapatkan informasi peringatan tsunami dari BMKG.",
     *      operationId="getTsunamiWarnings",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Data peringatan tsunami berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data peringatan tsunami berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="tanggal", type="string", example="07 Des 2025"),
     *                      @OA\Property(property="jam", type="string", example="11:55:55 WIB"),
     *                      @OA\Property(property="magnitudo", type="string", example="5.4"),
     *                      @OA\Property(property="kedalaman", type="string", example="103 km"),
     *                      @OA\Property(property="wilayah", type="string", example="150 km BaratLaut TANIMBAR"),
     *                      @OA\Property(property="peringatan_tsunami", type="string", example="Berpotensi tsunami"),
     *                      @OA\Property(property="status", type="string", example="Siaga")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Delete(
     *      path="/api/bmkg/admin/cache",
     *      tags={"BMKG Admin"},
     *      summary="Clear BMKG Cache",
     *      description="Endpoint untuk membersihkan cache data BMKG (Admin Only).",
     *      operationId="clearBMKCCache",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Cache BMKG berhasil dibersihkan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Cache BMKG berhasil dibersihkan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="cuaca_cache", type="boolean", example=true),
     *                  @OA\Property(property="gempa_cache", type="boolean", example=true),
     *                  @OA\Property(property="cleaned_by", type="string", example="Admin Test"),
     *                  @OA\Property(property="waktu_clean", type="string", example="2024-12-10T10:30:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */

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
