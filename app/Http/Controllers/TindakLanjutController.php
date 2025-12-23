<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Laporans;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Tindak Lanjut",
 *     description="API endpoints untuk manajemen data Tindak Lanjut Bencana"
 * )
 */
class TindakLanjutController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tindak-lanjut",
     *     tags={"Tindak Lanjut"},
     *     summary="Get all tindak lanjut",
     *     description="Mengambil semua data tindak lanjut dengan filter dan pagination",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="laporan_id",
     *         in="query",
     *         description="Filter by laporan id",
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
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Menuju Lokasi", "Selesai"})
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
     *             @OA\Property(property="message", type="string", example="Data tindak lanjut berhasil diambil"),
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $query = TindakLanjut::with(['laporan', 'petugas']);

        // Filter berdasarkan laporan_id
        if ($request->has('laporan_id')) {
            $query->where('laporan_id', $request->laporan_id);
        }

        // Filter berdasarkan id_petugas
        if ($request->has('id_petugas')) {
            $query->where('id_petugas', $request->id_petugas);
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $tindakLanjuts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data tindak lanjut berhasil diambil',
            'data' => $tindakLanjuts
        ]);
    }

    /**
     * @OA\Post(
     *     path="/tindak-lanjut",
     *     tags={"Tindak Lanjut"},
     *     summary="Create new tindak lanjut",
     *     description="Membuat data tindak lanjut baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"laporan_id", "id_petugas", "tanggal_tanggapan"},
     *             @OA\Property(property="laporan_id", type="integer", example=1),
     *             @OA\Property(property="id_petugas", type="integer", example=1),
     *             @OA\Property(property="tanggal_tanggapan", type="string", format="date-time", example="2025-12-16 10:00:00"),
     *             @OA\Property(property="status", type="string", enum={"Menuju Lokasi", "Selesai"}, default="Menuju Lokasi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tindak lanjut berhasil dibuat"),
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'laporan_id' => 'required|exists:laporan,id_laporan',
            'id_petugas' => 'required|exists:pengguna,id',
            'tanggal_tanggapan' => 'required|date',
            'status' => 'sometimes|in:Menuju Lokasi,Selesai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah laporan valid dan belum ditangani
        $laporan = Laporans::find($request->laporan_id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        // Buat tindak lanjut
        $tindakLanjut = TindakLanjut::create($request->all());

        // Load relasi untuk response
        $tindakLanjut->load(['laporan', 'petugas']);

        return response()->json([
            'success' => true,
            'message' => 'Tindak lanjut berhasil dibuat',
            'data' => $tindakLanjut
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Get specific tindak lanjut",
     *     description="Mengambil data tindak lanjut berdasarkan ID",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tindak lanjut",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data tindak lanjut berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tindak Lanjut not found")
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $tindakLanjut = TindakLanjut::with(['laporan', 'petugas'])->find($id);

        if (!$tindakLanjut) {
            return response()->json([
                'success' => false,
                'message' => 'Tindak lanjut tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data tindak lanjut berhasil diambil',
            'data' => $tindakLanjut
        ]);
    }

    /**
     * @OA\Put(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Update tindak lanjut",
     *     description="Mengupdate data tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tindak lanjut",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tanggal_tanggapan", type="string", format="date-time", example="2025-12-16 10:00:00"),
     *             @OA\Property(property="status", type="string", enum={"Menuju Lokasi", "Selesai"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tindak lanjut berhasil diupdate"),
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_tanggapan' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Menuju Lokasi,Selesai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tindakLanjut = TindakLanjut::find($id);

        if (!$tindakLanjut) {
            return response()->json([
                'success' => false,
                'message' => 'Tindak lanjut tidak ditemukan'
            ], 404);
        }

        $tindakLanjut->update($request->all());

        // Load relasi untuk response
        $tindakLanjut->load(['laporan', 'petugas']);

        return response()->json([
            'success' => true,
            'message' => 'Tindak lanjut berhasil diupdate',
            'data' => $tindakLanjut
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Delete tindak lanjut",
     *     description="Menghapus data tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tindak lanjut",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tindak lanjut berhasil dihapus")
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $tindakLanjut = TindakLanjut::find($id);

        if (!$tindakLanjut) {
            return response()->json([
                'success' => false,
                'message' => 'Tindak lanjut tidak ditemukan'
            ], 404);
        }

        $tindakLanjut->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tindak lanjut berhasil dihapus'
        ]);
    }
}