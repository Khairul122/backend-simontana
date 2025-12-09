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
     * @OA\Get(
     *      path="/api/tindaklanjut",
     *      tags={"Disaster Response Actions"},
     *      summary="Get List Tindaklanjut Bencana",
     *      description="Endpoint untuk mendapatkan daftar tindaklanjut bencana dengan berbagai filter",
     *      operationId="tindaklanjutIndex",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter status tindaklanjut",
     *          required=false,
     *          @OA\Schema(type="string", enum={"menunggu_penugasan","sedang_diproses","menunggu_verifikasi","selesai","dibatalkan"})
     *      ),
     *      @OA\Parameter(
     *          name="prioritas",
     *          in="query",
     *          description="Filter prioritas tindaklanjut",
     *          required=false,
     *          @OA\Schema(type="string", enum={"rendah","sedang","tinggi","darurat"})
     *      ),
     *      @OA\Parameter(
     *          name="jenis_tindakan",
     *          in="query",
     *          description="Filter jenis tindakan",
     *          required=false,
     *          @OA\Schema(type="string", enum={"evakuasi","penanganan_medis","distribusi_bantuan","perbaikan_infrastruktur","pembersihan","lainnya"})
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
     *          description="Pencarian berdasarkan deskripsi tindakan",
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
     *          description="Daftar tindaklanjut berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Daftar tindaklanjut berhasil diambil"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id_tindaklanjut", type="integer", example=1),
     *                      @OA\Property(property="id_laporan", type="integer", example=1),
     *                      @OA\Property(property="id_petugas", type="integer", example=2),
     *                      @OA\Property(property="jenis_tindakan", type="string", example="evakuasi"),
     *                      @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi warga ke lokasi aman"),
     *                      @OA\Property(property="status_tindakan", type="string", example="sedang_diproses"),
     *                      @OA\Property(property="prioritas", type="string", example="tinggi"),
     *                      @OA\Property(property="tanggal_tanggapan", type="string", example="2023-12-10T02:30:00.000000Z"),
     *                      @OA\Property(property="estimasi_waktu", type="string", example="2023-12-10T18:00:00.000000Z"),
     *                      @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string")),
     *                      @OA\Property(property="laporan", type="object",
     *                          @OA\Property(property="id_laporan", type="integer", example=1),
     *                          @OA\Property(property="lokasi", type="string", example="Jl. Contoh No. 123"),
     *                          @OA\Property(property="kategori", type="object",
     *                              @OA\Property(property="id_kategori", type="integer", example=1),
     *                              @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                              @OA\Property(property="icon", type="string", example="🌊")
     *                          )
     *                      ),
     *                      @OA\Property(property="petugas", type="object",
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="nama", type="string", example="Petugas BPBD"),
     *                          @OA\Property(property="username", type="string", example="petugas1")
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
     * @OA\Post(
     *      path="/api/tindaklanjut",
     *      tags={"Disaster Response Actions"},
     *      summary="Create Tindaklanjut Bencana Baru",
     *      description="Endpoint untuk membuat tindaklanjut bencana baru. Akses: Petugas BPBD, Admin",
     *      operationId="tindaklanjutStore",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"id_laporan","jenis_tindakan","deskripsi_tindakan","prioritas"},
     *              @OA\Property(property="id_laporan", type="integer", example=1, description="ID laporan yang akan ditindaklanjuti"),
     *              @OA\Property(property="jenis_tindakan", type="string", example="evakuasi", description="Jenis tindakan yang akan dilakukan", enum={"evakuasi","penanganan_medis","distribusi_bantuan","perbaikan_infrastruktur","pembersihan","lainnya"}),
     *              @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi warga ke lokasi aman yang telah disiapkan", description="Deskripsi detail tindakan yang akan dilakukan"),
     *              @OA\Property(property="prioritas", type="string", example="tinggi", description="Prioritas tindakan", enum={"rendah","sedang","tinggi","darurat"}),
     *              @OA\Property(property="estimasi_waktu", type="string", format="date", example="2023-12-10T18:00:00.000000Z", description="Estimasi waktu penyelesaian"),
     *              @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string", format="binary"), description="Dokumentasi pendukung (max 2MB per file, format: jpeg,png,jpg)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Tindaklanjut berhasil dibuat",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut berhasil dibuat"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_tindaklanjut", type="integer", example=1),
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="id_petugas", type="integer", example=2),
     *                  @OA\Property(property="jenis_tindakan", type="string", example="evakuasi"),
     *                  @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi warga ke lokasi aman"),
     *                  @OA\Property(property="status_tindakan", type="string", example="menunggu_penugasan"),
     *                  @OA\Property(property="prioritas", type="string", example="tinggi"),
     *                  @OA\Property(property="tanggal_tanggapan", type="string", example="2023-12-10T02:30:00.000000Z"),
     *                  @OA\Property(property="estimasi_waktu", type="string", example="2023-12-10T18:00:00.000000Z"),
     *                  @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string")),
     *                  @OA\Property(property="laporan", type="object",
     *                      @OA\Property(property="id_laporan", type="integer", example=1),
     *                      @OA\Property(property="kategori", type="object",
     *                          @OA\Property(property="id_kategori", type="integer", example=1),
     *                          @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                          @OA\Property(property="icon", type="string", example="🌊")
     *                      )
     *                  ),
     *                  @OA\Property(property="petugas", type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="nama", type="string", example="Petugas BPBD"),
     *                      @OA\Property(property="username", type="string", example="petugas1")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Hanya Petugas BPBD dan Admin yang dapat membuat tindaklanjut.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="id_laporan", type="array", @OA\Items(type="string", example="ID laporan wajib diisi")),
     *                  @OA\Property(property="jenis_tindakan", type="array", @OA\Items(type="string", example="Jenis tindakan wajib dipilih"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
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
     * @OA\Get(
     *      path="/api/tindaklanjut/{id}",
     *      tags={"Disaster Response Actions"},
     *      summary="Get Detail Tindaklanjut Bencana",
     *      description="Endpoint untuk mendapatkan detail tindaklanjut bencana berdasarkan ID",
     *      operationId="tindaklanjutShow",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID tindaklanjut",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Detail tindaklanjut berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Detail tindaklanjut berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_tindaklanjut", type="integer", example=1),
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="id_petugas", type="integer", example=2),
     *                  @OA\Property(property="jenis_tindakan", type="string", example="evakuasi"),
     *                  @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi warga ke lokasi aman"),
     *                  @OA\Property(property="status_tindakan", type="string", example="sedang_diproses"),
     *                  @OA\Property(property="prioritas", type="string", example="tinggi"),
     *                  @OA\Property(property="tanggal_tanggapan", type="string", example="2023-12-10T02:30:00.000000Z"),
     *                  @OA\Property(property="estimasi_waktu", type="string", example="2023-12-10T18:00:00.000000Z"),
     *                  @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string")),
     *                  @OA\Property(property="laporan", type="object",
     *                      @OA\Property(property="id_laporan", type="integer", example=1),
     *                      @OA\Property(property="lokasi", type="string", example="Jl. Contoh No. 123"),
     *                      @OA\Property(property="deskripsi", type="string", example="Terjadi banjir di kawasan tersebut"),
     *                      @OA\Property(property="status_laporan", type="string", example="dalam_penanganan"),
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
     *                  ),
     *                  @OA\Property(property="petugas", type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="nama", type="string", example="Petugas BPBD"),
     *                      @OA\Property(property="username", type="string", example="petugas1")
     *                  ),
     *                  @OA\Property(property="riwayat_tindakan", type="array", @OA\Items(type="object"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Anda tidak dapat melihat tindaklanjut ini.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut tidak ditemukan")
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
     * @OA\Put(
     *      path="/api/tindaklanjut/{id}",
     *      tags={"Disaster Response Actions"},
     *      summary="Update Tindaklanjut Bencana",
     *      description="Endpoint untuk memperbarui tindaklanjut bencana. Akses: Admin, Petugas BPBD (hanya yang ditugaskan)",
     *      operationId="tindaklanjutUpdate",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID tindaklanjut",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"status_tindakan"},
     *              @OA\Property(property="status_tindakan", type="string", example="sedang_diproses", description="Status tindakan", enum={"menunggu_penugasan","sedang_diproses","menunggu_verifikasi","selesai","dibatalkan"}),
     *              @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi sedang berlangsung dengan 3 tim", description="Deskripsi update tindakan"),
     *              @OA\Property(property="prioritas", type="string", example="tinggi", description="Prioritas tindakan", enum={"rendah","sedang","tinggi","darurat"}),
     *              @OA\Property(property="estimasi_waktu", type="string", format="date", example="2023-12-10T20:00:00.000000Z", description="Estimasi waktu penyelesaian"),
     *              @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string", format="binary"), description="Dokumentasi tambahan (max 2MB per file, format: jpeg,png,jpg)"),
     *              @OA\Property(property="catatan_update", type="string", example="Update progress evakuasi", description="Catatan update progress")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Tindaklanjut berhasil diperbarui",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut berhasil diperbarui"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id_tindaklanjut", type="integer", example=1),
     *                  @OA\Property(property="id_laporan", type="integer", example=1),
     *                  @OA\Property(property="jenis_tindakan", type="string", example="evakuasi"),
     *                  @OA\Property(property="deskripsi_tindakan", type="string", example="Evakuasi sedang berlangsung"),
     *                  @OA\Property(property="status_tindakan", type="string", example="sedang_diproses"),
     *                  @OA\Property(property="prioritas", type="string", example="tinggi"),
     *                  @OA\Property(property="tanggal_tanggapan", type="string", example="2023-12-10T02:30:00.000000Z"),
     *                  @OA\Property(property="estimasi_waktu", type="string", example="2023-12-10T20:00:00.000000Z"),
     *                  @OA\Property(property="dokumentasi", type="array", @OA\Items(type="string")),
     *                  @OA\Property(property="laporan", type="object",
     *                      @OA\Property(property="id_laporan", type="integer", example=1),
     *                      @OA\Property(property="kategori", type="object",
     *                          @OA\Property(property="id_kategori", type="integer", example=1),
     *                          @OA\Property(property="nama_kategori", type="string", example="Banjir"),
     *                          @OA\Property(property="icon", type="string", example="🌊")
     *                      )
     *                  ),
     *                  @OA\Property(property="petugas", type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="nama", type="string", example="Petugas BPBD"),
     *                      @OA\Property(property="username", type="string", example="petugas1")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="status_tindakan", type="array", @OA\Items(type="string", example="Status tindakan wajib dipilih"))
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
     * @OA\Delete(
     *      path="/api/tindaklanjut/{id}",
     *      tags={"Disaster Response Actions"},
     *      summary="Delete Tindaklanjut Bencana",
     *      description="Endpoint untuk menghapus tindaklanjut bencana. Akses: Admin saja",
     *      operationId="tindaklanjutDestroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID tindaklanjut",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Tindaklanjut berhasil dihapus",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut berhasil dihapus")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Access Denied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Akses ditolak. Hanya admin yang dapat menghapus tindaklanjut.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut tidak ditemukan")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Cannot Delete",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Tindaklanjut tidak dapat dihapus karena sedang diproses")
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
     * @OA\Get(
     *      path="/api/tindaklanjut/statistics",
     *      tags={"Disaster Response Actions"},
     *      summary="Get Tindaklanjut Bencana Statistics",
     *      description="Endpoint untuk mendapatkan statistik tindaklanjut bencana",
     *      operationId="tindaklanjutStatistics",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Statistik tindaklanjut berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Statistik tindaklanjut berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total_tindaklanjut", type="integer", example=85),
     *                  @OA\Property(property="menunggu_penugasan", type="integer", example=15),
     *                  @OA\Property(property="sedang_diproses", type="integer", example=35),
     *                  @OA\Property(property="menunggu_verifikasi", type="integer", example=20),
     *                  @OA\Property(property="selesai", type="integer", example=30),
     *                  @OA\Property(property="dibatalkan", type="integer", example=5),
     *                  @OA\Property(property="prioritas_darurat", type="integer", example=8),
     *                  @OA\Property(property="tindaklanjut_terlambat", type="integer", example=12),
     *                  @OA\Property(property="jenis_tindakan_terbanyak", type="object",
     *                      @OA\Property(property="jenis_tindakan", type="string", example="evakuasi"),
     *                      @OA\Property(property="total", type="integer", example=40)
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
