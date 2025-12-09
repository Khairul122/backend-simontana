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
     * @OA\Get(
     *      path="/api/laporan",
     *      tags={"Disaster Reports"},
     *      summary="Get List Laporan Bencana",
     *      description="Endpoint untuk mendapatkan daftar laporan bencana dengan berbagai filter.",
     *      operationId="laporanIndex",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter status laporan",
     *          required=false,
     *          @OA\Schema(type="string", enum={"pending","diverifikasi","dalam_penanganan","selesai","ditolak"})
     *      ),
     *      @OA\Parameter(
     *          name="kategori",
     *          in="query",
     *          description="Filter ID kategori bencana",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="Filter tanggal mulai (YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="Filter tanggal akhir (YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Pencarian berdasarkan lokasi atau deskripsi",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Jumlah data per halaman",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Daftar laporan berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar laporan berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id_laporan", type="integer", example=1),
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="lokasi", type="string", example="Jl. Contoh No. 123"),
     *                      @OA\Property(property="deskripsi", type="string", example="Terjadi banjir di kawasan tersebut"),
     *                      @OA\Property(property="status_laporan", type="string", example="pending"),
     *                      @OA\Property(property="tanggal_lapor", type="string", example="2023-12-10T01:23:45.000000Z"),
     *                      @OA\Property(property="kategori", type="object",
     *                          @OA\Property(property="id_kategori", type="integer", example=1),
     *                          @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                          @OA\Property(property="icon", type="string", example="🌊")
     *                      ),
     *                      @OA\Property(property="pengguna", type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="nama", type="string", example="John Doe"),
     *                          @OA\Property(property="username", type="string", example="johndoe"),
     *                          @OA\Property(property="role", type="string", example="Warga")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Post(
     *      path="/api/laporan",
     *      tags={"Disaster Reports"},
     *      summary="Create Laporan Bencana Baru",
     *      description="Endpoint untuk membuat laporan bencana baru. Akses: Warga, Operator Desa, Admin, Petugas BPBD",
     *      operationId="laporanStore",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"jenis_bencana","lokasi","deskripsi"},
     *              @OA\Property(property="jenis_bencana", type="string", example="Banjir", description="Jenis bencana yang terjadi"),
     *              @OA\Property(property="lokasi", type="string", example="Jl. Merdeka No. 123, Kelurahan Contoh", description="Lokasi detail kejadian bencana"),
     *              @OA\Property(property="deskripsi", type="string", example="Banjir setinggi 50 cm menggenangi rumah warga", description="Deskripsi detail kondisi bencana"),
     *              @OA\Property(property="foto", type="string", format="binary", description="Foto bukti kejadian (max 2MB, format: jpeg,png,jpg)"),
     *              @OA\Property(property="kontak_pelapor", type="string", example="08123456789", description="Nomor kontak pelapor")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Laporan berhasil dibuat",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Laporan berhasil dibuat"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="lokasi", type="string", example="Jl. Merdeka No. 123, Kelurahan Contoh"),
     *                  @OA\Property(property="deskripsi", type="string", example="Banjir setinggi 50 cm menggenangi rumah warga"),
     *                  @OA\Property(property="status_laporan", type="string", example="pending"),
     *                  @OA\Property(property="tanggal_lapor", type="string", example="2023-12-10T01:23:45.000000Z"),
     *                  @OA\Property(property="foto", type="string", example="laporan/1234567890_banjir.jpg"),
     *                  @OA\Property(property="kontak_pelapor", type="string", example="08123456789"),
     *                  @OA\Property(property="id_warga", type="integer", example=1),
     *                  @OA\Property(property="kategori", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                      @OA\Property(property="icon", type="string", example="🌊")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="jenis_bencana", type="array", @OA\Items(type="string", example="Jenis bencana wajib diisi")),
     *                  @OA\Property(property="lokasi", type="array", @OA\Items(type="string", example="Lokasi kejadian wajib diisi"))
     *              )
     *          )
     *      )
     * )
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
     * @OA\Get(
     *      path="/api/laporan/{id}",
     *      tags={"Disaster Reports"},
     *      summary="Get Detail Laporan Bencana",
     *      description="Endpoint untuk mendapatkan detail laporan bencana berdasarkan ID",
     *      operationId="laporanShow",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID laporan",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Detail laporan berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Detail laporan berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="lokasi", type="string", example="Jl. Contoh No. 123"),
     *                  @OA\Property(property="deskripsi", type="string", example="Terjadi banjir di kawasan tersebut"),
     *                  @OA\Property(property="status_laporan", type="string", example="pending"),
     *                  @OA\Property(property="tanggal_lapor", type="string", example="2023-12-10T01:23:45.000000Z"),
     *                  @OA\Property(property="foto", type="string", example="laporan/1234567890_banjir.jpg"),
     *                  @OA\Property(property="kontak_pelapor", type="string", example="08123456789"),
     *                  @OA\Property(property="kategori", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                      @OA\Property(property="icon", type="string", example="🌊")
     *                  ),
     *                  @OA\Property(property="pengguna", type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="nama", type="string", example="John Doe"),
     *                      @OA\Property(property="username", type="string", example="johndoe"),
     *                      @OA\Property(property="role", type="string", example="Warga")
     *                  ),
     *                  @OA\Property(property="tindaklanjut", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="riwayat_tindakan", type="array", @OA\Items(type="object"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Anda tidak dapat melihat laporan ini.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Put(
     *      path="/api/laporan/{id}",
     *      tags={"Disaster Reports"},
     *      summary="Update Laporan Bencana",
     *      description="Endpoint untuk memperbarui status laporan bencana. Akses: Admin, Operator Desa",
     *      operationId="laporanUpdate",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID laporan",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"status"},
     *              @OA\Property(property="status", type="string", example="diverifikasi", description="Status laporan", enum={"pending","diverifikasi","dalam_penanganan","selesai","ditolak"}),
     *              @OA\Property(property="catatan_verifikasi", type="string", example="Laporan telah diverifikasi dan akan ditindaklanjuti", description="Catatan verifikasi status")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Laporan berhasil diperbarui",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Laporan berhasil diperbarui"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="id_kategori", type="integer", example=1),
     *                  @OA\Property(property="lokasi", type="string", example="Jl. Merdeka No. 123, Kelurahan Contoh"),
     *                  @OA\Property(property="deskripsi", type="string", example="Banjir setinggi 50 cm menggenangi rumah warga"),
     *                  @OA\Property(property="status_laporan", type="string", example="diverifikasi"),
     *                  @OA\Property(property="tanggal_lapor", type="string", example="2023-12-10T01:23:45.000000Z"),
     *                  @OA\Property(property="kategori", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                      @OA\Property(property="icon", type="string", example="🌊")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Hanya admin dan operator yang dapat memperbarui laporan.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="status", type="array", @OA\Items(type="string", example="Status laporan wajib dipilih"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Delete(
     *      path="/api/laporan/{id}",
     *      tags={"Disaster Reports"},
     *      summary="Delete Laporan Bencana",
     *      description="Endpoint untuk menghapus laporan bencana. Akses: Admin saja",
     *      operationId="laporanDestroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID laporan",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Laporan berhasil dihapus",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Laporan berhasil dihapus")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Hanya admin yang dapat menghapus laporan.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Cannot Delete",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Laporan tidak dapat dihapus karena sudah ada tindaklanjut terkait")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Get(
     *      path="/api/laporan/statistics",
     *      tags={"Disaster Reports"},
     *      summary="Get Laporan Bencana Statistics",
     *      description="Endpoint untuk mendapatkan statistik laporan bencana",
     *      operationId="laporanStatistics",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Statistik laporan berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Statistik laporan berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total", type="integer", example=150),
     *                  @OA\Property(property="menunggu", type="integer", example=25),
     *                  @OA\Property(property="diverifikasi", type="integer", example=30),
     *                  @OA\Property(property="diproses", type="integer", example=45),
     *                  @OA\Property(property="selesai", type="integer", example=40),
     *                  @OA\Property(property="ditolak", type="integer", example=10),
     *                  @OA\Property(property="laporan_bulan_ini", type="integer", example=35),
     *                  @OA\Property(property="jenis_bencana_terbanyak", type="object",
     *                      @OA\Property(property="id_kategori", type="integer", example=1),
     *                      @OA\Property(property="total", type="integer", example=45)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();

            $baseQuery = Laporan::query();

            // Filter by user if not admin
            if ($user->isWarga() || $user->isOperatorDesa()) {
                $baseQuery->where('id_warga', $user->id);
            }

            $stats = [
                'total' => $baseQuery->count(),
                'menunggu' => $baseQuery->clone()->where('status_laporan', 'pending')->count(),
                'diverifikasi' => $baseQuery->clone()->where('status_laporan', 'diverifikasi')->count(),
                'diproses' => $baseQuery->clone()->where('status_laporan', 'dalam_penanganan')->count(),
                'selesai' => $baseQuery->clone()->where('status_laporan', 'selesai')->count(),
                'ditolak' => $baseQuery->clone()->where('status_laporan', 'ditolak')->count(),
                'laporan_bulan_ini' => $baseQuery->clone()
                    ->whereMonth('tanggal_lapor', now()->month)
                    ->whereYear('tanggal_lapor', now()->year)
                    ->count(),
                'jenis_bencana_terbanyak' => Laporan::select('id_kategori')
                    ->selectRaw('count(*) as total')
                    ->groupBy('id_kategori')
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
