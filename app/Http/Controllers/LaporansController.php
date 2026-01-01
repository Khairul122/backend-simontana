<?php

namespace App\Http\Controllers;

use App\Models\Laporans;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="Laporan",
 *     title="Laporan",
 *     description="Model laporan bencana",
 *     @OA\Property(property="id", type="integer", example=1, description="ID unique laporan"),
 *     @OA\Property(property="judul_laporan", type="string", example="Kebakaran Hutan di Desa Sukamaju", description="Judul laporan"),
 *     @OA\Property(property="deskripsi", type="string", example="Kebakaran terjadi di area perbukitan dengan luas sekitar 5 hektar", description="Deskripsi detail kejadian"),
 *     @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah", "Sedang", "Tinggi", "Sangat Tinggi"}, example="Tinggi", description="Tingkat keparahan bencana"),
 *     @OA\Property(property="latitude", type="number", format="double", example=-6.2088, description="Koordinat latitude"),
 *     @OA\Property(property="longitude", type="number", format="double", example=106.8456, description="Koordinat longitude"),
 *     @OA\Property(property="alamat", type="string", example="Jl. Sudirman No. 123", description="Alamat lengkap kejadian"),
 *     @OA\Property(property="jumlah_korban", type="integer", example=5, description="Jumlah korban jiwa"),
 *     @OA\Property(property="jumlah_rumah_rusak", type="integer", example=12, description="Jumlah rumah rusak"),
 *     @OA\Property(property="is_prioritas", type="boolean", example=true, description="Status prioritas laporan"),
 *     @OA\Property(property="view_count", type="integer", example=25, description="Jumlah view laporan"),
 *     @OA\Property(property="status", type="string", enum={"Draft", "Menunggu Verifikasi", "Diverifikasi", "Diproses", "Tindak Lanjut", "Selesai", "Ditolak"}, example="Menunggu Verifikasi", description="Status laporan"),
 *     @OA\Property(property="waktu_laporan", type="string", format="date-time", example="2024-12-22T10:30:00Z", description="Waktu laporan dibuat"),
 *     @OA\Property(property="waktu_verifikasi", type="string", format="date-time", example="2024-12-22T11:00:00Z", description="Waktu verifikasi"),
 *     @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-12-22T15:00:00Z", description="Waktu selesai penanganan"),
 *     @OA\Property(property="catatan_verifikasi", type="string", example="Laporan valid dan perlu ditindak lanjuti", description="Catatan verifikasi"),
 *     @OA\Property(property="catatan_proses", type="string", example="Tim SAR sudah diterjunkan ke lokasi", description="Catatan proses"),
 *     @OA\Property(property="data_tambahan", type="object", example={"cuaca":"hujan", "akses":"sulit"}, description="Data tambahan dalam format JSON"),
 *     @OA\Property(property="foto_bukti_1", type="string", example="storage/laporans/2024/12/foto1.jpg", description="Path foto bukti 1"),
 *     @OA\Property(property="foto_bukti_2", type="string", example="storage/laporans/2024/12/foto2.jpg", description="Path foto bukti 2"),
 *     @OA\Property(property="foto_bukti_3", type="string", example="storage/laporans/2024/12/foto3.jpg", description="Path foto bukti 3"),
 *     @OA\Property(property="video_bukti", type="string", example="storage/laporans/2024/12/video.mp4", description="Path video bukti"),
 *     @OA\Property(property="id_pelapor", type="integer", example=1, description="ID pelapor"),
 *     @OA\Property(property="id_kategori_bencana", type="integer", example=1, description="ID kategori bencana"),
 *     @OA\Property(property="id_desa", type="integer", example=1, description="ID desa lokasi"),
 *     @OA\Property(property="pelapor", ref="#/components/schemas/Pelapor", description="Data pelapor"),
 *     @OA\Property(property="kategori", ref="#/components/schemas/KategoriBencana", description="Data kategori bencana"),
 *     @OA\Property(property="desa", ref="#/components/schemas/Desa", description="Data desa lokasi"),
 *     @OA\Property(property="alamat_lengkap", type="string", example="Jl. Sudirman No. 123, RT 01/RW 02", description="Alamat detail manual (Jalan, RT/RW, Patokan)"),
 *     @OA\Property(property="administrative_area", type="string", example="Desa Sukamaju, Kecamatan Makmur, Kabupaten Sejahtera, Provinsi Jawa Barat", description="Wilayah administratif otomatis (Generated from Desa -> Provinsi)"),
 *     @OA\Property(property="coordinates", type="object", example={"lat":-6.2088, "lng":106.8456}, description="Koordinat untuk mapping"),
 *     @OA\Property(property="time_ago", type="string", example="2 jam yang lalu", description="Waktu relatif"),
 * )
 *
 * @OA\Schema(
 *     schema="Pelapor",
 *     title="Pelapor",
 *     description="Data pelapor laporan",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Ahmad Wijaya"),
 *     @OA\Property(property="email", type="string", example="ahmad@example.com"),
 * )
 *
 * @OA\Schema(
 *     schema="KategoriBencana",
 *     title="Kategori Bencana",
 *     description="Data kategori bencana",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama_kategori", type="string", example="Kebakaran"),
 *     @OA\Property(property="deskripsi", type="string", example="Kategori untuk kejadian kebakaran hutan dan permukiman"),
 * )
 *
 * @OA\Schema(
 *     schema="Desa",
 *     title="Desa",
 *     description="Data desa lokasi",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Sukamaju"),
 * )
 *
 * @OA\Schema(
 *     schema="LaporanStatistics",
 *     title="Laporan Statistics",
 *     description="Statistik laporan bencana",
 *     @OA\Property(property="total_laporan", type="integer", example=150),
 *     @OA\Property(property="laporan_perlu_verifikasi", type="integer", example=25),
 *     @OA\Property(property="laporan_ditindak", type="integer", example=45),
 *     @OA\Property(property="laporan_selesai", type="integer", example=60),
 *     @OA\Property(property="laporan_ditolak", type="integer", example=5),
 *     @OA\Property(property="weekly_stats", type="object", example={"mon":10, "tue":15, "wed":8}),
 *     @OA\Property(property="categories_stats", type="object", example={"Kebakaran":30, "Banjir":25, "Gempa":15}),
 *     @OA\Property(property="monthly_trend", type="object", example={"2024-12":120, "2024-11":95}),
 * )
 *
 * @OA\Schema(
 *     schema="LaporanStoreRequest",
 *     title="Laporan Store Request",
 *     description="Request body untuk membuat laporan baru",
 *     required={"judul_laporan", "deskripsi", "tingkat_keparahan", "latitude", "longitude", "id_kategori_bencana", "id_desa"},
 *     @OA\Property(property="judul_laporan", type="string", maxLength=255, example="Kebakaran Hutan di Desa Sukamaju"),
 *     @OA\Property(property="deskripsi", type="string", example="Kebakaran terjadi di area perbukitan dengan luas sekitar 5 hektar"),
 *     @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah","Sedang","Tinggi","Sangat Tinggi"}, example="Tinggi"),
 *     @OA\Property(property="latitude", type="number", format="double", minimum=-90, maximum=90, example=-6.2088),
 *     @OA\Property(property="longitude", type="number", format="double", minimum=-180, maximum=180, example=106.8456),
 *     @OA\Property(property="id_kategori_bencana", type="integer", example=1),
 *     @OA\Property(property="id_desa", type="integer", example=1),
 *     @OA\Property(property="alamat", type="string", maxLength=500, example="Jl. Sudirman No. 123"),
 *     @OA\Property(property="jumlah_korban", type="integer", minimum=0, example=5),
 *     @OA\Property(property="jumlah_rumah_rusak", type="integer", minimum=0, example=12),
 *     @OA\Property(property="is_prioritas", type="boolean", example=false),
 *     @OA\Property(property="data_tambahan", type="object", example={"cuaca":"hujan", "akses":"sulit"}),
 *     @OA\Property(property="waktu_laporan", type="string", format="date", example="2024-12-22"),
 *     @OA\Property(property="foto_bukti_1", type="string", format="binary", description="Upload foto bukti 1 (JPG/PNG max 5MB)"),
 *     @OA\Property(property="foto_bukti_2", type="string", format="binary", description="Upload foto bukti 2 (JPG/PNG max 5MB)"),
 *     @OA\Property(property="foto_bukti_3", type="string", format="binary", description="Upload foto bukti 3 (JPG/PNG max 5MB)"),
 *     @OA\Property(property="video_bukti", type="string", format="binary", description="Upload video bukti (MP4/AVI/MOV max 10MB)"),
 * )
 *
 * @OA\Schema(
 *     schema="LaporanUpdateRequest",
 *     title="Laporan Update Request",
 *     description="Request body untuk update laporan",
 *     @OA\Property(property="judul_laporan", type="string", maxLength=255, example="Kebakaran Hutan - Update"),
 *     @OA\Property(property="deskripsi", type="string", example="Deskripsi update kejadian"),
 *     @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah","Sedang","Tinggi","Sangat Tinggi"}),
 *     @OA\Property(property="latitude", type="number", format="double", minimum=-90, maximum=90),
 *     @OA\Property(property="longitude", type="number", format="double", minimum=-180, maximum=180),
 *     @OA\Property(property="id_kategori_bencana", type="integer"),
 *     @OA\Property(property="id_desa", type="integer"),
 *     @OA\Property(property="alamat", type="string", maxLength=500),
 *     @OA\Property(property="jumlah_korban", type="integer", minimum=0),
 *     @OA\Property(property="jumlah_rumah_rusak", type="integer", minimum=0),
 *     @OA\Property(property="is_prioritas", type="boolean"),
 *     @OA\Property(property="data_tambahan", type="object"),
 *     @OA\Property(property="foto_bukti_1", type="string", format="binary"),
 *     @OA\Property(property="foto_bukti_2", type="string", format="binary"),
 *     @OA\Property(property="foto_bukti_3", type="string", format="binary"),
 *     @OA\Property(property="video_bukti", type="string", format="binary"),
 *     @OA\Property(property="waktu_laporan", type="string", format="date"),
 * )
 *
 * @OA\RequestBody(
 *     request="LaporanStoreRequest",
 *     required=true,
 *     description="Data laporan baru",
 *     @OA\JsonContent(ref="#/components/schemas/LaporanStoreRequest")
 * )
 *
 * @OA\RequestBody(
 *     request="LaporanUpdateRequest",
 *     required=true,
 *     description="Data update laporan",
 *     @OA\JsonContent(ref="#/components/schemas/LaporanUpdateRequest")
 * )
 */

class LaporansController extends Controller
{
    /**
     * Handle file upload with automatic deletion of old file.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $fieldName - Field name in request
     * @param string|null $oldFile - Old file path to delete
     * @return string|null - Returns filename or null
     */
    private function handleFileUpload(Request $request, string $fieldName, ?string $oldFile = null): ?string
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $file = $request->file($fieldName);

        // Delete old file if exists
        if ($oldFile) {
            try {
                Storage::disk('public')->delete('laporans/' . $oldFile);
            } catch (\Exception $e) {
                // Silent fail if old file not found
                \Log::warning("Failed to delete old file: {$oldFile}", ['error' => $e->getMessage()]);
            }
        }

        // Generate unique filename
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store file
        $file->storeAs('laporans', $fileName, 'public');

        return $fileName;
    }

    /**
     * Delete multiple files from storage.
     *
     * @param array $files - Array of filenames
     * @return void
     */
    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file) {
                try {
                    Storage::disk('public')->delete('laporans/' . $file);
                } catch (\Exception $e) {
                    // Silent fail if file not found
                    \Log::warning("Failed to delete file: {$file}", ['error' => $e->getMessage()]);
                }
            }
        }
    }
    /**
     * Display a listing of the resource with eager loading.
     *
     * @OA\Get(
     *     path="/laporans",
     *     tags={"Laporan Management"},
     *     summary="Get all laporan dengan filter dan pagination",
     *     description="Mendapatkan daftar laporan bencana dengan opsi filter, search, dan pagination",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status laporan",
     *         @OA\Schema(type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Tindak Lanjut","Selesai","Ditolak"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="kategori_id",
     *         in="query",
     *         description="Filter berdasarkan ID kategori bencana",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter berdasarkan ID pelapor",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="prioritas",
     *         in="query",
     *         description="Filter laporan prioritas saja",
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id_desa",
     *         in="query",
     *         description="Filter berdasarkan ID desa",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search di judul, deskripsi, atau alamat",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="Latitude untuk filter lokasi radius",
     *         @OA\Schema(type="number", format="double")
     *     ),
     *
     *     @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="Longitude untuk filter lokasi radius",
     *         @OA\Schema(type="number", format="double")
     *     ),
     *
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Radius dalam km untuk filter lokasi",
     *         @OA\Schema(type="number")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah data per halaman",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Kolom untuk ordering",
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_direction",
     *         in="query",
     *         description="Arah ordering",
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved laporan list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data laporan berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Laporan")),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Laporans::with([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama',
                'tindakLanjut:id_tindaklanjut,laporan_id,id_petugas,tanggal_tanggapan,status,created_at',
                'tindakLanjut.petugas:id,nama',
                'tindakLanjut.laporan:id,id_pelapor,judul_laporan,deskripsi,tingkat_keparahan,latitude,longitude,alamat,jumlah_korban,jumlah_rumah_rusak,is_prioritas,view_count,status,waktu_laporan,waktu_verifikasi,waktu_selesai,catatan_verifikasi,catatan_proses,data_tambahan,foto_bukti_1,foto_bukti_2,foto_bukti_3,video_bukti,id_kategori_bencana,id_desa,alamat_lengkap',
                'tindakLanjut.laporan.pelapor:id,nama,email,no_telepon',
                'monitoring:id_monitoring,id_laporan,id_operator,waktu_monitoring,hasil_monitoring,koordinat_gps,created_at'
            ]);

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            if ($request->has('kategori_id') && $request->kategori_id) {
                $query->byCategory($request->kategori_id);
            }

            if ($request->has('user_id') && $request->user_id) {
                $query->byUser($request->user_id);
            }

            if ($request->has('prioritas') && $request->boolean('prioritas')) {
                $query->prioritas();
            }

            if ($request->has('id_desa') && $request->id_desa) {
                $query->where('id_desa', $request->id_desa);
            }

            // Apply date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Apply search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('judul_laporan', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%");
                });
            }

            // Apply location radius filter
            if ($request->has('lat') && $request->has('lng') && $request->has('radius')) {
                $query->byLocationRadius($request->lat, $request->lng, $request->radius);
            }

            // Ordering
            $orderBy = $request->get('order_by', 'created_at');
            $orderDirection = $request->get('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            // Pagination
            $limit = $request->get('limit', 15);
            $laporans = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Data laporan berhasil diambil',
                'data' => $laporans->items(),
                'pagination' => [
                    'current_page' => $laporans->currentPage(),
                    'last_page' => $laporans->lastPage(),
                    'per_page' => $laporans->perPage(),
                    'total' => $laporans->total(),
                    'from' => $laporans->firstItem(),
                    'to' => $laporans->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/laporans",
     *     tags={"Laporan Management"},
     *     summary="Create new laporan bencana",
     *     description="Membuat laporan bencana baru dengan upload bukti foto/video",
     *     security={{"jwt": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"judul_laporan", "deskripsi", "tingkat_keparahan", "latitude", "longitude", "id_kategori_bencana", "id_desa"},
     *                 @OA\Property(property="judul_laporan", type="string", maxLength=255, example="Kebakaran Hutan di Desa Sukamaju"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kebakaran terjadi di area perbukitan dengan luas sekitar 5 hektar"),
     *                 @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah", "Sedang", "Tinggi", "Sangat Tinggi"}, example="Tinggi"),
     *                 @OA\Property(property="latitude", type="number", format="double", example=-6.2088),
     *                 @OA\Property(property="longitude", type="number", format="double", example=106.8456),
     *                 @OA\Property(property="id_kategori_bencana", type="integer", example=1),
     *                 @OA\Property(property="id_desa", type="integer", example=1),
     *                 @OA\Property(property="alamat", type="string", maxLength=500, example="Jl. Sudirman No. 123"),
     *                 @OA\Property(property="jumlah_korban", type="integer", minimum=0, example=5),
     *                 @OA\Property(property="jumlah_rumah_rusak", type="integer", minimum=0, example=12),
     *                 @OA\Property(property="is_prioritas", type="boolean", example=false),
     *                 @OA\Property(property="data_tambahan", type="object", example={"cuaca":"hujan", "akses":"sulit"}),
     *                 @OA\Property(property="waktu_laporan", type="string", format="date", example="2024-12-22"),
     *                 @OA\Property(property="foto_bukti_1", type="string", format="binary", description="Upload foto bukti 1 (JPG/PNG max 5MB)"),
     *                 @OA\Property(property="foto_bukti_2", type="string", format="binary", description="Upload foto bukti 2 (JPG/PNG max 5MB)"),
     *                 @OA\Property(property="foto_bukti_3", type="string", format="binary", description="Upload foto bukti 3 (JPG/PNG max 5MB)"),
     *                 @OA\Property(property="video_bukti", type="string", format="binary", description="Upload video bukti (MP4/AVI/MOV max 10MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Laporan successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil dibuat"),
     *             @OA\Property(property="data", ref="#/components/schemas/Laporan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'tingkat_keparahan' => 'required|string|in:Rendah,Sedang,Tinggi,Sangat Tinggi',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'id_kategori_bencana' => 'required|exists:kategori_bencana,id',
                'id_desa' => 'required|exists:desa,id',
                'alamat' => 'nullable|string|max:500',
                'jumlah_korban' => 'nullable|integer|min:0',
                'jumlah_rumah_rusak' => 'nullable|integer|min:0',
                'is_prioritas' => 'nullable|boolean',
                'data_tambahan' => 'nullable|array',
                'foto_bukti_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_3' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'video_bukti' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
                'waktu_laporan' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['id_pelapor'] = auth()->id();
            $data['waktu_laporan'] = $request->waktu_laporan ?? now();
            $data['status'] = 'Draft';
            $data['view_count'] = 0;

            // Handle file uploads
            $fileFields = ['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'];
            foreach ($fileFields as $field) {
                $fileName = $this->handleFileUpload($request, $field);
                if ($fileName) {
                    $data[$field] = $fileName;
                }
            }

            $data['alamat_lengkap'] = $request->alamat_laporan ?? null;
            $data['jumlah_korban'] = $request->jumlah_korban ?? null;
            $data['jumlah_rumah_rusak'] = $request->jumlah_rumah_rusak ?? null;
            $data['is_prioritas'] = $request->boolean('is_prioritas', false);
            $data['data_tambahan'] = $request->data_tambahan ?? null;

            $laporan = Laporans::create($data);
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $laporan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Get detailed laporan by ID",
     *     description="Mengambil detail laporan bencana lengkap dengan semua relasi",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved laporan detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Detail laporan berhasil diambil"),
     *             @OA\Property(property="data", ref="#/components/schemas/Laporan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Laporan not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(Laporans $laporan): JsonResponse
    {
        try {
            // Increment view count
            $laporan->incrementViewCount();

            // Load relationships
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama',
                'tindakLanjut:id_tindaklanjut,laporan_id,id_petugas,tanggal_tanggapan,status,created_at',
                'tindakLanjut.petugas:id,nama',
                'tindakLanjut.laporan:id,id_pelapor,judul_laporan,deskripsi,tingkat_keparahan,latitude,longitude,alamat,jumlah_korban,jumlah_rumah_rusak,is_prioritas,view_count,status,waktu_laporan,waktu_verifikasi,waktu_selesai,catatan_verifikasi,catatan_proses,data_tambahan,foto_bukti_1,foto_bukti_2,foto_bukti_3,video_bukti,id_kategori_bencana,id_desa,alamat_lengkap',
                'tindakLanjut.laporan.pelapor:id,nama,email,no_telepon',
                'monitoring:id_monitoring,id_laporan,id_operator,waktu_monitoring,hasil_monitoring,koordinat_gps,created_at'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil diambil',
                'data' => $laporan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Update laporan bencana",
     *     description="Memperbarui data laporan bencana (hanya pelapor atau Admin/PetugasBPBD yang berhak)",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="judul_laporan", type="string", maxLength=255),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah", "Sedang", "Tinggi", "Sangat Tinggi"}),
     *                 @OA\Property(property="latitude", type="number", format="double"),
     *                 @OA\Property(property="longitude", type="number", format="double"),
     *                 @OA\Property(property="id_kategori_bencana", type="integer"),
     *                 @OA\Property(property="id_desa", type="integer"),
     *                 @OA\Property(property="alamat", type="string", maxLength=500),
     *                 @OA\Property(property="jumlah_korban", type="integer", minimum=0),
     *                 @OA\Property(property="jumlah_rumah_rusak", type="integer", minimum=0),
     *                 @OA\Property(property="is_prioritas", type="boolean"),
     *                 @OA\Property(property="data_tambahan", type="object"),
     *                 @OA\Property(property="waktu_laporan", type="string", format="date"),
     *                 @OA\Property(property="foto_bukti_1", type="string", format="binary"),
     *                 @OA\Property(property="foto_bukti_2", type="string", format="binary"),
     *                 @OA\Property(property="foto_bukti_3", type="string", format="binary"),
     *                 @OA\Property(property="video_bukti", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Laporan successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diperbarui"),
     *             @OA\Property(property="data", ref="#/components/schemas/Laporan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not the owner or admin"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            // Authorization check
            if (auth()->id() !== $laporan->id_pelapor && !auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk mengubah laporan ini',
                    'data' => null
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'tingkat_keparahan' => 'sometimes|string|in:Rendah,Sedang,Tinggi,Sangat Tinggi',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'id_kategori_bencana' => 'sometimes|exists:kategori_bencana,id',
                'id_desa' => 'sometimes|exists:desa,id',
                'alamat' => 'nullable|string|max:500',
                'jumlah_korban' => 'nullable|integer|min:0',
                'jumlah_rumah_rusak' => 'nullable|integer|min:0',
                'is_prioritas' => 'nullable|boolean',
                'data_tambahan' => 'nullable|array',
                'foto_bukti_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_3' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'video_bukti' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
                'waktu_laporan' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Handle file uploads with automatic old file deletion
            $fileFields = ['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'];
            foreach ($fileFields as $field) {
                $fileName = $this->handleFileUpload($request, $field, $laporan->$field);
                if ($fileName) {
                    $data[$field] = $fileName;
                }
            }

            // Handle nullable fields
            $data['alamat_lengkap'] = $request->alamat_laporan ?? $laporan->alamat_lengkap;
            $data['jumlah_korban'] = $request->jumlah_korban ?? $laporan->jumlah_korban;
            $data['jumlah_rumah_rusak'] = $request->jumlah_rumah_rusak ?? $laporan->jumlah_rumah_rusak;

            if ($request->has('is_prioritas')) {
                $data['is_prioritas'] = $request->boolean('is_prioritas');
            }

            if ($request->has('data_tambahan')) {
                $data['data_tambahan'] = $request->data_tambahan;
            }

            // Check if status is being changed
            $isStatusChanging = isset($data['status']) && $data['status'] !== $laporan->status;

            if ($isStatusChanging) {
                // Prepare update data with verification fields when status changes
                $updateData = $data;

                // Auto-fill data sistem (Back-end Logic) when status changes to 'Diverifikasi'
                if ($data['status'] === 'Diverifikasi') {
                    $user_id = auth()->id(); // Mengambil ID dari Token JWT
                    $updateData['id_verifikator'] = $user_id;
                    $updateData['waktu_verifikasi'] = now();

                    // Hanya isi penanggung jawab jika belum ada sebelumnya
                    if (is_null($laporan->id_penanggung_jawab)) {
                        $updateData['id_penanggung_jawab'] = $user_id;
                    }
                }

                $laporan->update($updateData);
            } else {
                $laporan->update($data);
            }

            // Load relationships for response
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diperbarui',
                'data' => $laporan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Delete laporan bencana",
     *     description="Menghapus laporan bencana beserta file bukti (hanya pelapor atau Admin yang berhak)",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Laporan successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil dihapus"),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not the owner or admin"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Laporan not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy(Laporans $laporan): JsonResponse
    {
        try {
            // Authorization check
            if (auth()->id() !== $laporan->id_pelapor && !auth()->user()->hasRole(['Admin', 'OperatorDesa'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk menghapus laporan ini',
                    'data' => null
                ], 403);
            }

            // Delete all associated files
            $filesToDelete = [
                $laporan->foto_bukti_1,
                $laporan->foto_bukti_2,
                $laporan->foto_bukti_3,
                $laporan->video_bukti
            ];
            $this->deleteFiles($filesToDelete);

            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get statistics for reports.
     *
     * @OA\Get(
     *     path="/laporans/statistics",
     *     tags={"Laporan Management"},
     *     summary="Get laporan statistics",
     *     description="Mengambil statistik laporan bencana (total, perlu verifikasi, ditindak, selesai, dll)",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Filter periode statistik",
     *         required=false,
     *         @OA\Schema(type="string", enum={"weekly", "monthly", "yearly"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Statistics successfully retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/LaporanStatistics"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = Laporans::query();

            // Filter by period if provided
            if ($request->has('period') && $request->period) {
                switch ($request->period) {
                    case 'weekly':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->subMonth());
                        break;
                    case 'yearly':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            }

            // Total laporan
            $total_laporan = $query->count();

            // Laporan perlu verifikasi (Status: 'Menunggu Verifikasi' atau 'Draft')
            $laporan_perlu_verifikasi = $query->clone()
                ->whereIn('status', ['Draft', 'Menunggu Verifikasi'])
                ->count();

            // Laporan ditindak (Status: 'Diproses', 'Diverifikasi', atau 'Tindak Lanjut')
            $laporan_ditindak = $query->clone()
                ->whereIn('status', ['Diverifikasi', 'Diproses', 'Tindak Lanjut'])
                ->count();

            // Laporan selesai (Status: 'Selesai')
            $laporan_selesai = $query->clone()
                ->where('status', 'Selesai')
                ->count();

            // Laporan ditolak (Status: 'Ditolak')
            $laporan_ditolak = $query->clone()
                ->where('status', 'Ditolak')
                ->count();

            // Weekly stats (last 7 days)
            $weekly_stats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = Laporans::whereDate('created_at', $date)->count();
                $weekly_stats[strtolower(now()->subDays($i)->format('D'))] = $count;
            }

            // Categories statistics
            $categories_stats = DB::table('laporans')
                ->join('kategori_bencana', 'laporans.id_kategori_bencana', '=', 'kategori_bencana.id')
                ->select('kategori_bencana.nama_kategori as category_name', DB::raw('count(*) as count'))
                ->groupBy('kategori_bencana.id', 'kategori_bencana.nama_kategori')
                ->orderBy('count', 'desc')
                ->get()
                ->keyBy('category_name')
                ->map(function ($item) {
                    return $item->count;
                })
                ->toArray();

            // Monthly trend
            $monthly_trend = DB::table('laporans')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();

            // Top pengguna
            $top_pengguna = DB::table('laporans')
                ->join('pengguna', 'laporans.id_pelapor', '=', 'pengguna.id')
                ->select('pengguna.nama as pengguna_name', DB::raw('count(*) as laporan_count'))
                ->groupBy('pengguna.id', 'pengguna.nama')
                ->orderBy('laporan_count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_laporan' => $total_laporan,
                    'laporan_perlu_verifikasi' => $laporan_perlu_verifikasi,
                    'laporan_ditindak' => $laporan_ditindak,
                    'laporan_selesai' => $laporan_selesai,
                    'laporan_ditolak' => $laporan_ditolak,
                    'laporan_baru' => $laporan_perlu_verifikasi, // Backward compatibility
                    'laporan_ditangani' => $laporan_ditindak, // Backward compatibility
                    'weekly_stats' => $weekly_stats,
                    'categories_stats' => $categories_stats,
                    'monthly_trend' => $monthly_trend,
                    'top_pengguna' => $top_pengguna
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Verify a report.
     *
     * @OA\Post(
     *     path="/laporans/{id}/verifikasi",
     *     tags={"Laporan Management"},
     *     summary="Verifikasi laporan bencana",
     *     description="Verifikasi laporan oleh Operator Desa atau Petugas BPBD",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"Diverifikasi", "Ditolak"}, example="Diverifikasi", description="Status verifikasi"),
     *             @OA\Property(property="catatan_verifikasi", type="string", maxLength=1000, example="Laporan valid dan perlu ditindak lanjuti")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Laporan successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diverifikasi"),
     *             @OA\Property(property="data", ref="#/components/schemas/Laporan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function verifikasi(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            // Authorization check
            if (!auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk memverifikasi laporan ini',
                    'data' => null
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Diverifikasi,Ditolak',
                'catatan_verifikasi' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare update data
            $updateData = [
                'status' => $request->status,
                'waktu_verifikasi' => now(),
                'id_verifikator' => auth()->id(), // Ambil dari token JWT/Session
                'catatan_verifikasi' => $request->catatan_verifikasi
            ];

            // Hanya isi penanggung jawab jika belum ada sebelumnya
            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = auth()->id();
            }

            $laporan->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diverifikasi',
                'data' => $laporan->load([
                    'pelapor:id,nama,email,alamat,no_telepon',
                    'kategori:id,nama_kategori,deskripsi',
                    'desa:id,nama,id_kecamatan',
                    'desa.kecamatan:id,nama,id_kabupaten',
                    'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                    'desa.kecamatan.kabupaten.provinsi:id,nama'
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Process a report.
     *
     * @OA\Post(
     *     path="/laporans/{id}/proses",
     *     tags={"Laporan Management"},
     *     summary="Proses laporan bencana",
     *     description="Update status penanganan laporan oleh Admin, Petugas BPBD, atau Operator Desa",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"Diproses", "Tindak Lanjut", "Selesai"}, example="Diproses", description="Status penanganan"),
     *             @OA\Property(property="catatan_proses", type="string", maxLength=1000, example="Tim SAR sudah diterjunkan ke lokasi")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status laporan successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status laporan berhasil diperbarui"),
     *             @OA\Property(property="data", ref="#/components/schemas/Laporan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function proses(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            // Authorization check
            if (!auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk memproses laporan ini',
                    'data' => null
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Diproses,Tindak Lanjut,Selesai',
                'catatan_proses' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare update data
            $updateData = [
                'status' => $request->status,
                'catatan_proses' => $request->catatan_proses
            ];

            // Hanya isi penanggung jawab jika belum ada sebelumnya
            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = auth()->id();
            }

            $laporan->update($updateData);

            if ($request->status === 'Selesai') {
                $laporan->update(['waktu_selesai' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status laporan berhasil diperbarui',
                'data' => $laporan->load([
                    'pelapor:id,nama,email,alamat,no_telepon',
                    'kategori:id,nama_kategori,deskripsi',
                    'desa:id,nama,id_kecamatan',
                    'desa.kecamatan:id,nama,id_kabupaten',
                    'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                    'desa.kecamatan.kabupaten.provinsi:id,nama'
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get report history.
     *
     * @OA\Get(
     *     path="/laporans/{id}/riwayat",
     *     tags={"Laporan Management"},
     *     summary="Get riwayat tindakan laporan",
     *     description="Mengambil riwayat seluruh tindakan yang pernah dilakukan pada laporan",
     *     security={{"jwt": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID laporan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Riwayat successfully retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Riwayat laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="laporan_id", type="integer"),
     *                     @OA\Property(property="aksi", type="string"),
     *                     @OA\Property(property="deskripsi", type="string"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="nama", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Laporan not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function riwayat(Laporans $laporan): JsonResponse
    {
        try {
            $history = $laporan->riwayatTindakan()
                ->with('user:id,nama')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat laporan berhasil diambil',
                'data' => $history
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat laporan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}