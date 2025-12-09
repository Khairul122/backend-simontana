<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\RiwayatTindakan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    /**
     * Display a listing of laporan.
     */
    public function index(Request $request)
    {
        try {
            $query = Laporan::with(['kategori', 'pengguna', 'tindaklanjut.petugas']);

            // Filter by status
            if ($request->has('status')) {
                $query->withStatus($request->input('status'));
            }

            // Filter by category
            if ($request->has('kategori')) {
                $query->withCategory($request->input('kategori'));
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->betweenDates($request->input('start_date'), $request->input('end_date'));
            }

            // Search functionality
            if ($request->has('search')) {
                $query->search($request->input('search'));
            }

            // Filter by user (for citizens and operators)
            $user = $request->user();
            if ($user->isWarga() || $user->isOperatorDesa()) {
                $query->where('user_id', $user->id);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $laporan = $query->orderBy('tanggal_lapor', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar laporan berhasil diambil',
                'data' => $laporan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created laporan in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'jenis_bencana' => 'required|string|max:255',
                'lokasi' => 'required|string|max:500',
                'deskripsi' => 'required|string',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'kontak_pelapor' => 'nullable|string|max:255'
            ], [
                'jenis_bencana.required' => 'Jenis bencana wajib diisi',
                'lokasi.required' => 'Lokasi kejadian wajib diisi',
                'deskripsi.required' => 'Deskripsi kejadian wajib diisi',
                'foto.image' => 'File harus berupa gambar',
                'foto.mimes' => 'Format foto harus jpeg, png, atau jpg',
                'foto.max' => 'Ukuran foto maksimal 2MB'
            ]);

            DB::beginTransaction();

            // Handle photo upload
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('laporan/foto', 'public');
            }

            $laporan = Laporan::create([
                'user_id' => $request->user()->id,
                'jenis_bencana' => $validated['jenis_bencana'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'],
                'foto' => $fotoPath,
                'kontak_pelapor' => $validated['kontak_pelapor'] ?? $request->user()->no_telepon,
                'status' => 'pending'
            ]);

            // Create riwayat tindakan for new report
            RiwayatTindakan::create([
                'laporan_id' => $laporan->id,
                'user_id' => $request->user()->id,
                'jenis_tindakan' => 'pembuatan_laporan',
                'deskripsi_tindakan' => 'Laporan baru dibuat oleh ' . $request->user()->nama,
                'status_sebelum' => null,
                'status_sesudah' => 'pending'
            ]);

            DB::commit();

            // Load relationships for response
            $laporan->load(['kategori', 'pengguna']);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $laporan
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
                'message' => 'Gagal membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified laporan.
     */
    public function show(string $id)
    {
        try {
            $laporan = Laporan::with([
                'kategori',
                'pengguna',
                'tindaklanjut.petugas',
                'riwayatTindakan.petugas'
            ])->findOrFail($id);

            // Check authorization
            $user = request()->user();
            if ($user->isWarga() && $laporan->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak dapat melihat laporan ini.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil diambil',
                'data' => $laporan
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified laporan in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $laporan = Laporan::findOrFail($id);

            // Check authorization - only admins and operators can update
            $user = $request->user();
            if (!$user->isAdmin() && !$user->isOperatorDesa()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin dan operator yang dapat memperbarui laporan.'
                ], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,diverifikasi,ditolak,dalam_penanganan,selesai',
                'catatan_verifikasi' => 'nullable|string|max:1000'
            ], [
                'status.required' => 'Status laporan wajib dipilih',
                'status.in' => 'Status laporan tidak valid'
            ]);

            $oldStatus = $laporan->status;

            DB::beginTransaction();

            $laporan->update([
                'status' => $validated['status']
            ]);

            // Create riwayat tindakan for status change
            RiwayatTindakan::create([
                'laporan_id' => $laporan->id,
                'user_id' => $user->id,
                'jenis_tindakan' => 'perubahan_status',
                'deskripsi_tindakan' => $validated['catatan_verifikasi'] ?? 'Status diperbarui',
                'status_sebelum' => $oldStatus,
                'status_sesudah' => $validated['status']
            ]);

            DB::commit();

            // Load relationships for response
            $laporan->load(['kategori', 'pengguna']);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diperbarui',
                'data' => $laporan
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);

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
                'message' => 'Gagal memperbarui laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified laporan from storage.
     */
    public function destroy(string $id)
    {
        try {
            $laporan = Laporan::findOrFail($id);

            // Check authorization - only admins can delete
            $user = request()->user();
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat menghapus laporan.'
                ], 403);
            }

            // Check if there are related tindaklanjut
            if ($laporan->tindaklanjut()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat dihapus karena sudah ada tindaklanjut terkait'
                ], 422);
            }

            DB::beginTransaction();

            // Delete associated photo if exists
            if ($laporan->foto) {
                Storage::disk('public')->delete($laporan->foto);
            }

            // Delete riwayat tindakan
            $laporan->riwayatTindakan()->delete();

            // Delete laporan
            $laporan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laporan statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();

            $baseQuery = Laporan::query();

            // Filter by user if not admin
            if ($user->isWarga() || $user->isOperatorDesa()) {
                $baseQuery->where('user_id', $user->id);
            }

            $stats = [
                'total_laporan' => $baseQuery->count(),
                'pending' => $baseQuery->clone()->where('status', 'pending')->count(),
                'diverifikasi' => $baseQuery->clone()->where('status', 'diverifikasi')->count(),
                'dalam_penanganan' => $baseQuery->clone()->where('status', 'dalam_penanganan')->count(),
                'selesai' => $baseQuery->clone()->where('status', 'selesai')->count(),
                'ditolak' => $baseQuery->clone()->where('status', 'ditolak')->count(),
                'laporan_bulan_ini' => $baseQuery->clone()
                    ->whereMonth('tanggal_laporan', now()->month)
                    ->whereYear('tanggal_laporan', now()->year)
                    ->count(),
                'jenis_bencana_terbanyak' => Laporan::select('jenis_bencana')
                    ->selectRaw('count(*) as total')
                    ->groupBy('jenis_bencana')
                    ->orderBy('total', 'desc')
                    ->first()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik laporan berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
