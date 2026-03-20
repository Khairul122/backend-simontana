<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Authentication", description="Authentication endpoints")
 * @OA\Tag(name="User Management", description="Manajemen pengguna")
 * @OA\Tag(name="Wilayah Management", description="Referensi dan manajemen wilayah")
 * @OA\Tag(name="Laporan Management", description="CRUD dan statistik laporan")
 * @OA\Tag(name="Laporan Workflow", description="Workflow status laporan")
 * @OA\Tag(name="Monitoring", description="Monitoring operasional")
 * @OA\Tag(name="Tindak Lanjut", description="Tindak lanjut operasional")
 * @OA\Tag(name="Riwayat Tindakan", description="Riwayat tindakan operasional")
 * @OA\Tag(name="BMKG Integration", description="Integrasi data BMKG")
 */
class ApiPaths
{
    /**
     * @OA\Get(
     *     path="/auth/roles",
     *     tags={"User Management"},
     *     summary="Daftar role yang tersedia",
     *     @OA\Response(response=200, description="Daftar role tersedia", @OA\JsonContent(ref="#/components/schemas/RoleListSuccessResponse"))
     * )
     */
    public function authRolesDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/check-token",
     *     tags={"User Management"},
     *     summary="Validasi token aktif",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Token valid dan data user ringkas tersedia", @OA\JsonContent(ref="#/components/schemas/CheckTokenSuccessResponse")),
     *     @OA\Response(response=401, description="Token tidak valid", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function checkTokenDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"User Management"},
     *     summary="Registrasi pengguna baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","username","email","password","password_confirmation","role"},
     *             @OA\Property(property="nama", type="string", example="Warga Baru"),
     *             @OA\Property(property="username", type="string", example="warga_baru"),
     *             @OA\Property(property="email", type="string", format="email", example="warga@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}, example="Warga"),
     *             @OA\Property(property="id_desa", type="integer", nullable=true, example=1)
     *         )
     *     ),
 *     @OA\Response(response=201, description="Registrasi berhasil dengan data user dan nested wilayah", @OA\JsonContent(ref="#/components/schemas/RegisterSuccessResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=429, description="Terlalu banyak percobaan registrasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function authRegisterDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"User Management"},
     *     summary="Login pengguna",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
 *     @OA\Response(response=200, description="Login berhasil dengan data user, token, dan expiry", @OA\JsonContent(ref="#/components/schemas/LoginSuccessResponse")),
     *     @OA\Response(response=401, description="Kredensial tidak valid", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=429, description="Terlalu banyak percobaan login", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function authLoginDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"User Management"},
     *     summary="Logout pengguna",
     *     security={{"jwt":{}}},
 *     @OA\Response(response=200, description="Logout berhasil (data null)", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function authLogoutDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"User Management"},
     *     summary="Refresh token",
     *     security={{"jwt":{}}},
 *     @OA\Response(response=200, description="Token berhasil diperbarui dengan payload token baru", @OA\JsonContent(ref="#/components/schemas/TokenRefreshSuccessResponse")),
     *     @OA\Response(response=401, description="Token tidak valid", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function authRefreshDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"User Management"},
     *     summary="Ambil profil user login",
     *     security={{"jwt":{}}},
 *     @OA\Response(response=200, description="Data user berhasil diambil dengan nested wilayah penuh", @OA\JsonContent(ref="#/components/schemas/CurrentUserSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function authMeDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/laporans",
     *     tags={"Wilayah Management"},
     *     summary="Daftar laporan dengan filter",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Selesai","Ditolak"})),
     *     @OA\Parameter(name="kategori_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="prioritas", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="id_desa", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="lat", in="query", @OA\Schema(type="number", format="double")),
     *     @OA\Parameter(name="lng", in="query", @OA\Schema(type="number", format="double")),
     *     @OA\Parameter(name="radius", in="query", description="Maksimum 100 km", @OA\Schema(type="number")),
     *     @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="order_direction", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Data laporan berhasil diambil dengan relasi nested penuh dan meta pagination", @OA\JsonContent(ref="#/components/schemas/LaporanListSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanIndexDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/laporans",
     *     tags={"Wilayah Management"},
     *     summary="Buat laporan baru",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"judul_laporan","deskripsi","tingkat_keparahan","latitude","longitude","id_kategori_bencana","id_desa"},
     *                 @OA\Property(property="judul_laporan", type="string"),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah","Sedang","Tinggi","Kritis"}),
     *                 @OA\Property(property="latitude", type="number", format="double"),
     *                 @OA\Property(property="longitude", type="number", format="double"),
     *                 @OA\Property(property="id_kategori_bencana", type="integer"),
     *                 @OA\Property(property="id_desa", type="integer"),
     *                 @OA\Property(property="alamat", type="string", nullable=true),
     *                 @OA\Property(property="jumlah_korban", type="integer", nullable=true),
     *                 @OA\Property(property="jumlah_rumah_rusak", type="integer", nullable=true),
     *                 @OA\Property(property="is_prioritas", type="boolean", nullable=true),
     *                 @OA\Property(property="foto_bukti_1", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="foto_bukti_2", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="foto_bukti_3", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="video_bukti", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
 *     @OA\Response(response=201, description="Laporan berhasil dibuat dengan relasi nested penuh", @OA\JsonContent(ref="#/components/schemas/LaporanDetailSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanStoreDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Detail laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Detail laporan berhasil diambil dengan nested pelapor, kategori, wilayah, monitoring, tindak lanjut", @OA\JsonContent(ref="#/components/schemas/LaporanDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Laporan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanShowDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Update laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="judul_laporan", type="string"),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah","Sedang","Tinggi","Kritis"}),
     *                 @OA\Property(property="latitude", type="number", format="double"),
     *                 @OA\Property(property="longitude", type="number", format="double"),
     *                 @OA\Property(property="id_kategori_bencana", type="integer"),
     *                 @OA\Property(property="id_desa", type="integer"),
     *                 @OA\Property(property="alamat", type="string", nullable=true),
     *                 @OA\Property(property="jumlah_korban", type="integer", nullable=true),
     *                 @OA\Property(property="jumlah_rumah_rusak", type="integer", nullable=true),
     *                 @OA\Property(property="is_prioritas", type="boolean", nullable=true),
     *                 @OA\Property(property="foto_bukti_1", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="foto_bukti_2", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="foto_bukti_3", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="video_bukti", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
 *     @OA\Response(response=200, description="Laporan berhasil diperbarui dengan relasi nested penuh", @OA\JsonContent(ref="#/components/schemas/LaporanDetailSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/laporans/{id}",
     *     tags={"Laporan Management"},
     *     summary="Hapus laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Laporan berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/laporans/statistics",
     *     tags={"Laporan Management"},
     *     summary="Statistik laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="period", in="query", @OA\Schema(type="string", enum={"weekly","monthly","yearly"})),
 *     @OA\Response(response=200, description="Statistik laporan berhasil diambil (agregasi status, tren, kategori, top pengguna)", @OA\JsonContent(ref="#/components/schemas/LaporanStatisticsSuccessResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanStatisticsDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg",
     *     tags={"BMKG Integration"},
     *     summary="Ringkasan data BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Data BMKG berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgIndexSuccessResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgIndexDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/gempa/terbaru",
     *     tags={"BMKG Integration"},
     *     summary="Gempa terbaru",
     *     @OA\Response(response=200, description="Data gempa terbaru berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgObjectSuccessResponse")),
     *     @OA\Response(response=404, description="Data tidak tersedia", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgGempaTerbaruDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/gempa/terkini",
     *     tags={"BMKG Integration"},
     *     summary="Daftar gempa terkini",
     *     @OA\Response(response=200, description="Daftar gempa berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgArraySuccessResponse")),
     *     @OA\Response(response=404, description="Data tidak tersedia", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgDaftarGempaDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/gempa/dirasakan",
     *     tags={"BMKG Integration"},
     *     summary="Gempa dirasakan",
     *     @OA\Response(response=200, description="Data gempa dirasakan berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgArraySuccessResponse")),
     *     @OA\Response(response=404, description="Data tidak tersedia", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgGempaDirasakanDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/prakiraan-cuaca",
     *     tags={"BMKG Integration"},
     *     summary="Prakiraan cuaca berdasarkan wilayah",
     *     @OA\Parameter(name="wilayah_id", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Data prakiraan cuaca berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgObjectSuccessResponse")),
     *     @OA\Response(response=404, description="Data tidak tersedia", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgPrakiraanCuacaDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/peringatan-tsunami",
     *     tags={"BMKG Integration"},
     *     summary="Peringatan tsunami",
     *     @OA\Response(response=200, description="Data peringatan tsunami berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgObjectSuccessResponse")),
     *     @OA\Response(response=404, description="Data tidak tersedia", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgPeringatanTsunamiDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/bmkg/cache/status",
     *     tags={"BMKG Integration"},
     *     summary="Status cache BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Status cache berhasil diambil", @OA\JsonContent(ref="#/components/schemas/BmkgObjectSuccessResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgCacheStatusDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/bmkg/cache/clear",
     *     tags={"BMKG Integration"},
     *     summary="Bersihkan cache BMKG",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Cache BMKG berhasil dibersihkan", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=500, description="Kesalahan server", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function bmkgClearCacheDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/laporans/{id}/verifikasi",
     *     tags={"Laporan Workflow"},
     *     summary="Verifikasi laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"Diverifikasi","Ditolak"}, example="Diverifikasi"),
     *             @OA\Property(property="catatan_verifikasi", type="string", nullable=true, maxLength=1000)
     *         )
     *     ),
 *     @OA\Response(response=200, description="Laporan berhasil diverifikasi dengan payload laporan nested penuh", @OA\JsonContent(ref="#/components/schemas/LaporanWorkflowSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Laporan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal atau INVALID_STATUS_TRANSITION", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function laporanVerifikasiDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/laporans/{id}/proses",
     *     tags={"Laporan Workflow"},
     *     summary="Proses laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"Diproses","Selesai"}, example="Diproses")
     *         )
     *     ),
 *     @OA\Response(response=200, description="Status laporan berhasil diperbarui dengan payload laporan nested penuh", @OA\JsonContent(ref="#/components/schemas/LaporanWorkflowSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Laporan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal atau INVALID_STATUS_TRANSITION", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function laporanProsesDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/laporans/{id}/riwayat",
     *     tags={"Laporan Workflow"},
     *     summary="Ambil riwayat tindakan laporan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Riwayat laporan berhasil diambil dengan nested petugas, tindak lanjut, dan laporan", @OA\JsonContent(ref="#/components/schemas/LaporanRiwayatSuccessResponse")),
     *     @OA\Response(response=404, description="Laporan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function laporanRiwayatDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/monitoring",
     *     tags={"Monitoring"},
     *     summary="Daftar monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id_laporan", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id_operator", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *     @OA\Response(response=200, description="Data monitoring berhasil diambil dengan nested laporan/operator penuh dan meta pagination", @OA\JsonContent(ref="#/components/schemas/MonitoringPaginatedSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function monitoringIndexDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/monitoring",
     *     tags={"Monitoring"},
     *     summary="Buat monitoring",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_laporan","id_operator","waktu_monitoring","hasil_monitoring"},
     *             @OA\Property(property="id_laporan", type="integer", example=1),
     *             @OA\Property(property="id_operator", type="integer", example=2),
     *             @OA\Property(property="waktu_monitoring", type="string", format="date-time"),
     *             @OA\Property(property="hasil_monitoring", type="string", example="Kondisi terkendali"),
     *             @OA\Property(property="koordinat_gps", type="string", nullable=true, example="-6.2,106.8")
     *         )
     *     ),
 *     @OA\Response(response=201, description="Monitoring berhasil dibuat dengan nested laporan/operator penuh", @OA\JsonContent(ref="#/components/schemas/MonitoringSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function monitoringStoreDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Detail monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Data monitoring berhasil diambil dengan nested laporan/operator penuh", @OA\JsonContent(ref="#/components/schemas/MonitoringSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Monitoring tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function monitoringShowDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Update monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="waktu_monitoring", type="string", format="date-time"),
     *             @OA\Property(property="hasil_monitoring", type="string"),
     *             @OA\Property(property="koordinat_gps", type="string", nullable=true)
     *         )
     *     ),
 *     @OA\Response(response=200, description="Monitoring berhasil diupdate dengan nested laporan/operator penuh", @OA\JsonContent(ref="#/components/schemas/MonitoringSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Monitoring tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function monitoringUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/monitoring/{id}",
     *     tags={"Monitoring"},
     *     summary="Hapus monitoring",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Monitoring berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Monitoring tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function monitoringDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/tindak-lanjut",
     *     tags={"Tindak Lanjut"},
     *     summary="Daftar tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="laporan_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id_petugas", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"Menuju Lokasi","Selesai"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *     @OA\Response(response=200, description="Data tindak lanjut berhasil diambil dengan nested laporan/petugas penuh dan meta pagination", @OA\JsonContent(ref="#/components/schemas/TindakLanjutPaginatedSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function tindakLanjutIndexDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/tindak-lanjut",
     *     tags={"Tindak Lanjut"},
     *     summary="Buat tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"laporan_id","tanggal_tanggapan"},
     *             @OA\Property(property="laporan_id", type="integer", example=1),
     *             @OA\Property(property="id_petugas", type="integer", nullable=true, example=2),
     *             @OA\Property(property="tanggal_tanggapan", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string", enum={"Menuju Lokasi","Selesai"}, example="Menuju Lokasi")
     *         )
     *     ),
 *     @OA\Response(response=201, description="Tindak lanjut berhasil dibuat dengan nested laporan/petugas penuh", @OA\JsonContent(ref="#/components/schemas/TindakLanjutSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function tindakLanjutStoreDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Detail tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Data tindak lanjut berhasil diambil dengan nested laporan/petugas penuh", @OA\JsonContent(ref="#/components/schemas/TindakLanjutSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Tindak lanjut tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function tindakLanjutShowDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Update tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="tanggal_tanggapan", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string", enum={"Menuju Lokasi","Selesai"})
     *         )
     *     ),
 *     @OA\Response(response=200, description="Tindak lanjut berhasil diupdate dengan nested laporan/petugas penuh", @OA\JsonContent(ref="#/components/schemas/TindakLanjutSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Tindak lanjut tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function tindakLanjutUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/tindak-lanjut/{id}",
     *     tags={"Tindak Lanjut"},
     *     summary="Hapus tindak lanjut",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Tindak lanjut berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Tindak lanjut tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function tindakLanjutDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/riwayat-tindakan",
     *     tags={"Riwayat Tindakan"},
     *     summary="Daftar riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="tindaklanjut_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id_petugas", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *     @OA\Response(response=200, description="Data riwayat tindakan berhasil diambil dengan nested tindak lanjut/petugas penuh dan meta pagination", @OA\JsonContent(ref="#/components/schemas/RiwayatTindakanPaginatedSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function riwayatIndexDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/riwayat-tindakan",
     *     tags={"Riwayat Tindakan"},
     *     summary="Buat riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tindaklanjut_id","keterangan","waktu_tindakan"},
     *             @OA\Property(property="tindaklanjut_id", type="integer", example=1),
     *             @OA\Property(property="id_petugas", type="integer", nullable=true, example=2),
     *             @OA\Property(property="keterangan", type="string", example="Evakuasi dilakukan"),
     *             @OA\Property(property="waktu_tindakan", type="string", format="date-time")
     *         )
     *     ),
 *     @OA\Response(response=201, description="Riwayat tindakan berhasil dibuat dengan nested tindak lanjut/petugas penuh", @OA\JsonContent(ref="#/components/schemas/RiwayatTindakanSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function riwayatStoreDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Detail riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Data riwayat tindakan berhasil diambil dengan nested tindak lanjut/petugas penuh", @OA\JsonContent(ref="#/components/schemas/RiwayatTindakanSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Riwayat tindakan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function riwayatShowDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Update riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="keterangan", type="string"),
     *             @OA\Property(property="waktu_tindakan", type="string", format="date-time")
     *         )
     *     ),
 *     @OA\Response(response=200, description="Riwayat tindakan berhasil diupdate dengan nested tindak lanjut/petugas penuh", @OA\JsonContent(ref="#/components/schemas/RiwayatTindakanSuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Riwayat tindakan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function riwayatUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/riwayat-tindakan/{id}",
     *     tags={"Riwayat Tindakan"},
     *     summary="Hapus riwayat tindakan",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Riwayat tindakan berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=403, description="Tidak memiliki izin", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Riwayat tindakan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function riwayatDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/users/profile",
     *     tags={"Authentication"},
     *     summary="Profil pengguna saat ini",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Profil berhasil diambil dengan nested wilayah", @OA\JsonContent(ref="#/components/schemas/UserDetailSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersProfileDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/users/profile",
     *     tags={"Authentication"},
     *     summary="Update profil pengguna saat ini",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string"),
     *             @OA\Property(property="no_telepon", type="string"),
     *             @OA\Property(property="alamat", type="string"),
     *             @OA\Property(property="id_desa", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil berhasil diupdate", @OA\JsonContent(ref="#/components/schemas/UserDetailSuccessResponse")),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function usersProfileUpdateDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Authentication"},
     *     summary="Daftar pengguna (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="role", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Data pengguna berhasil diambil dengan nested wilayah dan meta pagination", @OA\JsonContent(ref="#/components/schemas/UserListSuccessResponse")),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersIndexDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/users/statistics",
     *     tags={"Authentication"},
     *     summary="Statistik pengguna (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Response(response=200, description="Statistik pengguna berhasil diambil", @OA\JsonContent(ref="#/components/schemas/UserStatisticsSuccessResponse")),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersStatisticsDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Authentication"},
     *     summary="Detail pengguna (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data pengguna berhasil diambil", @OA\JsonContent(ref="#/components/schemas/UserDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Pengguna tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersShowDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     tags={"Authentication"},
     *     summary="Buat pengguna baru (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama","username","password","role"},
     *             @OA\Property(property="nama", type="string"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}),
     *             @OA\Property(property="no_telepon", type="string", nullable=true),
     *             @OA\Property(property="alamat", type="string", nullable=true),
     *             @OA\Property(property="id_desa", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pengguna berhasil ditambahkan", @OA\JsonContent(ref="#/components/schemas/UserDetailSuccessResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function usersStoreDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     tags={"Authentication"},
     *     summary="Update pengguna (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(type="object", additionalProperties=true)),
     *     @OA\Response(response=200, description="Pengguna berhasil diupdate", @OA\JsonContent(ref="#/components/schemas/UserDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Pengguna tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     tags={"Authentication"},
     *     summary="Hapus pengguna (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pengguna berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=404, description="Pengguna tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function usersDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/kategori-bencana",
     *     tags={"Laporan Management"},
     *     summary="Daftar kategori bencana",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_field", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_direction", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Daftar kategori bencana berhasil diambil", @OA\JsonContent(ref="#/components/schemas/KategoriBencanaListSuccessResponse"))
     * )
     */
    public function kategoriIndexDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/kategori-bencana/{id}",
     *     tags={"Laporan Management"},
     *     summary="Detail kategori bencana",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail kategori bencana berhasil diambil", @OA\JsonContent(ref="#/components/schemas/KategoriBencanaDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Kategori bencana tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function kategoriShowDoc(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/kategori-bencana",
     *     tags={"Laporan Management"},
     *     summary="Tambah kategori bencana (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"nama_kategori"}, @OA\Property(property="nama_kategori", type="string"), @OA\Property(property="deskripsi", type="string", nullable=true), @OA\Property(property="icon", type="string", nullable=true))),
     *     @OA\Response(response=201, description="Kategori bencana berhasil ditambahkan", @OA\JsonContent(ref="#/components/schemas/KategoriBencanaDetailSuccessResponse")),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function kategoriStoreDoc(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/kategori-bencana/{id}",
     *     tags={"Laporan Management"},
     *     summary="Update kategori bencana (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(type="object", additionalProperties=true)),
     *     @OA\Response(response=200, description="Kategori bencana berhasil diperbarui", @OA\JsonContent(ref="#/components/schemas/KategoriBencanaDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Kategori bencana tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function kategoriUpdateDoc(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/kategori-bencana/{id}",
     *     tags={"Laporan Management"},
     *     summary="Hapus kategori bencana (Admin only)",
     *     security={{"jwt":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Kategori bencana berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/KategoriBencanaDetailSuccessResponse")),
     *     @OA\Response(response=400, description="Kategori masih digunakan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Kategori bencana tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function kategoriDestroyDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/wilayah",
     *     tags={"Laporan Management"},
     *     summary="Daftar wilayah (all jenis atau per jenis)",
     *     @OA\Parameter(name="jenis", in="query", @OA\Schema(type="string", enum={"provinsi","kabupaten","kecamatan","desa"})),
     *     @OA\Parameter(name="include", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Data wilayah berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahPaginatedSuccessResponse"))
     * )
     */
    public function wilayahIndexDoc(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/wilayah/{id}",
     *     tags={"Laporan Management"},
     *     summary="Detail wilayah by jenis",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="jenis", in="query", required=true, @OA\Schema(type="string", enum={"provinsi","kabupaten","kecamatan","desa"})),
     *     @OA\Parameter(name="include", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Detail wilayah berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahDetailSuccessResponse")),
     *     @OA\Response(response=404, description="Wilayah tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function wilayahShowDoc(): void
    {
    }

    /**
     * @OA\Get(path="/wilayah/provinsi", tags={"Wilayah Management"}, summary="Daftar provinsi", @OA\Response(response=200, description="Data provinsi berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahCollectionSuccessResponse")))
     * @OA\Get(path="/wilayah/provinsi/{id}", tags={"Wilayah Management"}, summary="Detail provinsi", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Data provinsi berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahDetailSuccessResponse")), @OA\Response(response=404, description="Provinsi tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Get(path="/wilayah/kabupaten/{provinsi_id}", tags={"Wilayah Management"}, summary="Daftar kabupaten by provinsi", @OA\Parameter(name="provinsi_id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Data kabupaten berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahCollectionSuccessResponse")), @OA\Response(response=404, description="Provinsi tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Get(path="/wilayah/kecamatan/{kabupaten_id}", tags={"Wilayah Management"}, summary="Daftar kecamatan by kabupaten", @OA\Parameter(name="kabupaten_id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Data kecamatan berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahCollectionSuccessResponse")), @OA\Response(response=404, description="Kabupaten tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Get(path="/wilayah/desa/{kecamatan_id}", tags={"Wilayah Management"}, summary="Daftar desa by kecamatan", @OA\Parameter(name="kecamatan_id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Data desa berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahCollectionSuccessResponse")), @OA\Response(response=404, description="Kecamatan tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     */
    public function wilayahReferenceDoc(): void
    {
    }

    /**
     * @OA\Get(path="/wilayah/detail/{desa_id}", tags={"Wilayah Management"}, summary="Detail wilayah by desa", @OA\Parameter(name="desa_id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Detail wilayah berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahDetailSuccessResponse")), @OA\Response(response=404, description="Desa tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Get(path="/wilayah/hierarchy/{desa_id}", tags={"Wilayah Management"}, summary="Hierarchy wilayah by desa", @OA\Parameter(name="desa_id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Hirarki wilayah berhasil diambil", @OA\JsonContent(ref="#/components/schemas/WilayahHierarchySuccessResponse")), @OA\Response(response=404, description="Desa tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Get(path="/wilayah/search", tags={"Wilayah Management"}, summary="Pencarian wilayah", @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")), @OA\Parameter(name="jenis", in="query", @OA\Schema(type="string", enum={"provinsi","kabupaten","kecamatan","desa"})), @OA\Response(response=200, description="Hasil pencarian wilayah", @OA\JsonContent(ref="#/components/schemas/WilayahSearchSuccessResponse")), @OA\Response(response=400, description="Parameter q wajib", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     */
    public function wilayahUtilityDoc(): void
    {
    }

    /**
     * @OA\Post(path="/wilayah", tags={"Wilayah Management"}, summary="Tambah wilayah (Admin only)", security={{"jwt":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(required={"jenis","nama"}, @OA\Property(property="jenis", type="string", enum={"provinsi","kabupaten","kecamatan","desa"}), @OA\Property(property="nama", type="string"), @OA\Property(property="id_parent", type="integer", nullable=true))), @OA\Response(response=201, description="Wilayah berhasil ditambahkan", @OA\JsonContent(ref="#/components/schemas/WilayahCrudSuccessResponse")), @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")))
     * @OA\Put(path="/wilayah/{id}", tags={"Wilayah Management"}, summary="Update wilayah (Admin only)", security={{"jwt":{}}}, @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(required={"jenis","nama"}, @OA\Property(property="jenis", type="string", enum={"provinsi","kabupaten","kecamatan","desa"}), @OA\Property(property="nama", type="string"), @OA\Property(property="id_parent", type="integer", nullable=true))), @OA\Response(response=200, description="Wilayah berhasil diperbarui", @OA\JsonContent(ref="#/components/schemas/WilayahCrudSuccessResponse")), @OA\Response(response=404, description="Wilayah tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @OA\Delete(path="/wilayah/{id}", tags={"Wilayah Management"}, summary="Hapus wilayah (Admin only)", security={{"jwt":{}}}, @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="jenis", in="query", required=true, @OA\Schema(type="string", enum={"provinsi","kabupaten","kecamatan","desa"})), @OA\Response(response=200, description="Wilayah berhasil dihapus", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")), @OA\Response(response=400, description="Gagal menghapus wilayah", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")), @OA\Response(response=404, description="Wilayah tidak ditemukan", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     */
    public function wilayahCrudDoc(): void
    {
    }
}
