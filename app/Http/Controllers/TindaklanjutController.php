<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tindaklanjut;
use App\Models\Laporan;
use App\Models\RiwayatTindakan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TindaklanjutController extends Controller
{
    /**
     * Display a listing of tindaklanjut.
     */
    public function index(Request $request)
    {
        try {
            $query = Tindaklanjut::with(['laporan.kategori', 'petugas', 'riwayatTindakan.petugas']);

            // Filter by status
            if ($request->has('status')) {
                $query->withStatus($request->input('status'));
            }

            // Filter by priority
            if ($request->has('prioritas')) {
                $query->withPriority($request->input('prioritas'));
            }

            // Filter by action type
            if ($request->has('jenis_tindakan')) {
                $query->where('jenis_tindakan', $request->input('jenis_tindakan'));
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->betweenDates($request->input('start_date'), $request->input('end_date'));
            }

            // Search functionality
            if ($request->has('search')) {
                $query->search($request->input('search'));
            }

            // Filter by assigned petugas
            $user = $request->user();
            if ($user->isPetugasBPBD()) {
                $query->where('id_petugas', $user->id);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $tindaklanjut = $query->orderBy('tanggal_tanggapan', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar tindaklanjut berhasil diambil',
                'data' => $tindaklanjut
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tindaklanjut in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_laporan' => 'required|exists:laporan,id_laporan',
                'jenis_tindakan' => 'required|in:evakuasi,penanganan_medis,distribusi_bantuan,perbaikan_infrastruktur,pembersihan,lainnya',
                'deskripsi_tindakan' => 'required|string',
                'prioritas' => 'required|in:rendah,sedang,tinggi,darurat',
                'estimasi_waktu' => 'nullable|date',
                'dokumentasi' => 'nullable|array',
                'dokumentasi.*' => 'image|mimes:jpeg,png,jpg|max:2048'
            ], [
                'id_laporan.required' => 'ID laporan wajib diisi',
                'id_laporan.exists' => 'Laporan tidak ditemukan',
                'jenis_tindakan.required' => 'Jenis tindakan wajib dipilih',
                'jenis_tindakan.in' => 'Jenis tindakan tidak valid',
                'deskripsi_tindakan.required' => 'Deskripsi tindakan wajib diisi',
                'prioritas.required' => 'Prioritas wajib dipilih',
                'prioritas.in' => 'Prioritas tidak valid',
                'estimasi_waktu.date' => 'Format estimasi waktu tidak valid',
                'dokumentasi.array' => 'Dokumentasi harus berupa array',
                'dokumentasi.*.image' => 'File dokumentasi harus berupa gambar',
                'dokumentasi.*.mimes' => 'Format dokumentasi harus jpeg, png, atau jpg',
                'dokumentasi.*.max' => 'Ukuran dokumentasi maksimal 2MB'
            ]);

            // Check authorization - only BPBD and Admin can create tindaklanjut
            $user = $request->user();
            if (!$user->isPetugasBPBD() && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Petugas BPBD dan Admin yang dapat membuat tindaklanjut.'
                ], 403);
            }

            // Check if laporan exists and can be responded to
            $laporan = Laporan::findOrFail($validated['id_laporan']);
            if (!$laporan->canBeResponded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat ditindaklanjuti. Status: ' . $laporan->status_text
                ], 422);
            }

            // Check if tindaklanjut already exists for this laporan
            if ($laporan->tindaklanjut()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tindaklanjut untuk laporan ini sudah ada.'
                ], 422);
            }

            DB::beginTransaction();

            // Handle documentation upload
            $dokumentasiPaths = [];
            if ($request->hasFile('dokumentasi')) {
                foreach ($request->file('dokumentasi') as $file) {
                    $dokumentasiPaths[] = $file->store('tindaklanjut/dokumentasi', 'public');
                }
            }

            $tindaklanjut = Tindaklanjut::create([
                'id_laporan' => $validated['id_laporan'],
                'id_petugas' => $user->id,
                'tanggal_tanggapan' => now(),
                'jenis_tindakan' => $validated['jenis_tindakan'],
                'deskripsi_tindakan' => $validated['deskripsi_tindakan'],
                'status_tindakan' => 'menunggu_penugasan',
                'prioritas' => $validated['prioritas'],
                'estimasi_waktu' => $validated['estimasi_waktu'],
                'dokumentasi' => json_encode($dokumentasiPaths)
            ]);

            // Update laporan status
            $laporan->update(['status_laporan' => 'dalam_penanganan']);

            // Create riwayat tindakan
            RiwayatTindakan::create([
                'id_laporan' => $laporan->id_laporan,
                'id_tindaklanjut' => $tindaklanjut->id_tindaklanjut,
                'id_petugas' => $user->id,
                'tanggal_riwayat' => now(),
                'jenis_riwayat' => 'penugasan_petugas',
                'deskripsi_riwayat' => 'Tindaklanjut dibuat: ' . $validated['deskripsi_tindakan'],
                'status_sebelumnya' => 'sudah_diverifikasi',
                'status_sesudahnya' => 'dalam_penanganan'
            ]);

            DB::commit();

            // Load relationships for response
            $tindaklanjut->load(['laporan.kategori', 'petugas']);

            return response()->json([
                'success' => true,
                'message' => 'Tindaklanjut berhasil dibuat',
                'data' => $tindaklanjut
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
                'message' => 'Gagal membuat tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tindaklanjut.
     */
    public function show(string $id)
    {
        try {
            $tindaklanjut = Tindaklanjut::with([
                'laporan.kategori',
                'laporan.pengguna',
                'petugas',
                'riwayatTindakan.petugas'
            ])->findOrFail($id);

            // Check authorization
            $user = request()->user();
            if ($user->isPetugasBPBD() && $tindaklanjut->id_petugas !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak dapat melihat tindaklanjut ini.'
                ], 403);
            }

            // Parse documentation
            if ($tindaklanjut->dokumentasi) {
                $tindaklanjut->dokumentasi = json_decode($tindaklanjut->dokumentasi, true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail tindaklanjut berhasil diambil',
                'data' => $tindaklanjut
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tindaklanjut tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified tindaklanjut in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $tindaklanjut = Tindaklanjut::findOrFail($id);

            // Check authorization
            $user = $request->user();
            if (!$user->isAdmin() && ($user->isPetugasBPBD() && $tindaklanjut->id_petugas !== $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            $validated = $request->validate([
                'status_tindakan' => 'required|in:menunggu_penugasan,sedang_diproses,menunggu_verifikasi,selesai,dibatalkan',
                'deskripsi_tindakan' => 'nullable|string',
                'prioritas' => 'nullable|in:rendah,sedang,tinggi,darurat',
                'estimasi_waktu' => 'nullable|date',
                'dokumentasi' => 'nullable|array',
                'dokumentasi.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'catatan_update' => 'nullable|string|max:1000'
            ], [
                'status_tindakan.required' => 'Status tindakan wajib dipilih',
                'status_tindakan.in' => 'Status tindakan tidak valid'
            ]);

            $oldStatus = $tindaklanjut->status_tindakan;

            DB::beginTransaction();

            $updateData = [
                'status_tindakan' => $validated['status_tindakan']
            ];

            if ($request->has('deskripsi_tindakan')) {
                $updateData['deskripsi_tindakan'] = $validated['deskripsi_tindakan'];
            }

            if ($request->has('prioritas')) {
                $updateData['prioritas'] = $validated['prioritas'];
            }

            if ($request->has('estimasi_waktu')) {
                $updateData['estimasi_waktu'] = $validated['estimasi_waktu'];
            }

            // Handle new documentation uploads
            if ($request->hasFile('dokumentasi')) {
                $existingDocs = $tindaklanjut->dokumentasi ? json_decode($tindaklanjut->dokumentasi, true) : [];

                foreach ($request->file('dokumentasi') as $file) {
                    $existingDocs[] = $file->store('tindaklanjut/dokumentasi', 'public');
                }

                $updateData['dokumentasi'] = json_encode($existingDocs);
            }

            $tindaklanjut->update($updateData);

            // Update laporan status if tindaklanjut is completed
            if ($validated['status_tindakan'] === 'selesai') {
                $tindaklanjut->laporan->update(['status_laporan' => 'selesai']);
            }

            // Create riwayat tindakan
            RiwayatTindakan::create([
                'id_laporan' => $tindaklanjut->id_laporan,
                'id_tindaklanjut' => $tindaklanjut->id_tindaklanjut,
                'id_petugas' => $user->id,
                'tanggal_riwayat' => now(),
                'jenis_riwayat' => 'update_progress',
                'deskripsi_riwayat' => $validated['catatan_update'] ?? 'Status diperbarui dari ' . $oldStatus . ' menjadi ' . $validated['status_tindakan'],
                'status_sebelumnya' => $oldStatus,
                'status_sesudahnya' => $validated['status_tindakan']
            ]);

            DB::commit();

            // Load relationships for response
            $tindaklanjut->load(['laporan.kategori', 'petugas']);

            return response()->json([
                'success' => true,
                'message' => 'Tindaklanjut berhasil diperbarui',
                'data' => $tindaklanjut
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tindaklanjut tidak ditemukan'
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
                'message' => 'Gagal memperbarui tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tindaklanjut from storage.
     */
    public function destroy(string $id)
    {
        try {
            $tindaklanjut = Tindaklanjut::findOrFail($id);

            // Check authorization - only admins can delete
            $user = request()->user();
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat menghapus tindaklanjut.'
                ], 403);
            }

            // Check if tindaklanjut can be deleted
            if ($tindaklanjut->status_tindakan === 'sedang_diproses') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tindaklanjut tidak dapat dihapus karena sedang diproses'
                ], 422);
            }

            DB::beginTransaction();

            // Delete documentation files
            if ($tindaklanjut->dokumentasi) {
                $dokumentasi = json_decode($tindaklanjut->dokumentasi, true);
                foreach ($dokumentasi as $file) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Delete riwayat tindakan
            $tindaklanjut->riwayatTindakan()->delete();

            // Reset laporan status
            $tindaklanjut->laporan->update(['status_laporan' => 'sudah_diverifikasi']);

            // Delete tindaklanjut
            $tindaklanjut->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tindaklanjut berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tindaklanjut tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tindaklanjut statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();

            $baseQuery = Tindaklanjut::query();

            // Filter by assigned petugas if BPBD
            if ($user->isPetugasBPBD()) {
                $baseQuery->where('id_petugas', $user->id);
            }

            $stats = [
                'total_tindaklanjut' => $baseQuery->count(),
                'menunggu_penugasan' => $baseQuery->clone()->withStatus('menunggu_penugasan')->count(),
                'sedang_diproses' => $baseQuery->clone()->withStatus('sedang_diproses')->count(),
                'menunggu_verifikasi' => $baseQuery->clone()->withStatus('menunggu_verifikasi')->count(),
                'selesai' => $baseQuery->clone()->withStatus('selesai')->count(),
                'dibatalkan' => $baseQuery->clone()->withStatus('dibatalkan')->count(),
                'prioritas_darurat' => $baseQuery->clone()->withPriority('darurat')->count(),
                'tindaklanjut_terlambat' => $baseQuery->clone()
                    ->where('estimasi_waktu', '<', now())
                    ->whereNotIn('status_tindakan', ['selesai', 'dibatalkan'])
                    ->count(),
                'jenis_tindakan_terbanyak' => Tindaklanjut::select('jenis_tindakan')
                    ->selectRaw('count(*) as total')
                    ->groupBy('jenis_tindakan')
                    ->orderBy('total', 'desc')
                    ->first()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik tindaklanjut berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik tindaklanjut: ' . $e->getMessage()
            ], 500);
        }
    }
}
