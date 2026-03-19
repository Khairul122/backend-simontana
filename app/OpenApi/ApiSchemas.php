<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     required={"success","message"},
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Permintaan berhasil"),
 *     @OA\Property(property="data", nullable=true),
 *     @OA\Property(property="request_id", type="string", nullable=true, example="req_01HZY2P0W7D3G4")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     required={"success","message","code"},
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Permintaan gagal"),
 *     @OA\Property(property="code", type="string", example="REQUEST_FAILED"),
 *     @OA\Property(property="details", nullable=true),
 *     @OA\Property(property="request_id", type="string", nullable=true, example="req_01HZY2P0W7D3G4")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ErrorResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="errors", type="object", additionalProperties=true)
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserLite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Admin Simonta"),
 *     @OA\Property(property="username", type="string", example="admin"),
 *     @OA\Property(property="email", type="string", nullable=true, example="admin@simonta.id"),
 *     @OA\Property(property="role", type="string", example="Admin")
 * )
 *
 * @OA\Schema(
 *     schema="LaporanLite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="judul_laporan", type="string", example="Banjir di Desa Sukamaju"),
 *     @OA\Property(property="status", type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Selesai","Ditolak"}, example="Diproses")
 * )
 *
 * @OA\Schema(
 *     schema="MonitoringItem",
 *     type="object",
 *     @OA\Property(property="id_monitoring", type="integer", example=1),
 *     @OA\Property(property="id_laporan", type="integer", example=10),
 *     @OA\Property(property="id_operator", type="integer", example=2),
 *     @OA\Property(property="waktu_monitoring", type="string", format="date-time", example="2026-03-19T08:00:00Z"),
 *     @OA\Property(property="hasil_monitoring", type="string", example="Kondisi terkendali"),
 *     @OA\Property(property="koordinat_gps", type="string", nullable=true, example="-6.2,106.8"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="laporan", ref="#/components/schemas/LaporanLite", nullable=true),
 *     @OA\Property(property="operator", ref="#/components/schemas/UserLite", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="TindakLanjutItem",
 *     type="object",
 *     @OA\Property(property="id_tindaklanjut", type="integer", example=7),
 *     @OA\Property(property="laporan_id", type="integer", example=10),
 *     @OA\Property(property="id_petugas", type="integer", example=2),
 *     @OA\Property(property="tanggal_tanggapan", type="string", format="date-time", example="2026-03-19T09:00:00Z"),
 *     @OA\Property(property="status", type="string", enum={"Menuju Lokasi","Selesai"}, example="Menuju Lokasi"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="laporan", ref="#/components/schemas/LaporanLite", nullable=true),
 *     @OA\Property(property="petugas", ref="#/components/schemas/UserLite", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="RiwayatTindakanItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=15),
 *     @OA\Property(property="tindaklanjut_id", type="integer", example=7),
 *     @OA\Property(property="id_petugas", type="integer", example=2),
 *     @OA\Property(property="keterangan", type="string", example="Evakuasi dilakukan"),
 *     @OA\Property(property="waktu_tindakan", type="string", format="date-time", example="2026-03-19T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="tindakLanjut", ref="#/components/schemas/TindakLanjutItem", nullable=true),
 *     @OA\Property(property="petugas", ref="#/components/schemas/UserLite", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedMonitoringData",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MonitoringItem")),
 *     @OA\Property(property="per_page", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=120),
 *     @OA\Property(property="last_page", type="integer", example=6)
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedTindakLanjutData",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TindakLanjutItem")),
 *     @OA\Property(property="per_page", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=80),
 *     @OA\Property(property="last_page", type="integer", example=4)
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedRiwayatTindakanData",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RiwayatTindakanItem")),
 *     @OA\Property(property="per_page", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=200),
 *     @OA\Property(property="last_page", type="integer", example=10)
 * )
 *
 * @OA\Schema(
 *     schema="MonitoringSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/MonitoringItem"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TindakLanjutSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/TindakLanjutItem"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RiwayatTindakanSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/RiwayatTindakanItem"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanWorkflowSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/LaporanLite"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanRiwayatSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RiwayatTindakanItem"))
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="MonitoringPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/PaginatedMonitoringData"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TindakLanjutPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/PaginatedTindakLanjutData"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RiwayatTindakanPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/PaginatedRiwayatTindakanData"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="id_pelapor", type="integer", example=1),
 *     @OA\Property(property="id_kategori_bencana", type="integer", example=2),
 *     @OA\Property(property="id_desa", type="integer", example=31),
 *     @OA\Property(property="judul_laporan", type="string", example="Banjir di Desa Sukamaju"),
 *     @OA\Property(property="deskripsi", type="string", example="Air meluap ke permukiman"),
 *     @OA\Property(property="tingkat_keparahan", type="string", enum={"Rendah","Sedang","Tinggi","Kritis"}, example="Tinggi"),
 *     @OA\Property(property="status", type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Selesai","Ditolak"}, example="Diproses"),
 *     @OA\Property(property="latitude", type="number", format="double", example=-6.2),
 *     @OA\Property(property="longitude", type="number", format="double", example=106.8),
 *     @OA\Property(property="alamat_lengkap", type="string", nullable=true),
 *     @OA\Property(property="is_prioritas", type="boolean", example=false),
 *     @OA\Property(property="view_count", type="integer", example=12),
 *     @OA\Property(property="jumlah_korban", type="integer", nullable=true),
 *     @OA\Property(property="jumlah_rumah_rusak", type="integer", nullable=true),
 *     @OA\Property(property="waktu_laporan", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="waktu_verifikasi", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="waktu_selesai", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="catatan_verifikasi", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LaporanPaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=8),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=120),
 *     @OA\Property(property="from", type="integer", nullable=true, example=1),
 *     @OA\Property(property="to", type="integer", nullable=true, example=15)
 * )
 *
 * @OA\Schema(
 *     schema="LaporanListSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LaporanItem")),
 *             @OA\Property(property="pagination", ref="#/components/schemas/LaporanPaginationMeta")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanDetailSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/LaporanItem"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanStatisticsData",
 *     type="object",
 *     @OA\Property(property="total_laporan", type="integer", example=120),
 *     @OA\Property(property="laporan_perlu_verifikasi", type="integer", example=14),
 *     @OA\Property(property="laporan_ditindak", type="integer", example=42),
 *     @OA\Property(property="laporan_selesai", type="integer", example=55),
 *     @OA\Property(property="laporan_ditolak", type="integer", example=9),
 *     @OA\Property(property="laporan_baru", type="integer", example=14),
 *     @OA\Property(property="laporan_ditangani", type="integer", example=42),
 *     @OA\Property(property="weekly_stats", type="object", additionalProperties=true),
 *     @OA\Property(property="categories_stats", type="object", additionalProperties=true),
 *     @OA\Property(property="monthly_trend", type="object", additionalProperties=true),
 *     @OA\Property(property="top_pengguna", type="array", @OA\Items(type="object"))
 * )
 *
 * @OA\Schema(
 *     schema="LaporanStatisticsSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/LaporanStatisticsData"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="BmkgIndexData",
 *     type="object",
 *     @OA\Property(property="gempa_terbaru", type="object", additionalProperties=true, nullable=true),
 *     @OA\Property(property="daftar_gempa", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="gempa_dirasakan", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="cache_status", type="object", additionalProperties=true)
 * )
 *
 * @OA\Schema(
 *     schema="BmkgObjectSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="object", additionalProperties=true))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="BmkgArraySuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(type="object")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="BmkgIndexSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/BmkgIndexData"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LoginSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="user", ref="#/components/schemas/UserLite"),
 *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *                 @OA\Property(property="token_type", type="string", example="Bearer"),
 *                 @OA\Property(property="expires_in", type="integer", example=3600)
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CurrentUserSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/UserLite")
 *         )
 *     }
 * )
 */
class ApiSchemas
{
}
