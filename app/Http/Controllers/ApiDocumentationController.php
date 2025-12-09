<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * @OA\Get(
 *      path="/api/test",
 *      tags={"System"},
 *      summary="Test API Connection",
 *      description="Endpoint untuk testing koneksi API SIMONTA BENCANA.",
 *      operationId="testApi",
 *      @OA\Response(
 *          response=200,
 *          description="API running successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="SIMONTA BENCANA API is running"),
 *              @OA\Property(property="version", type="string", example="1.0.0"),
 *              @OA\Property(property="timestamp", type="string", example="2024-12-10T10:30:00.000000Z")
 *          )
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/dashboard",
 *      tags={"Dashboard"},
 *      summary="Get User Dashboard",
 *      description="Endpoint untuk mendapatkan dashboard user berdasarkan role.",
 *      operationId="getDashboard",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Dashboard accessed successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Dashboard accessed successfully"),
 *              @OA\Property(property="data",
 *                  @OA\Property(property="user", type="object",
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="nama", type="string", example="Admin Test"),
 *                      @OA\Property(property="username", type="string", example="admintest"),
 *                      @OA\Property(property="role", type="string", example="Admin")
 *                  ),
 *                  @OA\Property(property="role", type="string", example="Admin")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/desa-list/kecamatan",
 *      tags={"Village Management"},
 *      summary="Get All Kecamatan",
 *      description="Endpoint untuk mendapatkan semua data kecamatan.",
 *      operationId="getKecamatanList",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Data kecamatan berhasil diambil",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data kecamatan retrieved successfully"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id_kecamatan", type="integer", example=1),
 *                      @OA\Property(property="nama_kecamatan", type="string", example="Contoh Kecamatan"),
 *                      @OA\Property(property="id_kabupaten", type="integer", example=1)
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

/**
 * @OA\Get(
 *      path="/api/desa-list/kabupaten",
 *      tags={"Village Management"},
 *      summary="Get All Kabupaten",
 *      description="Endpoint untuk mendapatkan semua data kabupaten.",
 *      operationId="getKabupatenList",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Data kabupaten berhasil diambil",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data kabupaten retrieved successfully"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id_kabupaten", type="integer", example=1),
 *                      @OA\Property(property="nama_kabupaten", type="string", example="Contoh Kabupaten")
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

/**
 * @OA\Get(
 *      path="/api/admin/pengguna",
 *      tags={"Admin Management"},
 *      summary="Get All Users (Admin Only)",
 *      description="Endpoint untuk mendapatkan semua data pengguna (hanya untuk Admin).",
 *      operationId="adminGetPengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Data pengguna berhasil diambil",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin pengguna management"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="nama", type="string", example="Administrator"),
 *                      @OA\Property(property="username", type="string", example="admin"),
 *                      @OA\Property(property="email", type="string", example="admin@example.com"),
 *                      @OA\Property(property="role", type="string", example="Admin"),
 *                      @OA\Property(property="no_telepon", type="string", example="08123456789"),
 *                      @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123")
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/admin/pengguna",
 *      tags={"Admin Management"},
 *      summary="Create New User (Admin Only)",
 *      description="Endpoint untuk membuat pengguna baru (hanya untuk Admin).",
 *      operationId="adminCreatePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"nama","username","password","role"},
 *              @OA\Property(property="nama", type="string", example="John Doe", description="Nama lengkap pengguna"),
 *              @OA\Property(property="username", type="string", example="johndoe", description="Username unik"),
 *              @OA\Property(property="password", type="string", format="password", example="123456", description="Password minimal 6 karakter"),
 *              @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}, example="Warga", description="Role pengguna"),
 *              @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email pengguna"),
 *              @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon"),
 *              @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123", description="Alamat lengkap")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User created successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin create pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Put(
 *      path="/api/admin/pengguna/{id}",
 *      tags={"Admin Management"},
 *      summary="Update User (Admin Only)",
 *      description="Endpoint untuk mengupdate data pengguna (hanya untuk Admin).",
 *      operationId="adminUpdatePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID pengguna",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="nama", type="string", example="John Doe Updated", description="Nama lengkap pengguna"),
 *              @OA\Property(property="email", type="string", format="email", example="john.updated@example.com", description="Email pengguna"),
 *              @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon"),
 *              @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 456", description="Alamat lengkap")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User updated successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin update pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      )
 * )
 */

/**
 * @OA\Delete(
 *      path="/api/admin/pengguna/{id}",
 *      tags={"Admin Management"},
 *      summary="Delete User (Admin Only)",
 *      description="Endpoint untuk menghapus pengguna (hanya untuk Admin).",
 *      operationId="adminDeletePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID pengguna",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User deleted successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin delete pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/admin/system-monitoring",
 *      tags={"Admin Management"},
 *      summary="System Monitoring (Admin Only)",
 *      description="Endpoint untuk monitoring sistem (hanya untuk Admin).",
 *      operationId="adminSystemMonitoring",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="System monitoring data retrieved",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin system monitoring")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bpbd/reports",
 *      tags={"BPBD Management"},
 *      summary="Get BPBD Reports",
 *      description="Endpoint untuk mendapatkan laporan-laporan yang dikelola oleh Petugas BPBD.",
 *      operationId="bpbdGetReports",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="BPBD reports retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD reports management"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id_laporan", type="integer", example=1),
 *                      @OA\Property(property="id_kategori", type="integer", example=1),
 *                      @OA\Property(property="lokasi", type="string", example="Contoh Lokasi"),
 *                      @OA\Property(property="deskripsi", type="string", example="Deskripsi laporan"),
 *                      @OA\Property(property="status_laporan", type="string", example="Baru"),
 *                      @OA\Property(property="tanggal_lapor", type="string", example="2024-12-10T10:30:00.000000Z"),
 *                      @OA\Property(property="kategori", type="object",
 *                          @OA\Property(property="id_kategori", type="integer", example=1),
 *                          @OA\Property(property="nama_kategori", type="string", example="Banjir")
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bpbd/reports/{id}",
 *      tags={"BPBD Management"},
 *      summary="Get BPBD Report Details",
 *      description="Endpoint untuk mendapatkan detail laporan tertentu (Petugas BPBD).",
 *      operationId="bpbdGetReportDetails",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID laporan",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Report details retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD report details")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Report not found"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/bpbd/reports/{id}/response",
 *      tags={"BPBD Management"},
 *      summary="Create Response to Report",
 *      description="Endpoint untuk membuat respons/tanggapan terhadap laporan (Petugas BPBD).",
 *      operationId="bpbdCreateResponse",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID laporan",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"deskripsi","status"},
 *              @OA\Property(property="deskripsi", type="string", example="Tim akan segera diturunkan ke lokasi", description="Deskripsi respons"),
 *              @OA\Property(property="status", type="string", example="Diproses", description="Status respons"),
 *              @OA\Property(property="prioritas", type="string", enum={"Rendah","Sedang","Tinggi","Darurat"}, example="Tinggi", description="Tingkat prioritas")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Response created successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD create response to report")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      )
 * )
 */

/**
 * @OA\Put(
 *      path="/api/bpbd/responses/{id}",
 *      tags={"BPBD Management"},
 *      summary="Update Response Status",
 *      description="Endpoint untuk mengupdate status respons (Petugas BPBD).",
 *      operationId="bpbdUpdateResponse",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID respons",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="Selesai", description="Status baru respons"),
 *              @OA\Property(property="catatan", type="string", example="Penanganan selesai, kondisi aman", description="Catatan tambahan")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Response updated successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD update response status")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bpbd/statistics",
 *      tags={"BPBD Management"},
 *      summary="Get BPBD Disaster Statistics",
 *      description="Endpoint untuk mendapatkan statistik bencana (Petugas BPBD).",
 *      operationId="bpbdGetStatistics",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Statistics retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD disaster statistics")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/bpbd/notifications",
 *      tags={"BPBD Management"},
 *      summary="Send BPBD Notifications",
 *      description="Endpoint untuk mengirim notifikasi ke publik (Petugas BPBD).",
 *      operationId="bpbdSendNotifications",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"judul","pesan","target"},
 *              @OA\Property(property="judul", type="string", example="Peringatan Banjir", description="Judul notifikasi"),
 *              @OA\Property(property="pesan", type="string", example="Warga diminta waspada terhadap potensi banjir", description="Isi pesan notifikasi"),
 *              @OA\Property(property="target", type="string", enum={"Semua","Warga","Petugas","Admin"}, example="Semua", description="Target penerima notifikasi"),
 *              @OA\Property(property="kategori", type="string", enum={"Info","Peringatan","Darurat"}, example="Peringatan", description="Kategori notifikasi")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Notification sent successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BPBD send notifications")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - BPBD access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/operator/reports",
 *      tags={"Operator Management"},
 *      summary="Get Operator Village Reports",
 *      description="Endpoint untuk mendapatkan laporan di wilayah desa operator.",
 *      operationId="operatorGetReports",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Operator reports retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Operator village reports"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id_laporan", type="integer", example=1),
 *                      @OA\Property(property="id_kategori", type="integer", example=1),
 *                      @OA\Property(property="lokasi", type="string", example="Contoh Lokasi"),
 *                      @OA\Property(property="deskripsi", type="string", example="Deskripsi laporan"),
 *                      @OA\Property(property="status_laporan", type="string", example="Baru"),
 *                      @OA\Property(property="tanggal_lapor", type="string", example="2024-12-10T10:30:00.000000Z"),
 *                      @OA\Property(property="kategori", type="object",
 *                          @OA\Property(property="id_kategori", type="integer", example=1),
 *                          @OA\Property(property="nama_kategori", type="string", example="Banjir")
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Operator access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/operator/reports/{id}/verify",
 *      tags={"Operator Management"},
 *      summary="Verify Report",
 *      description="Endpoint untuk verifikasi laporan dari warga (Operator Desa).",
 *      operationId="operatorVerifyReport",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID laporan",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"status","catatan"},
 *              @OA\Property(property="status", type="string", enum={"Valid","Tidak Valid","Perlu Info Tambahan"}, example="Valid", description="Status verifikasi"),
 *              @OA\Property(property="catatan", type="string", example="Laporan telah diverifikasi dan benar", description="Catatan verifikasi")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Report verified successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Operator verify report")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Operator access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/operator/reports/{id}/monitor",
 *      tags={"Operator Management"},
 *      summary="Create Monitoring Record",
 *      description="Endpoint untuk membuat catatan monitoring (Operator Desa).",
 *      operationId="operatorCreateMonitoring",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID laporan",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"status_monitoring","lokasi_monitoring"},
 *              @OA\Property(property="status_monitoring", type="string", example="Dalam Pantauan", description="Status monitoring"),
 *              @OA\Property(property="lokasi_monitoring", type="string", example="Koordinat lokasi monitoring", description="Lokasi monitoring"),
 *              @OA\Property(property="catatan_monitoring", type="string", example="Kondisi terkendali", description="Catatan monitoring"),
 *              @OA\Property(property="foto_bukti", type="string", example="path/to/foto.jpg", description="Foto bukti monitoring")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Monitoring record created successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Operator create monitoring record")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Operator access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/operator/evacuation-sites",
 *      tags={"Operator Management"},
 *      summary="Get Evacuation Sites",
 *      description="Endpoint untuk mendapatkan data lokasi evakuasi (Operator Desa).",
 *      operationId="operatorGetEvacuationSites",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Evacuation sites retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Operator evacuation sites")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Operator access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/operator/evacuation-sites",
 *      tags={"Operator Management"},
 *      summary="Add Evacuation Site",
 *      description="Endpoint untuk menambah lokasi evakuasi baru (Operator Desa).",
 *      operationId="operatorAddEvacuationSite",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"nama_lokasi","alamat","kapasitas"},
 *              @OA\Property(property="nama_lokasi", type="string", example="Pos Evakuasi Utama", description="Nama lokasi evakuasi"),
 *              @OA\Property(property="alamat", type="string", example="Jl. Evakuasi No. 123", description="Alamat lengkap"),
 *              @OA\Property(property="kapasitas", type="integer", example=500, description="Kapasitas maksimal"),
 *              @OA\Property(property="koordinat", type="string", example="-6.200000,106.816666", description="Koordinat GPS"),
 *              @OA\Property(property="fasilitas", type="string", example="Makanan, Medis, Listrik", description="Fasilitas tersedia")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Evacuation site added successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Operator add evacuation site")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Operator access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/citizen/disaster-info",
 *      tags={"Citizen Access"},
 *      summary="Get Disaster Information",
 *      description="Endpoint untuk mendapatkan informasi bencana terkini (Warga).",
 *      operationId="citizenGetDisasterInfo",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Disaster information retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Citizen disaster information")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Citizen access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/citizen/evacuation-info",
 *      tags={"Citizen Access"},
 *      summary="Get Evacuation Information",
 *      description="Endpoint untuk mendapatkan informasi evakuasi (Warga).",
 *      operationId="citizenGetEvacuationInfo",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Evacuation information retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Citizen evacuation information")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Citizen access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bmkg/dashboard",
 *      tags={"BMKG Integration"},
 *      summary="Get BMKG Dashboard Data",
 *      description="Endpoint untuk mendapatkan data lengkap dari BMKG untuk dashboard.",
 *      operationId="bmkgDashboard",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Dashboard data retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data dashboard BMKG berhasil diambil"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="cuaca", type="object",
 *                      @OA\Property(property="status", type="string", example="available"),
 *                      @OA\Property(property="suhu", type="string", example="28°C"),
 *                      @OA\Property(property="kelembaban", type="string", example="75%"),
 *                      @OA\Property(property="cuaca", type="string", example="Cerah Berawan")
 *                  ),
 *                  @OA\Property(property="gempa", type="object",
 *                      @OA\Property(property="statistik", type="object",
 *                          @OA\Property(property="total_24_jam", type="integer", example=3),
 *                          @OA\Property(property="magnitudo_max", type="string", example="5.4"),
 *                          @OA\Property(property="terbanyak", type="string", example="Laut Banda")
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

/**
 * @OA\Get(
 *      path="/api/bmkg/cuaca",
 *      tags={"BMKG Integration"},
 *      summary="Get Weather Information",
 *      description="Endpoint untuk mendapatkan informasi cuaca dari BMKG.",
 *      operationId="bmkgCuaca",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="provinsi",
 *          in="query",
 *          required=false,
 *          description="Nama provinsi",
 *          @OA\Schema(type="string", example="Jawa Barat")
 *      ),
 *      @OA\Parameter(
 *          name="kota",
 *          in="query",
 *          required=false,
 *          description="Nama kota",
 *          @OA\Schema(type="string", example="Bandung")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Weather data retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data cuaca berhasil diambil"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="temp", type="string", example="28°C"),
 *                  @OA\Property(property="humidity", type="string", example="75%"),
 *                  @OA\Property(property="weather", type="string", example="Cerah Berawan")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bmkg/cuaca/peringatan",
 *      tags={"BMKG Integration"},
 *      summary="Get Weather Warnings",
 *      description="Endpoint untuk mendapatkan peringatan cuaca dari BMKG.",
 *      operationId="bmkgPeringatanCuaca",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Weather warnings retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Peringatan cuaca berhasil diambil"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="tanggal", type="string", example="2024-12-10"),
 *                      @OA\Property(property="peringatan", type="string", example="Hujan Lebat"),
 *                      @OA\Property(property="daerah", type="string", example="Jawa Barat bagian selatan")
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

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/terbaru",
 *      tags={"BMKG Integration"},
 *      summary="Get Latest Earthquake",
 *      description="Endpoint untuk mendapatkan informasi gempa terkini dari BMKG.",
 *      operationId="bmkgGempaTerbaru",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Latest earthquake data retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data gempa terkini berhasil diambil"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="tanggal", type="string", example="10 Des 2024"),
 *                  @OA\Property(property="jam", type="string", example="14:30:25 WIB"),
 *                  @OA\Property(property="magnitudo", type="string", example="5.2"),
 *                  @OA\Property(property="kedalaman", type="string", example="10 km"),
 *                  @OA\Property(property="lokasi", type="string", example="15 km tenggara Sukabumi")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/24-jam",
 *      tags={"BMKG Integration"},
 *      summary="Get 24 Hour Earthquakes",
 *      description="Endpoint untuk mendapatkan data gempa 24 jam terakhir dari BMKG.",
 *      operationId="bmkgGempa24Jam",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="24 hour earthquake data retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data gempa 24 jam berhasil diambil"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="tanggal", type="string", example="10 Des 2024"),
 *                      @OA\Property(property="jam", type="string", example="14:30:25 WIB"),
 *                      @OA\Property(property="magnitudo", type="string", example="5.2"),
 *                      @OA\Property(property="kedalaman", type="string", example="10 km"),
 *                      @OA\Property(property="lokasi", type="string", example="15 km tenggara Sukabumi")
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

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/riwayat",
 *      tags={"BMKG Integration"},
 *      summary="Get Earthquake History",
 *      description="Endpoint untuk mendapatkan riwayat gempa dari BMKG.",
 *      operationId="bmkgRiwayatGempa",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="tahun",
 *          in="query",
 *          required=false,
 *          description="Tahun untuk filter",
 *          @OA\Schema(type="integer", example=2024)
 *      ),
 *      @OA\Parameter(
 *          name="bulan",
 *          in="query",
 *          required=false,
 *          description="Bulan untuk filter (1-12)",
 *          @OA\Schema(type="integer", example=12)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Earthquake history retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Riwayat gempa berhasil diambil"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="tanggal", type="string", example="10 Des 2024"),
 *                      @OA\Property(property="jam", type="string", example="14:30:25 WIB"),
 *                      @OA\Property(property="magnitudo", type="string", example="5.2"),
 *                      @OA\Property(property="kedalaman", type="string", example="10 km"),
 *                      @OA\Property(property="lokasi", type="string", example="15 km tenggara Sukabumi")
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

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/statistik",
 *      tags={"BMKG Integration"},
 *      summary="Get Earthquake Statistics",
 *      description="Endpoint untuk mendapatkan statistik gempa dari BMKG.",
 *      operationId="bmkgStatistikGempa",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Earthquake statistics retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Statistik gempa berhasil diambil"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="total_24_jam", type="integer", example=3),
 *                  @OA\Property(property="magnitudo_max", type="string", example="5.4"),
 *                  @OA\Property(property="terbanyak", type="string", example="Laut Banda"),
 *                  @OA\Property(property="mingguan", type="integer", example=15),
 *                  @OA\Property(property="bulanan", type="integer", example=45)
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/cek-koordinat",
 *      tags={"BMKG Integration"},
 *      summary="Check Coordinates for Earthquakes",
 *      description="Endpoint untuk mengecek gempa berdasarkan koordinat.",
 *      operationId="bmkgCekGempaKoordinat",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="lat",
 *          in="query",
 *          required=true,
 *          description="Latitude",
 *          @OA\Schema(type="number", example=-6.200000)
 *      ),
 *      @OA\Parameter(
 *          name="lon",
 *          in="query",
 *          required=true,
 *          description="Longitude",
 *          @OA\Schema(type="number", example=106.816666)
 *      ),
 *      @OA\Parameter(
 *          name="radius",
 *          in="query",
 *          required=false,
 *          description="Radius dalam km",
 *          @OA\Schema(type="integer", example=100)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Coordinate-based earthquake data retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Data gempa berdasarkan koordinat berhasil diambil"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="tanggal", type="string", example="10 Des 2024"),
 *                      @OA\Property(property="jam", type="string", example="14:30:25 WIB"),
 *                      @OA\Property(property="magnitudo", type="string", example="5.2"),
 *                      @OA\Property(property="kedalaman", type="string", example="10 km"),
 *                      @OA\Property(property="lokasi", type="string", example="15 km tenggara Sukabumi")
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

/**
 * @OA\Get(
 *      path="/api/bmkg/gempa/peringatan-tsunami",
 *      tags={"BMKG Integration"},
 *      summary="Get Tsunami Warnings",
 *      description="Endpoint untuk mendapatkan peringatan tsunami dari BMKG.",
 *      operationId="bmkgPeringatanTsunami",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Tsunami warnings retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Peringatan tsunami berhasil diambil"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="tanggal", type="string", example="2024-12-10"),
 *                      @OA\Property(property="status", type="string", example="Aktif"),
 *                      @OA\Property(property="daerah", type="string", example="Pantai Selatan Jawa"),
 *                      @OA\Property(property="kedalaman", type="string", example="15 km"),
 *                      @OA\Property(property="magnitudo", type="string", example="7.2")
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

/**
 * @OA\Delete(
 *      path="/api/bmkg/admin/cache",
 *      tags={"BMKG Integration"},
 *      summary="Clear BMKG Cache (Admin Only)",
 *      description="Endpoint untuk membersihkan cache data BMKG (hanya Admin).",
 *      operationId="bmkgClearCache",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Cache cleared successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BMKG cache cleared successfully")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/bmkg/admin/status",
 *      tags={"BMKG Integration"},
 *      summary="Get BMKG API Status (Admin Only)",
 *      description="Endpoint untuk mendapatkan status API BMKG (hanya Admin).",
 *      operationId="bmkgStatus",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="API status retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="BMKG API status"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="status", type="string", example="Online"),
 *                  @OA\Property(property="last_update", type="string", example="2024-12-10T14:30:00Z"),
 *                  @OA\Property(property="cache_status", type="string", example="Active")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/osm/status",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Get OSM API Status",
 *      description="Endpoint untuk mendapatkan status API OpenStreetMap.",
 *      operationId="osmStatus",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="OSM API status retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="OSM API is available")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/osm/geocode",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Geocode Address",
 *      description="Endpoint untuk mengkonversi alamat menjadi koordinat.",
 *      operationId="osmGeocode",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"alamat"},
 *              @OA\Property(property="alamat", type="string", example="Jalan Sudirman No. 123, Jakarta", description="Alamat yang akan di-geocode")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Geocoding successful",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Geocoding successful"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="lat", type="number", example=-6.200000),
 *                  @OA\Property(property="lon", type="number", example=106.816666),
 *                  @OA\Property(property="display_name", type="string", example="Jalan Sudirman, Jakarta, Indonesia")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/osm/reverse-geocode",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Reverse Geocode Coordinates",
 *      description="Endpoint untuk mengkonversi koordinat menjadi alamat.",
 *      operationId="osmReverseGeocode",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"lat","lon"},
 *              @OA\Property(property="lat", type="number", example=-6.200000, description="Latitude"),
 *              @OA\Property(property="lon", type="number", example=106.816666, description="Longitude")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Reverse geocoding successful",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Reverse geocoding successful"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="display_name", type="string", example="Jalan Sudirman, Jakarta, Indonesia"),
 *                  @OA\Property(property="address", type="object",
 *                      @OA\Property(property="road", type="string", example="Jalan Sudirman"),
 *                      @OA\Property(property="city", type="string", example="Jakarta"),
 *                      @OA\Property(property="country", type="string", example="Indonesia")
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

/**
 * @OA\Get(
 *      path="/api/osm/disaster-locations",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Search Disaster Locations",
 *      description="Endpoint untuk mencari lokasi bencana.",
 *      operationId="osmSearchDisasterLocations",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="query",
 *          in="query",
 *          required=true,
 *          description="Query pencarian lokasi bencana",
 *          @OA\Schema(type="string", example="banjir jakarta")
 *      ),
 *      @OA\Parameter(
 *          name="limit",
 *          in="query",
 *          required=false,
 *          description="Batasi hasil pencarian",
 *          @OA\Schema(type="integer", example=10)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Disaster locations found",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Disaster locations retrieved"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="name", type="string", example="Banjir Jakarta Utara"),
 *                      @OA\Property(property="lat", type="number", example=-6.200000),
 *                      @OA\Property(property="lon", type="number", example=106.816666),
 *                      @OA\Property(property="jenis_bencana", type="string", example="Banjir")
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

/**
 * @OA\Get(
 *      path="/api/osm/nearby-hospitals",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Get Nearby Hospitals",
 *      description="Endpoint untuk mencari rumah sakit terdekat.",
 *      operationId="osmNearbyHospitals",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="lat",
 *          in="query",
 *          required=true,
 *          description="Latitude lokasi",
 *          @OA\Schema(type="number", example=-6.200000)
 *      ),
 *      @OA\Parameter(
 *          name="lon",
 *          in="query",
 *          required=true,
 *          description="Longitude lokasi",
 *          @OA\Schema(type="number", example=106.816666)
 *      ),
 *      @OA\Parameter(
 *          name="radius",
 *          in="query",
 *          required=false,
 *          description="Radius pencarian dalam km",
 *          @OA\Schema(type="integer", example=5)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Nearby hospitals found",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Nearby hospitals retrieved"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="name", type="string", example="Rumah Sakit Jakarta"),
 *                      @OA\Property(property="lat", type="number", example=-6.200000),
 *                      @OA\Property(property="lon", type="number", example=106.816666),
 *                      @OA\Property(property="distance", type="number", example=2.5)
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

/**
 * @OA\Get(
 *      path="/api/osm/evacuation-centers",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Get Evacuation Centers",
 *      description="Endpoint untuk mencari pusat evakuasi.",
 *      operationId="osmEvacuationCenters",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="lat",
 *          in="query",
 *          required=false,
 *          description="Latitude lokasi",
 *          @OA\Schema(type="number", example=-6.200000)
 *      ),
 *      @OA\Parameter(
 *          name="lon",
 *          in="query",
 *          required=false,
 *          description="Longitude lokasi",
 *          @OA\Schema(type="number", example=106.816666)
 *      ),
 *      @OA\Parameter(
 *          name="radius",
 *          in="query",
 *          required=false,
 *          description="Radius pencarian dalam km",
 *          @OA\Schema(type="integer", example=10)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Evacuation centers found",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Evacuation centers retrieved"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="name", type="string", example="Pos Evakuasi Utama"),
 *                      @OA\Property(property="lat", type="number", example=-6.200000),
 *                      @OA\Property(property="lon", type="number", example=106.816666),
 *                      @OA\Property(property="capacity", type="integer", example=500),
 *                      @OA\Property(property="distance", type="number", example=3.2)
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

/**
 * @OA\Get(
 *      path="/api/osm/disaster-map",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Get Disaster Map Data",
 *      description="Endpoint untuk mendapatkan data peta bencana.",
 *      operationId="osmDisasterMap",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="jenis_bencana",
 *          in="query",
 *          required=false,
 *          description="Filter jenis bencana",
 *          @OA\Schema(type="string", example="banjir")
 *      ),
 *      @OA\Parameter(
 *          name="start_date",
 *          in="query",
 *          required=false,
 *          description="Tanggal awal filter (YYYY-MM-DD)",
 *          @OA\Schema(type="string", example="2024-12-01")
 *      ),
 *      @OA\Parameter(
 *          name="end_date",
 *          in="query",
 *          required=false,
 *          description="Tanggal akhir filter (YYYY-MM-DD)",
 *          @OA\Schema(type="string", example="2024-12-10")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Disaster map data retrieved",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Disaster map data retrieved"),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="locations", type="array",
 *                      @OA\Items(type="object",
 *                          @OA\Property(property="id", type="integer", example=1),
 *                          @OA\Property(property="lat", type="number", example=-6.200000),
 *                          @OA\Property(property="lon", type="number", example=106.816666),
 *                          @OA\Property(property="jenis_bencana", type="string", example="Banjir"),
 *                          @OA\Property(property="status", type="string", example="Aktif")
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

/**
 * @OA\Delete(
 *      path="/api/osm/admin/cache",
 *      tags={"OpenStreetMap Integration"},
 *      summary="Clear OSM Cache (Admin Only)",
 *      description="Endpoint untuk membersihkan cache data OpenStreetMap (hanya Admin).",
 *      operationId="osmClearCache",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Cache cleared successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="OSM cache cleared successfully")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

class ApiDocumentationController extends Controller
{
    // Controller ini hanya untuk dokumentasi API yang didefinisikan di routes/api.php
    // Semua logika bisnis ada di controller masing-masing
}