<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Desa",
 *     description="API endpoints untuk manajemen data Desa"
 * )
 */
class DesaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/desa",
     *     tags={"Desa"},
     *     summary="Get all desa",
     *     description="Mengambil semua data desa dengan opsi include relasi",
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kecamatan,pengguna,laporan)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kecamatan","pengguna","laporan"})
     *     ),
     *     @OA\Parameter(
     *         name="id_kecamatan",
     *         in="query",
     *         description="Filter by kecamatan ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data desa berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Ie Meule"),
     *                     @OA\Property(property="id_kecamatan", type="integer", example=1),
     *                     @OA\Property(property="kecamatan", type="object"),
     *                     @OA\Property(property="pengguna", type="array", @OA\Items()),
     *                     @OA\Property(property="laporan", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Desa::query();

        // Filter by kecamatan jika diminta
        if ($request->has('id_kecamatan')) {
            $query->where('id_kecamatan', $request->get('id_kecamatan'));
        }

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kecamatan', 'pengguna', 'laporan'])) {
                    $query->with($include);
                }
            }
        }

        $desa = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data desa berhasil diambil',
            'data' => $desa
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/desa",
     *     tags={"Desa"},
     *     summary="Create new desa",
     *     description="Membuat data desa baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_kecamatan"},
     *             @OA\Property(property="nama", type="string", example="Ie Meulee", maxLength=100),
     *             @OA\Property(property="id_kecamatan", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Desa berhasil ditambahkan"),
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
            'id_kecamatan' => 'required|integer|exists:kecamatan,id'
        ]);

        // Cek apakah nama desa sudah ada di kecamatan yang sama
        $exists = Desa::where('nama', $request->nama)
            ->where('id_kecamatan', $request->id_kecamatan)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Desa dengan nama tersebut sudah ada di kecamatan ini'
            ], 422);
        }

        $desa = Desa::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Desa berhasil ditambahkan',
            'data' => $desa
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/desa/{id}",
     *     tags={"Desa"},
     *     summary="Get specific desa",
     *     description="Mengambil data desa berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID desa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (kecamatan,pengguna,laporan)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"kecamatan","pengguna","laporan"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data desa berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Desa not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Desa::query();

        // Include relasi jika diminta
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            foreach ($includes as $include) {
                if (in_array($include, ['kecamatan', 'pengguna', 'laporan'])) {
                    $query->with($include);
                }
            }
        }

        $desa = $query->find($id);

        if (!$desa) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data desa berhasil diambil',
            'data' => $desa
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/desa/{id}",
     *     tags={"Desa"},
     *     summary="Update desa",
     *     description="Mengupdate data desa",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID desa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","id_kecamatan"},
     *             @OA\Property(property="nama", type="string", example="Ie Meulee", maxLength=100),
     *             @OA\Property(property="id_kecamatan", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Desa berhasil diupdate"),
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

        $desa = Desa::find($id);

        if (!$desa) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'id_kecamatan' => 'required|integer|exists:kecamatan,id'
        ]);

        // Cek apakah nama desa sudah ada di kecamatan yang sama (exclude current record)
        $exists = Desa::where('nama', $request->nama)
            ->where('id_kecamatan', $request->id_kecamatan)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Desa dengan nama tersebut sudah ada di kecamatan ini'
            ], 422);
        }

        $desa->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Desa berhasil diupdate',
            'data' => $desa
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/desa/{id}",
     *     tags={"Desa"},
     *     summary="Delete desa",
     *     description="Menghapus data desa",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID desa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Desa berhasil dihapus")
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

        $desa = Desa::find($id);

        if (!$desa) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada relasi data (pengguna atau laporan)
        if ($desa->pengguna()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus desa karena masih memiliki data pengguna'
            ], 422);
        }

        if ($desa->laporan()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus desa karena masih memiliki data laporan'
            ], 422);
        }

        $desa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Desa berhasil dihapus'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/desa/statistics",
     *     tags={"Desa"},
     *     summary="Get desa statistics",
     *     description="Mengambil statistik data desa",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik desa berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_desa", type="integer", example=83273),
     *                 @OA\Property(property="total_pengguna", type="integer", example=1250),
     *                 @OA\Property(property="total_laporan", type="integer", example=340),
     *                 @OA\Property(property="desa_terbanyak", type="string", example="Bandung"),
     *                 @OA\Property(property="kecamatan_terbanyak", type="string", example="Bogor")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $totalDesa = Desa::count();

        // Kecamatan dengan desa terbanyak
        $kecamatanTerbanyak = Kecamatan::withCount('desas')
            ->orderBy('desas_count', 'desc')
            ->first();

        // Provinsi dengan desa terbanyak (gunakan query manual)
        $provinsiTerbanyak = \App\Models\Provinsi::select('provinsi.*')
            ->selectRaw('COUNT(d.id) as total_desas')
            ->join('kabupaten as k', 'provinsi.id', '=', 'k.id_provinsi')
            ->join('kecamatan as kc', 'k.id', '=', 'kc.id_kabupaten')
            ->join('desa as d', 'kc.id', '=', 'd.id_kecamatan')
            ->groupBy('provinsi.id')
            ->orderBy('total_desas', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Statistik desa berhasil diambil',
            'data' => [
                'total_desa' => $totalDesa,
                'total_kecamatan' => Kecamatan::count(),
                'total_kabupaten' => \App\Models\Kabupaten::count(),
                'kecamatan_terbanyak' => $kecamatanTerbanyak ? $kecamatanTerbanyak->nama : '-',
                'jumlah_desa_terbanyak' => $kecamatanTerbanyak ? $kecamatanTerbanyak->desas_count : 0,
                'provinsi_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->nama : '-',
                'jumlah_desa_provinsi_terbanyak' => $provinsiTerbanyak ? $provinsiTerbanyak->total_desas : 0
            ]
        ]);
    }
}
