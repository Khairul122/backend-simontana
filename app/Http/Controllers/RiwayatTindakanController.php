<?php

namespace App\Http\Controllers;

use App\Models\RiwayatTindakan;
use App\Models\TindakLanjut;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Riwayat Tindakan",
 *     description="API endpoints untuk manajemen data Riwayat Tindakan Bencana"
 * )
 */
class RiwayatTindakanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/riwayat-tindakan",
     *     tags={"Riwayat Tindakan"},
     *     summary="Get all riwayat tindakan",
     *     description="Mengambil semua data riwayat tindakan dengan filter dan pagination",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="tindaklanjut_id",
     *         in="query",
     *         description="Filter by tindak lanjut id",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_petugas",
     *         in="query",
     *         description="Filter by petugas id",
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
     *             @OA\Property(property="message", type="string", example="Data riwayat tindakan berhasil diambil"),
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

            $query = RiwayatTindakan::with(['tindakLanjut', 'petugas']);

            // Filter berdasarkan tindaklanjut_id
            if ($request->has('tindaklanjut_id')) {
                $query->where('tindaklanjut_id', $request->tindaklanjut_id);
            }

            // Filter berdasarkan id_petugas
            if ($request->has('id_petugas')) {
                $query->where('id_petugas', $request->id_petugas);
            }

            $perPage = $request->get('per_page', 20);
            $riwayatTindakans = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat tindakan berhasil diambil',
                'data' => $riwayatTindakans
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
     *     path="/riwayat-tindakan",
     *     tags={"Riwayat Tindakan"},
     *     summary="Create new riwayat tindakan",
     *     description="Membuat data riwayat tindakan baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tindaklanjut_id", "id_petugas", "keterangan", "waktu_tindakan"},
     *             @OA\Property(property="tindaklanjut_id", type="integer", example=1),
     *             @OA\Property(property="id_petugas", type="integer", example=1),
     *             @OA\Property(property="keterangan", type="string", example="Melakukan evakuasi warga terdampak"),
     *             @OA\Property(property="waktu_tindakan", type="string", format="date-time", example="2025-12-16 10:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Riwayat tindakan berhasil dibuat"),
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
                'tindaklanjut_id' => 'required|exists:tindaklanjut,id_tindaklanjut',
                'id_petugas' => 'required|exists:pengguna,id',
                'keterangan' => 'required|string',
                'waktu_tindakan' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah tindakan lanjut valid
            $tindakLanjut = TindakLanjut::find($request->tindaklanjut_id);
            if (!$tindakLanjut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tindak lanjut tidak ditemukan'
                ], 404);
            }

            // Buat riwayat tindakan
            $riwayatTindakan = RiwayatTindakan::create($request->all());

            // Load relasi untuk response
            $riwayatTindakan->load(['tindakLanjut', 'petugas']);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tindakan berhasil dibuat',
                'data' => $riwayatTindakan
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
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Get specific riwayat tindakan",
     *     description="Mengambil data riwayat tindakan berdasarkan ID",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID riwayat tindakan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data riwayat tindakan berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Riwayat Tindakan not found")
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

            $riwayatTindakan = RiwayatTindakan::with(['tindakLanjut', 'petugas'])->find($id);

            if (!$riwayatTindakan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat tindakan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat tindakan berhasil diambil',
                'data' => $riwayatTindakan
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
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Update riwayat tindakan",
     *     description="Mengupdate data riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID riwayat tindakan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="keterangan", type="string", example="Melakukan evakuasi warga terdampak"),
     *             @OA\Property(property="waktu_tindakan", type="string", format="date-time", example="2025-12-16 10:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Riwayat tindakan berhasil diupdate"),
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
                'keterangan' => 'sometimes|required|string',
                'waktu_tindakan' => 'sometimes|required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $riwayatTindakan = RiwayatTindakan::find($id);

            if (!$riwayatTindakan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat tindakan tidak ditemukan'
                ], 404);
            }

            $riwayatTindakan->update($request->all());

            // Load relasi untuk response
            $riwayatTindakan->load(['tindakLanjut', 'petugas']);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tindakan berhasil diupdate',
                'data' => $riwayatTindakan
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
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Delete riwayat tindakan",
     *     description="Menghapus data riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID riwayat tindakan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Riwayat tindakan berhasil dihapus")
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

            $riwayatTindakan = RiwayatTindakan::find($id);

            if (!$riwayatTindakan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat tindakan tidak ditemukan'
                ], 404);
            }

            $riwayatTindakan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tindakan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }
}