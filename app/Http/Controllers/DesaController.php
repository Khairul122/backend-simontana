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
