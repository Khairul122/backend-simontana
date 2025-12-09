<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBencana;
use App\Models\Laporan;
use Illuminate\Support\Facades\DB;

class KategoriBencanaController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/kategori-bencana",
     *      tags={"Kategori Bencana Management"},
     *      summary="Get All Kategori Bencana",
     *      description="Endpoint untuk mendapatkan daftar semua kategori bencana yang ada dalam sistem.",
     *      operationId="getAllKategoriBencana",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search kategori bencana by name",
     *          required=false,
     *          @OA\Schema(type="string", example="Banjir")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Items per page for pagination",
     *          required=false,
     *          @OA\Schema(type="integer", example=15)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Daftar kategori bencana berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar kategori bencana berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id_kategori", type="integer", example=1),
     *                          @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                          @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="jumlah_laporan", type="integer", example=5)
     *                      )
     *                  ),
     *                  @OA\Property(property="total", type="integer", example=10)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */
    /**
     * Display a listing of kategori_bencana.
     */
    public function index(Request $request)
    {
        try {
            $query = KategoriBencana::query();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('nama_kategori', 'LIKE', "%{$search}%");
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $kategori = $query->orderBy('nama_kategori')->paginate($perPage);

            // Add count of laporan for each category
            $kategori->getCollection()->transform(function ($item) {
                $item->jumlah_laporan = Laporan::where('id_kategori', $item->id_kategori)->count();
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori bencana berhasil diambil',
                'data' => $kategori
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/kategori-bencana",
     *      tags={"Kategori Bencana Management"},
     *      summary="Create Kategori Bencana",
     *      description="Endpoint untuk menambahkan kategori bencana baru ke dalam sistem.",
     *      operationId="createKategoriBencana",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nama_kategori"},
     *              @OA\Property(property="nama_kategori", type="string", maxLength=255, example="Kebakaran Hutan", description="Nama kategori bencana")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Kategori bencana berhasil ditambahkan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Kategori bencana berhasil ditambahkan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="nama_kategori", type="string", example="Kebakaran Hutan"),
     *                  @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-12-10T10:30:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="nama_kategori", type="array", @OA\Items(type="string", example="Nama kategori bencana wajib diisi"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */
    /**
     * Store a newly created kategori_bencana in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_bencana,nama_kategori'
            ], [
                'nama_kategori.required' => 'Nama kategori bencana wajib diisi',
                'nama_kategori.max' => 'Nama kategori bencana maksimal 255 karakter',
                'nama_kategori.unique' => 'Nama kategori bencana sudah ada'
            ]);

            DB::beginTransaction();

            $kategori = KategoriBencana::create([
                'nama_kategori' => $validated['nama_kategori']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bencana berhasil ditambahkan',
                'data' => $kategori
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/kategori-bencana/{id}",
     *      tags={"Kategori Bencana Management"},
     *      summary="Get Kategori Bencana Details",
     *      description="Endpoint untuk mendapatkan detail kategori bencana berdasarkan ID.",
     *      operationId="getKategoriBencanaById",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID kategori bencana",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Detail kategori bencana berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Detail kategori bencana berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                  @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="laporan_count", type="integer", example=15),
     *                  @OA\Property(property="laporan_terbaru", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id_laporan", type="integer", example=1),
     *                          @OA\Property(property="pengirim", type="string", example="Ahmad"),
     *                          @OA\Property(property="tanggal_lapor", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="status_laporan", type="string", example="Baru")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Kategori bencana tidak ditemukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Kategori bencana tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    /**
     * Display the specified kategori_bencana.
     */
    public function show(string $id)
    {
        try {
            $kategori = KategoriBencana::withCount('laporan')
                ->findOrFail($id);

            // Get additional statistics
            $kategori->laporan_terbaru = Laporan::where('id_kategori', $id)
                ->orderBy('tanggal_lapor', 'desc')
                ->take(5)
                ->get(['id_laporan', 'pengirim', 'tanggal_lapor', 'status_laporan']);

            return response()->json([
                'success' => true,
                'message' => 'Detail kategori bencana berhasil diambil',
                'data' => $kategori
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/kategori-bencana/{id}",
     *      tags={"Kategori Bencana Management"},
     *      summary="Update Kategori Bencana",
     *      description="Endpoint untuk memperbarui kategori bencana yang ada dalam sistem.",
     *      operationId="updateKategoriBencana",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID kategori bencana",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nama_kategori"},
     *              @OA\Property(property="nama_kategori", type="string", maxLength=255, example="Banjir Bandang", description="Nama kategori bencana yang diperbarui")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Kategori bencana berhasil diperbarui",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Kategori bencana berhasil diperbarui"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="nama_kategori", type="string", example="Banjir Bandang"),
     *                  @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-12-10T11:00:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="nama_kategori", type="array", @OA\Items(type="string", example="Nama kategori bencana sudah ada"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Kategori bencana tidak ditemukan"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */
    /**
     * Update the specified kategori_bencana in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_bencana,nama_kategori,' . $id . ',id_kategori'
            ], [
                'nama_kategori.required' => 'Nama kategori bencana wajib diisi',
                'nama_kategori.max' => 'Nama kategori bencana maksimal 255 karakter',
                'nama_kategori.unique' => 'Nama kategori bencana sudah ada'
            ]);

            $kategori = KategoriBencana::findOrFail($id);

            DB::beginTransaction();

            $kategori->update([
                'nama_kategori' => $validated['nama_kategori']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bencana berhasil diperbarui',
                'data' => $kategori
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/kategori-bencana/{id}",
     *      tags={"Kategori Bencana Management"},
     *      summary="Delete Kategori Bencana",
     *      description="Endpoint untuk menghapus kategori bencana dari sistem.",
     *      operationId="deleteKategoriBencana",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID kategori bencana",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Kategori bencana berhasil dihapus",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Kategori bencana berhasil dihapus")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Kategori bencana tidak ditemukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Kategori bencana tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict - Kategori bencana tidak dapat dihapus karena masih digunakan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Kategori bencana tidak dapat dihapus karena masih digunakan dalam laporan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */
    /**
     * Remove the specified kategori_bencana from storage.
     */
    public function destroy(string $id)
    {
        try {
            $kategori = KategoriBencana::findOrFail($id);

            // Check if there are laporan associated with this kategori
            $laporanCount = Laporan::where('id_kategori', $id)->count();
            if ($laporanCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Kategori bencana tidak dapat dihapus karena masih ada {$laporanCount} laporan terkait"
                ], 422);
            }

            DB::beginTransaction();
            $kategori->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bencana berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Kategori bencana tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/kategori-bencana-statistics",
     *      tags={"Kategori Bencana Management"},
     *      summary="Get Kategori Bencana Statistics",
     *      description="Endpoint untuk mendapatkan statistik kategori bencana.",
     *      operationId="getKategoriBencanaStatistics",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Statistik kategori bencana berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Statistik kategori bencana berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total_kategori", type="integer", example=8),
     *                  @OA\Property(property="kategori_terbanyak_laporan", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                      @OA\Property(property="laporan_count", type="integer", example=25)
     *                  ),
     *                  @OA\Property(property="kategori_paling_sedikit", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=8),
     *                      @OA\Property(property="nama_kategori", type="string", example="Tornado"),
     *                      @OA\Property(property="laporan_count", type="integer", example=2)
     *                  ),
     *                  @OA\Property(property="distribusi_kategori", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                          @OA\Property(property="jumlah_laporan", type="integer", example=25),
     *                          @OA\Property(property="persentase", type="number", format="float", example=31.25)
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */
    /**
     * Get kategori statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_kategori' => KategoriBencana::count(),
                'kategori_terbanyak_laporan' => KategoriBencana::withCount('laporan')
                    ->orderBy('laporan_count', 'desc')
                    ->first(),
                'daftar_kategori_dengan_jumlah' => KategoriBencana::withCount('laporan')
                    ->orderBy('nama_kategori')
                    ->get(['id_kategori', 'nama_kategori', 'laporan_count']),
                'total_laporan' => Laporan::count(),
                'laporan_bulan_ini' => Laporan::whereMonth('tanggal_lapor', now()->month)
                    ->whereYear('tanggal_lapor', now()->year)
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik kategori bencana berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik kategori bencana: ' . $e->getMessage()
            ], 500);
        }
    }
}
