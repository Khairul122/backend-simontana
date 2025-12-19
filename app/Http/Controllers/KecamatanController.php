<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Kecamatan",
 *     description="API endpoints untuk manajemen data Kecamatan"
 * )
 */
class KecamatanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/kecamatan",
     *     tags={"Kecamatan"},
     *     summary="Get all kecamatan",
     *     description="Mengambil semua data kecamatan dengan opsi include relasi",
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kabupaten,desas)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kabupaten","desas"})
     *     ),
     *     @OA\Parameter(
     *         name="id_kabupaten",
     *         in="query",
     *         description="Filter by kabupaten ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kecamatan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Bakongan"),
     *                     @OA\Property(property="id_kabupaten", type="integer", example=1),
     *                     @OA\Property(property="kabupaten", type="object"),
     *                     @OA\Property(property="desas", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kecamatan::query();

        // Filter by kabupaten jika diminta
        if ($request->has('id_kabupaten')) {
            $query->where('id_kabupaten', $request->get('id_kabupaten'));
        }

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kabupaten', 'desas'])) {
                    $query->with($include);
                }
            }
        }

        $kecamatan = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kecamatan berhasil diambil',
            'data' => $kecamatan
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/kecamatan",
     *     tags={"Kecamatan"},
     *     summary="Create new kecamatan",
     *     description="Membuat data kecamatan baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_kabupaten"},
     *             @OA\Property(property="nama", type="string", example="Bakongan Timur", maxLength=100),
     *             @OA\Property(property="id_kabupaten", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kecamatan berhasil ditambahkan"),
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
            'id_kabupaten' => 'required|integer|exists:kabupaten,id'
        ]);

        // Cek apakah nama kecamatan sudah ada di kabupaten yang sama
        $exists = Kecamatan::where('nama', $request->nama)
            ->where('id_kabupaten', $request->id_kabupaten)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan dengan nama tersebut sudah ada di kabupaten ini'
            ], 422);
        }

        $kecamatan = Kecamatan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kecamatan berhasil ditambahkan',
            'data' => $kecamatan
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/kecamatan/{id}",
     *     tags={"Kecamatan"},
     *     summary="Get specific kecamatan",
     *     description="Mengambil data kecamatan berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kecamatan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kabupaten,desas)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kabupaten","desas"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kecamatan berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kecamatan not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Kecamatan::query();

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kabupaten', 'desas'])) {
                    $query->with($include);
                }
            }
        }

        $kecamatan = $query->find($id);

        if (!$kecamatan) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kecamatan berhasil diambil',
            'data' => $kecamatan
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/kecamatan/{id}",
     *     tags={"Kecamatan"},
     *     summary="Update kecamatan",
     *     description="Mengupdate data kecamatan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kecamatan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_kabupaten"},
     *             @OA\Property(property="nama", type="string", example="Bakongan Timur", maxLength=100),
     *             @OA\Property(property="id_kabupaten", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kecamatan berhasil diupdate"),
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

        $kecamatan = Kecamatan::find($id);

        if (!$kecamatan) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'id_kabupaten' => 'required|integer|exists:kabupaten,id'
        ]);

        // Cek apakah nama kecamatan sudah ada di kabupaten yang sama (exclude current record)
        $exists = Kecamatan::where('nama', $request->nama)
            ->where('id_kabupaten', $request->id_kabupaten)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan dengan nama tersebut sudah ada di kabupaten ini'
            ], 422);
        }

        $kecamatan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kecamatan berhasil diupdate',
            'data' => $kecamatan
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/kecamatan/{id}",
     *     tags={"Kecamatan"},
     *     summary="Delete kecamatan",
     *     description="Menghapus data kecamatan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID kecamatan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kecamatan berhasil dihapus")
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

        $kecamatan = Kecamatan::find($id);

        if (!$kecamatan) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada relasi data
        if ($kecamatan->desas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kecamatan karena masih memiliki data desa'
            ], 422);
        }

        $kecamatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kecamatan berhasil dihapus'
        ]);
    }

    /**
     * Get kecamatan by kabupaten ID
     */
    public function getByKabupaten($kabupaten_id): JsonResponse
    {
        $kecamatan = Kecamatan::where('id_kabupaten', $kabupaten_id)
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kecamatan berhasil diambil',
            'data' => $kecamatan
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/kecamatan/statistics",
     *     tags={"Kecamatan"},
     *     summary="Get kecamatan statistics",
     *     description="Mengambil statistik data kecamatan",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik kecamatan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_kecamatan", type="integer", example=7247),
     *                 @OA\Property(property="total_desa", type="integer", example=83273),
     *                 @OA\Property(property="kecamatan_terbanyak", type="string", example="Papua"),
     *                 @OA\Property(property="kabupaten_terbanyak", type="string", example="Bogor")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $totalKecamatan = Kecamatan::count();

        // Kecamatan dengan desa terbanyak
        $kecamatanTerbanyak = Kecamatan::withCount('desas')
            ->orderBy('desas_count', 'desc')
            ->first();

        // Kabupaten dengan kecamatan terbanyak
        $kabupatenTerbanyak = Kabupaten::withCount('kecamatans')
            ->orderBy('kecamatans_count', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Statistik kecamatan berhasil diambil',
            'data' => [
                'total_kecamatan' => $totalKecamatan,
                'total_desa' => \App\Models\Desa::count(),
                'kecamatan_terbanyak' => $kecamatanTerbanyak ? $kecamatanTerbanyak->nama : '-',
                'jumlah_desa_terbanyak' => $kecamatanTerbanyak ? $kecamatanTerbanyak->desas_count : 0,
                'kabupaten_terbanyak' => $kabupatenTerbanyak ? $kabupatenTerbanyak->nama : '-',
                'jumlah_kecamatan_terbanyak' => $kabupatenTerbanyak ? $kabupatenTerbanyak->kecamatans_count : 0
            ]
        ]);
    }
}
