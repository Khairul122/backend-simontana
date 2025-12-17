<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Kabupaten",
 *     description="API endpoints untuk manajemen data Kabupaten"
 * )
 */
class KabupatenController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/kabupaten",
     *     tags={"Kabupaten"},
     *     summary="Get all kabupaten",
     *     description="Mengambil semua data kabupaten dengan opsi include relasi",
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (provinsi,kecamatans)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"provinsi","kecamatans"})
     *     ),
     *     @OA\Parameter(
     *         name="id_provinsi",
     *         in="query",
     *         description="Filter by provinsi ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kabupaten berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Aceh Barat"),
     *                     @OA\Property(property="id_provinsi", type="integer", example=1),
     *                     @OA\Property(property="provinsi", type="object"),
     *                     @OA\Property(property="kecamatans", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kabupaten::query();

        // Filter by provinsi jika diminta
        if ($request->has('id_provinsi')) {
            $query->where('id_provinsi', $request->get('id_provinsi'));
        }

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['provinsi', 'kecamatans'])) {
                    $query->with($include);
                }
            }
        }

        $kabupaten = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kabupaten berhasil diambil',
            'data' => $kabupaten
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/kabupaten",
     *     tags={"Kabupaten"},
     *     summary="Create new kabupaten",
     *     description="Membuat data kabupaten baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_provinsi"},
     *             @OA\Property(property="nama", type="string", example="Aceh Barat Daya", maxLength=100),
     *             @OA\Property(property="id_provinsi", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kabupaten berhasil ditambahkan"),
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
            'nama' => 'required|string|max:100',
            'id_provinsi' => 'required|integer|exists:provinsi,id'
        ]);

        // Cek apakah nama kabupaten sudah ada di provinsi yang sama
        $exists = Kabupaten::where('nama', $request->nama)
            ->where('id_provinsi', $request->id_provinsi)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten dengan nama tersebut sudah ada di provinsi ini'
            ], 422);
        }

        $kabupaten = Kabupaten::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten berhasil ditambahkan',
            'data' => $kabupaten
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/kabupaten/{id}",
     *     tags={"Kabupaten"},
     *     summary="Get specific kabupaten",
     *     description="Mengambil data kabupaten berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kabupaten",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (provinsi,kecamatans)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"provinsi","kecamatans"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kabupaten berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kabupaten not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Kabupaten::query();

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['provinsi', 'kecamatans'])) {
                    $query->with($include);
                }
            }
        }

        $kabupaten = $query->find($id);

        if (!$kabupaten) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kabupaten berhasil diambil',
            'data' => $kabupaten
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/kabupaten/{id}",
     *     tags={"Kabupaten"},
     *     summary="Update kabupaten",
     *     description="Mengupdate data kabupaten",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kabupaten",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_provinsi"},
     *             @OA\Property(property="nama", type="string", example="Aceh Barat Daya", maxLength=100),
     *             @OA\Property(property="id_provinsi", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kabupaten berhasil diupdate"),
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

        $kabupaten = Kabupaten::find($id);

        if (!$kabupaten) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'id_provinsi' => 'required|integer|exists:provinsi,id'
        ]);

        // Cek apakah nama kabupaten sudah ada di provinsi yang sama (exclude current record)
        $exists = Kabupaten::where('nama', $request->nama)
            ->where('id_provinsi', $request->id_provinsi)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten dengan nama tersebut sudah ada di provinsi ini'
            ], 422);
        }

        $kabupaten->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten berhasil diupdate',
            'data' => $kabupaten
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/kabupaten/{id}",
     *     tags={"Kabupaten"},
     *     summary="Delete kabupaten",
     *     description="Menghapus data kabupaten",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kabupaten",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kabupaten berhasil dihapus")
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

        $kabupaten = Kabupaten::find($id);

        if (!$kabupaten) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada relasi data
        if ($kabupaten->kecamatans()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kabupaten karena masih memiliki data kecamatan'
            ], 422);
        }

        $kabupaten->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten berhasil dihapus'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/kabupaten/statistics",
     *     tags={"Kabupaten"},
     *     summary="Get kabupaten statistics",
     *     description="Mengambil statistik data kabupaten",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik kabupaten berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_kabupaten", type="integer", example=514),
     *                 @OA\Property(property="total_kecamatan", type="integer", example=7247),
     *                 @OA\Property(property="kabupaten_terbanyak", type="string", example="Papua"),
     *                 @OA\Property(property="provinsi_terbanyak", type="string", example="Jawa Barat")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $totalKabupaten = Kabupaten::count();

        // Kabupaten dengan kecamatan terbanyak
        $kabupatenTerbanyak = Kabupaten::withCount('kecamatans')
            ->orderBy('kecamatans_count', 'desc')
            ->first();

        // Provinsi dengan kabupaten terbanyak
        $provinsiTerbanyak = Provinsi::withCount('kabupatens')
            ->orderBy('kabupatens_count', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Statistik kabupaten berhasil diambil',
            'data' => [
                'total_kabupaten' => $totalKabupaten,
                'total_kecamatan' => \App\Models\Kecamatan::count(),
                'kabupaten_terbanyak' => $kabupatenTerbanyak ? $kabupatenTerbanyak->nama : '-',
                'jumlah_kecamatan_terbanyak' => $kabupatenTerbanyak ? $kabupatenTerbanyak->kecamatans_count : 0,
                'provinsi_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->nama : '-',
                'jumlah_kabupaten_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->kabupatens_count : 0
            ]
        ]);
    }
}
