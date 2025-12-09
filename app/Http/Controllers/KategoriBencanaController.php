<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBencana;
use App\Models\Laporan;
use Illuminate\Support\Facades\DB;

class KategoriBencanaController extends Controller
{
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
