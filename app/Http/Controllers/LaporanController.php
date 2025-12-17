<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Laporan",
 *     description="API endpoints untuk manajemen data Laporan Bencana"
 * )
 */
class LaporanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/laporan",
     *     tags={"Laporan"},
     *     summary="Get all laporan",
     *     description="Mengambil semua data laporan dengan filter dan pagination",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Draft", "Menunggu Verifikasi", "Diverifikasi", "Diproses", "Selesai", "Ditolak"})
     *     ),
     *     @OA\Parameter(
     *         name="id_kategori_bencana",
     *         in="query",
     *         description="Filter by kategori bencana",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_desa",
     *         in="query",
     *         description="Filter by desa",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tingkat_keparahan",
     *         in="query",
     *         description="Filter by tingkat keparahan",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Rendah", "Sedang", "Tinggi", "Kritis"})
     *     ),
     *     @OA\Parameter(
     *         name="is_prioritas",
     *         in="query",
     *         description="Filter laporan prioritas",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search laporan",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi data",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pelapor", "kategoriBencana", "desa", "verifikator", "penanggungJawab"})
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
     *             @OA\Property(property="message", type="string", example="Data laporan berhasil diambil"),
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
        $query = Laporan::query();

        if ($user->role === 'Warga') {
            $query->where('id_pelapor', $user->id);
        }

        $query->filter($request->all())
              ->search($request->get('search'))
              ->orderByRelevance();

        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            $validIncludes = ['pelapor', 'kategoriBencana', 'desa', 'verifikator', 'penanggungJawab'];

            foreach ($includes as $include) {
                if (in_array($include, $validIncludes)) {
                    $query->with($include);
                }
            }
        }

        $perPage = $request->get('per_page', 20);
        $laporans = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil diambil',
            'data' => $laporans
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/laporan",
     *     tags={"Laporan"},
     *     summary="Create new laporan",
     *     description="Membuat laporan bencana baru dengan upload foto/video",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"judul_laporan", "deskripsi", "tingkat_keparahan", "latitude", "longitude", "waktu_laporan"},
     *                 @OA\Property(property="judul_laporan", type="string", maxLength=200, example="Kebakaran di Rumah Warga"),
     *                 @OA\Property(property="deskripsi", type="string", example="Terjadi kebakaran di rumah warga yang menghanguskan 1 rumah"),
     *                 @OA\Property(property="id_kategori_bencana", type="integer", example=1),
     *                 @OA\Property(property="id_desa", type="integer", example=1),
     *                 @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah", "Sedang", "Tinggi", "Kritis"}),
     *                 @OA\Property(property="latitude", type="number", format="float", example=-6.2088),
     *                 @OA\Property(property="longitude", type="number", format="float", example=106.8456),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="foto_bukti_1", type="string", format="binary"),
     *                 @OA\Property(property="foto_bukti_2", type="string", format="binary"),
     *                 @OA\Property(property="foto_bukti_3", type="string", format="binary"),
     *                 @OA\Property(property="video_bukti", type="string", format="binary"),
     *                 @OA\Property(property="jumlah_korban", type="integer", default=0),
     *                 @OA\Property(property="jumlah_rumah_rusak", type="integer", default=0),
     *                 @OA\Property(property="data_tambahan", type="object"),
     *                 @OA\Property(property="waktu_laporan", type="string", format="date-time"),
     *                 @OA\Property(property="is_prioritas", type="boolean", default=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil dibuat"),
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
            'judul_laporan' => 'required|string|max:200',
            'deskripsi' => 'required|string|max:2000',
            'id_kategori_bencana' => 'nullable|exists:kategori_bencanas,id',
            'id_desa' => 'nullable|exists:desas,id',
            'tingkat_keparahan' => 'required|in:Rendah,Sedang,Tinggi,Kritis',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat_lengkap' => 'nullable|string|max:500',
            'foto_bukti_1' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'foto_bukti_2' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'foto_bukti_3' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'video_bukti' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
            'jumlah_korban' => 'nullable|integer|min:0',
            'jumlah_rumah_rusak' => 'nullable|integer|min:0',
            'data_tambahan' => 'nullable|array',
            'waktu_laporan' => 'required|date',
            'is_prioritas' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['id_pelapor'] = $user->id;
            $data['status'] = $request->is_prioritas ? 'Menunggu Verifikasi' : 'Draft';

            foreach (['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('laporan/foto', 'public');
                }
            }

            if ($request->hasFile('video_bukti')) {
                $data['video_bukti'] = $request->file('video_bukti')->store('laporan/video', 'public');
            }

            $laporan = Laporan::create($data);

            DB::commit();

            $laporan->load(['pelapor', 'kategoriBencana', 'desa']);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $laporan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/laporan/{id}",
     *     tags={"Laporan"},
     *     summary="Get specific laporan",
     *     description="Mengambil data laporan berdasarkan ID",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi data",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pelapor", "kategoriBencana", "desa", "verifikator", "penanggungJawab", "riwayatTindakans", "tindakLanjuts", "monitorings"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data laporan berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Laporan not found")
     *     )
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
        $query = Laporan::query();

        if ($user->role === 'Warga') {
            $query->where('id_pelapor', $user->id);
        }

        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            $validIncludes = ['pelapor', 'kategoriBencana', 'desa', 'verifikator', 'penanggungJawab', 'riwayatTindakans', 'tindakLanjuts', 'monitorings'];

            foreach ($includes as $include) {
                if (in_array($include, $validIncludes)) {
                    $query->with($include);
                }
            }
        }

        $laporan = $query->find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        $laporan->incrementViewCount();

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil diambil',
            'data' => $laporan
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/laporan/{id}",
     *     tags={"Laporan"},
     *     summary="Update laporan",
     *     description="Mengupdate data laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="judul_laporan", type="string", maxLength=200),
     *             @OA\Property(property="deskripsi", type="string", maxLength=2000),
     *             @OA\Property(property="id_kategori_bencana", type="integer"),
     *             @OA\Property(property="id_desa", type="integer"),
     *             @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah", "Sedang", "Tinggi", "Kritis"}),
     *             @OA\Property(property="latitude", type="number", format="float"),
     *             @OA\Property(property="longitude", type="number", format="float"),
     *             @OA\Property(property="alamat_lengkap", type="string", maxLength=500),
     *             @OA\Property(property="jumlah_korban", type="integer", minimum=0),
     *             @OA\Property(property="jumlah_rumah_rusak", type="integer", minimum=0),
     *             @OA\Property(property="data_tambahan", type="object"),
     *             @OA\Property(property="is_prioritas", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diupdate"),
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
            'judul_laporan' => 'sometimes|required|string|max:200',
            'deskripsi' => 'sometimes|required|string|max:2000',
            'id_kategori_bencana' => 'sometimes|nullable|exists:kategori_bencanas,id',
            'id_desa' => 'sometimes|nullable|exists:desas,id',
            'tingkat_keparahan' => 'sometimes|required|in:Rendah,Sedang,Tinggi,Kritis',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'alamat_lengkap' => 'sometimes|nullable|string|max:500',
            'jumlah_korban' => 'sometimes|nullable|integer|min:0',
            'jumlah_rumah_rusak' => 'sometimes|nullable|integer|min:0',
            'data_tambahan' => 'sometimes|nullable|array',
            'is_prioritas' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (!$laporan->canBeEditedBy($user) && !$laporan->canBeVerifiedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk mengedit laporan ini'
            ], 403);
        }

        try {
            $laporan->update($request->all());
            $laporan->load(['pelapor', 'kategoriBencana', 'desa']);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diupdate',
                'data' => $laporan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/laporan/{id}",
     *     tags={"Laporan"},
     *     summary="Delete laporan",
     *     description="Menghapus data laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil dihapus")
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

        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (!$laporan->canBeDeletedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk menghapus laporan ini'
            ], 403);
        }

        try {
            foreach (['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'] as $field) {
                if ($laporan->$field) {
                    Storage::disk('public')->delete($laporan->$field);
                }
            }

            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/laporan/{id}/verifikasi",
     *     tags={"Laporan"},
     *     summary="Verifikasi laporan",
     *     description="Verifikasi atau tolak laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"action"},
     *             @OA\Property(property="action", type="string", enum={"verifikasi", "tolak"}),
     *             @OA\Property(property="catatan", type="string", example="Laporan telah diverifikasi dan valid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diverifikasi")
     *         )
     *     )
     * )
     */
    public function verifikasi(Request $request, $id): JsonResponse
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
            'action' => 'required|in:verifikasi,tolak',
            'catatan' => 'required_if:action,tolak|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (!$laporan->canBeVerifiedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk memverifikasi laporan ini'
            ], 403);
        }

        try {
            if ($request->action === 'verifikasi') {
                $laporan->markAsVerified($user->id, $request->catatan);
                $message = 'Laporan berhasil diverifikasi';
            } else {
                $laporan->markAsRejected($user->id, $request->catatan);
                $message = 'Laporan berhasil ditolak';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $laporan->fresh(['verifikator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/laporan/{id}/proses",
     *     tags={"Laporan"},
     *     summary="Proses laporan",
     *     description="Memproses atau menyelesaikan laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"action"},
     *             @OA\Property(property="action", type="string", enum={"proses", "selesai"}),
     *             @OA\Property(property="catatan", type="string", example="Penanganan sedang dilakukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diproses")
     *         )
     *     )
     * )
     */
    public function proses(Request $request, $id): JsonResponse
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
            'action' => 'required|in:proses,selesai',
            'catatan' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (!$laporan->canBeProcessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk memproses laporan ini'
            ], 403);
        }

        try {
            if ($request->action === 'proses') {
                $laporan->markAsProcessed($user->id, $request->catatan);
                $message = 'Laporan sedang diproses';
            } else {
                $laporan->markAsCompleted($user->id, $request->catatan);
                $message = 'Laporan telah selesai diproses';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $laporan->fresh(['penanggungJawab'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/laporan/statistics",
     *     tags={"Laporan"},
     *     summary="Get laporan statistics",
     *     description="Mengambil statistik data laporan",
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_laporan", type="integer", example=100),
     *                 @OA\Property(property="by_status", type="object"),
     *                 @OA\Property(property="by_keparahan", type="object"),
     *                 @OA\Property(property="by_kategori", type="object"),
     *                 @OA\Property(property="by_bulan", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
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

        $query = Laporan::query();

        if ($user->role === 'Warga') {
            $query->where('id_pelapor', $user->id);
        }

        $total = $query->count();

        $byStatus = Laporan::select('status', DB::raw('count(*) as count'))
            ->when($user->role === 'Warga', fn ($q) => $q->where('id_pelapor', $user->id))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byKeparahan = Laporan::select('tingkat_keparahan', DB::raw('count(*) as count'))
            ->when($user->role === 'Warga', fn ($q) => $q->where('id_pelapor', $user->id))
            ->groupBy('tingkat_keparahan')
            ->pluck('count', 'tingkat_keparahan')
            ->toArray();

        $prioritas = Laporan::where('is_prioritas', true)
            ->when($user->role === 'Warga', fn ($q) => $q->where('id_pelapor', $user->id))
            ->count();

        $bulanIni = Laporan::whereMonth('waktu_laporan', now()->month)
            ->whereYear('waktu_laporan', now()->year)
            ->when($user->role === 'Warga', fn ($q) => $q->where('id_pelapor', $user->id))
            ->count();

        $overdue = Laporan::where('status', 'Diproses')
            ->where('waktu_verifikasi', '<', now()->subHours(48))
            ->when($user->role === 'Warga', fn ($q) => $q->where('id_pelapor', $user->id))
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik laporan berhasil diambil',
            'data' => [
                'total_laporan' => $total,
                'by_status' => $byStatus,
                'by_keparahan' => $byKeparahan,
                'laporan_prioritas' => $prioritas,
                'laporan_bulan_ini' => $bulanIni,
                'laporan_overdue' => $overdue
            ]
        ]);
    }
}