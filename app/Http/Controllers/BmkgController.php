<?php

namespace App\Http\Controllers;

use App\Models\BencanaBmkg;
use App\Services\BmkgService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="BMKG Integration",
 *     description="API endpoints untuk integrasi data dari Badan Meteorologi, Klimatologi, dan Geofisika"
 * )
 */
class BmkgController extends Controller
{
    protected $bmkgService;

    public function __construct(BmkgService $bmkgService)
    {
        $this->bmkgService = $bmkgService;
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg",
     *     tags={"BMKG Integration"},
     *     summary="Get all BMKG data",
     *     description="Mengambil semua data dari BMKG dengan filter dan pagination",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="jenis_bencana",
     *         in="query",
     *         description="Filter by jenis bencana (gempa_bumi, cuaca_ekstrem, peringatan_dini)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"gempa_bumi", "cuaca_ekstrem", "peringatan_dini"})
     *     ),
     *     @OA\Parameter(
     *         name="lokasi",
     *         in="query",
     *         description="Filter by lokasi",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data BMKG berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $query = BencanaBmkg::query();

            // Filter berdasarkan jenis_bencana
            if ($request->has('jenis_bencana')) {
                $query->where('jenis_bencana', $request->jenis_bencana);
            }

            // Filter berdasarkan lokasi
            if ($request->has('lokasi')) {
                $query->where('lokasi', 'LIKE', '%' . $request->lokasi . '%');
            }

            $perPage = $request->get('per_page', 20);
            $bmkgData = $query->orderBy('waktu_pembaruan', 'desc')->paginate($perPage);

            // Decode isi_data dan lengkapi field-field tambahan untuk setiap item
            $bmkgData->getCollection()->transform(function ($item) {
                // Decode isi_data jika dalam bentuk JSON string
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }

                // Lengkapi field-field tambahan dari accessor model
                $item->tanggal = $item->tanggal;
                $item->jam = $item->jam;
                $item->waktu_lengkap = $item->waktu_lengkap;
                $item->magnitudo = $item->magnitudo;
                $item->kedalaman = $item->kedalaman;
                $item->koordinat = $item->koordinat;
                $item->lintang_text = $item->lintang_text;
                $item->bujur_text = $item->bujur_text;
                $item->lokasi_lengkap = $item->lokasi_lengkap;
                $item->potensi = $item->potensi;
                $item->dirasakan = $item->dirasakan;

                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data BMKG berhasil diambil',
                'data' => $bmkgData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data BMKG: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-autogempa",
     *     tags={"BMKG Integration"},
     *     summary="Sync autogempa data from BMKG",
     *     description="Sinkronisasi data autogempa dari server BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data autogempa berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function syncAutoEarthquakeData(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $jumlahDataBaru = $this->bmkgService->syncAutoEarthquake();

            // Ambil data gempa autogempa terbaru yang disimpan
            $latestAutoGempa = BencanaBmkg::where('jenis_bencana', 'autogempa')
                ->latest('waktu_pembaruan')
                ->first();

            // Decode isi_data jika dalam bentuk JSON string
            if ($latestAutoGempa && is_string($latestAutoGempa->isi_data)) {
                $latestAutoGempa->isi_data = json_decode($latestAutoGempa->isi_data, true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data autogempa berhasil',
                'data' => [
                    'jumlah_data_baru_disimpan' => $jumlahDataBaru,
                    'data_gempa_terbaru' => $latestAutoGempa
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data autogempa: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dari BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-gempa-terkini",
     *     tags={"BMKG Integration"},
     *     summary="Sync latest earthquake data from BMKG",
     *     description="Sinkronisasi data gempa M 5.0+ terkini dari server BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data gempa terkini berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function syncLatestEarthquakeData(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $jumlahDataBaru = $this->bmkgService->syncLatestEarthquakes();

            // Ambil data gempa terbaru yang disimpan
            $latestGempa = BencanaBmkg::where('jenis_bencana', 'gempa_terkini')
                ->latest('waktu_pembaruan')
                ->limit(5)  // Ambil 5 data terbaru
                ->get();

            // Decode isi_data untuk setiap item jika dalam bentuk JSON string
            $latestGempa = $latestGempa->map(function ($item) {
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data gempa terkini berhasil',
                'data' => [
                    'jumlah_data_baru_disimpan' => $jumlahDataBaru,
                    'data_gempa_terbaru' => $latestGempa
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data gempa terkini: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dari BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-gempa-dirasakan",
     *     tags={"BMKG Integration"},
     *     summary="Sync felt earthquake data from BMKG",
     *     description="Sinkronisasi data gempa dirasakan dari server BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data gempa dirasakan berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function syncEarthquakeFeltData(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $jumlahDataBaru = $this->bmkgService->syncEarthquakeFelt();

            // Ambil data gempa dirasakan terbaru yang disimpan
            $latestGempaDirasakan = BencanaBmkg::where('jenis_bencana', 'gempa_dirasakan')
                ->latest('waktu_pembaruan')
                ->limit(5)  // Ambil 5 data terbaru
                ->get();

            // Decode isi_data untuk setiap item jika dalam bentuk JSON string
            $latestGempaDirasakan = $latestGempaDirasakan->map(function ($item) {
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data gempa dirasakan berhasil',
                'data' => [
                    'jumlah_data_baru_disimpan' => $jumlahDataBaru,
                    'data_gempa_terbaru' => $latestGempaDirasakan
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data gempa dirasakan: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dari BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-cuaca",
     *     tags={"BMKG Integration"},
     *     summary="Sync weather data from BMKG",
     *     description="Sinkronisasi data cuaca dari server BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data cuaca berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function syncWeatherData(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
            if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            $jumlahDataBaru = $this->bmkgService->syncWeatherData();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data cuaca berhasil',
                'data' => [
                    'jumlah_data_baru' => $jumlahDataBaru
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-cap",
     *     tags={"BMKG Integration"},
     *     summary="Sync CAP data from BMKG",
     *     description="Sinkronisasi data Cuaca Awan Potensial dari server BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data CAP berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function syncCapData(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
            if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            $jumlahDataBaru = $this->bmkgService->syncCapData();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data CAP berhasil',
                'data' => [
                    'jumlah_data_baru' => $jumlahDataBaru
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-all",
     *     tags={"BMKG Integration"},
     *     summary="Sync all BMKG data",
     *     description="Sinkronisasi semua data dari server BMKG (gempa, cuaca, dan CAP)",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi semua data BMKG berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="sync_result", type="object",
     *                     @OA\Property(property="gempa", type="integer", example=2),
     *                     @OA\Property(property="cuaca", type="integer", example=5),
     *                     @OA\Property(property="cap", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function syncAllData(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $syncResult = $this->bmkgService->syncAllBmkgData();

            // Ambil data terbaru dari berbagai jenis bencana
            $latestAutoGempa = BencanaBmkg::where('jenis_bencana', 'autogempa')
                ->latest('waktu_pembaruan')
                ->first();

            $latestGempaTerkini = BencanaBmkg::where('jenis_bencana', 'gempa_terkini')
                ->latest('waktu_pembaruan')
                ->limit(5)
                ->get();

            $latestGempaDirasakan = BencanaBmkg::where('jenis_bencana', 'gempa_dirasakan')
                ->latest('waktu_pembaruan')
                ->limit(5)
                ->get();

            // Decode isi_data jika dalam bentuk JSON string
            if ($latestAutoGempa && is_string($latestAutoGempa->isi_data)) {
                $latestAutoGempa->isi_data = json_decode($latestAutoGempa->isi_data, true);
            }

            $latestGempaTerkini = $latestGempaTerkini->map(function ($item) {
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }
                return $item;
            });

            $latestGempaDirasakan = $latestGempaDirasakan->map(function ($item) {
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi semua data BMKG berhasil',
                'data' => [
                    'sync_result' => $syncResult,
                    'data_gempa_baru' => [
                        'autogempa' => $latestAutoGempa,
                        'gempa_terkini' => $latestGempaTerkini,
                        'gempa_dirasakan' => $latestGempaDirasakan
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi semua data BMKG: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dari BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/{jenis}/{id}",
     *     tags={"BMKG Integration"},
     *     summary="Get specific BMKG data by type and ID",
     *     description="Mengambil data BMKG spesifik berdasarkan jenis dan ID",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="jenis",
     *         in="path",
     *         description="Jenis bencana (gempa_bumi, cuaca_ekstrem, peringatan_dini)",
     *         required=true,
     *         @OA\Schema(type="string", enum={"gempa_bumi", "cuaca_ekstrem", "peringatan_dini"})
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID data",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data BMKG berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Data not found")
     * )
     */
    public function show(Request $request, $jenis, $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        try {
            $bencana = BencanaBmkg::where('jenis_bencana', $jenis)
                ->where('id_bencana', $id)
                ->first();

            if (!$bencana) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data BMKG tidak ditemukan'
                ], 404);
            }

            // Decode isi_data jika dalam bentuk JSON string
            if (is_string($bencana->isi_data)) {
                $bencana->isi_data = json_decode($bencana->isi_data, true);
            }

            // Lengkapi data untuk menyesuaikan dengan format yang diminta
            $bencana->tanggal = $bencana->tanggal;
            $bencana->jam = $bencana->jam;
            $bencana->waktu_lengkap = $bencana->waktu_lengkap;
            $bencana->magnitudo = $bencana->magnitudo;
            $bencana->kedalaman = $bencana->kedalaman;
            $bencana->koordinat = $bencana->koordinat;
            $bencana->lintang_text = $bencana->lintang_text;
            $bencana->bujur_text = $bencana->bujur_text;
            $bencana->lokasi_lengkap = $bencana->lokasi_lengkap;
            $bencana->potensi = $bencana->potensi;
            $bencana->dirasakan = $bencana->dirasakan;

            return response()->json([
                'success' => true,
                'message' => 'Data BMKG berhasil diambil',
                'data' => $bencana
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data BMKG spesifik: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/latest/{jenis?}",
     *     tags={"BMKG Integration"},
     *     summary="Get latest BMKG data by type",
     *     description="Mengambil data BMKG terbaru berdasarkan jenis (semua jika jenis tidak disertakan)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="jenis",
     *         in="path",
     *         description="Jenis bencana (gempa_bumi, cuaca_ekstrem, peringatan_dini), opsional",
     *         required=false,
     *         @OA\Schema(type="string", enum={"gempa_bumi", "cuaca_ekstrem", "peringatan_dini"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data yang dikembalikan",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data BMKG terbaru berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function showLatest(Request $request, $jenis = null): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        try {
            $limit = $request->get('limit', 10);

            if ($jenis && $jenis === 'gempa_bumi') {
                // Jika jenis adalah gempa_bumi, ambil data gempa terbaru dengan transformasi khusus
                $query = BencanaBmkg::where('jenis_bencana', 'gempa_bumi')
                    ->orderBy('waktu_pembaruan', 'desc');

                if ($request->input('jenis_gempa') === 'dirasakan') {
                    $query->where('jenis_bencana', 'gempa_dirasakan');
                } elseif ($request->input('jenis_gempa') === 'terkini') {
                    // Filter untuk gempa M 5.0+ jika perlu
                }

                $data = $query->limit($limit)->get();
            } else if ($jenis) {
                $data = BencanaBmkg::where('jenis_bencana', $jenis)
                    ->orderBy('waktu_pembaruan', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $data = BencanaBmkg::orderBy('waktu_pembaruan', 'desc')
                    ->limit($limit)
                    ->get();
            }

            // Transform data untuk menyesuaikan dengan format yang diminta
            $data = $data->map(function ($item) {
                // Decode isi_data jika dalam bentuk JSON string
                if (is_string($item->isi_data)) {
                    $item->isi_data = json_decode($item->isi_data, true);
                }

                // Pastikan field-field tambahan tersedia
                $item->tanggal = $item->tanggal;
                $item->jam = $item->jam;
                $item->waktu_lengkap = $item->waktu_lengkap;
                $item->magnitudo = $item->magnitudo;
                $item->kedalaman = $item->kedalaman;
                $item->koordinat = $item->koordinat;
                $item->lintang_text = $item->lintang_text;
                $item->bujur_text = $item->bujur_text;
                $item->lokasi_lengkap = $item->lokasi_lengkap;
                $item->potensi = $item->potensi;
                $item->dirasakan = $item->dirasakan;

                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data BMKG terbaru berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data BMKG terbaru: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data BMKG: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-gempa",
     *     tags={"BMKG Integration"},
     *     summary="Sync latest earthquake data from BMKG (alias to sync-gempa-terkini)",
     *     description="Sinkronisasi data gempa terkini dari server BMKG (alias untuk sync-gempa-terkini)",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data gempa berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function syncEarthquakeData(): JsonResponse
    {
        // Ini adalah fungsi alias yang mengarah ke syncLatestEarthquakeData
        return $this->syncLatestEarthquakeData();
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-prakiraan-cuaca",
     *     tags={"BMKG Integration"},
     *     summary="Sync weather forecast data from BMKG API",
     *     description="Sinkronisasi data prakiraan cuaca dari BMKG API berdasarkan kode administrasi",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="adm4_code",
     *         in="query",
     *         description="Kode administrasi level 4 (desa/kelurahan)",
     *         required=true,
     *         @OA\Schema(type="string", example="31.71.01.1001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data prakiraan cuaca berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=24),
     *                 @OA\Property(property="lokasi", type="string", example="Desa Example, Kecamatan Example")
     *             )
     *         )
     *     )
     * )
     */
    public function syncWeatherForecastData(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $adm4Code = $request->input('adm4_code');

            if (!$adm4Code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode administrasi (adm4) diperlukan'
                ], 400);
            }

            $jumlahDataBaru = $this->bmkgService->syncWeatherForecast($adm4Code);

            // Get location info from the synced data
            $latestForecast = BencanaBmkg::where('jenis_bencana', 'prakiraan_cuaca')
                ->where('sumber_data', 'LIKE', '%adm4=' . $adm4Code . '%')
                ->latest('waktu_pembaruan')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data prakiraan cuaca berhasil',
                'data' => [
                    'jumlah_data_baru' => $jumlahDataBaru,
                    'adm4_code' => $adm4Code,
                    'lokasi' => $latestForecast ? $latestForecast->lokasi : null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data prakiraan cuaca: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi data prakiraan cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/prakiraan-cuaca",
     *     tags={"BMKG Integration"},
     *     summary="Get weather forecast data",
     *     description="Mengambil data prakiraan cuaca dari database atau live API",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="lokasi",
     *         in="query",
     *         description="Filter berdasarkan nama lokasi (desa/kecamatan)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="adm4_code",
     *         in="query",
     *         description="Kode administrasi level 4 untuk data live dari BMKG",
     *         required=false,
     *         @OA\Schema(type="string", example="31.71.01.1001")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data yang dikembalikan",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Sumber data: database atau live",
     *         required=false,
     *         @OA\Schema(type="string", enum={"database", "live"}, default="database")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data prakiraan cuaca berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="lokasi", type="object",
     *                     @OA\Property(property="desa", type="string"),
     *                     @OA\Property(property="kecamatan", type="string"),
     *                     @OA\Property(property="kotkab", type="string"),
     *                     @OA\Property(property="provinsi", type="string"),
     *                     @OA\Property(property="lat", type="number"),
     *                     @OA\Property(property="lon", type="number"),
     *                     @OA\Property(property="timezone", type="string")
     *                 ),
     *                 @OA\Property(property="prakiraan", type="array", @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="local_datetime", type="string"),
     *                     @OA\Property(property="weather_desc", type="string"),
     *                     @OA\Property(property="temperature", type="number"),
     *                     @OA\Property(property="humidity", type="integer"),
     *                     @OA\Property(property="wind_speed", type="number"),
     *                     @OA\Property(property="wind_direction", type="string"),
     *                     @OA\Property(property="visibility", type="string"),
     *                     @OA\Property(property="image_url", type="string")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function getWeatherForecast(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        try {
            $source = $request->get('source', 'database');
            $limit = $request->get('limit', 20);

            if ($source === 'live' && $request->has('adm4_code')) {
                // Get live data from BMKG API
                $adm4Code = $request->get('adm4_code');
                $liveData = $this->bmkgService->getWeatherForecast($adm4Code);

                if (!$liveData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengambil data live dari BMKG API'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data prakiraan cuaca live berhasil diambil',
                    'data' => $liveData
                ]);
            } else {
                // Get data from database
                $lokasi = $request->get('lokasi');
                $forecastData = $this->bmkgService->getWeatherForecastFromDatabase($lokasi, $limit);

                if ($forecastData->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data prakiraan cuaca tidak tersedia di database'
                    ], 404);
                }

                $formattedData = $this->bmkgService->formatWeatherForecastData($forecastData);

                return response()->json([
                    'success' => true,
                    'message' => 'Data prakiraan cuaca berhasil diambil dari database',
                    'data' => [
                        'prakiraan' => $formattedData,
                        'total' => $formattedData->count()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data prakiraan cuaca: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data prakiraan cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/gempa/terbaru",
     *     tags={"BMKG Integration"},
     *     summary="Get latest earthquake data in BMKG format",
     *     description="Mengambil data gempa terbaru dalam format yang sesuai dengan dokumentasi BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data gempa terbaru berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Tanggal", type="string", example="16 Des 2024"),
     *                 @OA\Property(property="Jam", type="string", example="13:44:55 WIB"),
     *                 @OA\Property(property="DateTime", type="string", example="2024-12-16T13:44:55+00:00"),
     *                 @OA\Property(property="Magnitude", type="number", example=5.2),
     *                 @OA\Property(property="Kedalaman", type="string", example="10 km"),
     *                 @OA\Property(property="point", type="object",
     *                     @OA\Property(property="coordinates", type="string", example="106.45,-6.19")
     *                 ),
     *                 @OA\Property(property="Lintang", type="string", example="6.19 LS"),
     *                 @OA\Property(property="Bujur", type="string", example="106.45 BT"),
     *                 @OA\Property(property="Wilayah", type="string", example="Jawa Barat"),
     *                 @OA\Property(property="Potensi", type="string", example="Tidak berpotensi tsunami"),
     *                 @OA\Property(property="Dirasakan", type="string", example="II-III Bogor"),
     *                 @OA\Property(property="Shakemap", type="string", example="20241216134455.mmi.png")
     *             )
     *         )
     *     )
     * )
     */
    public function getLatestEarthquakeBmkg(): JsonResponse
    {
        try {
            // Temporarily bypass auth for testing
            // $user = auth()->user();

            // if (!$user) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Token tidak valid'
            //     ], 401);
            // }

            // Ambil data autogempa terbaru
            $latestGempa = BencanaBmkg::where('jenis_bencana', 'autogempa')
                ->latest('waktu_pembaruan')
                ->first();

            \Log::info('Latest gempa query result', [
                'found' => $latestGempa ? 'YES' : 'NO',
                'id_bencana' => $latestGempa ? $latestGempa->id_bencana : null,
                'jenis_bencana' => $latestGempa ? $latestGempa->jenis_bencana : null
            ]);

            if (!$latestGempa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gempa terbaru tidak tersedia'
                ], 404);
            }

            // Format data sesuai dengan dokumentasi BMKG
            $formattedData = $this->bmkgService->formatEarthquakeData($latestGempa);

            return response()->json([
                'success' => true,
                'message' => 'Data gempa terbaru berhasil diambil',
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data gempa terbaru: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data gempa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/gempa/terkini",
     *     tags={"BMKG Integration"},
     *     summary="Get latest M 5.0+ earthquake data in BMKG format",
     *     description="Mengambil data gempa M 5.0+ terkini dalam format yang sesuai dengan dokumentasi BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data yang dikembalikan (maksimal 15)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data gempa terkini berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Tanggal", type="string", example="16 Des 2024"),
     *                 @OA\Property(property="Jam", type="string", example="13:44:55 WIB"),
     *                 @OA\Property(property="DateTime", type="string", example="2024-12-16T13:44:55+00:00"),
     *                 @OA\Property(property="Magnitude", type="number", example=5.2),
     *                 @OA\Property(property="Kedalaman", type="string", example="10 km"),
     *                 @OA\Property(property="point", type="object",
     *                     @OA\Property(property="coordinates", type="string", example="106.45,-6.19")
     *                 ),
     *                 @OA\Property(property="Lintang", type="string", example="6.19 LS"),
     *                 @OA\Property(property="Bujur", type="string", example="106.45 BT"),
     *                 @OA\Property(property="Wilayah", type="string", example="Jawa Barat"),
     *                 @OA\Property(property="Potensi", type="string", example="Tidak berpotensi tsunami")
     *             ))
     *         )
     *     )
     * )
     */
    public function getLatestEarthquakesBmkg(Request $request): JsonResponse
    {
        try {
            // Temporarily bypass auth for testing
            // $user = auth()->user();

            // if (!$user) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Token tidak valid'
            //     ], 401);
            // }

            $limit = min($request->get('limit', 15), 15); // Maksimal 15 data sesuai BMKG

            // Ambil data gempa terkini
            $gempaData = BencanaBmkg::where('jenis_bencana', 'gempa_terkini')
                ->latest('waktu_pembaruan')
                ->limit($limit)
                ->get();

            if ($gempaData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gempa terkini tidak tersedia'
                ], 404);
            }

            // Format data sesuai dengan dokumentasi BMKG
            $formattedData = $this->bmkgService->formatMultipleEarthquakeData($gempaData);

            return response()->json([
                'success' => true,
                'message' => 'Data gempa terkini berhasil diambil',
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data gempa terkini: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data gempa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/gempa/dirasakan",
     *     tags={"BMKG Integration"},
     *     summary="Get felt earthquake data in BMKG format",
     *     description="Mengambil data gempa dirasakan dalam format yang sesuai dengan dokumentasi BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data yang dikembalikan (maksimal 15)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data gempa dirasakan berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Tanggal", type="string", example="16 Des 2024"),
     *                 @OA\Property(property="Jam", type="string", example="13:44:55 WIB"),
     *                 @OA\Property(property="DateTime", type="string", example="2024-12-16T13:44:55+00:00"),
     *                 @OA\Property(property="Magnitude", type="number", example=3.5),
     *                 @OA\Property(property="Kedalaman", type="string", example="10 km"),
     *                 @OA\Property(property="point", type="object",
     *                     @OA\Property(property="coordinates", type="string", example="106.45,-6.19")
     *                 ),
     *                 @OA\Property(property="Lintang", type="string", example="6.19 LS"),
     *                 @OA\Property(property="Bujur", type="string", example="106.45 BT"),
     *                 @OA\Property(property="Wilayah", type="string", example="Jawa Barat"),
     *                 @OA\Property(property="Dirasakan", type="string", example="II-III Bogor")
     *             ))
     *         )
     *     )
     * )
     */
    public function getEarthquakeFeltBmkg(Request $request): JsonResponse
    {
        try {
            // Temporarily bypass auth for testing
            // $user = auth()->user();

            // if (!$user) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Token tidak valid'
            //     ], 401);
            // }

            $limit = min($request->get('limit', 15), 15); // Maksimal 15 data sesuai BMKG

            // Ambil data gempa dirasakan
            $gempaData = BencanaBmkg::where('jenis_bencana', 'gempa_dirasakan')
                ->latest('waktu_pembaruan')
                ->limit($limit)
                ->get();

            if ($gempaData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gempa dirasakan tidak tersedia'
                ], 404);
            }

            // Format data sesuai dengan dokumentasi BMKG
            $formattedData = $this->bmkgService->formatMultipleEarthquakeData($gempaData);

            return response()->json([
                'success' => true,
                'message' => 'Data gempa dirasakan berhasil diambil',
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data gempa dirasakan: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data gempa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-peringatan-dini",
     *     tags={"BMKG Integration"},
     *     summary="Sync weather warning RSS data from BMKG",
     *     description="Sinkronisasi data peringatan dini cuaca (RSS Feed) dari BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Bahasa (id/en)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "en"}, default="id")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data peringatan dini cuaca berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function syncNowcastRssData(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $language = $request->get('language', 'id');
            $jumlahDataBaru = $this->bmkgService->syncNowcastRssData($language);

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data peringatan dini cuaca berhasil',
                'data' => [
                    'jumlah_data_baru' => $jumlahDataBaru,
                    'language' => $language
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data peringatan dini cuaca: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi data peringatan dini cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bmkg/sync-detail-peringatan",
     *     tags={"BMKG Integration"},
     *     summary="Sync CAP detail alert data from BMKG",
     *     description="Sinkronisasi data detail peringatan dini cuaca (CAP) dari BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="alert_code",
     *         in="query",
     *         description="Kode alert CAP (contoh: CJB20251013001)",
     *         required=true,
     *         @OA\Schema(type="string", example="CJB20251013001")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Bahasa (id/en)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "en"}, default="id")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sinkronisasi data detail peringatan berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="jumlah_data_baru", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function syncNowcastCapDetail(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya pengguna dengan role tertentu yang bisa sinkronisasi data
        if (!in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $alertCode = $request->get('alert_code');
            $language = $request->get('language', 'id');

            if (!$alertCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode alert diperlukan'
                ], 400);
            }

            $jumlahDataBaru = $this->bmkgService->syncNowcastCapDetail($alertCode, $language);

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data detail peringatan berhasil',
                'data' => [
                    'jumlah_data_baru' => $jumlahDataBaru,
                    'alert_code' => $alertCode,
                    'language' => $language
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat sinkronisasi data detail peringatan: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi data detail peringatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/peringatan-dini",
     *     tags={"BMKG Integration"},
     *     summary="Get weather warning RSS data",
     *     description="Mengambil data peringatan dini cuaca dari RSS feed BMKG atau database",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Bahasa (id/en)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "en"}, default="id")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Sumber data: live atau database",
     *         required=false,
     *         @OA\Schema(type="string", enum={"live", "database"}, default="database")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data yang dikembalikan",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data peringatan dini cuaca berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="channel", type="object"),
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function getNowcastRssFeed(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        try {
            $language = $request->get('language', 'id');
            $source = $request->get('source', 'database');
            $limit = $request->get('limit', 20);

            if ($source === 'live') {
                // Get live data from BMKG RSS feed
                $data = $this->bmkgService->getNowcastRssFeed($language);

                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengambil data live dari BMKG RSS'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data peringatan dini cuaca live berhasil diambil',
                    'data' => $data
                ]);
            } else {
                // Get data from database
                $data = BencanaBmkg::where('jenis_bencana', 'peringatan_dini_cuaca')
                    ->orderBy('waktu_pembaruan', 'desc')
                    ->limit($limit)
                    ->get();

                if ($data->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data peringatan dini cuaca tidak tersedia di database'
                    ], 404);
                }

                // Format data untuk response
                $formattedData = $data->map(function ($item) {
                    $isiData = $item->isi_data;

                    // Jika isi_data adalah string, decode ke array
                    if (is_string($isiData)) {
                        $isiData = json_decode($isiData, true);
                    }

                    return [
                        'id_bencana' => $item->id_bencana,
                        'judul' => $item->judul,
                        'lokasi' => $item->lokasi,
                        'waktu_pembaruan' => $item->waktu_pembaruan,
                        'peringkat' => $item->peringkat,
                        'sumber_data' => $item->sumber_data,
                        'rss_data' => [
                            'title' => $isiData['title'] ?? null,
                            'link' => $isiData['link'] ?? null,
                            'description' => $isiData['description'] ?? null,
                            'author' => $isiData['author'] ?? null,
                            'pubDate' => $isiData['pubDate'] ?? null,
                            'guid' => $isiData['guid'] ?? null
                        ]
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Data peringatan dini cuaca berhasil diambil dari database',
                    'data' => [
                        'items' => $formattedData,
                        'total' => $formattedData->count()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data peringatan dini cuaca: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data peringatan dini cuaca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bmkg/detail-peringatan/{alert_code}",
     *     tags={"BMKG Integration"},
     *     summary="Get CAP detail alert data",
     *     description="Mengambil data detail peringatan dini cuaca (CAP) dari BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="alert_code",
     *         in="path",
     *         description="Kode alert CAP (contoh: CJB20251013001)",
     *         required=true,
     *         @OA\Schema(type="string", example="CJB20251013001")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Bahasa (id/en)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "en"}, default="id")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Sumber data: live atau database",
     *         required=false,
     *         @OA\Schema(type="string", enum={"live", "database"}, default="live")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data detail peringatan berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="alert_code", type="string"),
     *                 @OA\Property(property="headline", type="string"),
     *                 @OA\Property(property="event", type="string"),
     *                 @OA\Property(property="urgency", type="string"),
     *                 @OA\Property(property="severity", type="string"),
     *                 @OA\Property(property="certainty", type="string"),
     *                 @OA\Property(property="effective", type="string"),
     *                 @OA\Property(property="expires", type="string"),
     *                 @OA\Property(property="senderName", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="web", type="string"),
     *                 @OA\Property(property="area", type="object",
     *                     @OA\Property(property="areaDesc", type="string"),
     *                     @OA\Property(property="polygons", type="array", @OA\Items(
*                         type="array",
*                         @OA\Items(type="number")
*                     ))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Alert not found")
     * )
     */
    public function getNowcastCapDetail(Request $request, $alertCode): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        try {
            $language = $request->get('language', 'id');
            $source = $request->get('source', 'live');

            if ($source === 'live') {
                // Get live data from BMKG CAP API
                $data = $this->bmkgService->getNowcastCapDetail($alertCode, $language);

                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data detail peringatan tidak ditemukan'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data detail peringatan live berhasil diambil',
                    'data' => $data
                ]);
            } else {
                // Get data from database
                $data = BencanaBmkg::where('jenis_bencana', 'detail_peringatan_dini')
                    ->where('id_bencana', $alertCode)
                    ->first();

                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data detail peringatan tidak tersedia di database'
                    ], 404);
                }

                // Decode isi_data jika dalam bentuk JSON string
                if (is_string($data->isi_data)) {
                    $data->isi_data = json_decode($data->isi_data, true);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data detail peringatan berhasil diambil dari database',
                    'data' => $data->isi_data
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil data detail peringatan: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data detail peringatan: ' . $e->getMessage()
            ], 500);
        }
    }
}