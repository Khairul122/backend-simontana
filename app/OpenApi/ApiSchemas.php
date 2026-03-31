<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
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
 *     schema="MetaResponse",
 *     type="object",
 *     @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     required={"success","message"},
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Permintaan berhasil"),
 *     @OA\Property(property="data", nullable=true),
 *     @OA\Property(property="meta", ref="#/components/schemas/MetaResponse", nullable=true),
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
 *     @OA\Property(property="errors", nullable=true),
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
 *     schema="WilayahNested",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Sukamaju"),
 *     @OA\Property(property="provinsi", type="object", additionalProperties=true, nullable=true),
 *     @OA\Property(property="kabupaten", type="object", additionalProperties=true, nullable=true),
 *     @OA\Property(property="kecamatan", type="object", additionalProperties=true, nullable=true),
 *     @OA\Property(property="desa", type="object", additionalProperties=true, nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="UserLite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Admin Simonta"),
 *     @OA\Property(property="username", type="string", example="admin"),
 *     @OA\Property(property="email", type="string", nullable=true, example="admin@simonta.id"),
 *     @OA\Property(property="role", type="string", example="Admin"),
 *     @OA\Property(property="no_telepon", type="string", nullable=true),
 *     @OA\Property(property="alamat", type="string", nullable=true),
 *     @OA\Property(property="id_desa", type="integer", nullable=true),
 *     @OA\Property(property="desa", ref="#/components/schemas/WilayahNested", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="KategoriBencanaNested",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="nama_kategori", type="string", example="Banjir"),
 *     @OA\Property(property="deskripsi", type="string", nullable=true),
 *     @OA\Property(property="icon", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LaporanLite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="id_pelapor", type="integer", example=1),
 *     @OA\Property(property="id_kategori_bencana", type="integer", nullable=true, example=2),
 *     @OA\Property(property="id_desa", type="integer", nullable=true, example=31),
 *     @OA\Property(property="judul_laporan", type="string", example="Banjir di Desa Sukamaju"),
 *     @OA\Property(property="deskripsi", type="string"),
 *     @OA\Property(property="tingkat_keparahan", type="string", example="Tinggi"),
 *     @OA\Property(property="status", type="string", enum={"Draft","Menunggu Verifikasi","Diverifikasi","Diproses","Selesai","Ditolak"}, example="Diproses"),
 *     @OA\Property(property="latitude", type="number", format="double", example=-6.2),
 *     @OA\Property(property="longitude", type="number", format="double", example=106.8),
 *     @OA\Property(property="alamat_lengkap", type="string", nullable=true),
 *     @OA\Property(property="pelapor", ref="#/components/schemas/UserLite", nullable=true),
 *     @OA\Property(property="kategori", ref="#/components/schemas/KategoriBencanaNested", nullable=true),
 *     @OA\Property(property="desa", ref="#/components/schemas/WilayahNested", nullable=true),
 *     @OA\Property(property="verifikator", ref="#/components/schemas/UserLite", nullable=true),
 *     @OA\Property(property="penanggungJawab", ref="#/components/schemas/UserLite", nullable=true),
 *     @OA\Property(property="monitoring", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="tindak_lanjut", type="array", @OA\Items(type="object"), nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="MonitoringItem",
 *     type="object",
 *     @OA\Property(property="id_monitoring", type="integer", example=1),
 *     @OA\Property(property="id_laporan", type="integer", example=10),
 *     @OA\Property(property="id_operator", type="integer", example=2),
 *     @OA\Property(property="waktu_monitoring", type="string", format="date-time"),
 *     @OA\Property(property="hasil_monitoring", type="string"),
 *     @OA\Property(property="koordinat_gps", type="string", nullable=true),
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
 *     @OA\Property(property="tanggal_tanggapan", type="string", format="date-time"),
 *     @OA\Property(property="status", type="string", enum={"Menuju Lokasi","Selesai"}, example="Menuju Lokasi"),
 *     @OA\Property(property="laporan", ref="#/components/schemas/LaporanLite", nullable=true),
 *     @OA\Property(property="petugas", ref="#/components/schemas/UserLite", nullable=true),
 *     @OA\Property(property="riwayat_tindakans", type="array", @OA\Items(type="object"), nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="RiwayatTindakanItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=15),
 *     @OA\Property(property="tindaklanjut_id", type="integer", example=7),
 *     @OA\Property(property="id_petugas", type="integer", example=2),
 *     @OA\Property(property="keterangan", type="string", example="Evakuasi dilakukan"),
 *     @OA\Property(property="waktu_tindakan", type="string", format="date-time"),
 *     @OA\Property(property="tindakLanjut", ref="#/components/schemas/TindakLanjutItem", nullable=true),
 *     @OA\Property(property="petugas", ref="#/components/schemas/UserLite", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedMonitoringData",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/MonitoringItem")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedTindakLanjutData",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TindakLanjutItem")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedRiwayatTindakanData",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/RiwayatTindakanItem")
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
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RiwayatTindakanItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="MonitoringPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/PaginatedMonitoringData"),
 *             @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"),
 *             example={
 *                 "success": true,
 *                 "message": "Data monitoring berhasil diambil",
 *                 "data": {
 *                     {
 *                         "id_monitoring": 99,
 *                         "id_laporan": 10,
 *                         "id_operator": 7,
 *                         "waktu_monitoring": "2026-03-20T09:00:00+07:00",
 *                         "hasil_monitoring": "Debit air mulai turun",
 *                         "operator": {
 *                             "id": 7,
 *                             "nama": "Operator Desa"
 *                         },
 *                         "laporan": {
 *                             "id": 10,
 *                             "judul_laporan": "Banjir di Desa Sukamaju"
 *                         }
 *                     }
 *                 },
 *                 "meta": {
 *                     "pagination": {
 *                         "current_page": 1,
 *                         "last_page": 3,
 *                         "per_page": 20,
 *                         "total": 45,
 *                         "from": 1,
 *                         "to": 20
 *                     }
 *                 }
 *             }
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TindakLanjutPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/PaginatedTindakLanjutData"),
 *             @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"),
 *             example={
 *                 "success": true,
 *                 "message": "Data tindak lanjut berhasil diambil",
 *                 "data": {
 *                     {
 *                         "id_tindaklanjut": 7,
 *                         "laporan_id": 10,
 *                         "id_petugas": 4,
 *                         "status": "Menuju Lokasi",
 *                         "petugas": {
 *                             "id": 4,
 *                             "nama": "Petugas BPBD"
 *                         },
 *                         "laporan": {
 *                             "id": 10,
 *                             "judul_laporan": "Banjir di Desa Sukamaju"
 *                         }
 *                     }
 *                 },
 *                 "meta": {
 *                     "pagination": {
 *                         "current_page": 1,
 *                         "last_page": 2,
 *                         "per_page": 20,
 *                         "total": 31,
 *                         "from": 1,
 *                         "to": 20
 *                     }
 *                 }
 *             }
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RiwayatTindakanPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/PaginatedRiwayatTindakanData"),
 *             @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"),
 *             example={
 *                 "success": true,
 *                 "message": "Data riwayat tindakan berhasil diambil",
 *                 "data": {
 *                     {
 *                         "id": 15,
 *                         "tindaklanjut_id": 7,
 *                         "id_petugas": 4,
 *                         "keterangan": "Evakuasi selesai",
 *                         "petugas": {
 *                             "id": 4,
 *                             "nama": "Petugas BPBD"
 *                         },
 *                         "tindakLanjut": {
 *                             "id_tindaklanjut": 7,
 *                             "status": "Selesai"
 *                         }
 *                     }
 *                 },
 *                 "meta": {
 *                     "pagination": {
 *                         "current_page": 1,
 *                         "last_page": 5,
 *                         "per_page": 20,
 *                         "total": 96,
 *                         "from": 1,
 *                         "to": 20
 *                     }
 *                 }
 *             }
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanItem",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/LaporanLite"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="is_prioritas", type="boolean", example=true),
 *             @OA\Property(property="view_count", type="integer", example=12),
 *             @OA\Property(property="jumlah_korban", type="integer", nullable=true, example=4),
 *             @OA\Property(property="jumlah_rumah_rusak", type="integer", nullable=true, example=9),
 *             @OA\Property(property="waktu_laporan", type="string", format="date-time", example="2026-03-20T08:30:00+07:00"),
 *             @OA\Property(property="waktu_verifikasi", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="waktu_selesai", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="catatan_verifikasi", type="string", nullable=true),
 *             @OA\Property(property="foto_bukti_1_url", type="string", nullable=true, example="http://localhost:8000/storage/laporans/sample1.jpg"),
 *             @OA\Property(property="foto_bukti_2_url", type="string", nullable=true, example="http://localhost:8000/storage/laporans/sample2.jpg"),
 *             @OA\Property(property="foto_bukti_3_url", type="string", nullable=true, example="http://localhost:8000/storage/laporans/sample3.jpg"),
 *             @OA\Property(property="video_bukti_url", type="string", nullable=true, example="http://localhost:8000/storage/laporans/sample.mp4")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanPaginationMeta",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/PaginationMeta")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LaporanListSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LaporanItem")),
 *             @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"),
 *             example={
 *                 "success": true,
 *                 "message": "Data laporan berhasil diambil",
 *                 "data": {
 *                     {
 *                         "id": 10,
 *                         "id_pelapor": 5,
 *                         "judul_laporan": "Banjir di Desa Sukamaju",
 *                         "status": "Diproses",
 *                         "pelapor": {
 *                             "id": 5,
 *                             "nama": "Andi Warga"
 *                         },
 *                         "kategori": {
 *                             "id": 2,
 *                             "nama_kategori": "Banjir"
 *                         },
 *                         "desa": {
 *                             "id": 31,
 *                             "nama": "Sukamaju"
 *                         }
 *                     }
 *                 },
 *                 "meta": {
 *                     "pagination": {
 *                         "current_page": 1,
 *                         "last_page": 8,
 *                         "per_page": 15,
 *                         "total": 120,
 *                         "from": 1,
 *                         "to": 15
 *                     }
 *                 },
 *                 "request_id": "req_01HZY2P0W7D3G4"
 *             }
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
 *     schema="WargaLaporanDetailData",
 *     type="object",
 *     @OA\Property(property="detail_laporan", ref="#/components/schemas/LaporanItem"),
 *     @OA\Property(property="tindak_lanjut", type="array", @OA\Items(ref="#/components/schemas/TindakLanjutItem")),
 *     @OA\Property(property="riwayat_tindakan", type="array", @OA\Items(ref="#/components/schemas/RiwayatTindakanItem"))
 * )
 *
 * @OA\Schema(
 *     schema="WargaLaporanDetailSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/WargaLaporanDetailData"))
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
 *     @OA\Property(property="top_pengguna", type="array", @OA\Items(type="object", additionalProperties=true))
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
 *     @OA\Property(property="daftar_gempa", type="array", @OA\Items(type="object", additionalProperties=true)),
 *     @OA\Property(property="gempa_dirasakan", type="array", @OA\Items(type="object", additionalProperties=true)),
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
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(type="object", additionalProperties=true)))
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
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/UserLite"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RegisterSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/UserLite"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TokenRefreshSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *                 @OA\Property(property="token_type", type="string", example="Bearer"),
 *                 @OA\Property(property="expires_in", type="integer", example=3600)
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RoleListSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(type="string", example="Admin"))
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CheckTokenSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="user_role", type="string", example="Admin"),
 *                 @OA\Property(property="user_name", type="string", example="Admin Simonta")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserListSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserLite")), @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserDetailSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/UserLite"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserStatisticsSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="total_users", type="integer", example=120),
 *                 @OA\Property(property="by_role", type="array", @OA\Items(type="object", additionalProperties=true)),
 *                 @OA\Property(property="recent_users", type="array", @OA\Items(ref="#/components/schemas/UserLite")),
 *                 @OA\Property(property="users_this_month", type="integer", example=15)
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="KategoriBencanaItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama_kategori", type="string", example="Banjir"),
 *     @OA\Property(property="deskripsi", type="string", nullable=true),
 *     @OA\Property(property="icon", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="KategoriBencanaListSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/KategoriBencanaItem")), @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="KategoriBencanaDetailSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/KategoriBencanaItem"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahBasicItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Sukamaju"),
 *     @OA\Property(property="jenis", type="string", nullable=true, example="desa"),
 *     @OA\Property(property="id_parent", type="integer", nullable=true, example=10)
 * )
 *
 * @OA\Schema(
 *     schema="WilayahCollectionSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(type="object", additionalProperties=true)))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahPaginatedSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="array", @OA\Items(type="object", additionalProperties=true)), @OA\Property(property="meta", ref="#/components/schemas/MetaResponse"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahDetailSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", type="object", additionalProperties=true))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahHierarchySuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="desa", type="object", additionalProperties=true),
 *                 @OA\Property(property="kecamatan", type="object", additionalProperties=true),
 *                 @OA\Property(property="kabupaten", type="object", additionalProperties=true),
 *                 @OA\Property(property="provinsi", type="object", additionalProperties=true)
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahSearchSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", nullable=true))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="WilayahCrudSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *         @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/WilayahBasicItem"))
 *     }
 * )
 */
class ApiSchemas
{
}
