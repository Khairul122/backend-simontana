<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;

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
class BPBDController extends Controller
{
    public function getReports()
    {
        $reports = Laporan::with(['kategori'])
            ->select('id_laporan', 'id_kategori', 'lokasi', 'deskripsi', 'status_laporan', 'tanggal_lapor')
            ->orderBy('tanggal_lapor', 'desc')
            ->take(10)
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'BPBD reports management',
            'data' => $reports
        ]);
    }

    public function getReportDetails($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'BPBD report details'
        ]);
    }

    public function createResponse(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'BPBD create response to report'
        ]);
    }

    public function updateResponse(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'BPBD update response status'
        ]);
    }

    public function getStatistics()
    {
        return response()->json([
            'success' => true,
            'message' => 'BPBD disaster statistics'
        ]);
    }

    public function sendNotifications(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'BPBD send notifications'
        ]);
    }
}