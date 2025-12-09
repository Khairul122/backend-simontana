<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDesaRequest;
use App\Http\Requests\UpdateDesaRequest;
use App\Models\Desa;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;

class DesaController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/desa",
     *      tags={"Village Management"},
     *      summary="Get All Desa (Admin)",
     *      description="Endpoint untuk mendapatkan daftar semua desa yang ada dalam sistem (Admin).",
     *      operationId="getAllDesaAdmin",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search desa by name, kecamatan, or kabupaten",
     *          required=false,
     *          @OA\Schema(type="string", example="Jakarta")
     *      ),
     *      @OA\Parameter(
     *          name="kecamatan",
     *          in="query",
     *          description="Filter by kecamatan",
     *          required=false,
     *          @OA\Schema(type="string", example="Menteng")
     *      ),
     *      @OA\Parameter(
     *          name="kabupaten",
     *          in="query",
     *          description="Filter by kabupaten",
     *          required=false,
     *          @OA\Schema(type="string", example="Jakarta Pusat")
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
     *          description="Daftar desa berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar desa berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id_desa", type="integer", example=1),
     *                          @OA\Property(property="nama_desa", type="string", example="Desa Example"),
     *                          @OA\Property(property="kecamatan", type="string", example="Menteng"),
     *                          @OA\Property(property="kabupaten", type="string", example="Jakarta Pusat"),
     *                          @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                          @OA\Property(property="jumlah_pengguna", type="integer", example=25)
     *                      )
     *                  ),
     *                  @OA\Property(property="total", type="integer", example=50)
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
     * Display a listing of desa.
     */
    public function index(Request $request)
    {
        try {
            $query = Desa::query();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('nama_desa', 'LIKE', "%{$search}%")
                      ->orWhere('kecamatan', 'LIKE', "%{$search}%")
                      ->orWhere('kabupaten', 'LIKE', "%{$search}%");
                });
            }

            // Filter by kecamatan
            if ($request->has('kecamatan')) {
                $query->where('kecamatan', $request->input('kecamatan'));
            }

            // Filter by kabupaten
            if ($request->has('kabupaten')) {
                $query->where('kabupaten', $request->input('kabupaten'));
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $desa = $query->orderBy('nama_desa')->paginate($perPage);

            // Load pengguna count for each desa
            $desa->getCollection()->transform(function ($item) {
                $item->jumlah_pengguna = Pengguna::where('id_desa', $item->id_desa)->count();
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar desa berhasil diambil',
                'data' => $desa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/desa",
     *      tags={"Village Management"},
     *      summary="Create Desa",
     *      description="Endpoint untuk menambahkan desa baru ke dalam sistem (Admin).",
     *      operationId="createDesa",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nama_desa","kecamatan","kabupaten"},
     *              @OA\Property(property="nama_desa", type="string", maxLength=255, example="Desa Baru", description="Nama desa"),
     *              @OA\Property(property="kecamatan", type="string", maxLength=255, example="Kecamatan Baru", description="Nama kecamatan"),
     *              @OA\Property(property="kabupaten", type="string", maxLength=255, example="Kabupaten Baru", description="Nama kabupaten")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Desa berhasil ditambahkan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Desa berhasil ditambahkan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_desa", type="integer", example=1),
     *                  @OA\Property(property="nama_desa", type="string", example="Desa Baru"),
     *                  @OA\Property(property="kecamatan", type="string", example="Kecamatan Baru"),
     *                  @OA\Property(property="kabupaten", type="string", example="Kabupaten Baru"),
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
     *                  @OA\Property(property="nama_desa", type="array", @OA\Items(type="string", example="Nama desa wajib diisi"))
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
     * Store a newly created desa in storage.
     */
    public function store(StoreDesaRequest $request)
    {
        try {
            DB::beginTransaction();

            $desa = Desa::create([
                'nama_desa' => $request->nama_desa,
                'kecamatan' => $request->kecamatan,
                'kabupaten' => $request->kabupaten
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Desa berhasil ditambahkan',
                'data' => $desa
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/desa/{id}",
     *      tags={"Village Management"},
     *      summary="Get Desa Details",
     *      description="Endpoint untuk mendapatkan detail desa berdasarkan ID.",
     *      operationId="getDesaById",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID desa",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Detail desa berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Detail desa berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_desa", type="integer", example=1),
     *                  @OA\Property(property="nama_desa", type="string", example="Desa Example"),
     *                  @OA\Property(property="kecamatan", type="string", example="Menteng"),
     *                  @OA\Property(property="kabupaten", type="string", example="Jakarta Pusat"),
     *                  @OA\Property(property="created_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-12-10T10:30:00.000000Z"),
     *                  @OA\Property(property="jumlah_pengguna", type="integer", example=25)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Desa tidak ditemukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Desa tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    /**
     * Display the specified desa.
     */
    public function show(string $id)
    {
        try {
            $desa = Desa::with(['pengguna' => function($query) {
                $query->select('id', 'nama', 'username', 'role', 'email', 'no_telepon', 'id_desa');
            }])->findOrFail($id);

            // Add statistics
            $desa->jumlah_pengguna = $desa->pengguna->count();
            $desa->jumlah_warga = $desa->pengguna->where('role', 'Warga')->count();
            $desa->jumlah_operator = $desa->pengguna->where('role', 'OperatorDesa')->count();

            return response()->json([
                'success' => true,
                'message' => 'Detail desa berhasil diambil',
                'data' => $desa
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/desa/{id}",
     *      tags={"Village Management"},
     *      summary="Update Desa",
     *      description="Endpoint untuk memperbarui desa yang ada dalam sistem (Admin).",
     *      operationId="updateDesa",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID desa",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nama_desa","kecamatan","kabupaten"},
     *              @OA\Property(property="nama_desa", type="string", maxLength=255, example="Desa Updated", description="Nama desa yang diperbarui"),
     *              @OA\Property(property="kecamatan", type="string", maxLength=255, example="Kecamatan Updated", description="Nama kecamatan yang diperbarui"),
     *              @OA\Property(property="kabupaten", type="string", maxLength=255, example="Kabupaten Updated", description="Nama kabupaten yang diperbarui")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Desa berhasil diperbarui",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Desa berhasil diperbarui"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_desa", type="integer", example=1),
     *                  @OA\Property(property="nama_desa", type="string", example="Desa Updated"),
     *                  @OA\Property(property="kecamatan", type="string", example="Kecamatan Updated"),
     *                  @OA\Property(property="kabupaten", type="string", example="Kabupaten Updated"),
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
     *                  @OA\Property(property="nama_desa", type="array", @OA\Items(type="string", example="Nama desa wajib diisi"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Desa tidak ditemukan"
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
     * Update the specified desa in storage.
     */
    public function update(UpdateDesaRequest $request, string $id)
    {
        try {
            $desa = Desa::findOrFail($id);

            DB::beginTransaction();

            $updateData = [];
            if ($request->has('nama_desa')) {
                $updateData['nama_desa'] = $request->nama_desa;
            }
            if ($request->has('kecamatan')) {
                $updateData['kecamatan'] = $request->kecamatan;
            }
            if ($request->has('kabupaten')) {
                $updateData['kabupaten'] = $request->kabupaten;
            }

            $desa->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Desa berhasil diperbarui',
                'data' => $desa
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/desa/{id}",
     *      tags={"Village Management"},
     *      summary="Delete Desa",
     *      description="Endpoint untuk menghapus desa dari sistem (Admin).",
     *      operationId="deleteDesa",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID desa",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Desa berhasil dihapus",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Desa berhasil dihapus")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Desa tidak ditemukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Desa tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict - Desa tidak dapat dihapus karena masih digunakan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Desa tidak dapat dihapus karena masih memiliki pengguna terkait")
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
     * Remove the specified desa from storage.
     */
    public function destroy(string $id)
    {
        try {
            $desa = Desa::findOrFail($id);

            // Check if there are users associated with this desa
            $penggunaCount = Pengguna::where('id_desa', $id)->count();
            if ($penggunaCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Desa tidak dapat dihapus karena masih ada {$penggunaCount} pengguna terkait"
                ], 422);
            }

            DB::beginTransaction();
            $desa->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Desa berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/desa-list/kecamatan",
     *      tags={"Village Management"},
     *      summary="Get All Kecamatan",
     *      description="Endpoint untuk mendapatkan daftar semua kecamatan yang ada dalam sistem.",
     *      operationId="getAllKecamatan",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Daftar kecamatan berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar kecamatan berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="string", example="Kecamatan Example")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    /**
     * Get kecamatan list for filter dropdown
     */
    public function getKecamatan()
    {
        try {
            $kecamatan = Desa::select('kecamatan')
                ->distinct()
                ->orderBy('kecamatan')
                ->pluck('kecamatan');

            return response()->json([
                'success' => true,
                'message' => 'Daftar kecamatan berhasil diambil',
                'data' => $kecamatan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kecamatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/desa-list/kabupaten",
     *      tags={"Village Management"},
     *      summary="Get All Kabupaten",
     *      description="Endpoint untuk mendapatkan daftar semua kabupaten yang ada dalam sistem.",
     *      operationId="getAllKabupaten",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Daftar kabupaten berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar kabupaten berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="string", example="Kabupaten Example")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    /**
     * Get kabupaten list for filter dropdown
     */
    public function getKabupaten()
    {
        try {
            $kabupaten = Desa::select('kabupaten')
                ->distinct()
                ->orderBy('kabupaten')
                ->pluck('kabupaten');

            return response()->json([
                'success' => true,
                'message' => 'Daftar kabupaten berhasil diambil',
                'data' => $kabupaten
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kabupaten: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/desa-statistics",
     *      tags={"Village Management"},
     *      summary="Get Desa Statistics",
     *      description="Endpoint untuk mendapatkan statistik desa.",
     *      operationId="getDesaStatistics",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Statistik desa berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Statistik desa berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total_desa", type="integer", example=150),
     *                  @OA\Property(property="total_kecamatan", type="integer", example=25),
     *                  @OA\Property(property="total_kabupaten", type="integer", example=8),
     *                  @OA\Property(property="desa_terbanyak_pengguna", type="object",
     *                      @OA\Property(property="id_desa", type="integer", example=1),
     *                      @OA\Property(property="nama_desa", type="string", example="Desa Example"),
     *                      @OA\Property(property="kecamatan", type="string", example="Menteng"),
     *                      @OA\Property(property="kabupaten", type="string", example="Jakarta Pusat"),
     *                      @OA\Property(property="jumlah_pengguna", type="integer", example=150)
     *                  ),
     *                  @OA\Property(property="distribusi_per_kabupaten", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="kabupaten", type="string", example="Jakarta Pusat"),
     *                          @OA\Property(property="jumlah_desa", type="integer", example=20),
     *                          @OA\Property(property="jumlah_pengguna", type="integer", example=500)
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
     * Get desa statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_desa' => Desa::count(),
                'total_kecamatan' => Desa::select('kecamatan')->distinct()->count(),
                'total_kabupaten' => Desa::select('kabupaten')->distinct()->count(),
                'desa_terbanyak_pengguna' => Desa::withCount('pengguna')
                    ->orderBy('pengguna_count', 'desc')
                    ->first(),
                'kecamatan_terbanyak_desa' => DB::table('desa')
                    ->select('kecamatan', DB::raw('count(*) as total'))
                    ->groupBy('kecamatan')
                    ->orderBy('total', 'desc')
                    ->first()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik desa berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik desa: ' . $e->getMessage()
            ], 500);
        }
    }
}
