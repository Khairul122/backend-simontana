<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Laporan;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Monitoring",
 *     description="API endpoints untuk manajemen data Monitoring Bencana"
 * )
 */
class MonitoringController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/monitoring",
     *     tags={"Monitoring"},
     *     summary="Get all monitoring",
     *     description="Mengambil semua data monitoring dengan filter dan pagination",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id_laporan",
     *         in="query",
     *         description="Filter by laporan id",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_operator",
     *         in="query",
     *         description="Filter by operator id",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *             @OA\Property(property="message", type="string", example="Data monitoring berhasil diambil"),
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

            $query = Monitoring::with(['laporan', 'operator']);

            // Filter berdasarkan id_laporan
            if ($request->has('id_laporan')) {
                $query->where('id_laporan', $request->id_laporan);
            }

            // Filter berdasarkan id_operator
            if ($request->has('id_operator')) {
                $query->where('id_operator', $request->id_operator);
            }

            $perPage = $request->get('per_page', 20);
            $monitorings = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data monitoring berhasil diambil',
                'data' => $monitorings
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
     *     path="/api/monitoring",
     *     tags={"Monitoring"},
     *     summary="Create new monitoring",
     *     description="Membuat data monitoring baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_laporan", "id_operator", "waktu_monitoring", "hasil_monitoring"},
     *             @OA\Property(property="id_laporan", type="integer", example=1),
     *             @OA\Property(property="id_operator", type="integer", example=1),
     *             @OA\Property(property="waktu_monitoring", type="string", format="date-time", example="2025-12-16 10:00:00"),
     *             @OA\Property(property="hasil_monitoring", type="string", example="Pemantauan terhadap lokasi bencana dilakukan secara berkala"),
     *             @OA\Property(property="koordinat_gps", type="string", example="-6.2088,106.8456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Monitoring berhasil dibuat"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'id_laporan' => 'required|exists:laporan,id_laporan',
                'id_operator' => 'required|exists:pengguna,id',
                'waktu_monitoring' => 'required|date',
                'hasil_monitoring' => 'required|string',
                'koordinat_gps' => 'sometimes|nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah laporan valid
            $laporan = Laporan::find($request->id_laporan);
            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            // Buat monitoring
            $monitoring = Monitoring::create($request->all());

            // Load relasi untuk response
            $monitoring->load(['laporan', 'operator']);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil dibuat',
                'data' => $monitoring
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Get specific monitoring",
     *     description="Mengambil data monitoring berdasarkan ID",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID monitoring",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data monitoring berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Monitoring not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $monitoring = Monitoring::with(['laporan', 'operator'])->find($id);

            if (!$monitoring) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monitoring tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data monitoring berhasil diambil',
                'data' => $monitoring
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Update monitoring",
     *     description="Mengupdate data monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID monitoring",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="hasil_monitoring", type="string", example="Pemantauan terhadap lokasi bencana dilakukan secara berkala"),
     *             @OA\Property(property="waktu_monitoring", type="string", format="date-time", example="2025-12-16 10:00:00"),
     *             @OA\Property(property="koordinat_gps", type="string", example="-6.2088,106.8456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Monitoring berhasil diupdate"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'hasil_monitoring' => 'sometimes|required|string',
                'waktu_monitoring' => 'sometimes|required|date',
                'koordinat_gps' => 'sometimes|nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $monitoring = Monitoring::find($id);

            if (!$monitoring) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monitoring tidak ditemukan'
                ], 404);
            }

            $monitoring->update($request->all());

            // Load relasi untuk response
            $monitoring->load(['laporan', 'operator']);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil diupdate',
                'data' => $monitoring
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Delete monitoring",
     *     description="Menghapus data monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID monitoring",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Monitoring berhasil dihapus")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $monitoring = Monitoring::find($id);

            if (!$monitoring) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monitoring tidak ditemukan'
                ], 404);
            }

            $monitoring->delete();

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }
}