<?php

namespace App\Http\Controllers;

use App\Models\KategoriBencana;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class KategoriBencanaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:Admin')->except(['index', 'show']); // Hanya Admin yang bisa create, update, delete
    }
    /**
     * @OA\Get(
     *     path="/kategori-bencana",
     *     summary="Get all kategori bencana",
     *     description="Mengambil semua data kategori bencana dengan filter dan pagination",
     *     tags={"Kategori Bencana"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan nama atau deskripsi kategori",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         description="Bidang untuk sorting (id, nama_kategori, created_at)",
     *         required=false,
     *         @OA\Schema(type="string", default="id")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Arah sorting (asc atau desc)",
     *         required=false,
     *         @OA\Schema(type="string", default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Jumlah item per halaman",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kategori bencana berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Daftar kategori bencana berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                         @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah"),
     *                         @OA\Property(property="icon", type="string", example="water"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01 10:00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01 10:00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = KategoriBencana::query();

        // Handle search parameter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('nama_kategori', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%");
        }

        // Handle sorting
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Handle pagination
        $perPage = $request->input('per_page', 15);
        $kategoriBencana = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori bencana berhasil diambil',
            'data' => $kategoriBencana
        ]);
    }

    /**
     * @OA\Post(
     *     path="/kategori-bencana",
     *     summary="Create new kategori bencana",
     *     description="Membuat kategori bencana baru - Hanya Admin yang dapat mengakses endpoint ini",
     *     tags={"Kategori Bencana"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_kategori"},
     *             @OA\Property(property="nama_kategori", type="string", example="Banjir", description="Nama dari kategori bencana"),
     *             @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah", description="Deskripsi dari kategori bencana"),
     *             @OA\Property(property="icon", type="string", example="water", description="Icon untuk kategori bencana")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori bencana berhasil dibuat",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori bencana berhasil ditambahkan"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah"),
     *                 @OA\Property(property="icon", type="string", example="water"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Data tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Anda tidak memiliki izin untuk mengakses resource ini"),
     *             @OA\Property(property="required_roles", type="array", @OA\Items(type="string", example="Admin")),
     *             @OA\Property(property="user_role", type="string", example="Warga")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_bencana,nama_kategori',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255'
        ]);

        $kategoriBencana = KategoriBencana::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Kategori bencana berhasil ditambahkan',
            'data' => $kategoriBencana
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/kategori-bencana/{id}",
     *     summary="Get kategori bencana by ID",
     *     description="Mengambil detail kategori bencana berdasarkan ID",
     *     tags={"Kategori Bencana"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari kategori bencana",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail kategori bencana berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Detail kategori bencana berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah"),
     *                 @OA\Property(property="icon", type="string", example="water"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori bencana tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Kategori bencana tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori bencana berhasil diambil',
            'data' => $kategoriBencana
        ]);
    }

    /**
     * @OA\Put(
     *     path="/kategori-bencana/{id}",
     *     summary="Update kategori bencana",
     *     description="Memperbarui data kategori bencana berdasarkan ID - Hanya Admin yang dapat mengakses endpoint ini",
     *     tags={"Kategori Bencana"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari kategori bencana",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama_kategori", type="string", example="Banjir Update", description="Nama dari kategori bencana"),
     *             @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah (update)", description="Deskripsi dari kategori bencana"),
     *             @OA\Property(property="icon", type="string", example="water-update", description="Icon untuk kategori bencana")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori bencana berhasil diperbarui",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori bencana berhasil diperbarui"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Banjir Update"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah (update)"),
     *                 @OA\Property(property="icon", type="string", example="water-update"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-02 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Data tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Anda tidak memiliki izin untuk mengakses resource ini"),
     *             @OA\Property(property="required_roles", type="array", @OA\Items(type="string", example="Admin")),
     *             @OA\Property(property="user_role", type="string", example="Warga")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori bencana tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Kategori bencana tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'nama_kategori' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_bencana', 'nama_kategori')->ignore($kategoriBencana->id)
            ],
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255'
        ]);

        $kategoriBencana->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Kategori bencana berhasil diperbarui',
            'data' => $kategoriBencana
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/kategori-bencana/{id}",
     *     summary="Delete kategori bencana",
     *     description="Menghapus data kategori bencana berdasarkan ID - Hanya Admin yang dapat mengakses endpoint ini",
     *     tags={"Kategori Bencana"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari kategori bencana",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori bencana berhasil dihapus",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori bencana berhasil dihapus"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kondisi banjir yang terjadi di suatu wilayah"),
     *                 @OA\Property(property="icon", type="string", example="water"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Anda tidak memiliki izin untuk mengakses resource ini"),
     *             @OA\Property(property="required_roles", type="array", @OA\Items(type="string", example="Admin")),
     *             @OA\Property(property="user_role", type="string", example="Warga")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori bencana tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Kategori bencana tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tidak dapat menghapus karena masih ada data terkait",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tidak dapat menghapus kategori bencana karena masih terdapat laporan yang terkait")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);
        }

        // Pastikan tidak ada laporan yang terkait dengan kategori ini
        if ($kategoriBencana->laporans()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kategori bencana karena masih terdapat laporan yang terkait'
            ], 400);
        }

        $kategoriBencana->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori bencana berhasil dihapus',
            'data' => $kategoriBencana
        ]);
    }
}