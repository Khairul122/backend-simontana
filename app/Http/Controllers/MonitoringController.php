<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Monitoring;
use App\Models\Laporan;
use App\Models\RiwayatTindakan;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Display a listing of monitoring.
     */
    public function index(Request $request)
    {
        try {
            $query = Monitoring::with(['laporan', 'operator']);

            // Filter by laporan
            if ($request->has('laporan_id')) {
                $query->forLaporan($request->input('laporan_id'));
            }

            // Filter by operator
            if ($request->has('operator_id')) {
                $query->forOperator($request->input('operator_id'));
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->betweenDates($request->input('start_date'), $request->input('end_date'));
            }

            // Search functionality
            if ($request->has('search')) {
                $query->search($request->input('search'));
            }

            // Filter by assigned operator (for operators and admins)
            $user = $request->user();
            if ($user->isOperatorDesa() && !$user->isAdmin()) {
                $query->where('id_operator', $user->id);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $monitoring = $query->orderBy('waktu_monitoring', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar monitoring berhasil diambil',
                'data' => $monitoring
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created monitoring in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_laporan' => 'required|exists:laporan,id_laporan',
                'hasil_monitoring' => 'required|string|max:1000',
                'koordinat_gps' => 'nullable|string|max:255',
                'waktu_monitoring' => 'nullable|date'
            ], [
                'id_laporan.required' => 'ID laporan wajib diisi',
                'id_laporan.exists' => 'Laporan tidak ditemukan',
                'hasil_monitoring.required' => 'Hasil monitoring wajib diisi',
                'hasil_monitoring.max' => 'Hasil monitoring maksimal 1000 karakter',
                'koordinat_gps.max' => 'Koordinat GPS maksimal 255 karakter',
                'waktu_monitoring.date' => 'Format waktu monitoring tidak valid'
            ]);

            // Check authorization - only operators and admins can create monitoring
            $user = $request->user();
            if (!$user->isOperatorDesa() && !$user->isAdmin() && !$user->isPetugasBPBD()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Operator Desa, Petugas BPBD, dan Admin yang dapat membuat monitoring.'
                ], 403);
            }

            // Check if laporan exists and can be monitored
            $laporan = Laporan::findOrFail($validated['id_laporan']);
            if (!in_array($laporan->status_laporan, ['Dilaporkan', 'Diverifikasi', 'Dalam Penanganan'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat dipantau. Status: ' . $laporan->status_laporan
                ], 422);
            }

            DB::beginTransaction();

            $monitoring = Monitoring::create([
                'id_laporan' => $validated['id_laporan'],
                'id_operator' => $user->id,
                'waktu_monitoring' => $validated['waktu_monitoring'] ?? now(),
                'hasil_monitoring' => $validated['hasil_monitoring'],
                'koordinat_gps' => $validated['koordinat_gps']
            ]);

            // Update laporan status if needed based on monitoring result
            $newStatus = $laporan->status_laporan;
            $hasil = strtolower($validated['hasil_monitoring']);

            if (strpos($hasil, 'aman') !== false || strpos($hasil, 'selesai') !== false) {
                $newStatus = 'Selesai';
            } elseif (strpos($hasil, 'pantau') !== false && $laporan->status_laporan === 'Dilaporkan') {
                $newStatus = 'Diverifikasi';
            }

            if ($newStatus !== $laporan->status_laporan) {
                $laporan->update(['status_laporan' => $newStatus]);
            }

            // Note: Riwayat tindakan untuk monitoring akan ditambahkan nanti
            // Create riwayat tindakan untuk monitoring
            // RiwayatTindakan::create([
            //     'tindaklanjut_id' => null,
            //     'petugas' => $user->nama,
            //     'keterangan' => 'Monitoring dilakukan: ' . $validated['hasil_monitoring'],
            //     'waktu_tindakan' => now()
            // ]);

            DB::commit();

            // Load relationships for response
            $monitoring->load(['laporan', 'operator']);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil dibuat',
                'data' => $monitoring
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
                'message' => 'Gagal membuat monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified monitoring.
     */
    public function show(string $id)
    {
        try {
            $monitoring = Monitoring::with([
                'laporan',
                'operator'
            ])->findOrFail($id);

            // Check authorization
            $user = request()->user();
            if ($user->isOperatorDesa() && $monitoring->id_operator !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak dapat melihat monitoring ini.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail monitoring berhasil diambil',
                'data' => $monitoring
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Monitoring tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified monitoring in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $monitoring = Monitoring::findOrFail($id);

            // Check authorization
            $user = $request->user();
            if (!$user->isAdmin() && ($user->isOperatorDesa() && $monitoring->id_operator !== $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            $validated = $request->validate([
                'hasil_monitoring' => 'required|string|max:1000',
                'koordinat_gps' => 'nullable|string|max:255',
                'waktu_monitoring' => 'nullable|date'
            ], [
                'hasil_monitoring.required' => 'Hasil monitoring wajib diisi',
                'hasil_monitoring.max' => 'Hasil monitoring maksimal 1000 karakter',
                'koordinat_gps.max' => 'Koordinat GPS maksimal 255 karakter',
                'waktu_monitoring.date' => 'Format waktu monitoring tidak valid'
            ]);

            DB::beginTransaction();

            $updateData = [
                'hasil_monitoring' => $validated['hasil_monitoring']
            ];

            if ($request->has('koordinat_gps')) {
                $updateData['koordinat_gps'] = $validated['koordinat_gps'];
            }

            if ($request->has('waktu_monitoring')) {
                $updateData['waktu_monitoring'] = $validated['waktu_monitoring'];
            }

            $monitoring->update($updateData);

            // Note: Riwayat tindakan untuk monitoring update akan ditambahkan nanti
            // Create riwayat tindakan untuk monitoring update
            // RiwayatTindakan::create([
            //     'tindaklanjut_id' => null,
            //     'petugas' => $user->nama,
            //     'keterangan' => 'Monitoring diperbarui: ' . $validated['hasil_monitoring'],
            //     'waktu_tindakan' => now()
            // ]);

            DB::commit();

            // Load relationships for response
            $monitoring->load(['laporan', 'operator']);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil diperbarui',
                'data' => $monitoring
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Monitoring tidak ditemukan'
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
                'message' => 'Gagal memperbarui monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified monitoring from storage.
     */
    public function destroy(string $id)
    {
        try {
            $monitoring = Monitoring::findOrFail($id);

            // Check authorization - only admins can delete
            $user = request()->user();
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat menghapus monitoring.'
                ], 403);
            }

            DB::beginTransaction();

            // Delete monitoring
            $monitoring->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Monitoring berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Monitoring tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monitoring statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();

            $baseQuery = Monitoring::query();

            // Filter by assigned operator if not admin
            if ($user->isOperatorDesa() && !$user->isAdmin()) {
                $baseQuery->where('id_operator', $user->id);
            }

            $stats = [
                'total_monitoring' => $baseQuery->count(),
                'monitoring_hari_ini' => $baseQuery->clone()
                    ->whereDate('waktu_monitoring', today())
                    ->count(),
                'monitoring_minggu_ini' => $baseQuery->clone()
                    ->whereBetween('waktu_monitoring', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'monitoring_bulan_ini' => $baseQuery->clone()
                    ->whereMonth('waktu_monitoring', now()->month)
                    ->whereYear('waktu_monitoring', now()->year)
                    ->count(),
                'monitoring_dengan_gps' => $baseQuery->clone()
                    ->whereNotNull('koordinat_gps')
                    ->count(),
                'laporan_terpantai' => $baseQuery->clone()
                    ->distinct('id_laporan')
                    ->count('id_laporan'),
                'monitoring_terbaru' => $baseQuery->clone()
                    ->orderBy('waktu_monitoring', 'desc')
                    ->with(['laporan', 'operator'])
                    ->limit(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik monitoring berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monitoring for specific laporan.
     */
    public function getByLaporan(string $laporanId)
    {
        try {
            $monitoring = Monitoring::with(['operator'])
                ->where('id_laporan', $laporanId)
                ->orderBy('waktu_monitoring', 'desc')
                ->get();

            // Check authorization
            $user = request()->user();
            if ($user->isOperatorDesa()) {
                // Operators can only see monitoring they created
                $monitoring = $monitoring->where('id_operator', $user->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Monitoring untuk laporan berhasil diambil',
                'data' => $monitoring
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil monitoring laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
