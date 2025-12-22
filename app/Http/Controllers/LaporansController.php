<?php

namespace App\Http\Controllers;

use App\Models\Laporans;
use App\Models\KategoriBencana;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Laporan Management",
 *     description="CRUD operations dan statistik untuk laporan bencana"
 * )
 */
class LaporansController extends Controller
{
    /**
     * Display a listing of the laporan.
     *
     * @OA\Get(
     *     path="/laporans",
     *     tags={"Laporan Management"},
     *     summary="Get all laporan",
     *     description="Retrieve list of laporan with optional filtering",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Selesai","Ditolak"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit results",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="judul_laporan", type="string"),
 *                 @OA\Property(property="deskripsi", type="string"),
 *                 @OA\Property(property="status", type="string"),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             ))
     *         )
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Laporans::with(['pelapor', 'kategori', 'desa.kecamatan.kabupaten.provinsi']);

            // Filter by status if provided
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            // Filter by date range if provided
            if ($request->has('start_date') && $request->start_date) {
                $startDate = $request->start_date;
                $endDate = $request->end_date ?? now();
                $query->dateRange($startDate, $endDate);
            }

            // Limit results if provided
            if ($request->has('limit') && $request->limit) {
                $laporans = $query->limit($request->limit)->latest()->get();
            } else {
                $laporans = $query->latest()->paginate(15);
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan retrieved successfully',
                'data' => $laporans
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laporan statistics.
     *
     * @OA\Get(
     *     path="/laporans/statistics",
     *     tags={"Laporan Management"},
     *     summary="Get laporan statistics",
     *     description="Retrieve comprehensive statistics for dashboard",
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Filter by period (weekly, monthly, yearly)",
     *         @OA\Schema(type="string", enum={"weekly","monthly","yearly"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_laporan", type="integer"),
     *                 @OA\Property(property="laporan_perlu_verifikasi", type="integer"),
     *                 @OA\Property(property="laporan_ditindak", type="integer"),
     *                 @OA\Property(property="laporan_selesai", type="integer"),
     *                 @OA\Property(property="laporan_ditolak", type="integer"),
     *                 @OA\Property(property="weekly_stats", type="object"),
     *                 @OA\Property(property="categories_stats", type="object")
     *             )
     *         )
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = Laporans::query();

            // Filter by period if provided
            if ($request->has('period') && $request->period) {
                switch ($request->period) {
                    case 'weekly':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->subMonth());
                        break;
                    case 'yearly':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            }

            // Total laporan
            $total_laporan = $query->count();

            // Laporan perlu verifikasi (Status: 'Menunggu Verifikasi' atau 'Draft')
            $laporan_perlu_verifikasi = $query->clone()
                ->whereIn('status', ['Draft', 'Menunggu Verifikasi'])
                ->count();

            // Laporan ditindak (Status: 'Diproses', 'Diverifikasi', atau 'Tindak Lanjut')
            $laporan_ditindak = $query->clone()
                ->whereIn('status', ['Diverifikasi', 'Diproses', 'Tindak Lanjut'])
                ->count();

            // Laporan selesai (Status: 'Selesai')
            $laporan_selesai = $query->clone()
                ->where('status', 'Selesai')
                ->count();

            // Laporan ditolak (Status: 'Ditolak')
            $laporan_ditolak = $query->clone()
                ->where('status', 'Ditolak')
                ->count();

            // Weekly stats (last 7 days)
            $weekly_stats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = Laporans::whereDate('created_at', $date)->count();
                $weekly_stats[strtolower(now()->subDays($i)->format('D'))] = $count;
            }

            // Categories statistics
            $categories_stats = DB::table('laporans')
                ->join('kategori_bencana', 'laporans.id_kategori_bencana', '=', 'kategori_bencana.id')
                ->select('kategori_bencana.nama_kategori as category_name', DB::raw('count(*) as count'))
                ->groupBy('kategori_bencana.id', 'kategori_bencana.nama_kategori')
                ->orderBy('count', 'desc')
                ->get()
                ->keyBy('category_name')
                ->map(function ($item) {
                    return $item->count;
                })
                ->toArray();

            // Monthly trend
            $monthly_trend = DB::table('laporans')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();

            // Top pengguna
            $top_pengguna = DB::table('laporans')
                ->join('pengguna', 'laporans.id_pelapor', '=', 'pengguna.id')
                ->select('pengguna.nama as pengguna_name', DB::raw('count(*) as laporan_count'))
                ->groupBy('pengguna.id', 'pengguna.nama')
                ->orderBy('laporan_count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_laporan' => $total_laporan,
                    'laporan_perlu_verifikasi' => $laporan_perlu_verifikasi,
                    'laporan_ditindak' => $laporan_ditindak,
                    'laporan_selesai' => $laporan_selesai,
                    'laporan_ditolak' => $laporan_ditolak,
                    'laporan_baru' => $laporan_perlu_verifikasi, // Backward compatibility
                    'laporan_ditangani' => $laporan_ditindak, // Backward compatibility
                    'weekly_stats' => $weekly_stats,
                    'categories_stats' => $categories_stats,
                    'monthly_trend' => $monthly_trend,
                    'top_pengguna' => $top_pengguna
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created laporan.
     *
     * @OA\Post(
     *     path="/laporans",
     *     tags={"Laporan Management"},
     *     summary="Create new laporan",
     *     description="Create a new laporan with validated data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_pelapor","id_kategori_bencana","judul_laporan","deskripsi"},
     *             @OA\Property(property="id_pelapor", type="integer"),
     *             @OA\Property(property="id_kategori_bencana", type="integer"),
     *             @OA\Property(property="judul_laporan", type="string"),
     *             @OA\Property(property="deskripsi", type="string"),
     *             @OA\Property(property="alamat", type="string"),
     *             @OA\Property(property="id_desa", type="integer"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="tanggal_kejadian", type="string", format="date"),
     *             @OA\Property(property="waktu_kejadian", type="string", format="datetime")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Laporan created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="judul_laporan", type="string"),
 *                 @OA\Property(property="deskripsi", type="string"),
 *                 @OA\Property(property="status", type="string"),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             )
     *         )
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_pelapor' => 'required|exists:pengguna,id',
                'id_kategori_bencana' => 'required|exists:kategori_bencana,id',
                'judul_laporan' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'alamat' => 'nullable|string',
                'id_desa' => 'nullable|exists:desa,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'tanggal_kejadian' => 'nullable|date',
                'waktu_kejadian' => 'nullable|date',
                'tingkat_keparahan' => 'nullable|string|in:Rendah,Sedang,Tinggi,Kritis',
                'foto_bukti' => 'nullable|array',
                'foto_bukti.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $laporan = Laporans::create([
                'id_pelapor' => $request->id_pelapor,
                'id_kategori_bencana' => $request->id_kategori_bencana,
                'judul_laporan' => $request->judul_laporan,
                'deskripsi' => $request->deskripsi,
                'alamat' => $request->alamat,
                'id_desa' => $request->id_desa,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'waktu_kejadian' => $request->waktu_kejadian,
                'tingkat_keparahan' => $request->tingkat_keparahan ?? 'Sedang',
                'status' => 'Draft',
            ]);

            // Handle file uploads if any
            if ($request->hasFile('foto_bukti')) {
                // Implementation for file upload
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan created successfully',
                'data' => $laporan->load(['pelapor', 'kategori', 'desa'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified laporan.
     *
     * @OA\Get(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Get specific laporan",
     *     description="Retrieve a specific laporan by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="judul_laporan", type="string"),
 *                 @OA\Property(property="deskripsi", type="string"),
 *                 @OA\Property(property="status", type="string"),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             )
     *         )
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $laporan = Laporans::with([
                'pelapor',
                'kategori',
                'desa.kecamatan.kabupaten.provinsi',
                'tindakLanjut',
                'monitoring',
                'riwayatTindakan'
            ])->find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan retrieved successfully',
                'data' => $laporan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified laporan.
     *
     * @OA\Put(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Update laporan",
     *     description="Update an existing laporan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="judul_laporan", type="string"),
     *             @OA\Property(property="deskripsi", type="string"),
     *             @OA\Property(property="alamat", type="string"),
     *             @OA\Property(property="tingkat_keparahan", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan updated successfully"
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'sometimes|required|string|max:255',
                'deskripsi' => 'sometimes|required|string',
                'alamat' => 'nullable|string',
                'tingkat_keparahan' => 'nullable|string|in:Rendah,Sedang,Tinggi,Kritis'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $laporan->update($request->only([
                'judul_laporan',
                'deskripsi',
                'alamat',
                'tingkat_keparahan'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Laporan updated successfully',
                'data' => $laporan->load(['pelapor', 'kategori', 'desa'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified laporan.
     *
     * @OA\Delete(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Delete laporan",
     *     description="Delete a specific laporan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan deleted successfully"
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify laporan.
     *
     * @OA\Post(
     *     path="/laporans/{id}/verifikasi",
     *     tags={"Laporan Management"},
     *     summary="Verify laporan",
     *     description="Change laporan status to verified",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan verified successfully"
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function verifikasi($id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            $laporan->update(['status' => 'Diverifikasi']);

            // Create riwayat tindakan
            $user = auth()->user();
            $laporan->riwayatTindakan()->create([
                'id_laporan' => $laporan->id,
                'id_pengguna' => $user->id,
                'tindakan' => 'Verifikasi',
                'deskripsi' => 'Laporan telah diverifikasi oleh ' . $user->nama,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan verified successfully',
                'data' => $laporan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process laporan.
     *
     * @OA\Post(
     *     path="/laporans/{id}/proses",
     *     tags={"Laporan Management"},
     *     summary="Process laporan",
     *     description="Change laporan status to being processed",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan processed successfully"
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function proses($id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            $laporan->update(['status' => 'Diproses']);

            // Create riwayat tindakan
            $user = auth()->user();
            $laporan->riwayatTindakan()->create([
                'id_laporan' => $laporan->id,
                'id_pengguna' => $user->id,
                'tindakan' => 'Proses',
                'deskripsi' => 'Laporan sedang diproses oleh ' . $user->nama,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan processed successfully',
                'data' => $laporan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laporan riwayat.
     *
     * @OA\Get(
     *     path="/laporans/{id}/riwayat",
     *     tags={"Laporan Management"},
     *     summary="Get laporan riwayat",
     *     description="Get history of actions for a specific laporan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Riwayat retrieved successfully"
     *     ),
     *     security={{"jwt": {}}}
     * )
     */
    public function riwayat($id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan not found',
                    'data' => null
                ], 404);
            }

            $riwayat = $laporan->riwayatTindakan()
                ->with('pengguna')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat retrieved successfully',
                'data' => $riwayat
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve riwayat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}