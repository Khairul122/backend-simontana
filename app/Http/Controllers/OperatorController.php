<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;

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
class OperatorController extends Controller
{
    public function getReports(Request $request)
    {
        $reports = Laporan::with(['kategori'])
            ->where('id_warga', $request->user()->id)
            ->select('id_laporan', 'id_kategori', 'lokasi', 'deskripsi', 'status_laporan', 'tanggal_lapor')
            ->orderBy('tanggal_lapor', 'desc')
            ->take(10)
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Operator village reports',
            'data' => $reports
        ]);
    }

    public function verifyReport(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Operator verify report'
        ]);
    }

    public function createMonitoring(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Operator create monitoring record'
        ]);
    }

    public function getEvacuationSites()
    {
        return response()->json([
            'success' => true,
            'message' => 'Operator evacuation sites'
        ]);
    }

    public function addEvacuationSite(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Operator add evacuation site'
        ]);
    }
}