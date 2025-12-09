<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
class SystemController extends Controller
{
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'SIMONTA BENCANA API is running',
            'version' => '1.0.0',
            'timestamp' => now()
        ]);
    }
}