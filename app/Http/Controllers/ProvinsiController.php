<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Provinsi",
 *     description="API endpoints untuk manajemen data Provinsi"
 * )
 */
class ProvinsiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/provinsi",
     *     tags={"Provinsi"},
     *     summary="Get all provinsi",
     *     description="Mengambil semua data provinsi dengan opsi include relasi",
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kabupatens)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kabupatens"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data provinsi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Aceh"),
     *                     @OA\Property(property="kabupatens", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Provinsi::query();

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kabupatens'])) {
                    $query->with($include);
                }
            }
        }

        $provinsi = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data provinsi berhasil diambil',
            'data' => $provinsi
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/provinsi",
     *     tags={"Provinsi"},
     *     summary="Create new provinsi",
     *     description="Membuat data provinsi baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama"},
     *             @OA\Property(property="nama", type="string", example="Papua Barat Daya", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Provinsi berhasil ditambahkan"),
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

        // Hanya admin yang dapat mengakses endpoint ini
        if ($user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $request->validate([
            'nama' => 'required|string|max:100|unique:provinsi,nama'
        ]);

        $provinsi = Provinsi::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Provinsi berhasil ditambahkan',
            'data' => $provinsi
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/provinsi/{id}",
     *     tags={"Provinsi"},
     *     summary="Get specific provinsi",
     *     description="Mengambil data provinsi berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kabupatens)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kabupatens"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data provinsi berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provinsi not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Provinsi::query();

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kabupatens'])) {
                    $query->with($include);
                }
            }
        }

        $provinsi = $query->find($id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data provinsi berhasil diambil',
            'data' => $provinsi
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/provinsi/{id}",
     *     tags={"Provinsi"},
     *     summary="Update provinsi",
     *     description="Mengupdate data provinsi",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama"},
     *             @OA\Property(property="nama", type="string", example="Daerah Khusus Jakarta", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Provinsi berhasil diupdate"),
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

        // Hanya admin yang dapat mengakses endpoint ini
        if ($user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $provinsi = Provinsi::find($id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100|unique:provinsi,nama,'.$id
        ]);

        $provinsi->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Provinsi berhasil diupdate',
            'data' => $provinsi
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/provinsi/{id}",
     *     tags={"Provinsi"},
     *     summary="Delete provinsi",
     *     description="Menghapus data provinsi",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Provinsi berhasil dihapus")
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

        // Hanya admin yang dapat mengakses endpoint ini
        if ($user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $provinsi = Provinsi::find($id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada relasi data
        if ($provinsi->kabupatens()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus provinsi karena masih memiliki data kabupaten'
            ], 422);
        }

        $provinsi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Provinsi berhasil dihapus'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/provinsi/statistics",
     *     tags={"Provinsi"},
     *     summary="Get provinsi statistics",
     *     description="Mengambil statistik data provinsi",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik provinsi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_provinsi", type="integer", example=38),
     *                 @OA\Property(property="total_kabupaten", type="integer", example=514),
     *                 @OA\Property(property="provinsi_terbanyak", type="string", example="Jawa Barat")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $totalProvinsi = Provinsi::count();

        // Provinsi dengan kabupaten terbanyak
        $provinsiTerbanyak = Provinsi::withCount('kabupatens')
            ->orderBy('kabupatens_count', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Statistik provinsi berhasil diambil',
            'data' => [
                'total_provinsi' => $totalProvinsi,
                'total_kabupaten' => \App\Models\Kabupaten::count(),
                'provinsi_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->nama : '-',
                'jumlah_kabupaten_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->kabupatens_count : 0
            ]
        ]);
    }
}