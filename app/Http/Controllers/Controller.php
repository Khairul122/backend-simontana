<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      title="SIMONTA BENCANA API Documentation",
 *      description="Sistem Informasi Manajemen Bencana Terpadu - RESTful API untuk pengelolaan data bencana, laporan, monitoring, dan integrasi dengan BMKG.",
 *      version="1.0.0",
 *      @OA\Contact(
 *          email="support@simonta-bencana.id"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Development server"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints untuk Authentication"
 * )
 *
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API Endpoints untuk Dashboard"
 * )
 *
 * @OA\Tag(
 *     name="Admin Management",
 *     description="API Endpoints untuk Manajemen Admin"
 * )
 *
 * @OA\Tag(
 *     name="BPBD Management",
 *     description="API Endpoints untuk Manajemen Petugas BPBD"
 * )
 *
 * @OA\Tag(
 *     name="Operator Management",
 *     description="API Endpoints untuk Manajemen Operator Desa"
 * )
 *
 * @OA\Tag(
 *     name="Citizen Access",
 *     description="API Endpoints untuk Akses Warga"
 * )
 *
 * @OA\Tag(
 *     name="Disaster Reports",
 *     description="API Endpoints untuk Laporan Bencana"
 * )
 *
 * @OA\Tag(
 *     name="Follow-up Actions",
 *     description="API Endpoints untuk Tindak Lanjut"
 * )
 *
 * @OA\Tag(
 *     name="Monitoring",
 *     description="API Endpoints untuk Monitoring"
 * )
 *
 * @OA\Tag(
 *     name="Village Management",
 *     description="API Endpoints untuk Manajemen Desa"
 * )
 *
 * @OA\Tag(
 *     name="Disaster Categories",
 *     description="API Endpoints untuk Kategori Bencana"
 * )
 *
 * @OA\Tag(
 *     name="BMKG Integration",
 *     description="API Endpoints untuk Integrasi BMKG"
 * )
 *
 * @OA\Tag(
 *     name="OpenStreetMap Integration",
 *     description="API Endpoints untuk Integrasi OpenStreetMap"
 * )
 *
 * @OA\Tag(
 *     name="System",
 *     description="API Endpoints untuk System dan Testing"
 * )
 */

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
